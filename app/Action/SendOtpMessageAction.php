<?php

namespace App\Action;

use App\Enum\EskizText;
use App\Services\Eskiz;
use Illuminate\Support\Facades\Cache;

class SendOtpMessageAction
{
    public function __construct(protected Eskiz $eskiz)
    {
    }

    public function __invoke(string $phone): void
    {
        $otp = mt_rand(100000, 999999);
        $message = sprintf(EskizText::CONFIRM_ORDER->getText(), $otp);

//        defer(fn () => $this->eskiz->sendSms($phone, $message));

        Cache::forget("otp_$phone");
        Cache::put("otp_$phone", 111111);
    }
}
