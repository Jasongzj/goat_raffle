<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = Auth::guard('admin')->attempt($credentials)) {
            return $this->unauthorized('账号或密码错误');
        }

        $data = [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('admin')->factory()->getTTL() * 60,
            'user' => Auth::guard('admin')->user(),
        ];

        return $this->success($data);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();

        return $this->message('退出登录成功');
    }

    public function resetPwd(Request $request)
    {

    }
}
