<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Services\UserService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class UserServiceTest extends TestCase
{
    private PDO $db;
    private UserService $service;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->sqliteCreateFunction('NOW', static fn (): string => '2026-07-16 12:00:00');
        $this->db->exec(
            'CREATE TABLE tbl_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                franchise_id INTEGER NULL,
                username TEXT NOT NULL UNIQUE,
                user_type TEXT NOT NULL,
                email TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                phone TEXT NULL,
                status TEXT NOT NULL,
                force_password_change INTEGER NOT NULL,
                created_at TEXT NOT NULL,
                UNIQUE(franchise_id, email)
            )'
        );
        $this->service = new UserService($this->db);
    }

    public function testCreatesUserThroughCentralPasswordPolicy(): void
    {
        $user = $this->service->createUser($this->validUser());

        self::assertSame(1, $user['id']);
        self::assertSame('root-admin', $user['username']);
        self::assertSame('super_admin', $user['user_type']);
        $hash = $this->db->query('SELECT password_hash FROM tbl_users')->fetchColumn();
        self::assertIsString($hash);
        self::assertStringContainsString('$argon2id$', $hash);
        self::assertNotSame('StrongPassword123!', $this->service->hashPassword('StrongPassword123!'));
        self::assertSame([], $this->service->validatePasswordStrength('StrongPassword123!'));
    }

    public function testRejectsMissingRequiredField(): void
    {
        $data = $this->validUser();
        $data['first_name'] = '';

        $this->expectException(InvalidArgumentException::class);
        $this->service->createUser($data);
    }

    public function testRejectsInvalidEmailAndUserType(): void
    {
        $invalidEmail = $this->validUser();
        $invalidEmail['email'] = 'not-an-email';
        try {
            $this->service->createUser($invalidEmail);
            self::fail('Invalid email was accepted.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('email', $exception->getMessage());
        }

        $invalidType = $this->validUser();
        $invalidType['user_type'] = 'root';
        $this->expectException(InvalidArgumentException::class);
        $this->service->createUser($invalidType);
    }

    public function testEnforcesTenantAssignmentRules(): void
    {
        $superAdmin = $this->validUser();
        $superAdmin['franchise_id'] = 1;
        try {
            $this->service->createUser($superAdmin);
            self::fail('Tenant-scoped super admin was accepted.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('NULL franchise_id', $exception->getMessage());
        }

        $owner = $this->validUser();
        $owner['user_type'] = 'owner';
        $this->expectException(InvalidArgumentException::class);
        $this->service->createUser($owner);
    }

    public function testRejectsWeakAndDuplicateCredentials(): void
    {
        $weak = $this->validUser();
        $weak['password'] = 'short';
        try {
            $this->service->createUser($weak);
            self::fail('Weak password was accepted.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('12 characters', $exception->getMessage());
        }

        $this->service->createUser($this->validUser());
        $this->expectException(RuntimeException::class);
        $this->service->createUser($this->validUser());
    }

    /**
     * @return array<string, mixed>
     */
    private function validUser(): array
    {
        return [
            'username' => 'root-admin',
            'email' => 'root@example.test',
            'password' => 'StrongPassword123!',
            'first_name' => 'Root',
            'last_name' => 'Admin',
            'user_type' => 'super_admin',
            'franchise_id' => null,
        ];
    }
}
