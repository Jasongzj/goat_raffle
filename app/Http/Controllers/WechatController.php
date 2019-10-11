<?php

namespace App\Http\Controllers;

use App\Services\WechatService;
use App\WechatHandlers\TextMessageHandler;
use App\WechatHandlers\MiniProgramHandler;
use EasyWeChat\Kernel\Messages\Message;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function miniProgramServer(WechatService $wechatService)
    {
        $app = $wechatService->getMiniProgram();
        $app->server->push(MiniProgramHandler::class, Message::MINIPROGRAM_PAGE);
        $app->server->push(TextMessageHandler::class, Message::TEXT);

        return $app->server->serve();
    }
}
