<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/17
 * Time: 13:45
 */

namespace Auth;

use Auth\Err\HttpCode as H;
use Auth\Err\CoreCode as E;
use Auth\Response;
use Auth\Oauth;
use Auth\Exception\UnauthorizedException;
use Flc\Alidayu\Client;
use Flc\Alidayu\App;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Flc\Alidayu\Requests\IRequest;
use think\Config;
use think\Controller;
use think\Log;
use think\Request;
use think\Db;
use think\cache;
use data\model\NsUser as User;

class Validate extends Controller{
    public $accessTokenInfo;

    public static $rule_mobile = [
        'mobile'     =>'require',
        'captcha'    =>'require'
    ];
    //手机客户端请求验证规则
    public static $rule_user = [
        'app_key'     =>  'require',
        'uid'         =>  'require',//uid
    ];

    //微信端请求验证规则
    public static $rule_wechat = [
        'app_key'     =>  'require',
        'open_id'     =>  'require',
        'nonce'       =>  'require',
        'union_id'    =>  'require',
        'access_token'=>  'require' //微信端的access_token用于验证用户的信息是否真实
    ];

    /**
     * 构造函数
     * 初始化检测请求时间，签名等
     */
    public function __construct()
    {

        $this->request = Request::instance();
        //为了调试注释掉时间验证与前面验证，请开发者自行测试
        if(Config::get('api_config.timestamp_enabled')){
            if(empty($this->request->param('login_type'))){
                Response::error(H::NOT_ACCEPTABLE,E::INVALID_PARAMETER,'login_type不能为空');
            }else{
                if($this->request->param('login_type') != 1){

                    if(empty($this->request->param('timestamp'))){
                        Response::error(H::NOT_ACCEPTABLE,E::INVALID_PARAMETER,'timestamp不能为空');
                    }else{
                        $this->checkTime();
                    }
                }
            }
        }

        if(Config::get('api_config.sign_enabled')){

            if(empty($this->request->param('login_type'))){
                Response::error(H::NOT_ACCEPTABLE,E::INVALID_PARAMETER,'login_type不能为空');
            }else{
                if($this->request->param('login_type') != 1){

                    if(empty($this->request->param('signature')) || empty($this->request->param('nonce'))){
                        Response::error(H::NOT_ACCEPTABLE,E::INVALID_PARAMETER,'signature或者nonce不能为空');
                    }else{
                        $this->checkSign();
                    }
                }
            }

        }
        parent::__construct();
    }

    public function wechat()
    {
        $this->checkAppKey(self::$rule_wechat);  //检测appkey
    }

    public function sendSms($phone,$number,$sign,$template){
        // 配置信息
        $config = [
            'app_key'    => Config::get('api_config.sms_app_key'),
            'app_secret' => Config::get('api_config.sms_app_secret'),
            // 'sandbox'    => true,  // 是否为沙箱环境，默认false
        ];

        $client = new Client(new App($config));

        $req = new AlibabaAliqinFcSmsNumSend();
        $req->setRecNum($phone)
            ->setSmsParam(['number'=>$number])
            ->setSmsFreeSignName($sign)
            ->setSmsTemplateCode($template);
        $resp = $client->execute($req);

        return $resp;
    }

