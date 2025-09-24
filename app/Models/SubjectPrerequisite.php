<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectPrerequisite extends Model
{
    use HasFactory;

    protected $table = 'subjectprerequisites';
    public $timestamps = false;

    protected $fillable = [
        'subject_id', 'prerequisite_id'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function prerequisite()
    {
        return $this->belongsTo(Subject::class, 'prerequisite_id', 'id');
    }
}