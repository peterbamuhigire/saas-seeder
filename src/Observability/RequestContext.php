<?php

declare(strict_types=1);

namespace App\Observability;

use App\Http\Request\RequestId;

final class RequestContext
{
    public function __construct(
        private readonly string $requestId,
        private readonly string $method,
        private readonly string $path,
        private readonly ?string $ipAddress,
        private readonly ?string $userAgent
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = $uri === '' ? 'cli' : ((string) parse_url($uri, PHP_URL_PATH) ?: 'unknown');

        return new self(
            RequestId::current(),
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'CLI')),
            $path,
            self::nullable($_SERVER['REMOTE_ADDR'] ?? null),
            self::nullable($_SERVER['HTTP_USER_AGENT'] ?? null)
        );
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @param array<string, mixed> $details
     * @return array<string, mixed>
     */
    public function withAuditDetails(array $details = []): array
    {
        return $details + [
            'request_id' => $this->requestId,
            'request_method' => $this->method,
            'request_path' => $this->path,
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function withLogContext(array $context = []): array
    {
        return $context + [
            'request_id' => $this->requestId,
            'request_method' => $this->method,
            'request_path' => $this->path,
            'ip_address' => $this->ipAddress,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
