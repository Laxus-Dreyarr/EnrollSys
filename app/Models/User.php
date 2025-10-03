<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'email2', 'password', 'profile', 'date_created', 
        'user_type', 'is_active', 'last_login'
    ];
    
    protected $hidden = [
        'password',
    ];
}