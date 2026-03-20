<?php

namespace App\Jobs;

use App\Services\Notification\SmsService;
use Illuminate\Foundation\Bus\Dispatchable;


class SendSmsOtpJob implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels , Dispatchable;

    public $phone;
    public $otp;

    public function __construct($phone, $otp)
    {
        $this->phone = $phone;
        $this->otp = $otp;
    }

    public function handle(SmsService $smsService)
    {
        $smsService->sendOtp($this->phone, $this->otp);
    }
}