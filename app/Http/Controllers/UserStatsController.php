<?php

namespace App\Http\Controllers;

use App\Models\UserStat;
use Illuminate\Support\Facades\Auth;

class UserStatsController extends Controller
{
    public function my()
    {
        $user =  Auth::guard('api')->user();
        $data = UserStat::query()->where('user_id', $user->id)
            ->select(['participated_raffle_amount', 'launched_raffle_amount', 'award_amount'])
            ->first();
        return $this->success($data);
    }
}
