<?php
declare(strict_types=1);

namespace App\Config;

final class Env
{
    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            $value = $_ENV[$key];
        } elseif (array_key_exists($key, $_SERVER)) {
            $value = $_SERVER[$key];
        } else {
            $value = getenv($key);
        }

        if (!is_string($value) || $value === '') {
            return $default;
        }

        return $value;
    }

    public static function require(string $key): string
    {
        $value = self::get($key);
        if ($value === null) {
            throw new \RuntimeException($key . ' is required.');
        }

        return $value;
    }
}
