<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request\JsonRequest;
use App\Http\Response\ApiError;

final class BearerAuth
{
    public static function token(?JsonRequest $request = null): ?string
    {
        $request ??= JsonRequest::fromGlobals();
        return $request->bearerToken();
    }

    public static function requireToken(?JsonRequest $request = null): string
    {
        $token = self::token($request);
        if ($token === null) {
            throw ApiError::unauthorized('Bearer token is required');
        }

        return $token;
    }

    public static function requireClaims(string $tokenType = 'access', ?JsonRequest $request = null): array
    {
        $token = self::requireToken($request);
        $jwtService = 'App\\Http\\Auth\\JwtService';

        if (!class_exists($jwtService)) {
            throw new ApiError('AUTH_RUNTIME_UNAVAILABLE', 'JWT runtime is not available', 501);
        }

        try {
            $claims = (new $jwtService())->verify($token, $tokenType);
        } catch (\Throwable $exception) {
            throw new ApiError('AUTH_INVALID_TOKEN', $exception->getMessage(), 401);
        }

        return is_array($claims) ? $claims : [];
    }
}
