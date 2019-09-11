<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\JsonResponse;
use App\Http\Requests\AwardPicture;
use App\Http\Requests\ContextPicture;
use App\Http\Requests\RaffleStoreRequest;
use App\Http\Requests\SubscriptionPicture;
use App\Http\Resources\RaffleResource;
use App\Models\Raffle;
use App\Models\RaffleAward;
use App\Models\RaffleWinner;
use App\Models\UserRaffle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaffleController extends Controller
{
    /**
     * 首页抽奖列表
     */
    public function index()
    {
        // 未开奖，按开奖截止时间排序
        $list = Raffle::query()
            ->where('status', Raffle::STATUS_NOT_END)
            ->select([
                'id', 'name', 'draw_time', 'img',
            ])
            ->where('sort', 0)
            ->orderByDesc('draw_time')
            ->paginate();
        return RaffleResource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }

    /**
     * 首页置顶抽奖
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function top()
    {
        $list = Raffle::query()
            ->where('status', Raffle::STATUS_NOT_END)
            ->select([
                'id', 'name', 'draw_time', 'img'
            ])
            ->where('sort', '>', 0)
            ->orderByDesc('sort')
            ->orderByDesc('draw_time')
            ->get();

        return RaffleResource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }

    /**
     * 抽奖详情
     * @param Raffle $raffle
     * @return mixed
     */
    public function show(Raffle $raffle)
    {
        $raffle->load([
            'userContact:id,user_id,type,subs_type,title,content',
            'awards:id,raffle_id,name,img,amount'
        ]);
        // 获取参与人员列表
        $participants = [];
        if ($raffle->current_participants) {
            $participants = UserRaffle::query()->where('user_raffle.raffle_id', $raffle->id)
                ->join('users', 'user_raffle.user_id', '=', 'users.id')
                ->select(['users.id', 'users.avatar_url'])
                ->orderByDesc('user_raffle.id')
                ->limit(10)
                ->get();
        }
        $raffle->participants_list = $participants;
        // 获取中奖名单
        $winners = [];
        if ($raffle->status == Raffle::STATUS_ENDED) {
            $awardIds = $raffle->awards->pluck('id')->all();
            $winnerList = RaffleWinner::query()
                ->whereIn('award_id', $awardIds)
                ->with('users:id,avatar_url,nick_name')
                ->select(['raffle_winners.award_id', 'raffle_winners.user_id'])
                ->orderBy('award_id')
                ->get();
            $winners = [];
            foreach ($winnerList as $winner) {
                foreach ($raffle->awards as $award) {
                    if ($winner->award_id == $award->id) {
                        $winners[] = [
                            'award_name' => $award->name,
                            'award_amount' => $award->amount,
                            'users' => $winner->users,
                        ];
                    }
                }
            }
            $raffle->winner_list = $winners;
        }
        $raffle->winner_list = $winners;
        return $this->success($raffle);
    }

    /**
     * 发起抽奖
     * @param RaffleStoreRequest $request
     * @return mixed
     */
    public function store(RaffleStoreRequest $request)
    {
        $attributes = $request->only([
            'draw_type', 'draw_time', 'draw_participants', 'desc',
            'context', 'context_img', 'award_type', 'contact_id',
        ]);
        $awards = $request->input('awards');

        $raffle = DB::transaction(function () use ($attributes, $awards) {
            // 根据奖项生成抽奖标题
            $attributes['name'] = '奖品：';
            foreach ($awards as $award) {
                $attributes['name'] .= $award['name'] . ' x ' . $award['amount'];
                if (empty($attributes['img']) && !empty($award['img'])) {
                    $attributes['img'] = $award['img'];
                }
            }

            $user = Auth::guard('api')->user();
            $raffle = new Raffle($attributes);
            $user->raffle()->save($raffle);
            // 保存奖项
            $raffle->awards()->createMany($awards);

            // 用户发起抽奖记录+1
            $user->stat()->increment('launched_raffle_amount', 1);

            return $raffle;
        });

        return $this->success(['id' => $raffle->id]);
    }

    /**
     * 上传奖品图
     * @param AwardPicture $request
     * @return mixed
     */
    public function uploadAwardPic(AwardPicture $request)
    {
        $url = $this->uploadRequestImg('awards', $request->file('img'));

        return $this->success($url);
    }

    /**
     * 上传图文图片
     * @param ContextPicture $request
     * @return mixed
     */
    public function uploadContext(ContextPicture $request)
    {
        $url = $this->uploadRequestImg('contexts', $request->file('img'));

        return $this->success($url);
    }

    /**
     * 上传关注二维码
     * @param SubscriptionPicture $request
     * @return mixed
     */
    public function uploadSubscription(SubscriptionPicture $request)
    {
        $url = $this->uploadRequestImg('subscriptions', $request->file('img'));

        return $this->success($url);
    }

    /**
     * 我发起的抽奖
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function launchedRaffle()
    {
        $user = Auth::guard('api')->user();
        $list = Raffle::query()
            ->where('user_id', $user->id)
            ->select([
                'id', 'name', 'draw_time', 'img', 'status'
            ])
            ->orderByDesc('id')
            ->get();
        return RaffleResource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }
}
