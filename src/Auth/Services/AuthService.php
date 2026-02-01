<?php
namespace App\Auth\Services;

use PDO;
use App\Auth\DTO\{AuthResult, LoginDTO};
use App\Auth\Services\{TokenService, PermissionService};
use App\Auth\Helpers\{PasswordHelper, CookieHelper};
use Exception;

class AuthService
{
    private PDO $db;
    private TokenService $tokenService;
    private PermissionService $permissionService;
    private PasswordHelper $passwordHelper;
    private CookieHelper $cookieHelper;

    public function __construct(
        PDO $db,
        TokenService $tokenService,
        PermissionService $permissionService,
        PasswordHelper $passwordHelper,
        CookieHelper $cookieHelper
    ) {
        $this->db = $db;
        $this->tokenService = $tokenService;
        $this->permissionService = $permissionService;
        $this->passwordHelper = $passwordHelper;
        $this->cookieHelper = $cookieHelper;
    }

    public function authenticate(LoginDTO $credentials): AuthResult 
{
        try {
        // Get franchise ID from username/email
        $stmt = $this->db->prepare("SELECT franchise_id FROM tbl_users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$credentials->getUsername(), $credentials->getUsername()]);
        $franchiseResult = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$franchiseResult) {
            $this->logFailedAttempt($credentials);
            return new AuthResult(0, 0, $credentials->getUsername(), 'USER_NOT_FOUND', [], null);
        }

        // Call authenticate procedure with correct parameters
        try {
            $stmt = $this->db->prepare("CALL sp_authenticate_user(?, ?, @p_user_id, @p_status, @p_password_hash)");
            $stmt->execute([$credentials->getUsername(), $franchiseResult['franchise_id']]);
            $stmt->closeCursor();

            // Get output parameters
            $result = $this->db->query("SELECT 
                @p_user_id as user_id, 
                @p_status as status, 
                @p_password_hash as stored_hash"
            )->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Fallback: stored procedure failed (possible schema mismatch). Use manual query.
            error_log("sp_authenticate_user failed: " . $e->getMessage());
            $result = null;
            // Manual authentication fallback
            // Handle NULL franchise_id for super admins
            if ($franchiseResult['franchise_id'] === null) {
                $stmt = $this->db->prepare("SELECT id, password_hash, user_type, failed_login_attempts, status FROM tbl_users WHERE (username = ? OR email = ?) AND franchise_id IS NULL LIMIT 1");
                $stmt->execute([$credentials->getUsername(), $credentials->getUsername()]);
            } else {
                $stmt = $this->db->prepare("SELECT id, password_hash, user_type, failed_login_attempts, status FROM tbl_users WHERE (username = ? OR email = ?) AND franchise_id = ? LIMIT 1");
                $stmt->execute([$credentials->getUsername(), $credentials->getUsername(), $franchiseResult['franchise_id']]);
            }
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($userRow) {
                $result = [
                    'user_id' => $userRow['id'],
                    'status' => 'SUCCESS',
                    'stored_hash' => $userRow['password_hash']
                ];
            }
        }

        if (!$result || ($result['status'] ?? '') !== 'SUCCESS') {
            $this->logFailedAttempt($credentials);
            return new AuthResult(0, 0, $credentials->getUsername(), $result['status'] ?? 'USER_NOT_FOUND', [], null);
        }

        if (!$this->passwordHelper->verifyPassword($credentials->getPassword(), $result['stored_hash'])) {
            $this->incrementFailedAttempts($result['user_id']);
            $this->logFailedAttempt($credentials);
            return new AuthResult(0, 0, $credentials->getUsername(), 'INVALID_PASSWORD', [], null);
        }

        // Get full user data with a safe fallback
        try {
            $userData = $this->getUserData($result['user_id']);
        } catch (\Exception $e) {
            error_log('sp_get_user_data failed, falling back to manual query: ' . $e->getMessage());
            // Manual fallback: query user fields directly
            $stmt = $this->db->prepare("SELECT id, franchise_id, username, email, first_name, last_name, phone, user_type, force_password_change, failed_login_attempts FROM tbl_users WHERE id = ? LIMIT 1");
            $stmt->execute([$result['user_id']]);
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$userRow) throw new \Exception('Failed to retrieve user data');
            $userData = $userRow;
            $userData['roles'] = [];
            $userData['permissions'] = [];
            $userData['franchise_name'] = '';
            $userData['currency'] = '';
            $userData['franchise_code'] = '';
            $userData['country'] = '';
            $userData['language'] = 'en';
            // grab franchise data (skip for super admins with null franchise_id)
            if ($userData['franchise_id'] !== null) {
                $stmt = $this->db->prepare("SELECT name, currency, country, language, code, timezone FROM tbl_franchises WHERE id = ? LIMIT 1");
                $stmt->execute([$userData['franchise_id']]);
                $fr = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fr) {
                    $userData['franchise_name'] = $fr['name'];
                    $userData['currency'] = $fr['currency'];
                    $userData['country'] = $fr['country'];
                    $userData['language'] = $fr['language'];
                    $userData['franchise_code'] = $fr['code'];
                    $userData['timezone'] = $fr['timezone'] ?? 'Africa/Kampala';
                }
            }

        }

