<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'code', 'name', 'description', 'units', 'year_level', 
        'semester', 'max_students', 'created_by', 'is_active'
    ];

    public function schedules()
    {
        return $this->hasMany(SubjectSchedule::class, 'subject_id', 'id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'subject_id', 'id');
    }

    public function prerequisites()
    {
        return $this->belongsToMany(Subject::class, 'subjectprerequisites', 'subject_id', 'prerequisite_id');
    }

    public function isPrerequisiteFor()
    {
        return $this->belongsToMany(Subject::class, 'subjectprerequisites', 'prerequisite_id', 'subject_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by', 'admin_id');
    }
}