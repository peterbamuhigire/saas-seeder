<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../../src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load Composer autoloader if available
$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Load .env file if available
if (class_exists('Dotenv\Dotenv')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
    } catch (Exception $e) {
        // Ignore if .env not found or invalid
    }
}

// Load global helper functions
$helpersFile = __DIR__ . '/../helpers.php';
if (file_exists($helpersFile)) {
    require_once $helpersFile;
}
