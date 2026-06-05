<?php
// Central init: session hardening, env helper, CSRF token

// Set secure session cookie params before session_start
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => null,
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        // fallback
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

function require_csrf_or_die() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sent = $_POST['csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
            http_response_code(400);
            exit('Invalid CSRF token');
        }
    }
}

function otp_allowed_resend(): bool {
    $otp = $_SESSION['otp'] ?? null;
    if (!$otp) return true;
    $last = $otp['last_sent'] ?? 0;
    return (time() - $last) >= 60; // allow resend every 60s
}
