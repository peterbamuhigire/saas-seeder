<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'testing';
$_ENV['PASSWORD_PEPPER'] = $_ENV['PASSWORD_PEPPER'] ?? 'test-password-pepper';
$_ENV['COOKIE_ENCRYPTION_KEY'] = $_ENV['COOKIE_ENCRYPTION_KEY'] ?? str_repeat('a', 32);
$_ENV['JWT_SECRET'] = $_ENV['JWT_SECRET'] ?? 'test-jwt-secret';
$_ENV['JWT_REFRESH_HASH_KEY'] = $_ENV['JWT_REFRESH_HASH_KEY'] ?? 'test-refresh-hash-key';
