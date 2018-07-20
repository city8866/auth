<?php
// +----------------------------------------------------------------------
    // | API设置
    // +----------------------------------------------------------------------
return [
        'api_config'=>[

            'cache_enabled'=>true,
            'sign_enabled'=>true,
            'timestamp_enabled'=>true,
            'expires'=>720000,
            'sms_app_key'=>'23306357',
            'sms_app_secret'=>'18794ead6bfbb3e806bae1438d98a855'
        ],

        // +----------------------------------------------------------------------
        // | OSS设置
        // +----------------------------------------------------------------------
        'oss'=>[
            'app_id'=>'***',
            'app_key'=>'***',
            'app_host'=>'http://***.oss-cn-hangzhou.aliyuncs.com',
            'app_expires'=>60,
            'app_max_size'=>1048576000
        ],

        // +----------------------------------------------------------------------
     // | 缓存设置
     // +----------------------------------------------------------------------
     
         'cache' =>  [
             // 使用复合缓存类型
             'type'  =>  'complex',
             // 默认使用的缓存
             'default'   =>  [
                 // 驱动方式
                 'type'   => 'File',
                 // 缓存保存目录
                 'path'   => CACHE_PATH,
             ],
             // 文件缓存
             'file'   =>  [
                    // 驱动方式
                 'type'   => 'file',
                 // 设置不同的缓存保存目录
                 'path'   => RUNTIME_PATH . 'file/',
             ],
             // redis缓存
             'redis'   =>  [
                 // 驱动方式
                'type'   => 'redis',
                 // 服务器地址
                 'host'       => '127.0.0.1',
                 //端口
                 'port'    =>'6379',
                 //密码
                 'password' => '',
                 //过期时间
                 'timeout' => 3600
             ],
         ],

]
    
?>