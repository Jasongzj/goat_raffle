<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Traits\JsonResponse;
use App\Models\Raffle;
use App\Models\RaffleWhitelist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\Resource;

class RaffleController extends Controller
{
    public function index(Request $request)
    {
        $query = Raffle::query();

        $list = $query->with([
            'launcher:id,nick_name',
            'awards:id,raffle_id,name,img,amount',
        ])
            ->select(['id', 'user_id', 'name', 'draw_time', 'img', 'sort'])
            ->orderBy('draw_time')
            ->paginate();
        return Resource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta);
    }

    public function show(Raffle $raffle)
    {
        $raffle->load([
                'launcher:id,nick_name,avatar_url',
                'awards:id,raffle_id,name,img,amount',
            ])->setVisible([
                'id', 'user_id', 'name', 'draw_time', 'img', 'launcher', 'awards',
            ]);

        $awardWhitelist = RaffleWhitelist::query()
            ->with('users:id,nick_name,avatar_url')
            ->where('raffle_id', $raffle->id)
            ->select(['id','user_id', 'award_id'])
            ->get()
            ->groupBy('award_id')
            ->toArray();
        foreach ($raffle->awards as $award) {
            $award['whitelist'] = $awardWhitelist[$award->id] ?? [];
        }

        return $this->success($raffle);
    }
}
