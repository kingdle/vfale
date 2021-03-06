<?php

namespace App\Http\Controllers;

use App\Prod;
use Auth;
use App\Album;
use App\Dynamic;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountController extends Controller
{
    public function count()
    {
        $userId = Auth::guard('api')->user()->id;
        $albums = Album::latest()->where('user_id', $userId)->count();//相片总数
        $dynamics = Dynamic::latest()->where('user_id', $userId)->count();//动态总数
        $orders = Order::latest()->where('user_id', $userId)->where('is_del', '=','F')->count();//订单总数

        $mgChart = [
            'albumsCount' => $albums,
            'dynamicsCount' => $dynamics,
            'ordersCount' => $orders,
        ];
        return json_encode($mgChart);
    }
    public function countOrder()
    {
        $userId = Auth::guard('api')->user()->id;
        $ordersCount = Order::latest()->where('to_user_id', $userId)->where('is_del', '=','F')->count();//订单总数
        $seedCount = Order::latest()->where('to_user_id', $userId)->where('is_del', '=','F')->sum('counts');//苗子总数

        $ordersList = Order::latest()->where('to_user_id', $userId)->where('state','0')->where('payment','0')->where('is_del', '=','F')->count();//未送苗及未收款总数
        $ordersSend = Order::latest()->where('to_user_id', $userId)->where('state','1')->where('is_del', '=','F')->count();//已送苗总数
        $ordersPayment = Order::latest()->where('to_user_id', $userId)->where('payment','1')->where('is_del', '=','F')->count();//已收款总数

//        $moneyCount = Order::latest()->where('user_id', $userId)->count();//订单总金额

        $orderChart = [
            'ordersCount' => $ordersCount,
            'seedCount' => $seedCount,
            'orderList' =>$ordersList,
            'ordersSend' => $ordersSend,
            'payment' => $ordersPayment
        ];
        return json_encode($orderChart);
    }
    public function moneyOrder($id)
    {
        $userId = $id;
        $ordersCount = Order::latest()->where('shop_id', $userId)->where('is_del', '=','F')->count();//订单总数
        $seedCount = Order::latest()->where('shop_id', $userId)->where('is_del', '=','F')->sum('counts');//苗子总数

        $ordersList = Order::latest()->where('shop_id', $userId)->where('state','0')->where('payment','0')->where('is_del', '=','F')->count();//未送苗及未收款总数
        $ordersSend = Order::latest()->where('shop_id', $userId)->where('state','1')->where('is_del', '=','F')->count();//已送苗总数
        $ordersPayment = Order::latest()->where('shop_id', $userId)->where('payment','1')->where('is_del', '=','F')->count();//已收款总数


        $unPayment = Order::latest()->where('shop_id', $userId)->where('payment','0')->where('is_del', '=','F')->sum('total_price');//未收款金额
        $Payment = Order::latest()->where('shop_id', $userId)->where('payment','1')->where('is_del', '=','F')->sum('total_price');//已收款金额
        $money=$unPayment+$Payment;

        if($money>10000){
            $money = round($money/10000, 2).'万元';
        }

        $orderChart = [
            'ordersCount' => $ordersCount,
            'seedCount' => $seedCount,
            'orderList' =>$ordersList,
            'orderSend' => $ordersSend,
            'orderPayment' => $ordersPayment,
            'unPayment' =>$unPayment.'元',
            'payment' => $Payment.'元',
            'money' => $money
        ];
        return json_encode($orderChart);
    }
    public function weCharts($id){
        $table= Order::where('shop_id',$id)->where('is_del', '=','F')->get(array('prod_id','counts','end_at'));
        foreach ($table as $value){
            $prod[]= $value->prod_id;
        }
        $prodT= Prod::whereIn('id',$prod)->get(['title','id']);
        foreach ($prodT as $value){
            $prodTitle[]= $value->title;
            $prods[]=Order::where('shop_id',$id)->where('prod_id',$value->id)->where('is_del', '=','F')->sum('counts');
        }
        $orderChart = [
            'prod' => $prodTitle,
            'counts' => $prods,
        ];
        return json_encode($orderChart);
    }
}
