<?php
declare(strict_types=1);

namespace App\Http\Response;

use RuntimeException;

final class ApiError extends RuntimeException
{
    private string $errorCode;
    private int $statusCode;
    private array $details;
    private ?string $documentationUrl;

    public function __construct(
        string $errorCode,
        string $message,
        int $statusCode = 400,
        array $details = [],
        ?string $documentationUrl = null
    ) {
        parent::__construct($message, $statusCode);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->details = $details;
        $this->documentationUrl = $documentationUrl;
    }

    public static function invalidJson(string $message = 'Malformed JSON body'): self
    {
        return new self('REQUEST_MALFORMED_JSON', $message, 400);
    }

    public static function methodNotAllowed(array $allowedMethods): self
    {
        return new self(
            'REQUEST_METHOD_NOT_ALLOWED',
            'Method not allowed',
            405,
            ['allowed_methods' => array_values($allowedMethods)]
        );
    }

    public static function unauthorized(string $message = 'Authentication required'): self
    {
        return new self('AUTH_UNAUTHORIZED', $message, 401);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function details(): array
    {
        return $this->details;
    }

    public function documentationUrl(): string
    {
        return $this->documentationUrl ?? '/docs/api/errors#' . $this->errorCode;
    }
}
