<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationFee extends Model
{
    use HasFactory;

    protected $table = 'organizationfees';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'org_id', 'student_id', 'amount', 'payment_date', 'status', 
        'receipt_url', 'red_flag', 'red_flag_reason'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'org_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}