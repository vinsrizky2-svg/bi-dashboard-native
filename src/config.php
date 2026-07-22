<?php
// ── Load .env jika vendor tersedia, fallback ke manual jika tidak ──
$vendorPath  = __DIR__ . '/../vendor/autoload.php';
$envFilePath = __DIR__ . '/../.env';

if (file_exists($vendorPath)) {
    require_once $vendorPath;
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} elseif (file_exists($envFilePath)) {
    // Fallback manual: parse .env tanpa library
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim(trim($val), '"\'');
        if (!empty($key)) {
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

function appName(): string {
    return $_ENV['APP_NAME'] ?? 'BI Dashboard';
}
