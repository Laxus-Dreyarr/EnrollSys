<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Csv;

class QualifiedForEnrollment extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $applicationNumber;
    public $program;
    public $name;

    /**
     * Create a new message instance.
     *
     * @param Csv $student
     * @return void
     */
    public function __construct(Csv $student)
    {
        $this->student = $student;
        $this->applicationNumber = $student->application_number;
        $this->program = $student->preferred_program;
        $this->name = $student->firstname . ' ' . $student->lastname;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Qualified for Enrollment at EVSU Ormoc Campus')
                    ->view('emails.qualified-enrollment')
                    ->with([
                        'student' => $this->student,
                        'applicationNumber' => $this->applicationNumber,
                        'program' => $this->program,
                        'name' => $this->name,
                    ]);
    }
}