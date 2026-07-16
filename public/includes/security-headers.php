<?php

declare(strict_types=1);

use App\Http\Middleware\SecurityHeadersMiddleware;

require_once __DIR__ . '/../../src/config/autoloader.php';

SecurityHeadersMiddleware::apply();
