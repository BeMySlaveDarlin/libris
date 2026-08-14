<?php

declare(strict_types=1);

namespace app\services\sms;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

final readonly class SmsPilotClient implements SmsSenderInterface
{
    public function __construct(
        private ClientInterface $http,
        private string $endpoint,
        private string $apiKey,
        private string $sender,
    ) {
    }

    public function send(string $phone, string $text): SmsResult
    {
        try {
            $response = $this->http->request('GET', $this->endpoint, [
                'query' => [
                    'send' => $text,
                    'to' => $phone,
                    'from' => $this->sender,
                    'apikey' => $this->apiKey,
                    'format' => 'json',
                ],
            ]);
        } catch (GuzzleException $exception) {
            return SmsResult::failed($exception->getMessage());
        }

        return $this->parse((string) $response->getBody());
    }

    private function parse(string $body): SmsResult
    {
        $payload = json_decode($body, true);
        if (!is_array($payload)) {
            return SmsResult::failed('Некорректный ответ провайдера: ' . $body);
        }

        if (isset($payload['error'])) {
            $error = $payload['error'];
            $description = is_array($error) ? ($error['description_ru'] ?? $error['description'] ?? '') : (string) $error;

            return SmsResult::failed('SMSPilot: ' . $description);
        }

        $message = $payload['send'][0] ?? null;

        return SmsResult::sent(isset($message['server_id']) ? (string) $message['server_id'] : null);
    }
}
