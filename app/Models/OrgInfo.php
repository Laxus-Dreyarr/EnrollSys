<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgInfo extends Model
{
    use HasFactory;

    protected $table = 'orgs_info';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'organization_id', 'firstname', 'lastname', 'middlename', 
        'birthdate', 'age', 'address'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'org_id');
    }
}