<?php
declare(strict_types=1);

namespace App\Http\Request;

use App\Http\Response\ApiError;

final class JsonRequest
{
    public function __construct(
        private readonly string $method,
        private readonly array $server,
        private readonly string $rawBody
    ) {
    }

    public static function fromGlobals(): self
    {
        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER,
            (string) file_get_contents('php://input')
        );
    }

    public function method(): string
    {
        return strtoupper($this->method);
    }

    public function jsonBody(): array
    {
        if (trim($this->rawBody) === '') {
            return [];
        }

        $decoded = json_decode($this->rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw ApiError::invalidJson('Invalid JSON body');
        }

        return $decoded;
    }

    public function bearerToken(): ?string
    {
        $header = $this->server['HTTP_AUTHORIZATION']
            ?? $this->server['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', trim((string) $header), $matches) !== 1) {
            return null;
        }

        $token = trim($matches[1]);
        return $token !== '' ? $token : null;
    }
}
