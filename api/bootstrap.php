<?php

declare(strict_types=1);

/**
 * API Bootstrap for SaaS Seeder Template.
 *
 * Temporary compatibility helpers remain for existing v1 auth endpoints until
 * Phase 04 endpoint rewrites remove global helper usage.
 */

use App\Config\Database;
use App\Http\Middleware\BearerAuth;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\MethodGuard;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Request\JsonRequest;
use App\Http\Request\RequestId;
use App\Http\Response\ApiError;
use App\Http\Response\ApiResponse;

error_reporting(E_ALL);
ini_set('display_errors', '0');
date_default_timezone_set('UTC');

require_once __DIR__ . '/../src/config/autoloader.php';

RequestId::initialize();
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

set_exception_handler(static function (Throwable $exception): void {
    ApiResponse::exception($exception);
});

if (!function_exists('api_error_code_for_status')) {
    function api_error_code_for_status(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'REQUEST_INVALID',
            401 => 'AUTH_UNAUTHORIZED',
            403 => 'AUTH_FORBIDDEN',
            404 => 'RESOURCE_NOT_FOUND',
            405 => 'REQUEST_METHOD_NOT_ALLOWED',
            409 => 'RESOURCE_CONFLICT',
            422 => 'VALIDATION_FAILED',
            423 => 'AUTH_ACCOUNT_LOCKED',
            500 => 'INTERNAL_SERVER_ERROR',
            default => $statusCode >= 500 ? 'INTERNAL_SERVER_ERROR' : 'REQUEST_INVALID',
        };
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, mixed $data = null, string $message = '', int $statusCode = 200): void
    {
        if ($success) {
            ApiResponse::success($data, $message, $statusCode);
        }

        ApiResponse::error(new ApiError(
            api_error_code_for_status($statusCode),
            $message !== '' ? $message : 'Request failed',
            $statusCode,
            is_array($data) ? $data : []
        ));
    }
}

if (!function_exists('errorResponse')) {
    function errorResponse(string $message, int $statusCode = 400, mixed $errors = null): void
    {
        if ($statusCode === 405 && !headers_sent()) {
            header('Allow: POST');
        }

        ApiResponse::error(new ApiError(
            api_error_code_for_status($statusCode),
            $message,
            $statusCode,
            is_array($errors) ? $errors : ($errors === null ? [] : ['errors' => $errors])
        ));
    }
}

if (!function_exists('json_response')) {
    function json_response(int $statusCode, array $payload): void
    {
        if (($payload['success'] ?? false) === true) {
            ApiResponse::success(
                $payload['data'] ?? null,
                (string) ($payload['message'] ?? ''),
                $statusCode
            );
        }

        ApiResponse::error(new ApiError(
            (string) ($payload['error']['code'] ?? api_error_code_for_status($statusCode)),
            (string) ($payload['error']['message'] ?? $payload['message'] ?? 'Request failed'),
            $statusCode,
            is_array($payload['errors'] ?? null) ? $payload['errors'] : []
        ));
    }
}

if (!function_exists('require_method')) {
    function require_method(string ...$methods): void
    {
        MethodGuard::require($methods);
    }
}

if (!function_exists('read_json_body')) {
    function read_json_body(): array
    {
        return JsonRequest::fromGlobals()->jsonBody();
    }
}

if (!function_exists('bearer_token')) {
    function bearer_token(): ?string
    {
        return BearerAuth::token();
    }
}

if (!function_exists('get_db')) {
    function get_db(): PDO
    {
        static $db = null;

        if (!$db instanceof PDO) {
            $db = (new Database())->getConnection();
        }

        return $db;
    }
}

if (!function_exists('require_auth')) {
    function require_auth(): array
    {
        $claims = BearerAuth::requireClaims('access');

        return [
            'user_id' => (int) ($claims['sub'] ?? 0),
            'franchise_id' => isset($claims['fid']) ? (int) $claims['fid'] : null,
            'claims' => $claims,
        ];
    }
}
