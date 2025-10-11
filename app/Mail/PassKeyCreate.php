<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PassKeyCreate extends Mailable
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
        return $this->subject('Passkey - EnrollSys')
                    ->view('emails.passkey')
                    ->with(['cid' => $this->cid]);
    }


}
