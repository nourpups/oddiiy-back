<?php

namespace App\Http\Controllers;

use App\Action\SendOtpMessageAction;
use App\Enum\EskizText;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Eskiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SmsVerficationController extends Controller
{
    public function __construct(protected SendOtpMessageAction $sendOtpMessageAction)
    {
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'phone'],
        ]);

        $phone = $data['phone'];

        try {
            ($this->sendOtpMessageAction)($phone);
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


    public function sendRegisterOtp(Request $request): JsonResponse
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
            ($this->sendOtpMessageAction)($phone);
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

    public function sendLoginOtp(Request $request): UserResource|JsonResponse
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
            ($this->sendOtpMessageAction)($phone);
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
