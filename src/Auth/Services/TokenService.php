<?php
namespace App\Auth\Services;

use App\Auth\Interfaces\TokenServiceInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use \PDO;

class TokenService implements TokenServiceInterface 
{
    private string $secretKey;
    private string $algorithm;
    private int $tokenExpiry;
    private PDO $db;

    public function __construct(PDO $db) 
    {
        $this->db = $db;
        // Load from .env file using environment variable
        $secretKey = $_ENV['JWT_SECRET_KEY'] ?? null;
        
        if (!$secretKey) {
            // Generate a fallback key if not set
            $secretKey = bin2hex(random_bytes(32));
            // Store it for future use
            file_put_contents('.env', "\nJWT_SECRET_KEY=$secretKey", FILE_APPEND);
        }
        
        $this->secretKey = $secretKey;
        $this->algorithm = 'HS256';
        $this->tokenExpiry = 3600 * 24; // 24 hours
    }
    public function generateToken(int $userId, int $franchiseId): string 
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->tokenExpiry;
        $sessionId = bin2hex(random_bytes(16));

        // Fetch current permission_version for franchise to embed in token
        $pv = 0;
        try {
            $stmt = $this->db->prepare('SELECT permission_version FROM tbl_franchises WHERE id = ? LIMIT 1');
            $stmt->execute([$franchiseId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($row['permission_version'])) {
                $pv = (int)$row['permission_version'];
            }
        } catch (\Exception $e) {
            // ignore - default pv=0
        }

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $userId,
            'franchise_id' => $franchiseId,
            'jti' => $sessionId,
            'pv' => $pv
        ];

        $token = JWT::encode($payload, $this->secretKey, $this->algorithm);
        
        $this->storeSession($userId, $franchiseId, $token, $sessionId, $expire, $pv);
        
        return $token;
    }
    public function getUserIdFromToken(string $token): ?int
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

            // Verify session is still valid
            $stmt = $this->db->prepare("CALL sp_validate_session(?)");
            $stmt->execute([$decoded->jti]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result['is_valid'] || $decoded->exp <= time()) {
                return null;
            }

            return (int)$decoded->user_id;
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Extract token from Authorization header
     * @return string|null The bearer token if present, null otherwise
     */
    public function getCurrentToken(): ?string
    {
        $headers = getallheaders();

        // Check if Authorization header exists
        if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
            return null;
        }

        // Get Authorization header value
        $authHeader = $headers['Authorization'] ?? $headers['authorization'];

        // Check for Bearer token format
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        // Return the token part
        return $matches[1];
    }

    private function storeSession(int $userId, int $franchiseId, string $token, string $sessionId, int $expiry, int $permVersion = 0): void 
    {
        $stmt = $this->db->prepare("CALL sp_create_user_session(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $franchiseId,
            $token,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            date('Y-m-d H:i:s', $expiry),
            0, // remember_me flag
            json_encode([
                'session_id' => $sessionId,
                'created_at' => date('Y-m-d H:i:s'),
                'perm_version' => $permVersion,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ])
        ]);
    }

    public function validateToken(string $token): bool 
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            
            $stmt = $this->db->prepare("CALL sp_validate_session(?)");
            $stmt->execute([$decoded->jti]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $decoded->exp > time() && $result['is_valid'];
        } catch (\Exception $e) {
                // Also check token permission version matches current franchise permission_version
                $franchiseId = $decoded->franchise_id ?? null;
                if ($franchiseId !== null) {
                    try {
                        $pvstmt = $this->db->prepare('SELECT permission_version FROM tbl_franchises WHERE id = ? LIMIT 1');
                        $pvstmt->execute([$franchiseId]);
                        $pvrow = $pvstmt->fetch(PDO::FETCH_ASSOC);
                        $currentPv = (int)($pvrow['permission_version'] ?? 0);
                        $tokenPv = (int)($decoded->pv ?? 0);
                        if ($tokenPv !== $currentPv) {
                            return false; // token stale due to permission changes
                        }
                    } catch (\Exception $e) {
                        // ignore and continue - defensive
                    }
                }
            return false;
        }
    }

    public function invalidateToken(string $token): void
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

            $stmt = $this->db->prepare("CALL sp_invalidate_session(?)");
            $stmt->execute([$decoded->jti]);
        } catch (\Exception $e) {
            throw new \Exception('Invalid token for invalidation');
        }
    }

    public function refreshToken(string $currentToken): string 
    {
        try {
            $decoded = JWT::decode($currentToken, new Key($this->secretKey, $this->algorithm));
            return $this->generateToken($decoded->user_id, $decoded->franchise_id);
        } catch (\Exception $e) {
            throw new \Exception('Invalid token for refresh');
        }
    }
}
