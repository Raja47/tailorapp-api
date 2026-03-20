<?php

namespace App\Services\Notification;

use Aws\Sns\SnsClient;

class SmsService
{
    public function sendOtp($phone, $otp)
    {
        $sns = new SnsClient([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $sns->publish([
            'Message' => "Your OTP is: $otp",
            'PhoneNumber' => $phone,
        ]);
    }
}