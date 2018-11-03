<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


//接口路由
Route::domain('www.ele.com')->group(function (){
    //商家列表
    Route::get('shop/list','ShopController@list');
    // 获得指定商家接口
    Route::get('shop/business','ShopController@business');
    //会员注册 原则上使用post,此处get前端已定
    Route::post('/regist','MemberController@regist');
    // 修改密码接口
    Route::post('/changePassword','MemberController@changePassword');
    // 忘记密码接口
    Route::post('/forgetPassword','MemberController@forgetPassword');

    //会员登录
    Route::post('/login','LoginController@login');
    //发送短信验证码
    Route::get('/sms','MemberController@sendSms');
    //用户地址管理接口
    Route::get('/address/list','AddressController@addresslist');
    // 指定地址接口
    Route::get('/address/','AddressController@address');
    // 保存新增地址接口
    Route::post('/address/add','AddressController@addAddress');
    // 保存修改地址接口
    Route::post('/address/edit','AddressController@editAddress');
    // 保存购物车接口
    Route::post('/cart/add','CartController@addCart');
    // 获取购物车数据接口
    Route::get('/cart','CartController@cart');
    // 获得订单列表接口
    Route::get('/orderList','OrderController@orderList');
    // 添加订单接口
    Route::post('/addorder','OrderController@addorder');
    // 获得指定订单接口
    Route::get('/orderOne','OrderController@order');




});
