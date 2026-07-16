<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$publicRoot = realpath($projectRoot . '/public');
$apiRoot = realpath($projectRoot . '/api');
$requestPath = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

if (str_starts_with($requestPath, '/api/')) {
    $relativePath = ltrim(substr($requestPath, strlen('/api/')), '/');
    $candidate = realpath($projectRoot . '/api/' . $relativePath);

    if ($candidate === false && pathinfo($relativePath, PATHINFO_EXTENSION) === '') {
        $candidate = realpath($projectRoot . '/api/' . $relativePath . '.php');
    }

    if (
        $apiRoot === false
        || $candidate === false
        || !str_starts_with($candidate, $apiRoot . DIRECTORY_SEPARATOR)
        || !is_file($candidate)
        || pathinfo($candidate, PATHINFO_EXTENSION) !== 'php'
    ) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => ['code' => 'NOT_FOUND', 'message' => 'API route not found'],
        ], JSON_UNESCAPED_SLASHES);
        return true;
    }

    $_SERVER['SCRIPT_FILENAME'] = $candidate;
    require $candidate;
    return true;
}

if ($publicRoot === false) {
    http_response_code(500);
    return true;
}

$publicCandidate = realpath($publicRoot . ($requestPath === '/' ? '/index.php' : $requestPath));
if (
    $publicCandidate !== false
    && str_starts_with($publicCandidate, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($publicCandidate)
) {
    if ($requestPath === '/') {
        require $publicCandidate;
        return true;
    }

    return false;
}

return false;
