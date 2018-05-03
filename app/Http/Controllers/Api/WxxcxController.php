<?php

namespace App\Http\Controllers\Api;

use App\User;
use Iwanli\Wxxcx\Wxxcx;
use Illuminate\Http\Request;
use Auth;

class WxxcxController extends Controller
{
    protected $wxxcx;

    function __construct(Wxxcx $wxxcx)
    {
        $this->wxxcx = $wxxcx;
    }

    /**
     * 小程序登录获取用户信息
     *
     * @author 晚黎
     * @date   2017-05-27T14:37:08+0800
     * @return [type]                   [description]
     */
    public function getWxUserInfo()
    {

//        $weixinSessionKey = Auth::guard('api')->user()->weixin_session_key;
        //code 在小程序端使用 wx.login 获取
        $code = request('code', '');
        //encryptedData 和 iv 在小程序端使用 wx.getUserInfo 获取
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');


        //根据 code 获取用户 session_key 等信息, 返回用户openid 和 session_key
        $userInfo = $this->wxxcx->getLoginInfo($code);

        return $userInfo;
        //获取解密后的用户信息
        $wxinfo=$this->wxxcx->getUserInfo($encryptedData, $iv);

//        $userid = Auth::guard('api')->user()->id;
//        $user = User::find($userid);
//        $attributes['phone'] = $wxinfo['phoneNumber'];
//        // 更新用户数据
//        $user->update($attributes);

        return $wxinfo;
    }
}
