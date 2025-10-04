<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $cid;

    public function __construct($otp)
    {
        $this->otp = $otp;
        $this->cid = uniqid(); // Generate unique Content-ID
    }

    public function build()
    {
        return $this->subject('Password Reset Code - EnrollSys')
                    ->view('emails.password-reset')
                    ->with(['cid' => $this->cid]);
    }
}