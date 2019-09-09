<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-09-09
 * Time: 15:08
 */

namespace App\WechatHandlers;


use App\Services\WechatService;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\Text;

class MiniProgramHandler implements EventHandlerInterface
{
    public function handle($payload = null)
    {
        $openid = $payload['FromUserName'];
        $wechatService = new WechatService();
        $message = '我收到你的小程序卡片了';
        $wechatService->getMiniProgram()->customer_service->message(new Text($message))->to($openid)->send();
    }
}
