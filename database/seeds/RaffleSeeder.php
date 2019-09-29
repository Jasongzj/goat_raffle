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
        $raffle = factory(\App\Models\Raffle::class)->times(20)->create();

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
