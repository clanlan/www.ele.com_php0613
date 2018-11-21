<?php

namespace App\Http\Controllers;

use App\Model\Cart;
use App\Model\Goods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // 保存购物车接口
    public function addCart(Request $request){
        $user_id=Auth::user()->id;
        $amount=$request->goodsCount;
        foreach($request->goodsList as $k=>$goods_id){
            //判断购物车是否已经存在订单中的商品 如果存在,数量amount累加
            //查询出重复的商品
            $carts=Cart::where('user_id',$user_id)->where('goods_id',$goods_id)->get();
            if(count($carts)!=0){
                foreach($carts as $cart){
                    $cart->update([
                        'amount'=>($cart->amount)+$amount[$k],
                    ]);
                }
            }else{
                //购物车没有的产品直接新增
                Cart::create([
                    'user_id'=>$user_id,
                    'goods_id'=>$goods_id,
                    'amount'=>$amount[$k],
                ]);
            }
        }

        return [
            "status"=> "true",
            "message"=> "添加成功",
        ];
    }
    // 获取购物车数据接口
    public function cart(){
        $user_id=Auth::user()->id;
        //查询出当前登陆用户购物车的数据
        $carts=Cart::where('user_id',$user_id)->get();
        $datas=[];
        $total=0;
        //遍历出每条购物车数据
        foreach($carts as $cart){
            //根据购物车的goods_Id查出goods信息
            $goods=Goods::find($cart->goods_id);
            $data=[
                "goods_id"=>$goods->id,
                "goods_name"=>$goods->goods_name,
                "goods_img"=>$goods->goods_img,
                "amount"=>$cart->amount,
                "goods_price"=>($goods->goods_price)*($cart->amount),
            ];
            $datas[]=$data;
            $total+=($goods->goods_price)*($cart->amount);
        }
        return [
            'goods_list'=>$datas,
            'totalCost'=>$total,
        ];
    }
}
