<?php

namespace App\Http\Controllers;

use Auth;
use App\Album;
use App\Dynamic;
use App\Order;
use Illuminate\Http\Request;

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
        $seedCount = Order::latest()->where('to_user_id', $userId)->where('is_del', '=','F')->sum('count');//苗子总数
//        $moneyCount = Order::latest()->where('user_id', $userId)->count();//订单总金额

        $orderChart = [
            'ordersCount' => $ordersCount,
            'seedCount' => $seedCount,
        ];
        return json_encode($orderChart);
    }
}
