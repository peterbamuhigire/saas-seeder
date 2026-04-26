<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Http\Response\ApiError;
use PHPUnit\Framework\TestCase;

final class ApiResponseTest extends TestCase
{
    public function testApiErrorCarriesStableCodeStatusAndDocumentationUrl(): void
    {
        $error = new ApiError('VALIDATION_FAILED', 'Invalid input', 422);

        self::assertSame('VALIDATION_FAILED', $error->errorCode());
        self::assertSame(422, $error->statusCode());
        self::assertSame('/docs/api/errors#VALIDATION_FAILED', $error->documentationUrl());
    }
}
