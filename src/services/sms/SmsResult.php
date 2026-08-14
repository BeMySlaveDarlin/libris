<?php

declare(strict_types=1);

namespace app\services\sms;

final readonly class SmsResult
{
    private function __construct(
        public bool $success,
        public ?string $messageId,
        public ?string $error,
    ) {
    }

    public static function sent(?string $messageId): self
    {
        return new self(true, $messageId, null);
    }

    public static function failed(string $error): self
    {
        return new self(false, null, $error);
    }
}
