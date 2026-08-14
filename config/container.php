<?php

declare(strict_types=1);

use app\services\catalog\BookService;
use app\services\catalog\CatalogService;
use app\services\catalog\ReportService;
use app\services\sms\SmsPilotClient;
use app\services\sms\SmsSenderInterface;
use app\services\storage\CoverStorage;
use app\services\storage\FileStorageInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use yii\di\Container;

$params = require __DIR__ . '/params.php';

return [
    'singletons' => [
        \yii\mail\MailerInterface::class => [
            'class' => \yii\symfonymailer\Mailer::class,
            'useFileTransport' => true,
            'viewPath' => '@app/mail',
        ],
        ClientInterface::class => static fn(): ClientInterface => new Client([
            'timeout' => $params['smsPilot']['timeout'],
        ]),
        SmsSenderInterface::class => static fn(Container $container): SmsSenderInterface => new SmsPilotClient(
            $container->get(ClientInterface::class),
            $params['smsPilot']['endpoint'],
            $params['smsPilot']['apiKey'],
            $params['smsPilot']['sender'],
        ),
        FileStorageInterface::class => static fn(): FileStorageInterface => new CoverStorage(
            (string) Yii::getAlias($params['coverPath']),
            (string) Yii::getAlias($params['coverUrl']),
        ),
        BookService::class => static fn(Container $container): BookService => new BookService(
            Yii::$app->getDb(),
            $container->get(FileStorageInterface::class),
            Yii::$app->get('queue'),
        ),
        CatalogService::class => static fn(): CatalogService => new CatalogService(Yii::$app->getDb()),
        ReportService::class => static fn(): ReportService => new ReportService(
            Yii::$app->getDb(),
            (int) $params['reportAuthorsLimit'],
        ),
    ],
];
