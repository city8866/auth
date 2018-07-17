<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/17
 * Time: 13:43
 */

namespace Auth;

use think\Controller;
use think\Request;
use think\Config;
use think\Exception;


use Auth\Oauth;
use Auth\Response;
use Auth\Err\CoreCode as E;
use Auth\Err\HttpCode as H;
use Auth\Exception\UnauthorizedException;

class Client extends Controller{

    /**
     * 对应操作
     * @var array
     */
    public $methodToAction = [
        'get' => 'read',
        'post' => 'save',
        'put' => 'update',
        'delete' => 'delete',
        'patch' => 'patch',
        'head' => 'head',
        'options' => 'options',
    ];

    public $accessRoute = [];
    /**
     *  允许访问的请求类型
     * @var string
     */
    public $restMethodList = 'get|post|put|delete|patch|head|options';
    /**
     * 默认不验证
     * @var bool
     */
    public $apiAuth = false;

    /**
     * 默认返回资源类型
     * @var string
     */
    protected $restDefaultType = 'json';

    protected $request;

    protected $redis;
    /**
     * 当前请求类型
     * @var string
     */
    protected $method;
    /**
     * 当前资源类型
     * @var string
     */
    protected $type;

    public static $app;
    /**
     * 返回的资源类的
     * @var string
     */
    protected $restTypeList = 'json';
    /**
     * REST允许输出的资源类型列表
     * @var array
     */
    protected $restOutputType = [
        'json' => 'application/json',
    ];

    /**
     * 客户端信息
     */
    protected $clientInfo;

    public function __construct()
    {
        $this->request = Request::instance();
        $this->init();
        $this->clientInfo = $this->checkAuth();
        parent::__construct();
    }

    /**
     * 初始化方法
     * 检测请求类型，数据格式等操作
     */
    public function init()
    {
        // 资源类型检测
        $request = Request::instance();
        $ext = $request->ext();
        if ('' == $ext) {
            // 自动检测资源类型
            $this->type = $request->type();
        } elseif (!preg_match('/\(' . $this->restTypeList . '\)$/i', $ext)) {
            // 资源类型非法 则用默认资源类型访问
            $this->type = $this->restDefaultType;
        } else {
            $this->type = $ext;
        }

        $response = new Response();
        $response->setType($this->type);
        //$this->setType();
        // 请求方式检测
        $method = strtolower($request->method());
        $this->method = $method;
        //这里可以加入header，防止前端ajax跨域
        if (false === stripos($this->restMethodList, $method)) {
            Response::error(H::METHOD_NOT_ALLOWED,E::INVALID_OPERATION,'方法不被允许','可允许的方法为:'.$this->restMethodList);
        }
    }

    public function checkAuth(){

        $oauth = new Oauth();
        $clientInfo = $oauth->authenticate();
        return $clientInfo;
    }
    /**
     * 空操作
     * 404
     */
    public function _empty()
    {
        Response::error(H::NOT_FOUND,E::PAGE_NOT_FOUND,'找不到此方法');
    }
}
