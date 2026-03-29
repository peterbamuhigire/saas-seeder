<?php
declare(strict_types=1);

/**
 * Session Configuration and Helper Functions
 *
 * All session variables are prefixed with 'saas_app_' to avoid conflicts.
 * When using this template for a specific SaaS app, do a global find/replace:
 * Find: 'saas_app_'
 * Replace with: 'yourapp_' (e.g., 'invoice_', 'crm_', 'academy_')
 */

// Session prefix constant - change this when creating a new SaaS app
define('SESSION_PREFIX', 'saas_app_');

/**
 * Start session with secure settings
 */
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');

        // Only set secure cookie if using HTTPS (allows localhost HTTP development)
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
        ini_set('session.cookie_secure', $isHttps ? '1' : '0');

        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', '1800'); // 30 minutes
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.sid_length', '48');
        ini_set('session.sid_bits_per_character', '6');
        session_start();
    }
}

/**
 * Set a session variable with prefix
 *
 * @param string $key Session key (without prefix)
 * @param mixed $value Value to store
 */
function setSession(string $key, $value): void {
    $_SESSION[SESSION_PREFIX . $key] = $value;
}

/**
 * Get a session variable with prefix
 *
 * @param string $key Session key (without prefix)
 * @param mixed $default Default value if not set
 * @return mixed
 */
function getSession(string $key, mixed $default = null): mixed {
    return $_SESSION[SESSION_PREFIX . $key] ?? $default;
}

/**
 * Check if session variable exists
 *
 * @param string $key Session key (without prefix)
 * @return bool
 */
function hasSession(string $key): bool {
    return isset($_SESSION[SESSION_PREFIX . $key]);
}

/**
 * Remove a session variable
 *
 * @param string $key Session key (without prefix)
 */
function unsetSession(string $key): void {
    unset($_SESSION[SESSION_PREFIX . $key]);
}

/**
 * Get all session variables (with prefix removed from keys)
 *
 * @return array
 */
function getAllSession(): array {
    $data = [];
    $prefixLength = strlen(SESSION_PREFIX);

    foreach ($_SESSION as $key => $value) {
        if (strpos($key, SESSION_PREFIX) === 0) {
            $cleanKey = substr($key, $prefixLength);
            $data[$cleanKey] = $value;
        }
    }

    return $data;
}

/**
 * Clear all session variables with prefix
 */
function clearPrefixedSession(): void {
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, SESSION_PREFIX) === 0) {
            unset($_SESSION[$key]);
        }
    }
}

/**
 * Regenerate session ID for security
 */
function regenerateSession(): void {
    session_regenerate_id(true);
}
