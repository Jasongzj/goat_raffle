<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaffleStoreRequest;
use App\Models\Raffle;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaffleController extends Controller
{
    /**
     * 首页抽奖列表
     */
    public function index()
    {
    }

    /**
     * 抽奖详情
     * @param Raffle $raffle
     */
    public function show(Raffle $raffle)
    {

    }

    /**
     * 发起抽奖
     * @param RaffleStoreRequest $request
     * @return mixed
     */
    public function store(RaffleStoreRequest $request)
    {
        $attributes = $request->only([
            'draw_type', 'draw_time', 'draw_participants', 'desc',
            'copy_title', 'copy_content', 'award_type', 'contact_id',
        ]);
        $awards = $request->input('awards');

        DB::transaction(function () use ($attributes, $awards) {
            // 根据奖项生成抽奖标题
            $attributes['name'] = '奖品：';
            foreach ($awards as $award) {
                $attributes['name'] .= $award['name'] . ' x ' . $award['amount'];
            }

            $user = Auth::guard('api')->user();
            $raffle = new Raffle($attributes);
            $user->raffle()->save($raffle);
            // 保存奖项
            $raffle->awards()->createMany($awards);

            // 用户全部抽奖、发起抽奖记录+1
            $user->stat()->increment('raffle_amount', 1);
            $user->stat()->increment('launched_raffle_amount', 1);
        });

        return $this->message('发起抽奖成功');
    }
}
