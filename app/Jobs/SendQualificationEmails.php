<?php

namespace App\Jobs;

use App\Models\Csv;  // Changed from CsvStudent to Csv
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\QualificationEmail;

class SendQualificationEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $student;

    /**
     * Create a new job instance.
     */
    public function __construct(Csv $student)  // Changed to Csv
    {
        $this->student = $student;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send email to student
        Mail::to($this->student->email)
            ->send(new QualificationEmail($this->student));
        
        // Update status to mark email as sent
        $this->student->update(['email_sent' => true]);
    }
}