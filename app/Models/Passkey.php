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


    // Add this - it tells Laravel to treat these as Carbon instances
    protected $casts = [
        'expiration_date' => 'datetime',
        'date_created' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(AdminInfo::class, 'created_by', 'id');
    }

     /**
     * Scope a query to only include expired passkeys.
     * This checks the expiration_date column against current time
     */
    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<=', now()->setTimezone('Asia/Manila'));
    }

    /**
     * Scope a query to only include valid (non-expired) passkeys.
     */
    public function scopeValid($query)
    {
        return $query->where('expiration_date', '>', now());
    }

    /**
     * Check if this specific passkey instance is expired.
     */
    public function isExpired()
    {
        return now()->greaterThan($this->expiration_date);
    }

    /**
     * Check if this specific passkey instance is still valid.
     */
    public function isValid()
    {
        return now()->lessThanOrEqualTo($this->expiration_date);
    }

    
    
}