<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable; // Add this
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Csv;
use App\Mail\QualifiedForEnrollment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendQualificationEmails implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // Add Batchable

    public $student;
    public $tries = 3;
    public $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param Csv $student
     * @return void
     */
    public function __construct(Csv $student)
    {
        $this->student = $student;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check if batch was cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        try {
            Log::info("Sending qualification email to: " . $this->student->email);
            
            Mail::to($this->student->email)
                ->send(new QualifiedForEnrollment($this->student));
            
            DB::table('csv')
                ->where('id', $this->student->id)
                ->update(['email_sent' => 1]);
            
            Log::info("Email successfully sent to: " . $this->student->email);
            
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$this->student->email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Job failed for student {$this->student->email}: " . $exception->getMessage());
    }
}