<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable=['order_id','goods_id','amount','goods_name','goods_img','goods_price'];

}
