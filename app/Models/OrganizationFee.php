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
        'org_id',
        'student_id',
        'year_level',
        'amount',
        'status',
        'receipt_url',
        'notes',
        'uploaded_date'
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