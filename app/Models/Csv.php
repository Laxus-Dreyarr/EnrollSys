<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Csv extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'csv';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_number',
        'preferred_program',
        'lastname',
        'firstname',
        'middlename',
        'email',
        'contact_number',
        'email_sent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_sent' => 'boolean',
    ];

    /**
     * Scope a query to only include students who haven't received emails.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotNotified($query)
    {
        return $query->where(function($q) {
            $q->where('email_sent', false)
              ->orWhereNull('email_sent');
        });
    }

    /**
     * Get the student's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $middleInitial = $this->middlename ? ' ' . substr($this->middlename, 0, 1) . '.' : '';
        return $this->firstname . $middleInitial . ' ' . $this->lastname;
    }

    /**
     * Get the student's formal name (Lastname, Firstname Middleinitial).
     *
     * @return string
     */
    public function getFormalNameAttribute()
    {
        $middleInitial = $this->middlename ? ' ' . substr($this->middlename, 0, 1) . '.' : '';
        return $this->lastname . ', ' . $this->firstname . $middleInitial;
    }

    /**
     * Mark email as sent for this student.
     *
     * @return bool
     */
    public function markEmailAsSent()
    {
        return $this->update([
            'email_sent' => true,
            'email_sent_at' => now(),
        ]);
    }

    /**
     * Get the enrollment link for this student.
     *
     * @return string
     */
    public function getEnrollmentLinkAttribute()
    {
        return route('enrollment.form', [
            'id' => $this->id,
            'code' => $this->generateEnrollmentCode(),
        ]);
    }

    /**
     * Generate a unique enrollment code for the student.
     *
     * @return string
     */
    private function generateEnrollmentCode()
    {
        return 'ENROLL-' . strtoupper(substr($this->lastname, 0, 3)) . 
               '-' . $this->application_number . 
               '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if student can enroll (within deadline).
     *
     * @return bool
     */
    public function canEnroll()
    {
        // Default enrollment deadline is 30 days from email sent date
        if (!$this->email_sent_at) {
            return false;
        }

        $deadline = $this->email_sent_at->addDays(30);
        return now()->lte($deadline);
    }

    /**
     * Get days remaining for enrollment.
     *
     * @return int|null
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->email_sent_at) {
            return null;
        }

        $deadline = $this->email_sent_at->addDays(30);
        return now()->diffInDays($deadline, false); // Negative if past deadline
    }
}