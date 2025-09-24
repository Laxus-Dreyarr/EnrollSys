<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectSchedule extends Model
{
    use HasFactory;

    protected $table = 'subjectschedules';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'subject_id', 'Section', 'Type', 'day', 'start_time', 
        'end_time', 'room'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'subsched_id', 'id');
    }
}