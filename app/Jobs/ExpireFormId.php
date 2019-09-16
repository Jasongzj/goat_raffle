<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExpireFormId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 获取所有用户ID
        $userIds = User::query()->select('id')->get()->pluck('id')->all();

        // 处理每位用户的过期Form_id
        $expiredAt = Carbon::now()->getTimestamp();
        foreach ($userIds as $userId) {
            \Redis::zRemRangeByScore('form_id_of_'. $userId, 0, $expiredAt);
        }
    }
}
