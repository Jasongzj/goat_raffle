<?php

namespace App\Http\Controllers;

use App\Http\Requests\AwardPicture;
use App\Http\Requests\ContextPicture;
use App\Http\Requests\RaffleStoreRequest;
use App\Http\Requests\SubscriptionPicture;
use App\Http\Resources\RaffleResource;
use App\Models\Raffle;
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
                'id', 'name', 'draw_time', 'img'
            ])
            ->orderByDesc('draw_time')
            ->offset(3)
            ->paginate();
        return RaffleResource::collection($list);
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
            ->orderByDesc('sort')
            ->orderByDesc('draw_time')
            ->limit(3)
            ->get();
        return RaffleResource::collection($list);
    }

    /**
     * 抽奖详情
     * @param Raffle $raffle
     */
    public function show(Raffle $raffle)
    {
        $raffle->load('userContact');
        if ($raffle->current_participants) {
            $raffle->load(['participants', function($query) {

            }]);
        }
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
            'context', 'award_type', 'contact_id',
        ]);
        $awards = $request->input('awards');

        DB::transaction(function () use ($attributes, $awards) {
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
        });

        return $this->message('发起抽奖成功');
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
        return RaffleResource::collection($list);
    }
}
