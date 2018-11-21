<?php

namespace App\Http\Controllers;

use App\Model\Address;
use App\Model\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    // 地址列表接口
    public function addresslist(){
        $user_id=Auth::user()->id;
        $address=Address::where('user_id',$user_id)->get();
        $datas=[];
        foreach ($address as $address){
            $data=[
                'id'=>$address->id,
                'provence'=>$address->provence,
                'city'=>$address->city,
                'area'=>$address->county,
                "detail_address"=>$address->address,
                "name"=>$address->name,
                "tel"=>$address->tel,
            ];
            $datas[]=$data;
        }
        return $datas;
    }
    // 指定地址接口
    public function address(Request $request){
        //差验证
        $address=Address::find($request->id);
        return [
            'id'=>$address->id,
            'provence'=>$address->province,
            'city'=>$address->city,
            'area'=>$address->county,
            "detail_address"=>$address->address,
            "name"=>$address->name,
            "tel"=>$address->tel,
        ];
    }
    // 保存新增地址接口
    public function addAddress(Request $request){
        $user_id=Auth::user()->id;
        Address::create([
            'user_id'=>$user_id,
            'name'=>$request->name,
            'tel'=>$request->tel,
            'province'=>$request->provence,
            'city'=>$request->city,
            'county'=>$request->area,
            'address'=>$request->detail_address,
        ]);
        return [
            "status"=> "true",
            "message"=> "添加成功",
        ];
    }
    // 保存修改地址接口
    public function editAddress(Request $request){
        $id=$request->id;
        Address::where('id',$id)->update([
            'name'=>$request->name,
            'tel'=>$request->tel,
            'province'=>$request->provence,
            'city'=>$request->city,
            'county'=>$request->area,
            'address'=>$request->detail_address,
        ]);
        return [
            "status"=> "true",
            "message"=> "修改成功",
        ];

    }
}
