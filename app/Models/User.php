<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower($this->attributes['email']));
        return 'https://www.wufu-app.com/static/index/default/images/20200821/ef5fce3d873619b99223514aee544ca1.jpg';
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }
    public function feed()
    {
        $ids = $this->followings->pluck('id')->toArray();
        array_push($ids, $this->id);
        return Status::whereIn('user_id', $ids)->with('user')->orderBy('created_at', 'desc');
        // return $this->statuses()->orderBy('created_at','desc');
    }

    //获取粉丝列表
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }
    //获取关注人列表
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    //关注
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        return $this->followings()->sync($user_ids, false);
    }

    //取消关注
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids, false);
    }
    //判断是否在关注列表里
    public function isFollowing($user_id)
    {
        // dump($this->followers());
        // dump($this->followers);

        return $this->followings->contains($user_id);
    }
}
