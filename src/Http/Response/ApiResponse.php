<?php
declare(strict_types=1);

namespace App\Http\Response;

use App\Http\Request\RequestId;
use Throwable;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $statusCode = 200): never
    {
        $payload = [
            'success' => true,
            'request_id' => RequestId::current(),
        ];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        self::send($payload, $statusCode);
    }

    public static function error(ApiError $error): never
    {
        $payload = [
            'success' => false,
            'error' => [
                'code' => $error->errorCode(),
                'message' => $error->getMessage(),
                'details' => (object) $error->details(),
                'documentation_url' => $error->documentationUrl(),
            ],
            'request_id' => RequestId::current(),
        ];

        self::send($payload, $error->statusCode());
    }

    public static function exception(Throwable $exception): never
    {
        if ($exception instanceof ApiError) {
            self::error($exception);
        }

        error_log(sprintf(
            'API Exception [%s]: %s',
            RequestId::current(),
            $exception->getMessage()
        ));

        self::error(new ApiError('INTERNAL_SERVER_ERROR', 'Internal server error', 500));
    }

    public static function send(array $payload, int $statusCode = 200): never
    {
        if (!array_key_exists('request_id', $payload)) {
            $payload['request_id'] = RequestId::current();
        }

        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
