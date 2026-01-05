<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Remove this line if present:
// use Illuminate\Database\Eloquent\SoftDeletes;

class Csv extends Model
{
    // Remove this line if present:
    // use SoftDeletes;
    
    protected $table = 'csv';
    public $timestamps = false;
    
    protected $fillable = [
        'application_number',
        'preferred_program',
        'lastname',
        'firstname',
        'middlename',
        'email',
        'contact_number',
        'email_sent'
    ];
    
    // Remove this if present:
    // protected $dates = ['deleted_at'];
}