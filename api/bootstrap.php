<?php

declare(strict_types=1);

/**
 * API Bootstrap for SaaS Seeder Template.
 */

use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Request\RequestId;
use App\Http\Response\ApiResponse;

error_reporting(E_ALL);
ini_set('display_errors', '0');
date_default_timezone_set('UTC');

require_once __DIR__ . '/../src/config/autoloader.php';

RequestId::initialize();
set_exception_handler(static function (Throwable $exception): void {
    ApiResponse::exception($exception);
});

SecurityHeadersMiddleware::apply();

$allowedOrigins = array_filter(array_map(
    'trim',
    explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '')
));
CorsMiddleware::apply($allowedOrigins, $_ENV['APP_ENV'] ?? 'development');
CorsMiddleware::handlePreflight();

if (!session_id()) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', '1800');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.sid_length', '48');
    ini_set('session.sid_bits_per_character', '6');
    session_start();
}
