<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentRequest extends Model
{
    use HasFactory;

    protected $table = 'enrollmentrequests';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'request_date', 'status', 'instructor_id', 
        'processed_date', 'rejection_reason'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }
}