<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'auditlogs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'details', 'ip_address', 'timestamp', 'access_by'
    ];

    public function user()
    {
        return $this->morphTo();
    }

    public function accessedBy()
    {
        return $this->belongsTo(Admin::class, 'access_by', 'admin_id');
    }
}