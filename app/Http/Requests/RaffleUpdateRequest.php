<?php

namespace App\Http\Requests;

use App\Models\Raffle;
use App\Models\UserContact;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RaffleUpdateRequest extends Request
{
    public function rules()
    {
        return [
            'awards.*.name' => 'required',
            'awards.*.amount' => 'required',
            'draw_type' => function ($attribute, $value, $fail) {
                if (!$value) {
                    return $fail('请选择开奖方式');
                }
                if (!in_array($value, array_keys(Raffle::$drawTypeMap))) {
                    return $fail('开奖方式不合法');
                }
            },
            'draw_time' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->lessThanOrEqualTo(Carbon::now())) {
                        return $fail('开奖时间不可晚于当前时间');
                    }
                }
            ],
            'draw_participants' => function ($attribute, $value, $fail) {
                if ($this->request->get('draw_type') != Raffle::DRAW_BASE_ON_TIME && !$value) {
                    return $fail('开奖人数不能为空');
                }
            },
            'award_type' => function ($attribute, $value, $fail) {
                if (!$value) {
                    return $fail('请选择发奖方式');
                }
                if (!in_array($value, array_keys(Raffle::$awardTypeMap))) {
                    return $fail('发奖方式不合法');
                }
            },
            'contact_id' => function ($attribute, $value, $fail) {
                if (!UserContact::query()->where('id', $value)->exists()) {
                    return $fail('选择的联系方式不存在');
                }
            }
        ];
    }
}
