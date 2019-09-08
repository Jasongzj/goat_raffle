<?php

namespace App\Http\Controllers;

use App\Services\WechatService;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function miniProgramServer(WechatService $wechatService)
    {
        $app = $wechatService->getMiniProgram();
        $app->server->push(function ($message) {
            return '消息已收到';

        });

        return $app->server->serve();
    }
}
