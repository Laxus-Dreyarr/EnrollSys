<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $table = 'instructor';
    protected $primaryKey = 'instructor_id';
    public $timestamps = false;

    protected $fillable = [
        'instructor_id', 'email5', 'password', 'profile', 'date_created', 
        'user_type', 'is_active', 'last_login'
    ];

    protected $hidden = [
        'password',
    ];

    public function info()
    {
        return $this->hasOne(InstructorInfo::class, 'instructor_id', 'instructor_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'instructor_id', 'instructor_id');
    }

    public function notifications()
    {
        return $this->hasMany(NotificationInstructor::class, 'user_id', 'instructor_id');
    }
}