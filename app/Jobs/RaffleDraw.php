<?php

namespace App\Jobs;

use App\Models\Raffle;
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
     * Create a new job instance.
     *
     * @return void
     */
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
            if ($raffle->current_participants == 0) {
                $this->handleNoParticipant($raffle);
                continue;
            }
            // 进行抽奖
            $awards = $raffle->awards;
            $userIds = $raffle->participants->pluck('user_id')->all();
            foreach ($awards as $award) {
                $winners = [];
                $amount = (count($userIds) > $award->amount) ? $award->amount : count($userIds);
                $awardUserKeys = array_rand($userIds, $amount);

                if ($amount == 1) {
                    $winners[] = [
                        'raffle_id' => $raffle->id,
                        'award_id' => $award->id,
                        'user_id' => $userIds[$awardUserKeys],
                    ];
                    unset($userIds[$awardUserKeys]);
                } else {
                    foreach ($awardUserKeys as $key) {
                        $winners[] = [
                            'raffle_id' => $raffle->id,
                            'award_id' => $award->id,
                            'user_id' => $userIds[$key],
                        ];
                        // 剔除已中奖用户
                        unset($userIds[$key]);
                    }
                }

                // 修改开奖状态
                $raffle->status = Raffle::STATUS_ENDED;
                $raffle->save();
                // 记录中奖者
                $raffle->winners()->createMany($winners);
                // 中奖者的中奖记录统计 + 1
                $winnerUserIds = array_column($winners, 'user_id');
                UserStat::query()
                    ->whereIn('user_id', $winnerUserIds)
                    ->increment('award_amount');

                // 恢复索引数组
                $userIds = array_values($userIds);
            }

            // 通知参与者活动开奖
            $this->notifyParticipants($raffle);
        }
    }

    /**
     * 处理无人参与的抽奖场景
     * @param Raffle $raffle
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function handleNoParticipant(Raffle $raffle)
    {
        $raffle->status = Raffle::STATUS_ENDED;
        $raffle->save();

        // Redis 按过期时间排序，取最临近过期的值
        for ($i = 0; $i < 10; $i++) {
            $formId = $this->getFormId($raffle->user_id);
            logger('用户'.$raffle->user_id . '本次form_id：' . $formId);
            if ($formId) {
                // 通知发起者活动未开奖
                $notification = '你发起的抽奖未开奖，因参与人数为0';
                $result = $raffle->sendWechatMessage($raffle->launcher->openid, $formId, $notification);
                // 删除使用的formId
                Redis::zrem('form_id_of_'.$raffle->user_id, $formId);

                // 发送成功则不继续发送
                if ($result) {
                    break;
                }
            }
        }
    }

    /**
     * 发送模版消息通知参与者正在开奖
     * @param Raffle $raffle
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    protected function notifyParticipants(Raffle $raffle)
    {
        foreach ($raffle->participants as $participant) {
            // Redis 按过期时间排序，取最临近过期的值
            for ($i = 0; $i < 10; $i++) {
                $formId = $this->getFormId($participant->user_id);
                logger('用户'. $participant->user_id . '本次form_id：' . $formId);
                if ($formId) {
                    $notification = $raffle->launcher->nick_name . ' 发起的活动正在开奖，快来看看你中奖了没有';
                    $result = $raffle->sendWechatMessage($participant->user->openid, $formId, $notification);
                    // 删除使用的formId
                    Redis::zrem('form_id_of_'.$participant->id, $formId);

                    if ($result) {
                        break;
                    }
                }
            }

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
        $formId = Redis::zrange('form_id_of_'.$userId, 0, 1);
        if ($formId) {
            return $formId[0];
        }
        return '';
    }
}
