<?php

declare(strict_types=1);

namespace App\Auth\Security;

final class DeviceFingerprint
{
    public function hash(?string $userAgent, ?string $ipAddress): string
    {
        return hash('sha256', trim((string) $userAgent) . '|' . trim((string) $ipAddress));
    }
}
