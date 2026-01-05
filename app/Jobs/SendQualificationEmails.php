<?php

namespace App\Jobs;

use App\Models\CsvStudent;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendQualificationEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $student;

    public function __construct(CsvStudent $student)
    {
        $this->student = $student;
    }

    public function handle()
    {
        // Send individual email
        \Mail::to($this->student->email)->send(
            new \App\Mail\QualificationNotification($this->student)
        );
    }
}
