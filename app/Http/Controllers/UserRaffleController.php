<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\JsonResponse;
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

        $raffle = DB::transaction(function () use ($raffle, $user) {

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

            return $raffle;
        });

        $participants_list = UserRaffle::query()->where('user_raffle.raffle_id', $raffle->id)
            ->join('users', 'user_raffle.user_id', '=', 'users.id')
            ->select(['users.id', 'users.avatar_url'])
            ->orderByDesc('user_raffle.id')
            ->limit(10)
            ->get();

        $data = [
            'current_participants' => $raffle->current_participants,
            'participants_list' => $participants_list,
        ];

        return $this->success($data);
    }

    // TODO 参与抽奖用户明细
}
