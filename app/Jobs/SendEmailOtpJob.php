<?php

namespace App\Jobs;

use App\Services\Notification\EmailService;
use Illuminate\Foundation\Bus\Dispatchable;


class SendEmailOtpJob implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels , Dispatchable;

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