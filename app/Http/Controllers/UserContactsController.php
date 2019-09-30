<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\JsonResponse;
use App\Models\UserContact;
use App\Services\WechatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserContactResource;

class UserContactsController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->input('page_size') ?? 15;
        $user = Auth::guard('api')->user();
        $list = UserContact::query()
            ->where('user_id', $user->id)
            ->select(['id', 'type', 'subs_type', 'title', 'content', 'img'])
            ->paginate($pageSize);

        return UserContactResource::collection($list)->additional(JsonResponse::$resourceAdditionalMeta );
    }

    /**
     * 添加快捷关注
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\WechatException
     */
    public function store(Request $request)
    {
        $checkContent = join(',', $request->only(['content', 'title']));
        $this->contentCheck($checkContent);
        $user = Auth::guard('api')->user();
        $attributes = $request->only([
            'type', 'subs_type', 'content', 'title', 'img',
        ]);
        $user->contacts()->create($attributes);

        return $this->message('添加成功');
    }

    /**
     * 更新快捷关注
     * @param UserContact $contact
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\WechatException
     */
    public function update(UserContact $contact, Request $request)
    {
        $checkContent = join(',', $request->only(['content', 'title']));
        $this->contentCheck($checkContent);
        $attributes = $request->only([
            'type', 'subs_type', 'content', 'title', 'img',
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
