<?php

declare(strict_types=1);

namespace app\tests\Support\Fake;

use app\services\sms\SmsResult;
use app\services\sms\SmsSenderInterface;

final class RecordingSmsSender implements SmsSenderInterface
{
    /** @var list<array{phone: string, text: string}> */
    public array $sent = [];

    public function send(string $phone, string $text): SmsResult
    {
        $this->sent[] = ['phone' => $phone, 'text' => $text];

        return SmsResult::sent('server-' . count($this->sent));
    }
}
