<?php

namespace App\Http\Controllers\Admin;

use App\Models\RaffleWhitelist;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function search(Request $request)
    {
        if (!$request->input('name')) {
            return $this->success([]);
        }

        $query = User::query();

        if ($raffleId = $request->input('raffle_id')) {
            $whitelistUserIds = RaffleWhitelist::query()->where('raffle_id', $raffleId)
                ->get(['user_id'])
                ->pluck('user_id')
                ->all();
            $query = $query->whereNotIn('id', $whitelistUserIds);
        }

        $list = $query
            ->where('nick_name', 'like', '%' . $request->input('name') . '%')
            ->select(['id', 'nick_name', 'avatar_url'])
            ->get();
        
        return $this->success($list);
    }
}
