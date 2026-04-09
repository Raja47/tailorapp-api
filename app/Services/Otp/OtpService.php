<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Redis;

class OtpService
{
    private $ttl;
    private $maxAttempts;
    private $resendCooldown;

    public function __construct()
    {
        $this->ttl = env('OTP_TTL', 150);
        $this->maxAttempts = env('OTP_MAX_ATTEMPTS', 3);
        $this->resendCooldown = env('OTP_RESEND_COOLDOWN', 150);
    }

    public function canSend($type, $identifier)
    {
        return !Redis::exists("otp_resend:$type:$identifier");
    }

    public function createOtp($type, $identifier)
    {
        $otpKey = "otp:$type:$identifier";
        $resendKey = "otp_resend:$type:$identifier";

        if (!$this->canSend($type, $identifier)) {
            return ['success'=>false,'message'=>'Please wait before requesting another OTP'];
        }

        $otp = rand(100000, 999999);

        Redis::setex($otpKey, $this->ttl, $otp);
        Redis::setex($resendKey, $this->resendCooldown, 1);
        Redis::del("otp_attempts:$type:$identifier"); // reset attempts

        return ['success'=>true,'otp'=>$otp];
    }

    public function verifyOtp($type, $identifier, $otp)
    {
        $otpKey = "otp:$type:$identifier";
        $attemptKey = "otp_attempts:$type:$identifier";

        $storedOtp = Redis::get($otpKey);
        if(!$storedOtp){
            return ['success'=>false,'message'=>'OTP expired'];
        }

        $attempts = Redis::get($attemptKey) ?? 0;

        if($attempts >= $this->maxAttempts){
            Redis::del($otpKey);
            return ['success'=>false,'message'=>'Too many attempts'];
        }

        if($storedOtp == $otp){
            Redis::del($otpKey);
            Redis::del($attemptKey);
            return ['success'=>true];
        }

        Redis::incr($attemptKey);
        Redis::expire($attemptKey, $this->ttl);

        return ['success'=>false,'message'=>'Invalid OTP'];
    }
}