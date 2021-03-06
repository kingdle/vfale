<?php

return [
	/**
	 * 小程序APPID
	 */
    'appid' => env('WECHAT_MINI_PROGRAM_APPID', ''),
    /**
     * 小程序Secret
     */
    'secret' => env('WECHAT_MINI_PROGRAM_SECRET', ''),
    /**
     * 小程序登录凭证 code 获取 session_key 和 openid 地址，不需要改动
     */
    'code2session_url' => "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",


    'qrcode_url' => "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WECHAT_MINI_PROGRAM_APPID')."&secret=".env('WECHAT_MINI_PROGRAM_SECRET', ''),

    'getwxacodeunlimit_url'=>"https://api.weixin.qq.com/wxa/getwxacode?access_token="
];
