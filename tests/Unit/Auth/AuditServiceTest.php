<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Services\AuditService;
use App\Observability\RequestContext;
use PDO;
use PHPUnit\Framework\TestCase;

final class AuditServiceTest extends TestCase
{
    public function testLogAddsRequestContextToAuditDetails(): void
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->sqliteCreateFunction('NOW', static fn (): string => date('Y-m-d H:i:s'));
        $db->exec(
            'CREATE TABLE tbl_audit_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                franchise_id INTEGER NULL,
                action TEXT NOT NULL,
                entity_type TEXT NULL,
                entity_id INTEGER NULL,
                details TEXT NULL,
                ip_address TEXT NULL,
                user_agent TEXT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $service = new AuditService(
            $db,
            new RequestContext('req-audit-001', 'POST', '/api/v1/auth/login', '203.0.113.9', 'Audit Test Agent')
        );

        $service->log('auth.login.success', 42, 7, 'auth', 42, ['status' => 'SUCCESS']);

        $row = $db->query('SELECT * FROM tbl_audit_log LIMIT 1')->fetch(PDO::FETCH_ASSOC);

        self::assertIsArray($row);
        self::assertSame('203.0.113.9', $row['ip_address']);
        self::assertSame('Audit Test Agent', $row['user_agent']);

        $details = json_decode((string) $row['details'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('SUCCESS', $details['status']);
        self::assertSame('req-audit-001', $details['request_id']);
        self::assertSame('/api/v1/auth/login', $details['request_path']);
        self::assertSame('POST', $details['request_method']);
    }
}
