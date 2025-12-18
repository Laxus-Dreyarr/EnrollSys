<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendQuestion extends Mailable
{
    use Queueable, SerializesModels;

    public $q_name;
    public $q_email;
    public $q_subject;
    public $q_ms;

    public function __construct($q_name, $q_email, $q_subject, $q_ms)
    {
        $this->q_name = $q_name;
        $this->q_email = $q_email;
        $this->q_subject = $q_subject;
        $this->q_ms = $q_ms;
    }

    public function build()
    {
        return $this->subject('Question - EnrollSys')
                    ->view('emails.send-question')
                    ->with([
                        'q_name' => $this->q_name,
                        'q_email' => $this->q_email,
                        'q_subject' => $this->q_subject,
                        'q_ms' => $this->q_ms,
                    ]);
    }
}