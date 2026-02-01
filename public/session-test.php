<?php
/**
 * Session Test Page
 * Use this to debug session issues
 */

require_once __DIR__ . '/../src/config/auth.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <style>
        body { font-family: monospace; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; margin: 20px 0; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Session Debug Information</h1>

    <h2>Session Status</h2>
    <table>
        <tr>
            <th>Check</th>
            <th>Status</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Session Started</td>
            <td class="<?php echo session_status() === PHP_SESSION_ACTIVE ? 'success' : 'error'; ?>">
                <?php echo session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO'; ?>
            </td>
            <td><?php echo session_id(); ?></td>
        </tr>
        <tr>
            <td>isLoggedIn()</td>
            <td class="<?php echo isLoggedIn() ? 'success' : 'error'; ?>">
                <?php echo isLoggedIn() ? 'YES' : 'NO'; ?>
            </td>
            <td>-</td>
        </tr>
        <tr>
            <td>User ID</td>
            <td class="<?php echo hasSession('user_id') ? 'success' : 'error'; ?>">
                <?php echo hasSession('user_id') ? 'SET' : 'NOT SET'; ?>
            </td>
            <td><?php echo hasSession('user_id') ? getSession('user_id') : '-'; ?></td>
        </tr>
        <tr>
            <td>User Type</td>
            <td class="<?php echo hasSession('user_type') ? 'success' : 'error'; ?>">
                <?php echo hasSession('user_type') ? 'SET' : 'NOT SET'; ?>
            </td>
            <td><?php echo hasSession('user_type') ? getSession('user_type') : '-'; ?></td>
        </tr>
        <tr>
            <td>Username</td>
            <td class="<?php echo hasSession('username') ? 'success' : 'error'; ?>">
                <?php echo hasSession('username') ? 'SET' : 'NOT SET'; ?>
            </td>
            <td><?php echo hasSession('username') ? getSession('username') : '-'; ?></td>
        </tr>
        <tr>
            <td>Full Name</td>
            <td class="<?php echo hasSession('full_name') ? 'success' : 'error'; ?>">
                <?php echo hasSession('full_name') ? 'SET' : 'NOT SET'; ?>
            </td>
            <td><?php echo hasSession('full_name') ? getSession('full_name') : '-'; ?></td>
        </tr>
        <tr>
            <td>Franchise ID</td>
            <td class="<?php echo hasSession('franchise_id') ? 'success' : 'error'; ?>">
                <?php echo hasSession('franchise_id') ? 'SET' : 'NOT SET'; ?>
            </td>
            <td><?php echo hasSession('franchise_id') ? getSession('franchise_id') : '-'; ?></td>
        </tr>
        <tr>
            <td>Last Activity</td>
            <td class="<?php echo hasSession('last_activity') ? 'success' : 'error'; ?>">
                <?php echo hasSession('last_activity') ? 'SET' : 'NOT SET'; ?>
            </td>
            <td><?php echo hasSession('last_activity') ? date('Y-m-d H:i:s', getSession('last_activity')) : '-'; ?></td>
        </tr>
    </table>

    <h2>All Prefixed Session Variables</h2>
    <table>
        <tr>
            <th>Key</th>
            <th>Value</th>
        </tr>
        <?php
        $allSessions = getAllSession();
        if (empty($allSessions)) {
            echo '<tr><td colspan="2">No session variables set</td></tr>';
        } else {
            foreach ($allSessions as $key => $value) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($key) . '</td>';
                echo '<td>' . htmlspecialchars(is_array($value) || is_object($value) ? json_encode($value) : $value) . '</td>';
                echo '</tr>';
            }
        }
        ?>
    </table>

    <h2>Raw $_SESSION Array</h2>
    <pre><?php print_r($_SESSION); ?></pre>

    <h2>Session Configuration</h2>
    <table>
        <tr>
            <th>Setting</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>session.cookie_httponly</td>
            <td><?php echo ini_get('session.cookie_httponly'); ?></td>
        </tr>
        <tr>
            <td>session.cookie_secure</td>
            <td><?php echo ini_get('session.cookie_secure'); ?></td>
        </tr>
        <tr>
            <td>session.cookie_samesite</td>
            <td><?php echo ini_get('session.cookie_samesite'); ?></td>
        </tr>
        <tr>
            <td>session.gc_maxlifetime</td>
            <td><?php echo ini_get('session.gc_maxlifetime'); ?> seconds (<?php echo ini_get('session.gc_maxlifetime') / 60; ?> minutes)</td>
        </tr>
    </table>

    <h2>Server Information</h2>
    <table>
        <tr>
            <th>Variable</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>HTTPS</td>
            <td><?php echo isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set'; ?></td>
        </tr>
        <tr>
            <td>SERVER_PORT</td>
            <td><?php echo $_SERVER['SERVER_PORT'] ?? 'not set'; ?></td>
        </tr>
        <tr>
            <td>HTTP_HOST</td>
            <td><?php echo $_SERVER['HTTP_HOST'] ?? 'not set'; ?></td>
        </tr>
        <tr>
            <td>REQUEST_URI</td>
            <td><?php echo $_SERVER['REQUEST_URI'] ?? 'not set'; ?></td>
        </tr>
    </table>

    <div style="margin-top: 30px;">
        <a href="sign-in.php">← Back to Sign In</a> |
        <a href="index.php">Go to Index</a> |
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
