<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request\JsonRequest;
use App\Http\Response\ApiError;
use App\Http\Response\ApiResponse;

final class MethodGuard
{
    /**
     * @param list<string> $allowedMethods
     */
    public static function require(array $allowedMethods, ?JsonRequest $request = null): void
    {
        $allowedMethods = array_values(array_unique(array_map('strtoupper', $allowedMethods)));
        $request ??= JsonRequest::fromGlobals();

        if (in_array($request->method(), $allowedMethods, true)) {
            return;
        }

        if (!headers_sent()) {
            header('Allow: ' . implode(', ', $allowedMethods));
        }

        ApiResponse::error(ApiError::methodNotAllowed($allowedMethods));
    }
}
