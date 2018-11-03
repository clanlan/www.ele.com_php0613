<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //排除不需要csrf_token验证的请求
        '/login',  //登陆
        '/regist',  //注册
        '/changePassword',  //修改密码
        '/forgetPassword', //忘记密码
        '/address/add',  //新增地址
        '/address/edit', //保存修改地址
        '/cart/add', //写入购物车
        '/addorder',  //添加订单
    ];
}
