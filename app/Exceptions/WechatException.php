<?php

namespace App\Exceptions;

use App\Http\Controllers\Traits\JsonResponse;
use Exception;

class WechatException extends Exception
{
    use JsonResponse;

    public function render()
    {
        return $this->failed($this->getMessage(), $this->getCode());
    }
}
