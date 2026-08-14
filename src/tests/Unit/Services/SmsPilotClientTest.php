<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\services\sms\SmsPilotClient;
use Codeception\Test\Unit;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class SmsPilotClientTest extends Unit
{
    public function testReturnsMessageIdOnSuccess(): void
    {
        $client = $this->client(new Response(200, [], json_encode([
            'send' => [['server_id' => '4242', 'phone' => '79001234567', 'status' => '0']],
        ])));

        $result = $client->send('+79001234567', 'Новая книга');

        $this->assertTrue($result->success);
        $this->assertSame('4242', $result->messageId);
    }

    public function testReturnsProviderErrorDescription(): void
    {
        $client = $this->client(new Response(200, [], json_encode([
            'error' => ['code' => 101, 'description_ru' => 'Неверный APIKEY'],
        ])));

        $result = $client->send('+79001234567', 'Новая книга');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Неверный APIKEY', (string) $result->error);
    }

    public function testFailsGracefullyOnTransportError(): void
    {
        $client = $this->client(new ConnectException('timeout', new Request('GET', 'https://smspilot.ru/api.php')));

        $result = $client->send('+79001234567', 'Новая книга');

        $this->assertFalse($result->success);
        $this->assertNull($result->messageId);
    }

    public function testFailsOnNonJsonResponse(): void
    {
        $result = $this->client(new Response(200, [], 'gateway is down'))->send('+79001234567', 'Текст');

        $this->assertFalse($result->success);
    }

    private function client(Response|ConnectException $queued): SmsPilotClient
    {
        $handler = HandlerStack::create(new MockHandler([$queued]));

        return new SmsPilotClient(
            new Client(['handler' => $handler]),
            'https://smspilot.ru/api.php',
            'test-key',
            'INFORM',
        );
    }
}
