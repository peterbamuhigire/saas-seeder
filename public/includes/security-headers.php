<?php
/**
 * Security headers — include early in every page (before any output).
 */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-store');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'development';
if ($appEnv === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

header("Content-Security-Policy-Report-Only: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://rsms.me; img-src 'self' data:; font-src 'self' https://rsms.me; connect-src 'self'; frame-ancestors 'none'; object-src 'none'");
