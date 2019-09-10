<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-09-09
 * Time: 15:08
 */

namespace App\WechatHandlers;


use App\Models\Raffle;
use App\Models\UserContact;
use App\Services\WechatService;
use Carbon\Carbon;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MiniProgramHandler implements EventHandlerInterface
{
    public function handle($payload = null)
    {
        switch ($payload['Event']) {
            case 'user_enter_tempsession':
                $this->sendContactQrCode($payload['FromUserName'], $payload['SessionFrom']);
                break;
        }
    }


    /**
     * 发送抽奖对应联系方式的二维码
     * @param $openid
     * @param $sessionFrom
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendContactQrCode($openid, $sessionFrom)
    {
        $wechatService = new WechatService();
        $miniProgram = $wechatService->getMiniProgram();
        // 获取SessionFrom中的 contact_id
        $sessionFrom = json_decode($sessionFrom, true);
        $contactId = $sessionFrom['contact_id'];

        if (!$contactId) {
            return ;
        }

        // 获取缓存的 media_id
        $mediaId = Cache::get('user_contact_img:'.$contactId);

        if (!$mediaId) {
            $contact = UserContact::query()->find($contactId);

            if (!$contact || $contact->type != UserContact::TYPE_SUBS || !$contact->img) {
                return ;
            }
            // 保存素材到本地
            $filename = bin2hex(random_bytes(8)) . '.jpg';
            $path = 'tmp_contacts/' . $filename;
            $disk = Storage::disk('public');
            $disk->put($path, file_get_contents($contact->img));
            $realPath = storage_path('app/public/' . $path);
            // 上传临时素材
            $response = $miniProgram->media->uploadImage($realPath);
            $mediaId = $response['media_id'];
            // 缓存临时素材 media_id , 3天有效
            Cache::put('user_contact_img:'.$contactId, $mediaId, Carbon::now()->addDays(3));

            $disk->delete($path);
        }
        // 发送临时素材
        $miniProgram->customer_service->message(new Image($mediaId))->to($openid)->send();

    }
}
