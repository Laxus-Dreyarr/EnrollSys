<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class EnrolledSub extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'enrolled_sub';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'student_id',
        'subject_id',
        'subject_code',
        'subject_name',
        'units',
        'year_level',
        'semester',
        'grade',
        'date_enrolled'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'date_enrolled' => 'datetime',
        'units' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    
     public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }
}


    