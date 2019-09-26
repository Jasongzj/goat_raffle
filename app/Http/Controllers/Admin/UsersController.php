<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function search(Request $request)
    {
        if (!$request->input('name')) {
            return $this->success([]);
        }
        $list = User::query()
            ->where('nick_name', 'like', '%' . $request->input('name') . '%')
            ->select(['id', 'nick_name', 'avatar_url'])
            ->get();
        return $this->success($list);
    }
}
