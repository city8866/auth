<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/17
 * Time: 13:47
 */

namespace Auth\Err;

class TipCode{

    //程序错误代码
    const SUCCESS = '成功'; //成功
    const UNKNOWN_ERROR = '未知错误';  //未知错误
    const SERVICE_TEMPORARILY_UNAVAILABLE = 2;   //服务暂不可用
    const UNSUPPORTED_API_METHOD = 3;   //未知的方法
    const API_REQUEST_LIMIT_REACHED = 4;   //接口调用次数已达到设定的上限
    const UNAUTHORIZED_CLIENT_IP_ADDRESS = 5;   //请求来自未经授权的IP地址
    const NO_PERMISSION_TO_ACCESS_DATA = 6;   //无权限访问该用户数据
    const NO_PERMISSION_TO_ACCESS_DATA_FROM_REFER = 7;   //来自该refer的请求无访问权限
    const UNAUTHORIZED = 8;   //未授权
    const INVALID_PARAMETER = 100;   //请求参数无效
    const INVALID_API_KEY = 101;   //api key无效
    const INVALID_SESSION_KEY = 102;   //session key无效
    const INVALID_CALL_ID = 103;   //call_id参数无效
    const INCORRECT_SIGNATURE = 104;   //无效签名
    const TOO_MANY_PARAMETERS = 105;   //请求参数过多
    const INVALID_TIMESTAMP = 107;   //timestamp参数无效
    const TIME_OUT = 115;           //超时
    const INVALID_CODE = 116;        //无效的验证码
    const INVALID_USER_ID = 108;   //无效的user id
    const INVALID_USER_INFO_FIELD = 109;   //无效的用户资料字段名
    const INVALID_ACCESS_TOKEN =110;   //无效的access token
    const ACCESS_TOKEN_EXPIRED = 111;   //access token过期
    const SESSION_KEY_EXPIRED = 112;   //session key过期
    const INVALID_IP_ADDRESS = 114;   //无效的ip参数
    const BACKEND_NOT_OPEN = 117;     //后台未开启
    const PAGE_NOT_FOUND = 118;
    const SEND_SMS_ERR = 210;         //发送短信失败
    const INVALID_OPERATION = 801;   //无效的操作方法
    const DATABASE_ERR_OCCURRED = 805;   //数据库操作出错

}