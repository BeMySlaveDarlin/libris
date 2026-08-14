<?php

declare(strict_types=1);

namespace app\tests\Support\Fake;

use app\services\sms\SmsResult;
use app\services\sms\SmsSenderInterface;

final class FailingSmsSender implements SmsSenderInterface
{
    public function __construct(private readonly string $error)
    {
    }

    public function send(string $phone, string $text): SmsResult
    {
        return SmsResult::failed($this->error);
    }
}
