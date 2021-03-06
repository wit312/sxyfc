<?php

namespace app\attachment\storges;

use app\common\model\File;
use Qcloud\Cos\Client;

/**
 * @property Client cosClient
 */
class Qcloud extends StorgeEngine
{

    private $cosClient;

    public function __construct($config)
    {
        $this->config = $config;
        $this->cosClient = new Client(
            array(
                'region' => $config['local'],
                'credentials' => array(
                    'appId' => $config['appid'],
                    'secretId' => $config['secretid'],
                    'secretKey' => $config['secretkey']
                )
            )
        );
        $this->bucket = $config['bucket'];
        // 初始化签权对象
        $this->user = check_user();
        if (!$this->user) {
            $this->user = check_admin();
        }
    }

    public function test()
    {

        $test_file = SYS_PATH . 'statics/images/logo.png';

        try {
            $result = $this->cosClient->putObject(array(
                //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
                'Bucket' => 'default',
                'Key' => 'statics/images/logo.png',
                'Body' => fopen($test_file, 'rb'),
            ));
            $ret['code'] = 1;
            $ret['msg'] = "测试成功";
            $ret['data'] = $result;
        } catch (\Exception $e) {
            $ret['code'] = 2;
            $ret['msg'] = "上传失败";
        }

        return $ret;

    }

    public function form($field)
    {
        // TODO: Implement form() method.
    }

    public function upload(File $file ,$local_path = "" )
    {
        // TODO: Implement upload() method.
        $file_name = $file['filename'];
        if(!$local_path){
            $file_path = to_local_media($file);
        }else{
            $file_path = $local_path;
        }
        $result = $this->cosClient->putObject(array(
            //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
            'Bucket' => 'default',
            'Key' =>  $file['url'],
            'Body' => fopen($file_path, 'rb'),
        ));


        $ret['code'] = 2;
        $ret['msg'] = "上传失败";
        $ret['result'] = $result;


        return $ret;
    }
}