<?php

namespace App\Http\Controllers;

use App\Models\RaffleWinner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;

class RaffleWinnersController extends Controller
{
    /**
     * 我的中奖记录
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function myAwards(Request $request)
    {
        $user = Auth::guard('api')->user();
        $list = RaffleWinner::query()->where('raffle_winners.user_id', $user->id)
            ->join('raffle', 'raffle_winners.raffle_id', '=', 'raffle.id')
            ->select(['raffle.id', 'raffle.name', 'raffle.draw_time', 'raffle.img'])
            ->orderByDesc('raffle.draw_time')
            ->paginate(5);
        return Resource::collection($list)->additional($list);
    }
}
