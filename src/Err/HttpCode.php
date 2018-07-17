<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/17
 * Time: 13:47
 */

namespace Auth\Err;

class HttpCode{

    //http 错误代码
    const SUCCESS = 200;      //成功
    const CREATE  = 201;      //创建
    const ACCEPTED = 202;      //接收
    const NO_CONTENT = 204;      //没内容
    const RESET_CONTENT = 205;      //重置内容
    const FOUND = 302;      //发现
    const NOT_MODIFIED = 304;      //未修改
    const BAD_REQUEST = 400;      //坏的请求
    const UNAUTHORIZED = 401;      //未授权
    const NOT_FOUND = 404;      //找不到请求
    const FORBIDDEN = 403;      //禁止访问
    const METHOD_NOT_ALLOWED = 405;      //方法不被允许
    const NOT_ACCEPTABLE = 406;      //不可接受
    const REQUEST_TIME_OUT = 408;      //请求超时
    const CONFLICT = 409;      //内容已存在
    const INTERNAL_SERVER_ERROR = 500;      //内部服务器错误
    const BAD_GATEWAY = 502;      //网关错误
    const GATEWAY_TIME_OUT = 504;      //网关超时
}