<?php

declare(strict_types=1);

$db = require __DIR__ . '/db.php';
$db['dsn'] = getenv('DB_TEST_DSN') ?: 'mysql:host=mysql;dbname=libris_test';

return $db;
