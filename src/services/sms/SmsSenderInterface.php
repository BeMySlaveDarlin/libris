<?php

declare(strict_types=1);

namespace app\services\sms;

interface SmsSenderInterface
{
    public function send(string $phone, string $text): SmsResult;
}