        // Generate token (use franchise_id 1 as default for super admins who have null franchise_id)
        $token = $this->tokenService->generateToken($result['user_id'], $userData['franchise_id'] ?? 1);

        // Set session data
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['franchise_id'] = $userData['franchise_id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['user_type'] = $userData['user_type'] ?? 'staff';  // ✅ Add user_type for API permission checks
        $_SESSION['full_name'] = trim($userData['first_name'] . ' ' . $userData['last_name']);
        $_SESSION['role_name'] = $userData['roles'][0] ?? 'User';
        $_SESSION['auth_token'] = $token;
        $_SESSION['last_activity'] = time();
        $_SESSION['franchise_name'] = $userData['franchise_name'];
        $_SESSION['currency'] = $userData['currency'];
        $_SESSION['franchise_code'] = $userData['franchise_code'];
        $_SESSION['franchise_country'] = $userData['country'] ?? '';
        $_SESSION['franchise_language'] = $userData['language'] ?? 'en';
        $_SESSION['language'] = $_SESSION['franchise_language']; // Set active language
        $_SESSION['timezone'] = $userData['timezone'] ?? 'Africa/Kampala';
        $_SESSION['force_password_change'] = $userData['force_password_change'] ?? 0;

        // Set application-wide timezone for this session
        // This affects all PHP date/time functions throughout the application
        date_default_timezone_set($_SESSION['timezone']);

        // Reset failed attempts
        $this->resetFailedAttempts($result['user_id']);

        // Get user permissions
        $permissions = $this->permissionService->getUserPermissions($result['user_id']);

        return new AuthResult(
            $result['user_id'],
            $userData['franchise_id'],
            $userData['username'],
            'SUCCESS',
            $userData,
            $token
        );
    } catch (Exception $e) {
        // Log exception and return failure result
        error_log('Authentication error: ' . $e->getMessage());
        return new AuthResult(0, 0, $credentials->getUsername(), 'SYSTEM_ERROR', [], null, $e->getMessage());
    }
}

    
    
    private function getUserData(int $userId): array 
    {
        try {
            $stmt = $this->db->prepare("CALL sp_get_user_data(?, @p_status)");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            // Get output parameter
            $statusResult = $this->db->query("SELECT @p_status as status")->fetch(PDO::FETCH_ASSOC);
            
            if ($statusResult['status'] !== 'Success' || !$result) {
                throw new \Exception("Failed to retrieve user data");
            }
            
            // ✅ CRITICAL FIX: If user_type is missing, fetch it directly
            if (!isset($result['user_type']) || empty($result['user_type']) || !isset($result['force_password_change'])) {
                $stmt = $this->db->prepare("SELECT user_type, force_password_change FROM tbl_users WHERE id = ?");
                $stmt->execute([$userId]);
                $userExtra = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!isset($result['user_type']) || empty($result['user_type'])) {
                    $result['user_type'] = $userExtra['user_type'] ?? 'staff';
                    error_log("✅ Retrieved user_type from tbl_users: " . $result['user_type']);
                }
                
                if (!isset($result['force_password_change'])) {
                    $result['force_password_change'] = $userExtra['force_password_change'] ?? 0;
                }
            }
    
            // Convert comma-separated strings to arrays
            $result['roles'] = $result['roles'] ? explode(',', $result['roles']) : [];
            $result['permissions'] = $result['permissions'] ? explode(',', $result['permissions']) : [];
            
            // Fetch timezone and language from franchise table
            $stmt = $this->db->prepare("SELECT timezone, language FROM tbl_franchises WHERE id = ?");
            $stmt->execute([$result['franchise_id']]);
            $franchiseData = $stmt->fetch(PDO::FETCH_ASSOC);
            $result['timezone'] = $franchiseData['timezone'] ?? 'Africa/Kampala';
            $result['language'] = $franchiseData['language'] ?? 'en';

            // ✅ CRITICAL FIX: Fetch distributor_code for distributor users
            if (($result['user_type'] ?? '') === 'distributor') {
                // Correctly link user -> distributor via distributor_id
                $stmt = $this->db->prepare("
                    SELECT d.distributor_code 
                    FROM tbl_users u
                    JOIN tbl_distributors d ON u.distributor_id = d.id
                    WHERE u.id = ?
                ");
                $stmt->execute([$userId]);
                $distributorData = $stmt->fetch(PDO::FETCH_ASSOC);
                $result['distributor_code'] = $distributorData['distributor_code'] ?? null;
                error_log("✅ Retrieved distributor_code for user {$userId}: " . ($result['distributor_code'] ?? 'NULL'));
            }

            return $result;
    
        } catch (\PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }
    
    public function createUserSession(int $userId, bool $rememberMe = false): object 
{
    try {
        // Set expiry based on remember me flag
        $expiryDays = $rememberMe ? 30 : 1;
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryDays} days"));

        // Get user's franchise ID
        $stmt = $this->db->prepare("SELECT franchise_id FROM tbl_users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new \Exception("User not found");
        }

        // Generate token
        $token = $this->tokenService->generateToken($userId, $result['franchise_id']);

        // Create session record
        $stmt = $this->db->prepare("CALL sp_create_user_session(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $result['franchise_id'],
            $token,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            $expiresAt,
            $rememberMe ? 1 : 0,
            json_encode([]) // Empty session data
        ]);

        // Set remember me cookie if requested
        if ($rememberMe) {
            $this->cookieHelper->createSecureCookie(
                'remember_token',
                $token,
                86400 * $expiryDays
            );
        }

        return new class($token) {
            private string $token;
            
            public function __construct(string $token) {
                $this->token = $token;
            }
            
            public function getToken(): string {
                return $this->token;
            }
        };

    } catch (\Exception $e) {
        throw new \Exception("Failed to create user session: " . $e->getMessage());
    }
}

    
    

    private function incrementFailedAttempts(int $userId): void
    {
        $stmt = $this->db->prepare("CALL sp_increment_failed_attempts(?)");
        $stmt->execute([$userId]);
    }

    private function resetFailedAttempts(int $userId): void
    {
        $stmt = $this->db->prepare("CALL sp_reset_failed_attempts(?)");
        $stmt->execute([$userId]);
    }

    private function logFailedAttempt(LoginDTO $credentials): void
    {
        $stmt = $this->db->prepare("CALL sp_log_failed_login(?, ?, ?, ?)");
        $stmt->execute([
            $credentials->getUsername(),
            $credentials->getIpAddress(),
            $credentials->getUserAgent(),
            date('Y-m-d H:i:s')
        ]);
    }

    public function logout(string $token): bool
    {
        try {
            $this->tokenService->invalidateToken($token);
            $this->cookieHelper->removeCookie('remember_token');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function hashPassword(string $password): string 
    {
        return $this->passwordHelper->hashPassword($password);
    }

    public function verifyPassword(string $password, string $storedHash): bool 
    {
        return $this->passwordHelper->verifyPassword($password, $storedHash);
    }
}
