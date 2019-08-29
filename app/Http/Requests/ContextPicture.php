<?php

namespace App\Http\Requests;


class ContextPicture extends Request
{
    public function rules()
    {
        return [
            'img' => 'required|file'
        ];
    }
}
