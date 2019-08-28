<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\JsonResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, JsonResponse;

    /**
     * 上传图片
     * @param $dir
     * @param UploadedFile $file
     * @return mixed
     */
    public function uploadRequestImg($dir, UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(20) . '.' . $extension;
        $qiniuPath = $dir . '/' . $filename;
        $disk = Storage::disk('qiniu');
        // 上传图片
        $disk->putFileAs($dir, $file, $filename);
        $url = $disk->getUrl($qiniuPath);

        return $url;
    }
}
