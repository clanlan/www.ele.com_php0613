<?php

namespace App\Http\Controllers;

use App\Model\Member;
use App\Model\SignatureHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class MemberController extends Controller
{
    //会员注册
    public function regist(Request $request){
        //验证
        $validator=Validator::make($request->all(),[
            'username'=>'required|between:2,20',
            'tel'=>['required','regex:/^1[3-9]\d{9}$/','unique:members,tel'],
            'password' => ['required', 'regex:/^\w{6,16}$/'],
            'sms'=>['required','regex:/^\d{4}$/'],
        ],[
            'username.required' => '用户名不能为空',
            'username.between'=>'用户名只能输入2-10个字',
            'tel.required' => '手机号不能为空',
            'tel.regex' => '手机号格式不正确',
            'tel.unique' => '电话号码已存在',
            'password.required' => '请输入密码',
            'password.regex' => '6-16位密码.可以是数字,字母或下划线',
            'sms.requires'=>'验证码必填',
            'sms.regex'=>'验证码格式不对',
        ]);
        if($validator->fails()){
            return [
                'status' => "false",
                'message' =>$validator->errors()->first(),
            ];
        }
        //验证码与手机号码关联,才能解决同时注册或临时修改手机号的问题
        $code=Redis::get('code'.$request->tel);
        //sms必须验证,如果不验证,sms和$code都为空时会有漏洞
        if($request->sms == $code){
            Member::create([
                'username'=>$request->username,
                'tel'=>$request->tel,
                'password'=>bcrypt($request->password),
            ]);
            return [
                'status' => "true",
                'message' => '注册成功!',
            ];
        }else{
            return [
                'status' => 'false',
                'message' => '注册失败!',
            ];
        }
    }

    //短信验证接口
    public function sendSms(Request $request) {
        //验证手机号码
        $validator=Validator::make($request->all(),[
            'tel'=>['required','regex:/^1[3-9]\d{9}$/'],
        ],[
            'tel.required' => '手机号不能为空',
            'tel.regex' => '手机号格式不正确',
            'tel.unique' => '电话号码已存在',
        ]);
        if($validator->fails()){
            return [
                'status' => "false",
                'message' =>$validator->errors()->first(),
            ];
        }
        $code=rand(1000,9999);
        $tel=$request->tel;
        /*------------短信验证start--------------*/

//        $params = array ();
//        // *** 需用户填写部分 ***
//        // 必填：是否启用https
//        $security = false;
//
//        // 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
//        $accessKeyId = "LTAIwhrnSJoK0njy";
//        $accessKeySecret = "Epwhs44E23R0vZNZJNkfxZ5kpNaX82";
//
//        // 必填: 短信接收号码
//        $params["PhoneNumbers"] = $tel;
//
//        // 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
//        $params["SignName"] = "君蓝科技";
//
//        // 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
//        $params["TemplateCode"] = "SMS_149335590";
//
//        // 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
//        $params['TemplateParam'] = Array (
//            "code" => $code,
//        );
//
//        // 可选: 设置发送短信流水号
//        $params['OutId'] = "12345";
//
//        // 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
//        $params['SmsUpExtendCode'] = "1234567";
//
//
//        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
//        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
//            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
//        }
//
//        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
//        $helper = new SignatureHelper();
//
//        // 此处可能会抛出异常，注意catch
//        $content = $helper->request(
//            $accessKeyId,
//            $accessKeySecret,
//            "dysmsapi.aliyuncs.com",
//            array_merge($params, array(
//                "RegionId" => "cn-hangzhou",
//                "Action" => "SendSms",
//                "Version" => "2017-05-25",
//            )),
//            $security
//        );
//        //将验证码保存到redis5分钟
//        //code.tel保存redis的key时关联电话号码
        /*------------短信验证end--------------*/
        Redis::setex('code'.$tel,300,$code);
        return [
            "status"=>"true",
            "message"=> "获取短信验证码成功"
        ];
    }

    // 修改密码接口
    public function changePassword(Request $request){
        //验证
        $validator=Validator::make($request->all(),[
            'oldPassword'=>'required',
            'newPassword' => ['required', 'regex:/^\w{6,16}$/','different:oldPassword'],
        ],[
            'oldPassword.required' => '请输入旧密码',
            'newPassword.required' => '请输入密码',
            'newPassword.regex' => '6-16为密码.可以是数字,字母或下划线',
            'newPassword.different' => '新密码不能和旧密码相同',
        ]);
        if($validator->fails()){
            return [
                'status' => "false",
                'message' =>$validator->errors()->first(),
            ];
        }
        $user=Auth::user();
        if(Hash::check($request->oldPassword, $user->password)){
            $user->update([ 'password'=>bcrypt($request->newPassword),]);
            return [
                'status' => "true",
                'message' =>"修改成功",
            ];
        }else{
            return [
                'status' => "false",
                'message' =>"修改失败!",
            ];
        }
    }
    // 忘记密码接口
    public function forgetPassword(Request $request){
        //验证数据
        $validator=Validator::make($request->all(),[
            'tel'=>['required','regex:/^1[3-9]\d{9}$/'],
            'password' => ['required', 'regex:/^\w{6,16}$/'],
        ],[
            'tel.required' => '手机号不能为空',
            'tel.regex' => '手机号格式不正确',
            'password.required' => '请输入密码',
            'password.regex' => '6-16为密码.可以是数字,字母或下划线',
        ]);
        if($validator->fails()){
            return [
                'status' => "false",
                'message' =>$validator->errors()->first(),
            ];
        }
        $member= Member::where('tel',$request->tel)->first();
        $code=Redis::get('code'.$request->tel);
        //验证码与缓存的一致时更新数据
        if($request->sms == $code){
            if($member){
                $member->update([
                    'password'=>bcrypt($request->password),
                ]);
                return [
                    'status' => "true",
                    'message' => '重置成功!',
                ];
            }else{
                return [
                    'status' => 'false',
                    'message' => '电话号码不存在',
                ];
            }
        }else{
            return [
                'status' => 'false',
                'message' => '验证码不正确',
            ];
        }

    }
}
