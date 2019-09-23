<?php


namespace App\Services;


use App\Models\Raffle;
use App\Models\RaffleWinner;
use App\Models\UserRaffle;
use Illuminate\Support\Facades\Auth;

class RaffleService extends AbstractService
{
    /**
     * 抽奖详情
     * @param Raffle $raffle
     * @return Raffle
     */
    public function show(Raffle $raffle)
    {
        // 获取参与人员列表
        $participants = [];
        if ($raffle->current_participants) {
            $participants = UserRaffle::getParticipantsList($raffle->id);
        }
        $raffle->participants_list = $participants;

        $raffle->is_winner = false;
        $raffle->is_fill_address = false;
        $raffle->my_address = null;
        $raffle->my_message = null;

        // 获取中奖名单
        $winners = [];
        $winnerCount = 0;
        $completedWinnerCount = 0;
        if ($raffle->status == Raffle::STATUS_ENDED && $raffle->current_participants > 0) {
            $raffle = $this->getWinnerList($raffle, $winners, $winnerCount, $completedWinnerCount);
        }
        $raffle->winner_list = $winners;
        $raffle->winner_count = $winnerCount;
        $raffle->completed_winner_count = $completedWinnerCount;

        return $raffle;
    }

    /**
     * 获取中奖者列表数据
     * @param Raffle $raffle
     * @param $winners
     * @param $winnerCount
     * @param $completedWinnerCount
     * @return Raffle
     */
    protected function getWinnerList(Raffle $raffle, &$winners, &$winnerCount, &$completedWinnerCount)
    {
        $awardIds = $raffle->awards->pluck('id')->all();
        $winnerList = RaffleWinner::getListByAwardIds($awardIds);

        // 当前用户是否中奖
        $user = Auth::guard('api')->user();

        // 重组中奖者列表数据
        foreach ($raffle->awards as $key => $award) {
            $winners[] = [
                'award_name' => $award->name,
                'award_amount' => $award->amount,
            ];
            foreach ($winnerList as $winner) {
                if ($winner->award_id == $award->id) {
                    $winners[$key]['users'][] = $winner->users;
                    $winnerCount++;

                    // 已完善收货地址，统计+1
                    if ($winner->address)  {
                        $completedWinnerCount++;
                    }

                    // 当前用户是否中奖，获取收货地址
                    if ($winner->user_id == $user->id) {
                        $raffle->is_winner = true;
                        $raffle->my_award = $winners[$key]['award_name'];
                        if ($winner->address) {
                            $raffle->is_fill_address = true;
                            $raffle->my_address = $winner->address;
                            $raffle->my_message = $winner->message;
                        }
                    }
                }
            }
            if (!$winners[$key]['users']) {
                unset($winners[$key]);
                break;
            }
        }
        return $raffle;
    }
}
