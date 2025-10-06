<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'students';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'student_id', 'id_no'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function userInfo()
    {
        return $this->belongsTo(UserInfo::class, 'student_id', 'id');
    }
}