<?php
declare(strict_types=1);

namespace App\Http\Request;

final class RequestId
{
    private const HEADER = 'X-Request-Id';

    public static function current(): string
    {
        if (!empty($GLOBALS['api_request_id']) && is_string($GLOBALS['api_request_id'])) {
            return $GLOBALS['api_request_id'];
        }

        return self::initialize();
    }

    public static function initialize(?string $candidate = null): string
    {
        $requestId = self::normalize($candidate ?? self::headerValue()) ?? self::generate();
        $GLOBALS['api_request_id'] = $requestId;

        if (!headers_sent()) {
            header(self::HEADER . ': ' . $requestId);
        }

        return $requestId;
    }

    private static function headerValue(): ?string
    {
        return $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
    }

    private static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || strlen($value) > 128) {
            return null;
        }

        return preg_match('/^[A-Za-z0-9._:-]+$/', $value) === 1 ? $value : null;
    }

    private static function generate(): string
    {
        return bin2hex(random_bytes(16));
    }
}
