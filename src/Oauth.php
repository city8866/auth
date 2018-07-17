<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/17
 * Time: 13:45
 */

namespace Auth;

use Auth\Exception\UnauthorizedException;
use Auth\Response;
use Auth\Err\CoreCode as E;
use Auth\Err\HttpCode as H;

use data\model\NsUser as User;
use think\Config;
use think\Exception;
use think\Log;
use think\Request;
use think\Db;
use think\Cache;

class Oauth{

    /**
     * accessToken存储前缀
     *
     * @var string
     */
    public static $accessTokenPrefix = 'accessToken_';

    /**
     * accessTokenAndClientPrefix存储前缀
     *
     * @var string
     */
    public static $accessTokenAndClientPrefix = 'accessTokenAndClient_';

    /**
     * 过期时间秒数
     *
     * @var int
     */
    public static $expires = 72000;


    /**
     * 客户端信息
     *
     * @var
     */
    public $clientInfo;

    public function __construct()
    {
        self::$expires = Config::get('api_config.expires');
    }

    final function authenticate(){

        try{
            $clientInfo = $this->getClient();
            $checkClient = $this->certification($clientInfo);
            if($checkClient){
                return $clientInfo;
            }
        } catch (UnauthorizedException $e){
            Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,$e->getError().'未授权，用户认证失败');
        }
    }

    public function getClient(){

        $request = Request::instance();
        try{
            $this->clientInfo = $request->param();
            $this->clientInfo['authorization'] = $request->header('authorization');
            $this->clientInfo['uid'] = $request->param('uid');
        } catch (UnauthorizedException $e) {
            Response::error(H::UNAUTHORIZED, E::UNAUTHORIZED, '未授权，路由信息不正确');
        }

        return $this->clientInfo;
    }

    public function certification($data = []){

        if(Config::get('api_config.cache_enabled')){

            $getCacheAccessToken = Cache::store('redis')->get(self::$accessTokenPrefix . $data['authorization']);

            if(empty($getCacheAccessToken)){
                Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,'token不存在或者token错误，请检查后重试');
            }

            if($getCacheAccessToken['expires_time'] < time() ){
                Response::error(H::UNAUTHORIZED,E::TIME_OUT,'token已过期,请重新登录');
            }

            if(!empty(Request::instance()->param('uid'))){

                if(Cache::store('redis')->get(self::$accessTokenAndClientPrefix . Request::instance()->param('uid')) !== $data['authorization']){
                    Response::error(H::UNAUTHORIZED,E::INVALID_USER_ID,'uid与token不匹配，请重试');
                }
            }

            return true;

        }else{

            if(!empty($data['authorization'])){
                if(!empty($data['uid'])){

                    $condition = [
                        'uid'=>$data['uid'],
                        'access_token'=>$data['authorization']
                    ];

                    $ret = Db::name('ns_user')->where($condition)->find();

                    if($ret){

                        if($ret['expires_at'] <= time()){
                            Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,'token已过期,请重新获取');
                        }

                        return true;
                    }else{
                        Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,'uid和token不匹配');
                    }
                }
            }else{
                Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,'token不能为空');
            }
        }

    }

    /**
     * 计算ORDER的MD5签名
     */
    private function _getOrderMd5($params = [] , $app_secret = '') {
        ksort($params);
        $params['key'] = $app_secret;
        //Log::write('sys_query'.urlencode(http_build_query($params)));
        return strtolower(md5(urlencode(http_build_query($params))));
    }


    /**
     * 生成签名
     * _字符开头的变量不参与签名
     */
    public function makeSign ($data = [],$app_secret = '')
    {

        unset($data['version']);
        unset($data['signature']);
        unset($data['login_type']);
        unset($data['undefined']);
        foreach ($data as $k => $v) {

            if(substr($data[$k],0,1) == '_'){

                unset($data[$k]);
            }
        }

        return $this->_getOrderMd5($data,$app_secret);
    }
}