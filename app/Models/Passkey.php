<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passkey extends Model
{
    use HasFactory;

    protected $table = 'passkeys';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'passkey', 'email3', 'created_by', 'date_created', 
        'expiration_date', 'is_used', 'user_type'
    ];

    public function creator()
    {
        return $this->belongsTo(AdminInfo::class, 'created_by', 'id');
    }
}