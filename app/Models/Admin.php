<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    // use HasApiTokens, HasFactory;
    use HasFactory, Notifiable;


    protected $table = 'admin';
    protected $primaryKey = 'admin_id';
    public $timestamps = false;

    protected $fillable = [
        'admin_id', 'email', 'password', 'profile', 'date_created', 
        'user_type', 'is_active', 'last_login'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Specify the guard for this model
    protected $guard = 'admin';

    public function info()
    {
        return $this->hasOne(AdminInfo::class, 'admin_id', 'admin_id');
    }
}