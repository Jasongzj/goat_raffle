<?php

namespace App\Http\Controllers;

use App\Models\Raffle;
use App\Models\UserRaffle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRaffleController extends Controller
{
    public function participatedRaffle()
    {
        $user = Auth::guard('api')->user();
    }

    public function store(Raffle $raffle)
    {
        if (Carbon::now() > $raffle->draw_time) {
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

            $raffle->current_participants += 1;
            $raffle->save();
        });

        return $this->message('参与成功');
    }
}
