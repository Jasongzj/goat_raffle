<?php

namespace App\Http\Requests;


use App\Models\Raffle;
use App\Models\UserContact;

class RaffleStoreRequest extends Request
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
            'draw_time' => function ($attribute, $value, $fail) {
                if ($this->request->get('draw_type') != Raffle::DRAW_BASE_ON_PARTICIPANTS && !$value) {
                    return $fail('请设置开奖/截止时间');
                }
            },
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

    public function messages()
    {
        return [
            'awards.*.name.required' => '请输入奖项名称',
            'awards.*.amount.required' => '请输入奖项数量',
        ];
    }
}
