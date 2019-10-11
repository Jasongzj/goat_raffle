<?php


namespace App\WechatHandlers;


use App\Services\WechatService;
use Carbon\Carbon;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\Image;
use Illuminate\Support\Facades\Cache;

class TextMessageHandler implements EventHandlerInterface
{

    /**
     * @param mixed $payload
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($payload = null)
    {
        $wechatService = new WechatService();
        $miniProgram = $wechatService->getMiniProgram();
        switch ($payload['Content']) {
            case 1:
                $mediaId = Cache::get('official_account_code');
                if (!$mediaId) {
                    $qrCodePath = storage_path('app/official_account/qrcode.jpg');
                    // 上传公众号二维码素材
                    $response = $miniProgram->media->uploadImage($qrCodePath);
                    $mediaId = $response['media_id'];
                    // 缓存临时素材 media_id , 3天有效
                    Cache::put('official_account_code', $mediaId, Carbon::now()->addDays(3));
                }

                // 回复公众号图片
                    $miniProgram->customer_service
                    ->message(new Image($mediaId))
                    ->to($payload['FromUserName'])
                    ->send();
                break;
        }
    }
}
