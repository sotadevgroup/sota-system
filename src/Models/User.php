<?php

namespace Sota\System\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as BaseUser;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseUser implements JWTSubject
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email'
    ];

    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function getName() {
        return trim($this->first_name.' '.$this->last_name);
    }
}
