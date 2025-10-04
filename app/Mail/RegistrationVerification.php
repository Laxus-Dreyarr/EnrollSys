<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationCode;
    public $cid;
    // public $name;

    // public function __construct($verificationCode, $name = null)
    // {
    //     $this->verificationCode = $verificationCode;
    //     $this->name = $name;
    // }

    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;
        $this->cid = uniqid();
    }

    // public function build()
    // {
    //     return $this->subject('Email Verification - EnrollSys')
    //                 ->view('emails.registration-verification')
    //                 ->with([
    //                     'verificationCode' => $this->verificationCode,
    //                     'name' => $this->name,
    //                 ]);
    // }

    public function build()
    {
        return $this->subject('Email Verification - EnrollSys')
                    ->view('emails.registration-verification')
                    ->with(['cid' => $this->cid]);
    }
}
