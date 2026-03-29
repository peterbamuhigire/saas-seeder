<?php

declare(strict_types=1);

/**
 * API Bootstrap for SaaS Seeder Template
 *
 * Include at the top of API endpoints for standardized configuration.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

date_default_timezone_set('UTC');

// Load autoloader
require_once __DIR__ . '/../src/config/autoloader.php';

// Security headers for API responses
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS — configurable origins from .env, dev fallback to wildcard
$allowedOrigins = array_filter(array_map('trim',
    explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '')
));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$appEnv = $_ENV['APP_ENV'] ?? 'development';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    } elseif ($appEnv === 'development') {
        header('Access-Control-Allow-Origin: *');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit;
}

// Set CORS for non-preflight requests too
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} elseif ($appEnv === 'development') {
    header('Access-Control-Allow-Origin: *');
}

// Set JSON content type
header('Content-Type: application/json; charset=utf-8');

// Configure secure session for API
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

// Helper function for JSON response
if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, $data = null, string $message = '', int $statusCode = 200): void {
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Helper function for error response
if (!function_exists('errorResponse')) {
    function errorResponse(string $message, int $statusCode = 400, $errors = null): void {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Set global exception handler for uncaught exceptions
set_exception_handler(function ($exception) {
    error_log('API Exception: ' . $exception->getMessage());
    errorResponse('Internal server error', 500);
});
