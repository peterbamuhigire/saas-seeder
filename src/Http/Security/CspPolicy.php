<?php

declare(strict_types=1);

namespace App\Http\Security;

final class CspPolicy
{
    public function reportOnlyHeader(): string
    {
        return "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; object-src 'none'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'";
    }
}
