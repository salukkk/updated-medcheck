<?php
include_once __DIR__ . '/init.php';

require_csrf_or_die();

$user_otp = $_POST['otp'] ?? '';
$otp = $_SESSION['otp'] ?? null;

if (!$otp) {
    echo "<script>alert('No OTP session found'); window.location.href='verify.php';</script>";
    exit();
}

if ($otp['used'] ?? false) {
    echo "<script>alert('OTP already used'); window.location.href='verify.php';</script>";
    exit();
}

if (time() > ($otp['expires'] ?? 0)) {
    echo "<script>alert('OTP expired'); window.location.href='forgot-password.php';</script>";
    exit();
}

if (hash_equals((string)$otp['code'], (string)$user_otp)) {
    $_SESSION['otp']['used'] = true;
    $_SESSION['reset_email'] = $_SESSION['email'] ?? null;
    echo "<script>alert('OTP Verified Successfully'); window.location.href='changepassword.php';</script>";
    exit();
} else {
    $_SESSION['otp']['attempts'] = ($_SESSION['otp']['attempts'] ?? 0) + 1;
    echo "<script>alert('Invalid OTP'); window.location.href='verify.php';</script>";
    exit();
}

?>