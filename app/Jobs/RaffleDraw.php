<?php

namespace App\Jobs;

use App\Models\Raffle;
use App\Models\RaffleWhitelist;
use App\Models\User;
use App\Models\UserStat;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

class RaffleDraw implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Raffle
     */
    protected $raffle;

    /**
     * @var
     */
    protected $award;

    public function __construct()
    {
        //
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function handle()
    {
        // 获取当前时间里需要开奖的抽奖列表
        $drawTime = Carbon::now()->startOfMinute();
        $raffleList = Raffle::query()->where('status', Raffle::STATUS_NOT_END)
            ->where('draw_time', $drawTime)
            ->get();

        foreach ($raffleList as $raffle) {
            $this->raffle = $raffle;

            if ($this->raffle->current_participants == 0) {
                $this->handleNoParticipant();
                continue;
            }
            // 进行抽奖
            $awards = $this->raffle->awards;
            $userIds = $this->raffle->participants->pluck('user_id')->all();

            // 获取抽奖白名单
            $whitelist = RaffleWhitelist::query()
                ->where('raffle_id', $raffle->id)
                ->get()
                ->groupBy('award_id')
                ->toArray();

            foreach ($awards as $award) {
                $this->award = $award;
                $winners = [];
                $amount = (count($userIds) > $this->award->amount) ? $this->award->amount : count($userIds);

                $awardWhitelist = $whitelist[$this->award->id] ?? [];
                if ($awardWhitelist) {
                    $whitelistUserIds = array_column($awardWhitelist,'user_id');
                    $userIds = array_diff($userIds, $whitelistUserIds);  // 先剔除参与名单中白名单的用户

                    $whitelistAmount = (count($whitelistUserIds) > $amount) ? $amount : count($whitelistUserIds);
                    $this->drawWinners($whitelistAmount, $whitelistUserIds, $winners);

                    // 剩余未中奖的白名单用户为普通用户
                    $userIds = array_merge($userIds, array_values($whitelistUserIds));
                    // 奖项有剩余则继续抽取普通用户
                    if ($amount > $whitelistAmount) {
                        $restAmount = $amount - $whitelistAmount;
                        $this->drawWinners($restAmount,$userIds, $winners);
                        // 恢复索引数组
                        $userIds = array_values($userIds);
                    }
                } else {
                    // 普通开奖
                    $this->drawWinners($amount, $userIds, $winners);
                    // 恢复索引数组
                    $userIds = array_values($userIds);
                }

                // 修改开奖状态
                $this->raffle->status = Raffle::STATUS_ENDED;
                $this->raffle->save();
                // 记录中奖者
                $this->raffle->winners()->createMany($winners);
                // 中奖者的中奖记录统计 + 1
                $winnerUserIds = array_column($winners, 'user_id');
                UserStat::query()
                    ->whereIn('user_id', $winnerUserIds)
                    ->increment('award_amount');
            }
            // 通知发起者活动开奖
            $this->notifyLauncher();

            // 通知参与者活动开奖
            $this->notifyParticipants();
        }
    }

    /**
     * 处理无人参与的抽奖场景
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function handleNoParticipant()
    {
        $this->raffle->status = Raffle::STATUS_ENDED;
        $this->raffle->save();

        $notification = '你发起的抽奖未开奖，因参与人数为0';
        $this->handleNotification($this->raffle->launcher, $this->raffle->id, $notification);
    }

    /**
     * 通知发起者抽奖结果
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    protected function notifyLauncher()
    {
        $notification = '你发起的抽奖正在开奖，点击查看中奖名单';
        $this->handleNotification($this->raffle->launcher, $this->raffle->id, $notification);
    }


    /**
     * 发送模版消息通知参与者正在开奖
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    protected function notifyParticipants()
    {
        foreach ($this->raffle->participants as $participant) {
            $notification = $this->raffle->launcher->nick_name . ' 发起的活动正在开奖，快来看看你中奖了没有';
            $this->handleNotification($participant->user, $this->raffle->id, $notification);
        }
    }

    /**
     * 获取用户的formid
     * @param $userId
     * @return mixed
     */
    protected function getFormId($userId)
    {
        // 返回的是个 array
        // Redis 按过期时间排序，取最临近过期的值
        if (app()->environment('production')) {
            $formId = Redis::zrange('form_id_of_'.$userId, 0, 1);
        } else {
            $formId = Redis::zrange('test_form_id_of_'.$userId, 0, 1);
        }

        if ($formId) {
            return $formId[0];
        }
        return '';
    }

    /**
     * 随机抽取中奖者
     * @param int $amount
     * @param array $userIds
     * @param array $winners
     * @return void
     */
    protected function drawWinners(int $amount, array &$userIds, array &$winners)
    {
        if (!$userIds || !$amount) {
            return;
        }
        $awardUserKeys = array_rand($userIds, $amount);

        if ($amount == 1) {
            $winners[] = [
                'raffle_id' => $this->raffle->id,
                'award_id' => $this->award->id,
                'user_id' => $userIds[$awardUserKeys],
            ];
            unset($userIds[$awardUserKeys]);
        } else {
            foreach ($awardUserKeys as $key) {
                $winners[] = [
                    'raffle_id' => $this->raffle->id,
                    'award_id' => $this->award->id,
                    'user_id' => $userIds[$key],
                ];
                // 剔除已中奖用户
                unset($userIds[$key]);
            }
        }
    }

    /**
     * 处理模版消息消息通知
     * @param $participant
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    protected function handleNotification(User $user, $raffleId, $notification)
    {
        for ($i = 0; $i < 5; $i++) {
            $formId = $this->getFormId($user->id);
            logger('用户' . $user->id . '本次form_id：' . $formId);
            if ($formId) {
                $result = $this->raffle->sendWechatMessage($user->openid, $formId, $raffleId, $notification);
                // 删除使用的formId
                if (app()->environment('production')) {
                    Redis::zrem('form_id_of_' . $user->id, $formId);
                } else {
                    Redis::zrem('test_form_id_of_' . $user->id, $formId);
                }

                if ($result) {
                    break;
                }
            }
        }
    }
}
