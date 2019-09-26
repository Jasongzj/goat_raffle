<?php

namespace App\Http\Controllers\Admin;

use App\Models\RaffleWhitelist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RaffleWhitelistController extends Controller
{
    /**
     * 配置白名单
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $raffleId = $request->input('raffle_id');
        $awardId = $request->input('award_id');
        $time = Carbon::now();
        $whitelist = [];
        foreach ($request->input('user_ids') as $userId) {
            $whitelist[] = [
                'raffle_id' => $raffleId,
                'award_id' => $awardId,
                'user_id' => $userId,
                'created_at' => $time,
                'updated_at' => $time,
            ];
        }
        $result = DB::transaction(function () use ($whitelist) {
            RaffleWhitelist::query()->delete();
            $result = DB::table('raffle_whitelist')->insert($whitelist);
            return $result;
        });

        if ($result) {
            return $this->message('配置成功');
        } else {
            return $this->failed('配置失败', 400);
        }
    }
}
