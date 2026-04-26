<?php

declare(strict_types=1);

namespace App\Observability;

final class Logger
{
    public function __construct(private readonly ?RequestContext $context = null)
    {
    }

    public static function fromGlobals(): self
    {
        return new self(RequestContext::fromGlobals());
    }

    /**
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function write(string $level, string $message, array $context): void
    {
        $payload = [
            'level' => $level,
            'message' => $message,
        ];

        if ($this->context !== null) {
            $payload += $this->context->withLogContext();
        }

        if ($context !== []) {
            $payload['context'] = $context;
        }

        error_log((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
