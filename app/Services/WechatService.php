<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-08-20
 * Time: 18:40
 */

namespace App\Services;


use App\Exceptions\WechatException;
use EasyWeChat\Kernel\Support\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WechatService extends AbstractService
{
    /**
     * 根据code获取用户信息
     * @param $code
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function session($code)
    {
        $response = $this->getMiniProgram()->auth->session($code);

        $this->checkResponse($response);

        return $response;
    }

    /**
     * 解密微信加密的用户信息
     * @param $sessionKey
     * @param $iv
     * @param $encrypted
     * @return array
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     */
    public function decryptData($sessionKey, $iv, $encrypted)
    {
        $response = $this->getMiniProgram()->encryptor->decryptData($sessionKey, $iv, $encrypted);

        $this->checkResponse($response);

        return $response;
    }

    /**
     * 校验微信返回响应
     * @param $response
     * @throws WechatException
     */
    public function checkResponse($response)
    {
        if (is_array($response) && isset($response['errcode'])) {
            Log::error('请求微信接口错误' . $response['errcode'] . '|' . $response['errmsg']);
            throw new WechatException($response['errmsg'], $response['errcode']);
        }
    }

    /**
     * 生成小程序菊花码
     * @param $scene
     * @param $page
     * @return mixed
     * @throws WechatException
     */
    public function generateWxCode($scene, $page)
    {
        $optional = [
            'page' => $page,
        ];
        $response = $this->getMiniProgram()->app_code->getUnlimit($scene, $optional);

        $this->checkResponse($response);

        // 上传七牛云
        $contents = $response->getBody()->getContents();
        $filename = md5($contents) . File::getStreamExt($contents);
        $path = 'app_codes/' . $filename;
        $storage = Storage::disk('qiniu');
        $storage->put($path, $contents);
        $url = $storage->getUrl($path);

        return $url;
    }
}
