<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorizationRequest;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\WeappAuthorizationRequest;
use League\OAuth2\Server\AuthorizationServer;
use Zend\Diactoros\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use App\Traits\PassportToken;

class AuthorizationsController extends Controller
{
    use PassportToken;
    public function socialStore(Request $request,$type)
    {
        if (!in_array($type, ['weixin'])) {
            return response()->json([
                'status'=>'false',
                'status_code' => 404,
                'message' => 'errorBadRequest',
            ]);
        }

        $driver = \Socialite::driver($type);

        try {
            if ($code = $request->code) {
                $response = $driver->getAccessTokenResponse($code);
                $token = array_get($response, 'access_token');
            } else {
                $token = $request->access_token;

                if ($type == 'weixin') {
                    $driver->setOpenId($request->openid);
                }
            }

            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'status'=>'false',
                'status_code' => 404,
                'message' => '参数错误，未获取用户信息',
            ]);
        }

        switch ($type) {
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;

                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                // 没有用户，默认创建一个用户
                if (!$user) {
                    $user = User::create([
                        'nickname' => $oauthUser->getNickname(),
                        'avatar_url' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }

                break;
        }
        return response()->json([
            'status'=>'true',
            'message' => '微信登录成功',
            'token' => $user->id
        ],200);
    }
    public function weappStore(WeappAuthorizationRequest $request)
    {
        $code = $request->code;

        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($code);

        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])) {
            return response()->json([
                'status'=>'false',
                'message' => 'code已过期或不正确',
            ],401);
        }

        //找到 openid 对应的用户
        $user = User::where('weapp_openid', $data['openid'])->first();
        //把session_key
        $attributes['weixin_session_key'] = $data['session_key'];

        // 未找到对应用户则需要提交用户名密码进行用户绑定
        if (!$user) {
            // 如果未提交用户名密码，403 错误提示
            $username = $request->username;

            if (!$username) {
                return response()->json([
                    'status'=>'false',
                    'message' => '用户不存在',
                ],403);
            }

            // 用户名可以是邮箱或电话
            filter_var($username, FILTER_VALIDATE_EMAIL) ? $credentials['email'] = $username : $credentials['phone'] = $username;
            $credentials['password'] = $request->password;

            // 验证用户名和密码是否正确
            if (!auth()->attempt($credentials)) {
                return response()->json([
                    'status'=>'false',
                    'message' => '用户名或密码错误',
                ],401);
            }
            // 获取对应的用户
            $user = User::where('phone', $credentials['phone'])->first();
            $attributes['weapp_openid'] = $data['openid'];
        }

        // 更新用户数据
        $user->update($attributes);

        // 为对应用户创建 JWT
        // $token = Auth::guard('api')->fromUser($user);

        // 直接创建token并设置有效期
        $createToken = $user->createToken($user->weapp_openid);

        $createToken->token->expires_at = Carbon::now()->addDays(15);
        $createToken->token->save();

        $token = $createToken->accessToken;

        return response()->json([
            'access_token'=>$token,
            'token_type'=>"Bearer",
            'expires_in' => '21600',
        ],201);

//        return response()->respondWithToken($token)->setStatusCode(201);
    }

    public function weappRegister(WeappAuthorizationRequest $request)
    {
        $code = $request->code;
        $title = $request->shopname;
        $summary = $request->summary;
        $address = $request->address;
        $longitude = $request->longitude;
        $latitude = $request->latitude;

        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($code);

        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])) {
            return response()->json([
                'status'=>'false',
                'message' => 'code已过期或不正确',
            ],401);
        }

        //找到 openid 对应的用户
        $user = User::where('weapp_openid', $data['openid'])->first();
        //把session_key
        $attributes['weixin_session_key'] = $data['session_key'];

        // 未找到对应用户则新建用户
        if (!$user) {
            // 如果未提交用户名密码，403 错误提示
            $phone = $request->phone;

            if (!$username) {
                return response()->json([
                    'status'=>'false',
                    'message' => '用户不存在',
                ],403);
            }

            // 用户名可以是邮箱或电话
            filter_var($username, FILTER_VALIDATE_EMAIL) ? $credentials['email'] = $username : $credentials['phone'] = $username;
            $credentials['password'] = $request->password;

            // 验证用户名和密码是否正确
            if (!auth()->attempt($credentials)) {
                return response()->json([
                    'status'=>'false',
                    'message' => '用户名或密码错误',
                ],401);
            }
            // 获取对应的用户
            $user = User::where('phone', $credentials['phone'])->first();
            $attributes['weapp_openid'] = $data['openid'];
        }

        // 更新用户数据
        $user->update($attributes);

        // 为对应用户创建 JWT
        // $token = Auth::guard('api')->fromUser($user);

        // 直接创建token并设置有效期
        $createToken = $user->createToken($user->weapp_openid);

        $createToken->token->expires_at = Carbon::now()->addDays(15);
        $createToken->token->save();

        $token = $createToken->accessToken;

        return response()->json([
            'access_token'=>$token,
            'token_type'=>"Bearer",
            'expires_in' => '21600',
        ],201);

//        return response()->respondWithToken($token)->setStatusCode(201);
    }


    public function store(AuthorizationRequest $request)
    {
        $username = $request->phone;

        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['phone'] = $username;

        $credentials['password'] = $request->password;

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'status'=>'false',
                'status_code' => 404,
                'message' => '用户名或密码错误',
            ]);
        }

        return $this->respondWithToken($token)->setStatusCode(201);

    }
    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => '21600'
        ]);
    }
    public function update()
    {
        $user = Auth::guard('api')->user();
        $createToken = $user->createToken($user->weapp_openid);

        $createToken->token->expires_at = Carbon::now()->addDays(15);
        $createToken->token->save();

        $token = $createToken->accessToken;
        return response()->json([
            'access_token'=>$token,
            'token_type'=>"Bearer",
            'expires_in' => '21600',
        ]);
    }

    public function destroy()
    {
        if (Auth::guard('api')->check()){
            Auth::guard('api')->user()->token()->revoke();

        }
        return response()->json([
            'status'=>'true',
            'message' => '退出成功',
        ],204);
    }
}
