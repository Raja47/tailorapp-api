<?php

namespace App\Jobs;

use App\Services\Notification\EmailService;

class SendEmailOtpJob implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels;

    public $email;
    public $otp;

    public function __construct($email, $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    public function handle(EmailService $emailService)
    {
        $emailService->sendOtp($this->email, $this->otp);
    }
}