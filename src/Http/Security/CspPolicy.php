<?php

declare(strict_types=1);

namespace App\Http\Security;

final class CspPolicy
{
    public function enforcedHeader(): string
    {
        return "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; img-src 'self' data:; font-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; connect-src 'self'";
    }
}
