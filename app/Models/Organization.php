<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organization';
    protected $primaryKey = 'org_id';
    public $timestamps = false;

    protected $fillable = [
        'org_id', 'email4', 'password', 'profile', 'date_created', 
        'user_type', 'is_active', 'last_login'
    ];

    protected $hidden = [
        'password',
    ];

    public function info()
    {
        return $this->hasOne(OrgInfo::class, 'organization_id', 'org_id');
    }

    public function organizationFees()
    {
        return $this->hasMany(OrganizationFee::class, 'org_id', 'org_id');
    }
}