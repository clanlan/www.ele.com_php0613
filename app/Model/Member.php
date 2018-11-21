<?php

namespace App\Model;

use App\User;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Member extends User
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password','tel','sms',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
