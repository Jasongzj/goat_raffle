<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-08-20
 * Time: 18:40
 */

namespace App\Services;


use App\Exceptions\WechatException;
use Illuminate\Support\Facades\Log;

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
        if (isset($response['errcode'])) {
            Log::error('请求微信接口错误' . $response['errcode'] . '|' . $response['errmsg']);
            throw new WechatException($response['errmsg'], $response['errcode']);
        }
    }
}
