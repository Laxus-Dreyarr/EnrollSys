<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'student_id', 'id_no', 'year_level', 'status', 'is_regular'
    ];
    
    public function userInfo()
    {
        return $this->belongsTo(UserInfo::class, 'student_id', 'user_id');
    }
}