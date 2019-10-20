<?php


namespace App\WechatHandlers;


use App\Models\User;
use App\Services\WechatService;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

class OAEventMessageHandler implements EventHandlerInterface
{

    /**
     * @param mixed $payload
     */
    public function handle($payload = null)
    {
        $wechatService = new WechatService();
        $app = $wechatService->getOfficialAccount();
        switch (strtolower($payload['Event'])) {
            case 'subscribe':    // 关注
                // 查询用户基本信息
                $user = $app->user->get($payload['FromUserName']);
                // 记录用户 openid 和 unionid
                logger('系统返回信息:' . json_encode($user));
                // TODO 推送关注消息
                break;
            case 'unsubscribe':  // 取消关注
                break;
        }
    }
}
