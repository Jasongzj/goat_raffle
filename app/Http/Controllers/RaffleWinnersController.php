<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\JsonResponse;
use App\Models\Raffle;
use App\Models\RaffleWinner;
use App\Services\WechatService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class RaffleWinnersController extends Controller
{
    /**
     * 填写中奖收货地址
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\WechatException
     */
    public function fillInAddress(Request $request)
    {
        $attributes = $request->only(['address', 'message']);
        $this->contentCheck(join(',', $attributes));

        $user = Auth::guard('api')->user();
        $winner = RaffleWinner::query()
            ->where('raffle_id', $request->input('rid'))
            ->where('user_id', $user->id)
            ->first();
        if (!$winner) {
            return $this->failed('查无你的中奖记录', 400);
        }

        $winner->update($attributes);

        return $this->message('登记成功');
    }

    /**
     * 中奖用户地址详情
     * @param Request $request
     * @return mixed
     */
    public function infos(Request $request)
    {
        $user = Auth::guard('api')->user();
        $raffle = Raffle::query()->where('id', $request->input('rid'))
            ->where('user_id', $user->id)
            ->first();
        if (!$raffle) {
            return $this->failed('这不是你发起的抽奖', 400);
        }

        if ($raffle->status !== Raffle::STATUS_ENDED) {
            return $this->failed('该抽奖未结束', 400);
        }
        $winners = RaffleWinner::query()
            ->with([
                'users:id,avatar_url,nick_name',
                'award:id,name'
            ])
            ->where('raffle_id', $request->input('rid'))
            ->orderBy('award_id')
            ->get();
        $data = [];
        foreach ($winners as $winner) {
            if ($winner->address) {
                $data['completed'][] = $winner;
            } else {
                $data['uncompleted'][] = $winner;
            }
        }
        return $this->success($data);
    }
}
