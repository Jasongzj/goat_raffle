<?php

namespace App\Http\Controllers;

use App\Exceptions\WechatException;
use App\Http\Controllers\Traits\JsonResponse;
use App\Services\WechatService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
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
     * @throws WechatException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function uploadRequestImg($dir, UploadedFile $file)
    {
        // 保存图片到本地
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(20) . '.' . $extension;
        Storage::disk('local')->putFileAs('tmp', $file, $filename);
        $localPath = storage_path('app/tmp/' . $filename);

        // 鉴黄处理
        $wechatService = new WechatService();
        $response = $wechatService->getMiniProgram()->content_security->checkImage($localPath);
        if ($response['errcode']) {
            Log::notice('上传的图片包含违规内容，文件名：' . $filename);
            throw new WechatException('上传的资源包含敏感或违规内容', 400);
        }


        $qiniuPath = $dir . '/' . $filename;
        $disk = Storage::disk('qiniu');
        // 上传图片
        $disk->putFileAs($dir, $file, $filename);
        $url = $disk->getUrl($qiniuPath);

        // 删除本地文件
        unlink($localPath);

        return $url;
    }

    public function contentCheck($content)
    {
        // 鉴黄处理
        $wechatService = new WechatService();
        $response = $wechatService->getMiniProgram()->content_security->checkText($content);
        if ($response['errcode']) {
            Log::notice('上传的文本包含违规内容，文件内容：' . $content);
            throw new WechatException('上传的文本包含敏感内容', 400);
        }
    }
}
