<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    public $user;
    public $mailTo;
    public $appLogo;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details,$user,$mailTo,$appLogo)
    {
        $this->details = $details;
        $this->user = $user;
        $this->mailTo = $mailTo;
        $this->appLogo = $appLogo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->subject('Mail from Saas Taxi')->view('taxi.email.code')->with(['details' => $this->details, 'user' => $this->user,'mailTo' => $this->mailTo,'appLogo' => $this->appLogo]);
    }
}
