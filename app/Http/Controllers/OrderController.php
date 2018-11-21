<?php

namespace App\Http\Controllers;

use App\Model\Address;
use App\Model\Cart;
use App\Model\Goods;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 获得订单列表接口
    public function orderList(){
        $user_id=Auth::user()->id;
        $oreders=Order::where('user_id',$user_id)->orderBy('id','desc')->get();
        $datas=[];
        foreach($oreders as $order){
            $total=0;
            $goods_list=[];
            $order_details=OrderDetail::where('order_id',$order->id)->get();
            foreach($order_details as $detail){
                $goods=[
                    "goods_id"=>$detail->goods_id,
                    "goods_name"=>$detail->goods_name,
                    "goods_img"=>$detail->goods_img,
                    "amount"=>$detail->amount,
                    "goods_price"=>$detail->goods_price,

                ];
                $goods_list[]=$goods;
                //商品总价
                $total+=($detail->goods_price)*($detail->amount);
            }

            $shop=Shop::find($order->shop_id);
            //判断订单状态
            if($order->status==-1){ $order_status="已取消";}
            if($order->status==0){ $order_status="待支付";}
            if($order->status==1){ $order_status="待发送";}
            if($order->status==2){ $order_status="待确认";}
            if($order->status==3){ $order_status="已完成";}
            $data=[
                "id"=> $order->id,
                "order_code"=>$order->sn,
                "order_birth_time"=>$order->created_at->toDateTimeString(),
                "order_status"=>$order_status,
                "shop_id"=>$order->shop_id,
                "shop_name"=> $shop->shop_name,
                "shop_img"=> $shop->shop_img,
                "goods_list"=>$goods_list,
                "order_price"=>round($total,2),
                "order_address"=>$order->province.$order->city.$order->county.$order->address,
            ];
            $datas[]=$data;
        }
        return $datas;

    }
    // 添加订单接口
    public function addorder(Request $request){
        //通过address_id查询出地址
        $address=Address::find($request->address_id);
        //通过address下面的user_id查询出用户购物车数据
        $carts=Cart::where('user_id',$address->user_id)->get();
        //遍历购物车,得到订单总价
        $total=0;
        foreach($carts as $cart){
            //根据购物车的goods_Id查出goods和shop信息
            $goods=Goods::find($cart->goods_id);
            $total+=($goods->goods_price)*($cart->amount);
        }
        //$shop_id-->购物车找最新的goods_id-->goods-->shop_id
        $cart2=Cart::where('user_id',$address->user_id)->latest()->first();
        $shop_id=Goods::find($cart2->goods_id)->shop_id;
        //手动开启事务,同时添加orders表与oreder_details表的数据,并清空购物车
        DB::beginTransaction();
        try{
            //新增一条数据到orders表
            $order_id=DB::table('orders')->insertGetId([
                'user_id'=>$address->user_id,
                'shop_id'=>$shop_id,  //店铺id
                'sn'=>date("Ymdhis", time()).rand(1000,9999), //订单号
                'province'=>$address->province,
                'city'=>$address->city,
                'county'=>$address->county,
                'address'=>$address->address,
                'tel'=>$address->tel,
                'name'=>$address->name,
                'total'=>$total,
                'status'=>0,
                'created_at'=>date('y-m-d h:i:s',time()),
                'out_trade_no'=>str_random(10),  //第三方交易号,微信支付 待定
            ]);
            //订单详情表新增数据
            foreach($carts as $cart){
                //根据购物车的goods_Id查出goods信息
                $goods=Goods::find($cart->goods_id);
                OrderDetail::create([
                    'order_id'=>$order_id,
                    'goods_id'=>$goods->id,
                    'amount'=>$cart->amount,
                    'goods_name'=>$goods->goods_name,
                    'goods_img'=>$goods->goods_img,
                    'goods_price'=>$goods->goods_price,
                ]);
            }
            //购物车清空 删除购物车表里当前用户的数据
            DB::table('carts')->where('user_id',$address->user_id)->delete();

            DB::commit(); //提交事务
        }catch(Exception $e){
            DB::rollBack();
        }
        return [
            "status"=> "true",
            "message"=> "添加成功",
            "order_id"=>$order_id,
            ];
    }
    // 获得指定订单接口
    public function order(Request $request){
        $order=Order::find($request->id);
        //获取订单详情
        $order_detail=OrderDetail::where('order_id',$request->id)->get();
        $goods_list=[];
        $total=0;
        foreach($order_detail as $detail){
            $data=[
                "goods_id"=>$detail->goods_id,
                "goods_name"=>$detail->goods_name,
                "goods_img"=>$detail->goods_img,
                "amount"=>$detail->amount,
                "goods_price"=>$detail->goods_price,
            ];
            $goods_list[]=$data;
            //商品总价
            $total+=($detail->goods_price)*($detail->amount);
        }
        //通过goods_id获取店铺信息
        $shop=Shop::find($order->shop_id);
        //判断订单状态
        if($order->status==-1){ $order_status="已取消";}
        if($order->status==0){ $order_status="待支付";}
        if($order->status==1){ $order_status="待发送";}
        if($order->status==2){ $order_status="待确认";}
        if($order->status==3){ $order_status="已完成";}
        return [
        "id"=>$order->id,
        "order_code"=>$order->sn,
        "order_birth_time"=>date('y-m-d h:i',time($order->created_at)),
        "order_status"=>"代付款",  //$order->status变量无法使用
        "shop_id"=>$shop->id,
        "shop_name"=>$shop->shop_name,
        "shop_img"=>$shop->shop_img,
        "goods_list"=>$goods_list,
        "order_price"=>$total,
        "order_address"=>$order->province.$order->city.$order->county.$order->address,
        ];
    }
}
