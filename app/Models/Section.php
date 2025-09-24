<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'subsched_id', 'section_name', 'instructor_id', 'max_students', 'current_students'
    ];

    public function subjectSchedule()
    {
        return $this->belongsTo(SubjectSchedule::class, 'subsched_id', 'id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'section_id', 'id');
    }
}