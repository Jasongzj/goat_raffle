<?php
/**
 * Created by PhpStorm.
 * User: jasongao
 * Date: 2019-07-23
 * Time: 19:58
 */

namespace App\Services;

use Overtrue\LaravelWeChat\Facade as EasyWeChat;
use GuzzleHttp\Client;

abstract class AbstractService
{
    protected static $guzzleOptions = ['http_errors' => false];

    protected static $miniProgram;

    /**
     * 获取微信小程序实例
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function getMiniProgram()
    {
        return EasyWeChat::miniProgram();
    }

    /**
     * 获取 Client 的实例
     * @return Client
     */
    public function getHttpClient()
    {
        return new Client(self::$guzzleOptions);
    }

    /**
     * 设置 Http Client 的配置
     * @param array $config
     * @return array
     */
    public static function setGuzzleOptions($config = [])
    {
        return self::$guzzleOptions = $config;
    }

    /**
     * 获取七牛云服务
     * @return QiniuService
     */
    public function getQiniuService()
    {
        return new QiniuService();
    }
}
