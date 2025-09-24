<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationInstructor extends Model
{
    use HasFactory;

    protected $table = 'notifications_instructor';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'title', 'message', 'is_read', 'created_at'
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'user_id', 'instructor_id');
    }
}