    /**
     * 为客户端提供access_token
     * 手机号登录，手机号登录必须是注册过的手机号，不支持手机号注册
     */
    public function check($phone = '',$sign = '',$template = '')
    {
        //获取短信验证码
        if(!empty($phone)){
            $res  = Db::name('ns_user')->field('uid')->where('mobile',$phone)->find();  //取数据库对应手机号绑定用户
            if($res){
                $number = rand(100000,999999);
                $obj = $this->sendSms($phone,$number,$sign,$template);
                if($obj->result->success){
                    $data = [
                        'phone'=>$phone,
                        'code'=>$number,
                        'type'=>1,//1短信验证码2营销短信
                        'create_time'=>time(),
                        'status'=>1
                    ];
                    $list = Db::name('sys_sms')->insert($data);
                    if($list){
                        Response::success($obj);
                    }else{
                        Response::error(H::NOT_ACCEPTABLE,E::DATABASE_ERR_OCCURRED,'写入短信信息失败');
                    }
                }

            }else{
                Response::error(H::NOT_ACCEPTABLE,E::INVALID_PARAMETER,'未注册的手机号,请注册后操作');
            }
        }else{
            if(empty($this->request->param('login_type'))){
                Response::error(H::NOT_ACCEPTABLE,E::INVALID_PARAMETER,'必选参数不能为空');
            }else{
                if($this->request->param('login_type') == 1){
                    $this->checkAppKey(self::$rule_mobile);
                    $condition['phone'] = ['eq',$this->request->param('mobile')];
                    $condition['code'] = ['eq',$this->request->param('captcha')];
                    $condition['create_time'] = ['lt',time()-300];
                    $info = Db::name('sys_sms')->where($condition)->order('create_time DESC')->limit(1)->find();
                    if($info){
                        $result = Db::name('ns_user')->field('uid,role,status,expires_at')->where(['mobile'=>$this->request->param('mobile')])->find();
                    }else{
                        Response::error(H::NOT_ACCEPTABLE,E::INVALID_PARAMETER,'短信验证码错误或者已过期');
                    }
                }else{
                    $this->checkAppKey(self::$rule_user);
                    $result = Db::name('ns_user')->field('uid,role,status,expires_at')->where(['uid'=>$this->request->param('uid'),'auth_key'=>$this->request->param('app_key')])->find();  //取数据库对应手机号绑定用户
                }
                if(!empty($result)){
                    try {
                        $result['auth_key'] = $this->request->param('app_key');
                        $accessTokenInfo = $this->setAccessToken($result);
                        Response::success($accessTokenInfo);
                    } catch (UnauthorizedException $e) {
                        Response::error(Http::INTERNAL_SERVER_ERROR,E::UNKNOWN_ERROR,'服务器错误','服务器开小差了，亲');
                    }
                }else{
                    Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,'查无此用户');
                }
            }

        }
    }

    /**
     * 检测时间+_300秒内请求会异常
     */
    public function checkTime()
    {
        $time = $this->request->param('timestamp');
        $newTime = substr($time,0,10);
        if($newTime > time()+300  || $newTime < time()-300){
            Response::error(H::UNAUTHORIZED,E::TIME_OUT,'请求时间错误');
        }
    }

    /**
     * 检测appKey的有效性
     * @param 验证规则数组
     */
    public function checkAppKey($rule)
    {
        $result = $this->validate($this->request->param(),$rule);
        if(true !== $result){
            Response::error(H::METHOD_NOT_ALLOWED,E::INVALID_PARAMETER,'必选参数不能为空',$result);
        }
    }

    /**
     * 检查签名
     */
    public function checkSign()
    {
        $baseAuth = new Oauth();
        $app_secret = User::get(['auth_key' => $this->request->param('app_key')]);
        $sign = $baseAuth->makesign($this->request->param(),$app_secret['auth_secret']);     //生成签名

        if(empty($this->request->param('signature'))){
            Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,'signature不能为空');
        }
        Log::write('new_sign'.$this->request->param('signature'));
        Log::write('sys_sign'.$sign);
        if($sign !== $this->request->param('signature')){
            Response::error(H::UNAUTHORIZED,E::UNAUTHORIZED,'签名错误，请检查后重试!');
        }
    }


    /**
     * 设置AccessToken
     * @param $clientInfo
     * @return int
     */
    protected function setAccessToken($clientInfo)
    {
        //生成令牌
        $accessToken = self::buildAccessToken();
        $this->accessTokenInfo = [
            'access_token' => $accessToken,//访问令牌
            'expires_time' => time() + Oauth::$expires,      //过期时间时间戳
            'client' => $clientInfo,//用户信息
        ];
        $ret = self::saveAccessToken($accessToken, $this->accessTokenInfo);

        if($ret){
            return $this->accessTokenInfo;
        }else{
            Response::error(H::NOT_ACCEPTABLE,E::DATABASE_ERR_OCCURRED,'认证信息写入失败');
        }
    }

    /**
     * 生成AccessToken
     * @return string
     */
    protected static function buildAccessToken($length = 32)
    {
        //生成AccessToken
        $str_pol = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle($str_pol), 0, $length);

    }

    /**
     * 存储
     * @param $accessToken
     * @param $accessTokenInfo
     */
    protected static function saveAccessToken($accessToken, $accessTokenInfo)
    {

        $condition['uid'] = ['eq',$accessTokenInfo['client']['uid']];

        $data = [
            'updated_at'=>time(),
            'expires_at'=>$accessTokenInfo['expires_time'],
            'access_token'=>$accessTokenInfo['access_token']
        ];

        if(Config::get('api_config.cache_enabled')){
            //存储accessToken
            //Cache::set(Oauth::$accessTokenPrefix . $accessToken, $accessTokenInfo, Oauth::$expires);
            Cache::store('redis')->set(Oauth::$accessTokenPrefix . $accessToken, $accessTokenInfo, Oauth::$expires);
            //存储用户与信息索引 用于比较,这里涉及到user_id，如果有需要请关掉注释
            Cache::store('redis')->set(Oauth::$accessTokenAndClientPrefix . $accessTokenInfo['client']['uid'], $accessToken, Oauth::$expires);
            //Cache::set(self::$accessTokenAndClientPrefix . $accessTokenInfo['client']['user_id'], $accessToken, self::$expires);
            $ret = Db::name('ns_user')->where($condition)->update($data);
        }else{
            $ret = Db::name('ns_user')->where($condition)->update($data);
        }

        return $ret;
    }
}