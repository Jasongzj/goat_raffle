<?php

namespace App\Http\Controllers;

use App\Models\UserContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserContactsController extends Controller
{
    public function index()
    {
        $user = Auth::guard('api')->user();
        $list = UserContact::query()
            ->where('user_id', $user->id)
            ->select(['id', 'phone', 'wechat', 'qrcode'])
            ->get();
        return $this->success($list);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $attributes = $request->only([
            'phone', 'wechat', 'qrcode',
        ]);
        $user->contacts()->create($attributes);

        return $this->message('添加成功');
    }

    public function update(UserContact $contact, Request $request)
    {
        $attributes = $request->only([
            'phone', 'wechat', 'qrcode',
        ]);
        $contact->update($attributes);
        return $this->message('更新成功');
    }

    public function destroy(UserContact $contact)
    {
        $contact->delete();

        return $this->message('删除成功');
    }
}
