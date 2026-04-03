<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Otp\OtpService;
use App\Services\Notification\EmailService;
use App\Services\Notification\SmsService;
use App\Jobs\SendEmailOtpJob;
use App\Jobs\SendSmsOtpJob;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'new_registration' => 'required|boolean',
            'type' => 'required|in:email,phone',
            'identifier' => 'required'
        ]);
        $type = $request->type;
        if($type == 'email'){
            $exists = \App\Models\Tailor::where('email', $request->identifier)->exists();
        } else {
            $exists = \App\Models\Tailor::where('number', $request->identifier)->exists();
        }

        if($request->new_registration && $exists){
            return response()->json(['success'=>false, 'message'=>"Tailor already exists with this $type"], 422);
        }

        if(!$request->new_registration && !$exists){
            return response()->json(['success'=>false, 'message'=>"No tailor found with this $type"], 422);
        }

        

        $result = $this->otpService->createOtp($request->type, $request->identifier);

        if(!$result['success']){
            return response()->json($result, 429);
        }

        $otp = $result['otp'];

        // Dispatch sending to queue
        if($request->type === 'email'){
            SendEmailOtpJob::dispatch($request->identifier, $otp);
        } else {
            SendSmsOtpJob::dispatch($request->identifier, $otp);
        }

        return response()->json(['message'=>'OTP sent']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,phone',
            'identifier' => 'required',
            'otp' => 'required'
        ]);

        $result = $this->otpService->verifyOtp(
            $request->type,
            $request->identifier,
            $request->otp
        );

        if(!$result['success']){
            return response()->json($result, 422);
        }

        return response()->json(['message'=>'OTP verified']);
    }
}