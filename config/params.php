<?php

declare(strict_types=1);

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Libris mailer',
    'coverMaxSize' => 5 * 1024 * 1024,
    'coverExtensions' => ['jpg', 'jpeg', 'png', 'webp'],
    'coverPath' => '@webroot/uploads/covers',
    'coverUrl' => '@web/uploads/covers',
    'reportAuthorsLimit' => 10,
    'smsPilot' => [
        'apiKey' => getenv('SMSPILOT_API_KEY') ?: '',
        'sender' => getenv('SMSPILOT_SENDER') ?: 'INFORM',
        'endpoint' => 'https://smspilot.ru/api.php',
        'timeout' => 5,
    ],
];
