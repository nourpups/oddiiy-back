<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Eskiz
{
    protected string $token;

    public function __construct()
    {
        if (! Cache::has('eskiz_token')) {
            $this->getToken();
        }

        $this->token = Cache::get('eskiz_token');
    }

    public function getToken(): void
    {
        $response = Http::post('https://notify.eskiz.uz/api/auth/login', [
            'email' => config('eskiz.email'),
            'password' => config('eskiz.password'),
        ]);

        if ($response->successful()) {
            $token = $response->json()['data']['token'];
            Cache::put('eskiz_token', $token, now()->addMinutes(59));
            Log::info('Eskiz token olindi!');
        } else {
            Log::error('Eskiz token olishda xatolik: '.$response->body());
            throw new \Exception('Eskiz token olishda xatolik: '.$response->body());
        }

    }

    public function sendSms($phone, $message)
    {
        $phone = preg_replace('/\D/', '', $phone);

        $response = Http::withToken($this->token)
            ->post('https://notify.eskiz.uz/api/message/sms/send', [
                'mobile_phone' => $phone,
                'message' => $message,
            ]);

        if ($response->status() === 401) {
            $this->getToken();

            return $this->sendSms($phone, $message);
        }

        if (!$response->successful()) {
            Log::error('SMS yuborishda xatolik: '.$response->body());
            throw new \Exception('SMS yuborishda xatolik: '.$response->body());
        }

        Log::info('SMS muvaffaqiyatli yuborildi: '.json_encode($response->json()));

        return $response->json();
    }
}

