<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendOtp($email, $otp)
    {
        Mail::raw("Your OTP is: $otp", function($msg) use ($email){
            $msg->to($email)
                ->subject('OTP Verification');
        });
    }
}