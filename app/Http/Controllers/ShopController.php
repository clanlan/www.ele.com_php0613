<?php

namespace App\Http\Controllers;

use App\Model\Shop;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    //商家列表接口
    public function list(Request $request){
        $wheres=[];
        $wheres[]=['status',1];
        if($request->keyword){
            $wheres[]=['shop_name','like',"%{$request->keyword}%"];
            $shops=Shop::where($wheres)->get();
        }else{
            //查询结果从第0条开始取10条
            $shops=Shop::where($wheres)->get();
        }
        $datas=[];
        foreach($shops as $shop){
            $data=[
                "id"=> $shop->id,
                "shop_name"=>$shop->shop_name,
                "shop_img"=>$shop->shop_img,
                "shop_rating"=>$shop->shop_rating,
                "brand"=>$shop->brand,
                "on_time"=>$shop->on_time,
                "fengniao"=>$shop->fengniao,
                "bao"=>$shop->bao,
                "piao"=>$shop->piao,
                "zhun"=>$shop->zhun,
                "start_send"=>$shop->start_send,
                "send_cost"=>$shop->send_cost,
                "distance"=>'1km',
                "estimate_time"=>30,
                "notice"=>$shop->notice,
                "discount"=>$shop->discount,
            ];
            $datas[]=$data;
        }
        return $datas;

    }
    // 获得指定商家接口
    public function business(Request $request){
        $id=$request->id;
        $shop=Shop::find($id);
        //查询出评论  未做功能暂用默认值
        $evaluate=[
            [
            "user_id"=>12344,
            "username"=>"w******k",
            "user_img"=>"/images/slider-pic4.jpeg",
            "time"=>"2017-2-22",
            "evaluate_code"=>1,
            "send_time"=>30,
            "evaluate_details"=>"不怎么好吃",
                ],
        ];
        //查询出店铺商品分类及分类下的商品
        $categorys=DB::table('goods_categories')->where('shop_id',$id)->get();
        //所有商品分类保存在cates
        $cates=[];
        foreach($categorys as $cate){
            //查询当前分类下面的产品
            $goods=DB::table('goods')->where('category_id',$cate->id)->get();
            $goodsList=[];
            foreach($goods as $g){
                $goodsOne=[
                    "goods_id"=>$g->id,
                    "goods_name"=>$g->goods_name,
                    "rating"=>$g->rating,
                    "goods_price"=>$g->goods_price,
                    "description"=>$g->description,
                    "month_sales"=>$g->month_sales,
                    "rating_count"=>$g->rating_count,
                    "tips"=>$g->tips,
                    "satisfy_count"=>$g->satisfy_count,
                    "satisfy_rate"=>$g->satisfy_rate,
                    "goods_img"=>$g->goods_img,
                ];
                $goodsList[]=$goodsOne;
            }

            //将遍历的分类保存到cates[]
            $cate=[
            "description"=>$cate->description,//分类描述
            "is_selected"=>$cate->is_selected==1 ? true : false, //三位运算符
            "name"=>$cate->name,
            "type_accumulation"=>$cate->type_accumulation,//类型id
            "goods_list"=>$goodsList, //当前分类下面的商品
            ];
            $cates[]=$cate;
        }
        return [
            "id"=>$shop->id,
            "shop_name"=>$shop->shop_name,
            "shop_img"=>$shop->shop_img,
            "shop_rating"=>$shop->shop_rating,
            "service_code"=> 4.6,
            "foods_code"=> 4.4,
            "high_or_low"=> true,
            "h_l_percent"=> 30,
            "brand"=> $shop->brand==1 ? true : false,
            "on_time"=>$shop->on_time==1 ? true : false,
            "fengniao"=>$shop->fengniao==1 ? true : false,
            "bao"=>$shop->fengniao==1 ? true : false,
            "piao"=>$shop->fengniao==1 ? true : false,
            "zhun"=>$shop->fengniao==1 ? true : false,
            "start_send"=>$shop->start_send,
            "send_cost"=>$shop->send_cost,
            "distance"=> 637,
            "estimate_time"=>31,
            "notice"=>$shop->notice,
            "discount"=>$shop->discoutn,
            "evaluate"=>$evaluate,
            "commodity"=>$cates,
        ];

    }
}
