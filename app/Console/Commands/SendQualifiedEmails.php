<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Csv;
use App\Jobs\SendQualificationEmails;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class SendQualifiedEmails extends Command
{
    protected $signature = 'emails:send-qualified {--batch=100} {--test}';
    protected $description = 'Send qualification emails to qualified students';

    public function handle()
    {
        $batchSize = $this->option('batch');
        $isTest = $this->option('test');
        
        // Get qualified students who haven't received emails yet
        $query = Csv::where(function($q) {
            $q->where('email_sent', 0)
              ->orWhereNull('email_sent');
        });
        
        if ($isTest) {
            // Send test email to first student only
            $student = $query->first();
            if ($student) {
                $this->info("Sending test email to: {$student->email}");
                SendQualificationEmails::dispatchSync($student);
                $this->info("Test email sent!");
                return;
            }
        }
        
        $total = $query->count();
        $this->info("Found {$total} qualified students to notify.");
        
        if ($total === 0) {
            $this->info("No students to notify.");
            return;
        }
        
        $this->info("Processing in batches of {$batchSize}...");
        
        // Process in batches to avoid memory issues
        $jobs = [];
        $sentCount = 0;
        
        // Pass $total to the closure using the 'use' keyword
        $query->chunk($batchSize, function ($students) use (&$jobs, &$sentCount, $total) {
            foreach ($students as $student) {
                $jobs[] = new SendQualificationEmails($student);
                $sentCount++;
            }
            
            $this->info("Queued {$sentCount}/{$total} students...");
        });
        
        // Dispatch all jobs as a batch
        $batch = Bus::batch($jobs)
            ->name('Qualification Emails')
            ->dispatch();
        
        $this->info("✅ All {$sentCount} emails have been queued!");
        $this->info("Batch ID: {$batch->id}");
        $this->info("Run 'php artisan queue:work' to process the emails.");
    }
}