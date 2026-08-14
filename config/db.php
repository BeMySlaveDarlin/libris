<?php

declare(strict_types=1);

return [
    'class' => \yii\db\Connection::class,
    'dsn' => getenv('DB_DSN') ?: 'mysql:host=mysql;dbname=libris',
    'username' => getenv('DB_USERNAME') ?: 'libris',
    'password' => getenv('DB_PASSWORD') ?: 'libris',
    'charset' => 'utf8mb4',
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
