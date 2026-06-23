<?php

function configureSession(): void
{
    $secure = defined('SESSION_SECURE') ? SESSION_SECURE : false;

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    if ($secure) {
        ini_set('session.cookie_secure', '1');
    }

    $sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0750, true);
    }
    session_save_path($sessionPath);
    ini_set('session.save_path', $sessionPath);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function sendSecurityHeaders(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('X-XSS-Protection: 0');

    if (defined('APP_ENV') && APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function isProduction(): bool
{
    return defined('APP_ENV') && APP_ENV === 'production';
}

function isDebug(): bool
{
    return defined('APP_DEBUG') && APP_DEBUG === true;
}

/**
 * Returns true when the action is allowed, false when rate-limited.
 */
function rateLimitAllow(string $bucket, int $maxAttempts = 5, int $windowSeconds = 900): bool
{
    $now = time();
    $key = 'rate_' . $bucket;

    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'reset' => $now + $windowSeconds];
    }

    if ($now >= $_SESSION[$key]['reset']) {
        $_SESSION[$key] = ['count' => 0, 'reset' => $now + $windowSeconds];
    }

    if ($_SESSION[$key]['count'] >= $maxAttempts) {
        return false;
    }

    $_SESSION[$key]['count']++;
    return true;
}

function isHoneypotFilled(?string $value): bool
{
    return $value !== null && trim($value) !== '';
}

function validateEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return 'Password must include at least one letter and one number.';
    }
    return null;
}

function sanitizePhone(string $phone): string
{
    return preg_replace('/[^\d+\s\-()]/', '', $phone) ?? '';
}

function sanitizePincode(string $pincode): string
{
    return preg_replace('/\D/', '', $pincode) ?? '';
}

function normalizeEmail(string $email): string
{
    return strtolower(trim($email));
}

function emailsMatch(string $a, string $b): bool
{
    return hash_equals(normalizeEmail($a), normalizeEmail($b));
}

function regenerateSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function logError(string $message): void
{
    $line = '[' . date('c') . '] ' . $message . PHP_EOL;
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}



