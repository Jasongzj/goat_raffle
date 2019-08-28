<?php

namespace App\Http\Requests;


class AwardPicture extends Request
{
    public function rules()
    {
        return [
            'img' => 'required|file'
        ];
    }
}
