<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\JsonResponse;
use App\Http\Requests\AwardPicture;
use App\Http\Requests\ContextPicture;
use App\Http\Requests\RaffleStoreRequest;
use App\Http\Requests\RaffleUpdateRequest;
use App\Http\Requests\SubscriptionPicture;
use App\Http\Resources\RaffleResource;
use App\Models\Raffle;
use App\Services\RaffleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaffleController extends Controller
{
    /**
     * 首页抽奖列表
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // 获取开奖时间过期不超过3天及未开奖的抽奖，随机展示50条
        $page = $request->input('page') ?? 1;
        $resource = Raffle::getIndexResource();
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $list = new LengthAwarePaginator(
            $resource->slice($offset, $perPage), count($resource), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Resource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }

    /**
     * 首页置顶抽奖
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function top()
    {
        $list = Raffle::query()
            ->with(['awards:id,raffle_id,name,img,amount',])
            ->where('status', Raffle::STATUS_NOT_END)
            ->select([
                'id', 'name', 'draw_time', 'img'
            ])
            ->where('sort', '>', 0)
            ->limit(5)
            ->orderByDesc('sort')
            ->orderBy('draw_time')
            ->get();

        return Resource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }

    /**
     * 抽奖详情
     * @param Raffle $raffle
     * @param RaffleService $raffleService
     * @return mixed
     */
    public function show(Raffle $raffle, RaffleService $raffleService)
    {
        $raffle->load([
            'launcher:id,nick_name,avatar_url',
            'userContact:id,user_id,type,subs_type,title,content',
            'awards:id,raffle_id,name,img,amount',
        ]);

        $raffle = $raffleService->show($raffle);

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
            'is_sharable'
        ]);
        $awards = $request->input('awards');

        $raffle = DB::transaction(function () use ($attributes, $awards) {
            // 根据奖项生成抽奖标题
            $attributes['name'] = '';
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
     * 编辑抽奖内容
     * @param RaffleUpdateRequest $request
     * @param Raffle $raffle
     * @return mixed
     */
    public function update(RaffleUpdateRequest $request, Raffle $raffle)
    {
        if ($raffle->current_participants > 0) {
            return $this->failed('已有用户参与，无法修改', 400);
        }

        $user = Auth::guard('api')->user();
        if ($raffle->user_id != $user->id) {
            return $this->failed('这不是你发起的抽奖！', 400);
        }

        $attributes = $request->only([
            'draw_type', 'draw_time', 'draw_participants', 'desc',
            'context', 'context_img', 'award_type', 'contact_id',
            'is_sharable'
        ]);
        $awards = $request->input('awards');

        $raffle = DB::transaction(function () use ($attributes, $awards, $raffle) {
            // 根据奖项生成抽奖标题
            foreach ($awards as $award) {
                $attributes['name'] .= $award['name'] . ' x ' . $award['amount'];
                if (empty($attributes['img']) && !empty($award['img'])) {
                    $attributes['img'] = $award['img'];
                }
            }

            $raffle->update($attributes);
            // 更新奖项
            $raffle->awards()->delete();
            $raffle->awards()->createMany($awards);

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
            ->with([
                'awards:id,raffle_id,name,img,amount',
                'launcher:id,nick_name,avatar_url',
            ])
            ->where('user_id', $user->id)
            ->select([
                'id', 'name', 'draw_time', 'img', 'status', 'user_id'
            ])
            ->orderByDesc('id')
            ->get();
        return Resource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }

    /**
     * 我参与的抽奖
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function participatedRaffle()
    {
        $user = Auth::guard('api')->user();
        $list = Raffle::query()
            ->with([
                'awards:id,raffle_id,name,img,amount',
                'launcher:id,nick_name,avatar_url',
            ])
            ->where('user_raffle.user_id', $user->id)
            ->join('user_raffle', 'user_raffle.raffle_id', '=', 'raffle.id')
            ->select(['raffle.id', 'raffle.name', 'raffle.draw_time', 'raffle.img', 'raffle.user_id'])
            ->orderBy('user_raffle.id')
            ->paginate(5);
        return Resource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }

    /**
     * 我的中奖记录
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function myAwards(Request $request)
    {
        $user = Auth::guard('api')->user();
        $list = Raffle::query()
            ->with([
                'awards:id,raffle_id,name,img,amount',
                'launcher:id,nick_name,avatar_url',
            ])
            ->where('raffle_winners.user_id', $user->id)
            ->join('raffle_winners', 'raffle.id', '=', 'raffle_winners.raffle_id')
            ->select(['raffle.id', 'raffle.name', 'raffle.draw_time', 'raffle.img', 'raffle.user_id'])
            ->orderByDesc('raffle.draw_time')
            ->paginate(5);

        return Resource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }
}
