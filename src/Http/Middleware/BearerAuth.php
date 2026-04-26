<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Auth\Token\AccessTokenService;
use App\Config\Database;
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
        if ($tokenType !== 'access') {
            throw new ApiError('AUTH_TOKEN_TYPE_UNSUPPORTED', 'Only access bearer tokens are supported here', 400);
        }

        $token = self::requireToken($request);
        $db = Database::getInstance()->getConnection();
        $claims = (new AccessTokenService($db))->validateClaims($token);

        if ($claims === null) {
            throw new ApiError('AUTH_INVALID_TOKEN', 'Invalid access token', 401);
        }

        return $claims->toArray();
    }
}
