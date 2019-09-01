<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserRaffleResource;
use App\Models\Raffle;
use App\Models\UserRaffle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRaffleController extends Controller
{
    /**
     * 我参与的抽奖
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function participatedRaffle()
    {
        $user = Auth::guard('api')->user();
        $list = UserRaffle::query()->where('user_raffle.user_id', $user->id)
            ->join('raffle', 'user_raffle.raffle_id', '=', 'raffle.id')
            ->select(['raffle.id', 'raffle.name', 'raffle.draw_time', 'raffle.img'])
            ->orderBy('user_raffle.id')
            ->paginate(5);
        return UserRaffleResource::collection($list);
    }

    /**
     * 参与抽奖
     * @param Raffle $raffle
     * @return mixed
     */
    public function store(Raffle $raffle)
    {
        if ($raffle->status == Raffle::STATUS_ENDED) {
            return $this->failed('活动已开奖，无法参与', 400);
        }
        $user = Auth::guard('api')->user();

        $exist = UserRaffle::query()->where('user_id', $user->id)
            ->where('raffle_id', $raffle->id)
            ->exists();

        if ($exist) {
            return $this->failed('你已参与该抽奖，请勿重复参加', 400);
        }

        DB::transaction(function () use ($raffle, $user) {

            $userRaffle = new UserRaffle([
                'user_id' => $user->id,
                'raffle_id' => $raffle->id,
            ]);
            $userRaffle->save();

            // 当前参与人数+1
            $raffle->current_participants += 1;
            $raffle->save();

            // 我参与的抽奖记录+1
            $user->stat()->increment('participated_raffle_amount', 1);
        });

        return $this->message('参与成功');
    }
}
