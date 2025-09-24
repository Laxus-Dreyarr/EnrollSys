<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'type', 'file_path', 'upload_date', 'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}