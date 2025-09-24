<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorInfo extends Model
{
    use HasFactory;

    protected $table = 'instructor_info';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'instructor_id', 'firstname', 'lastname', 'middlename', 
        'birthdate', 'age', 'address', 'department'
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id', 'instructor_id');
    }
}