<?php

namespace App\Http\Controllers;

use App\Enum\EskizText;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Eskiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmsVerficationController extends Controller
{
    public function sendOtp(Request $request, Eskiz $eskizService): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'phone'],
        ]);

        $phone = $data['phone'];

        try {
            $otp = mt_rand(100000, 999999);
            $message = sprintf(EskizText::CONFIRM_ORDER->getText(), $otp);
            $eskizService->sendSms($phone, $message);

            Cache::forget("otp_$phone");
            Cache::put("otp_$phone", $otp);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
        ]);
    }


    public function sendRegisterOtp(Request $request, Eskiz $eskizService): JsonResponse
    {
        $validator = Validator::make(
            data: $request->all(),
            rules: [
                'phone' => ['required', 'phone', 'unique:users,phone'],
            ],
            messages: [
                'phone.unique' => [__('validation.unique')]
            ],
            attributes: [
                'phone' => __('messages.phone')
            ]
        );

        $phone = $validator->validated()['phone'];

        try {
            $otp = mt_rand(100000, 999999);
            $message = sprintf(EskizText::REGISTER->getText(), $otp);

            $eskizService->sendSms($phone, $message);

            Cache::forget("otp_$phone");
            Cache::put("otp_$phone", $otp);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function sendLoginOtp(Request $request, Eskiz $eskizService): UserResource|JsonResponse
    {
        $validator = Validator::make(
            data: $request->all(),
            rules: [
                'phone' => ['required', 'phone', 'exists:users,phone'],
            ],
            messages: [
                'phone.exists' => [__('validation.exists')]
            ],
            attributes: [
                'phone' => __('messages.phone')
            ]
        );

        $phone = $validator->validated()['phone'];

        try {
            $otp = mt_rand(100000, 999999);
            $message = sprintf(EskizText::RESET_PASSWORD->getText(), $otp);
            $eskizService->sendSms($phone, $message);

            Cache::forget("otp_$phone");
            Cache::put("otp_$phone", $otp);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function verifyOtp(Request $request): UserResource|JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'phone'],
            'otp' => ['required', 'digits:6'],
        ]);

        $phone = $data['phone'];
        $user = User::query()->where('phone', $phone)->first();

        $otp = Cache::get("otp_$phone");

        if (!$otp || $otp !== $data['otp']) {
            return response()->json([
                'errors' => [
                    'otp' => __('auth.otp')
                ]
            ], 400);
        }

        Cache::forget("otp_{$phone}");

        return response()->json([
            'verified' => true,
            'user_exists' => (bool)$user
        ]);
    }
}
