<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

    public function build()
    {
        $css = file_get_contents(public_path('css/email.css')); // Your CSS file
        $html = view('emails.password-reset', ['otp' => $this->otp])->render();
        
        $inliner = new CssToInlineStyles();
        $html = $inliner->convert($html, $css);
        
        return $this->html($html)
                    ->subject('Password Reset Code - EnrollSys');
    }
}