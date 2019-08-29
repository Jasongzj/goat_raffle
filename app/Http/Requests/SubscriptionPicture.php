<?php

namespace App\Http\Requests;


class SubscriptionPicture extends Request
{
    public function rules()
    {
        return [
            'img' => 'required|file'
        ];
    }
}
