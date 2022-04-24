<?php

namespace support\hsk99\util;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

class Qiniu
{
    /**
     * 上传文件
     *
     * @author HSK
     * @date 2022-04-16 23:46:28
     *
     * @param \Webman\Http\UploadFile $file
     *
     * @return array
     */
    public static function upload(\Webman\Http\UploadFile $file): array
    {
        $accessKey = get_system('qiniu_accessKey');
        $secretKey = get_system('qiniu_secretKey');
        $bucket    = get_system('qiniu_bucket');
        $bucketUrl = get_system('qiniu_bucketUrl');
        $key       = config('app.project', 'webman-admin') . '/' . $file->getUploadExtension() . '/' . date('Ymd') . '/' . uniqid() . '.' . $file->getUploadExtension();
        $value     = $file->getPathName();

        $auth          = new Auth($accessKey, $secretKey);
        $config        = new Config();
        $bucketManager = new BucketManager($auth, $config);
        $uploadManager = new UploadManager();
        $token         = $auth->uploadToken($bucket);

        list($uploadInfo, $uploadError) = $uploadManager->putFile($token, $key, $value);
        if ($uploadError) {
            throw new \Exception($uploadError, 500);
        }
        list($fileInfo, $fileError) = $bucketManager->stat($bucket, $key);
        if ($fileError) {
            throw new \Exception($fileError, 500);
        }

        return [
            'name' => $file->getUploadName(),
            'href' => (substr($bucketUrl, -1, 1) === '/' ? $bucketUrl : $bucketUrl . '/') . $key,
            'mime' => $file->getUploadMineType(),
            'size' => byte_size($fileInfo['fsize']),
            'type' => 3,
            'ext'  => $file->getUploadExtension(),
        ];
    }

    /**
     * 删除文件
     *
     * @author HSK
     * @date 2022-04-17 00:06:50
     *
     * @param string $key
     *
     * @return boolean
     */
    public static function delete(string $key): bool
    {
        $accessKey = get_system('qiniu_accessKey');
        $secretKey = get_system('qiniu_secretKey');
        $bucket    = get_system('qiniu_bucket');
        $bucketUrl = get_system('qiniu_bucketUrl');

        $auth          = new Auth($accessKey, $secretKey);
        $config        = new Config();
        $bucketManager = new BucketManager($auth, $config);

        $key = str_replace((substr($bucketUrl, -1, 1) === '/' ? $bucketUrl : $bucketUrl . '/'), '', $key);

        list($result, $error) = $bucketManager->delete($bucket, $key);
        if ($error) {
            throw new \Exception($error, 500);
        }

        return true;
    }
}
