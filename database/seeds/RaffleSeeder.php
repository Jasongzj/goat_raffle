<?php

use Illuminate\Database\Seeder;

class RaffleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 重置统计数据
        \App\Models\UserStat::query()->update([
            'launched_raffle_amount' => 0,
            'participated_raffle_amount' => 0,
            'award_amount' => 0,
        ]);
        \App\Models\RaffleWhitelist::query()->delete();
        \App\Models\RaffleWinner::query()->delete();
        \App\Models\UserRaffle::query()->delete();
        \App\Models\RaffleAward::query()->delete();
        \App\Models\Raffle::query()->delete();

        $raffle = factory(\App\Models\Raffle::class)->times(50)->create();

        foreach ($raffle as $item) {
            $nums = random_int(1, 3);
            $awards = factory(\App\Models\RaffleAward::class)->times($nums)->create(['raffle_id' => $item->id]);
            // 根据奖项生成抽奖标题
            $name = '';
            foreach ($awards as $award) {
                $name .= $award['name'] . ' x ' . $award['amount'] . ' ';
            }
            $item->update(['name' => $name]);
            // 用户发起抽奖统计+1
            $user = \App\Models\User::query()->where('id', $item->user_id)->first();
            $user->stat()->increment('launched_raffle_amount', 1);
        }
    }
}
