<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    //登陆接口
    public function login(Request $request)
    {
        //验证数据
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ], [
            'name.required' => '用户名不能为空',
            'password.required' => '必须输入密码',
        ]);
        if($validator->fails()){
            return [
                'status' => "false",
                'message' => $validator->errors()->first(),
            ];
        }
        if(Auth::attempt(['username' => $request->name, 'password' => $request->password])) {
            return [
                'status' => "true",
                'message' => '登陆成功!',
                'user_id' => Auth::user()->id,
                'username' => Auth::user()->username,
            ];
        } else {
            return [
                'status' => 'false',
                'message' => '用户名或密码错误!',
            ];
        }
    }
}
