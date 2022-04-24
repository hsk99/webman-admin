<?php

namespace support\hsk99\util;

use OSS\OssClient;

class Oss
{
    /**
     * 上传文件
     *
     * @author HSK
     * @date 2022-04-16 23:10:59
     *
     * @param \Webman\Http\UploadFile $file
     *
     * @return array
     */
    public static function upload(\Webman\Http\UploadFile $file): array
    {
        $accessKeyId     = get_system('oss_accessKeyId');
        $accessKeySecret = get_system('oss_accessKeySecret');
        $endpoint        = get_system('oss_endpoint');
        $bucket          = get_system('oss_bucket');

        $object   = config('app.project', 'webman-admin') . '/' . $file->getUploadExtension() . '/' . date('Ymd') . '/' . uniqid() . '.' . $file->getUploadExtension();
        $filePath = $file->getPathName();

        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

        $result = $ossClient->uploadFile($bucket, $object, $filePath);

        return [
            'name' => $file->getUploadName(),
            'href' => $result['info']['url'],
            'mime' => $file->getUploadMineType(),
            'size' => byte_size($result['info']['upload_content_length']),
            'type' => 2,
            'ext'  => $file->getUploadExtension(),
        ];
    }

    /**
     * 删除文件
     *
     * @author HSK
     * @date 2022-04-16 23:34:58
     *
     * @param string $object
     *
     * @return boolean
     */
    public static function delete(string $object): bool
    {
        $accessKeyId     = get_system('oss_accessKeyId');
        $accessKeySecret = get_system('oss_accessKeySecret');
        $endpoint        = get_system('oss_endpoint');
        $bucket          = get_system('oss_bucket');

        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

        $object = str_replace([$endpoint . '/', $bucket . '.', 'http://', 'https://'], '', $object);

        $ossClient->deleteObject($bucket, $object);

        return true;
    }
}
