<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WechatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * 微信授权
     * @param Request $request
     * @param WechatService $service
     * @return mixed
     * @throws \App\Exceptions\WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function wxAuth(Request $request, WechatService $service)
    {
        $response = $service->session($request->input('code'));

        $user = User::query()
            ->where('openid', $response['openid'])
            ->first();

        if (!$user) {
            // 保存用户资料
            $user = new User(['openid' => $response['openid']]);
            $user->save();
            // 初始化用户统计
            $user->stat()->create();
        }

        // 缓存 session key
        $cacheKey = User::$cacheSessionKeyPrefix. $user->id . User::$cacheSessionKeySuffix;
        Cache::put($cacheKey, $response['session_key'], now()->addHours(2));

        $token = Auth::guard('api')->login($user);

        $auth = [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ];

        $data = [
            'auth' => $auth,
            'user' => $user,
        ];

        return $this->success($data);
    }
}
