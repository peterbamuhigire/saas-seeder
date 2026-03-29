<?php
namespace App\Auth\Helpers;

/**
 * Helper class for secure cookie management
 */
class CookieHelper 
{
    private string $domain;
    private bool $secure;
    private string $sameSite;
    private string $encryptionKey;

    public function __construct()
    {
        // Get required environment variables — these MUST be set in .env
        // Never auto-generate secrets at runtime; fail loudly instead.
        $domain = $_ENV['COOKIE_DOMAIN'] ?? $_SERVER['COOKIE_DOMAIN'] ?? 'localhost';
        $this->domain = $domain;

        // Fix: parentheses ensure ?? resolves before ===
        $this->secure = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        $this->sameSite = 'Strict';

        // Encryption key must be configured in .env
        $encryptionKey = $_ENV['COOKIE_ENCRYPTION_KEY'] ?? $_SERVER['COOKIE_ENCRYPTION_KEY'] ?? null;
        if (!$encryptionKey) {
            throw new \RuntimeException(
                'COOKIE_ENCRYPTION_KEY is not set in .env. '
                . 'Generate one with: php -r "echo bin2hex(random_bytes(32));"'
            );
        }
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * Create secure HTTP-only cookie
     */
    public function createSecureCookie(
        string $name, 
        string $value, 
        int $expiry = 86400,
        bool $encrypt = true
    ): bool {
        $cookieValue = $encrypt ? $this->encryptValue($value) : $value;
        
        $options = [
            'expires' => time() + $expiry,
            'path' => '/',
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => true,
            'samesite' => $this->sameSite
        ];
        
        return setcookie($name, $cookieValue, $options);
    }

    /**
     * Get and validate cookie value
     */
    public function getCookie(string $name, bool $decrypt = true): ?string 
    {
        if (!isset($_COOKIE[$name])) {
            return null;
        }

        $value = $_COOKIE[$name];
        if (empty($value)) {
            $this->removeCookie($name);
            return null;
        }

        return $decrypt ? $this->decryptValue($value) : $value;
    }

    /**
     * Remove cookie
     */
    public function removeCookie(string $name): bool 
    {
        if (!isset($_COOKIE[$name])) {
            return true;
        }

        unset($_COOKIE[$name]);
        
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => true,
            'samesite' => $this->sameSite
        ]);
    }

    /**
     * Encrypt cookie value
     */
    private function encryptValue(string $value): string 
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $value,
            'AES-256-CBC',
            $this->encryptionKey,
            0,
            $iv
        );
        
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt cookie value
     */
    private function decryptValue(string $value): ?string 
    {
        try {
            $decoded = base64_decode($value);
            $iv = substr($decoded, 0, 16);
            $encrypted = substr($decoded, 16);
            
            return openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $this->encryptionKey,
                0,
                $iv
            );
        } catch (\Exception $e) {
            return null;
        }
    }
}
