<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminInfo extends Model
{
    use HasFactory;

    protected $table = 'admin_info';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'admin_id', 'firstname', 'lastname', 'middlename', 
        'birthdate', 'age', 'address'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }
}