<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Config\Database;
use App\Http\RateLimit\DatabaseRateLimitStore;
use App\Http\RateLimit\RateLimiter;
use App\Http\RateLimit\RateLimitPolicy;
use App\Http\Response\ApiError;
use App\Http\Response\ApiResponse;
use Throwable;

final class RateLimitMiddleware
{
    public static function enforce(RateLimitPolicy $policy, string $identity): void
    {
        try {
            $db = Database::getInstance()->getConnection();
            $limiter = new RateLimiter(new DatabaseRateLimitStore($db), $_ENV['APP_KEY'] ?? '');
            $result = $limiter->hit($policy, $identity);

            header('RateLimit-Limit: ' . $result->limit);
            header('RateLimit-Remaining: ' . $result->remaining);
            header('RateLimit-Reset: ' . $result->resetAt);

            if (!$result->allowed) {
                header('Retry-After: ' . max(1, $result->resetAt - time()));
                ApiResponse::error(new ApiError('RATE_LIMIT_EXCEEDED', 'Too many requests', 429));
            }
        } catch (Throwable $e) {
            error_log('Rate limit check failed: ' . $e->getMessage());
        }
    }
}
