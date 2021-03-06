<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone', 'email', 'password','avatar',
        'country','province','city','district','town','address','villageInfo','latitude','longitude',
        'language','is_active',
        'username', 'weixin_openid', 'weixin_unionid', 'nickname','avatar_url','gender',
        'weixin_session_key','weapp_openid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function shop(){
        return $this->hasOne('App\Shop','user_id','id');
    }
    public function seed(){
        return $this->hasOne('App\Seed','user_id','id');
    }
    public function dynamic(){
        return $this->hasMany('App\Dynamic','user_id','id');
    }
    public function answers(){
        return $this->hasMany(Answer::class);
    }
    public function message(){
        return $this->hasMany('App\Message','user_id','id');
    }
    public function order(){
        return $this->hasMany('App\Order','user_id','id');
    }
    public function findForPassport($username)
    {
        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['phone'] = $username;

        return self::where($credentials)->first();
    }
    public function favorites(){
        return $this->belongsToMany(Shop::class,'favorites')->withTimestamps();
    }
    public function follows(){
        return $this->belongsToMany(Dynamic::class,'user_dynamic')->withTimestamps();
    }
    public function followThis($dynamicId){
        return $this->follows()->toggle($dynamicId);
    }
    public function followed($dynamicId){
        return !! $this->follows()->where('question_id',$dynamicId)->count();
    }
}
