<?php

namespace App\Http\Controllers;

use App\Services\WechatService;
use App\WechatHandlers\MiniProgramHandler;
use EasyWeChat\Kernel\Messages\Message;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function miniProgramServer(WechatService $wechatService)
    {
        $app = $wechatService->getMiniProgram();
        $app->server->push(MiniProgramHandler::class, Message::EVENT);

        return $app->server->serve();
    }
}