<?php
include_once __DIR__ . '/init.php';
include_once __DIR__ . '/sendEmail.php';

$email = trim(strtolower($_POST['email'] ?? ''));
require_csrf_or_die();
if (!$email) {
    echo "<script>alert('Please provide a valid email address.'); window.location.href='forgot-password.php';</script>";
    exit();
}

include_once __DIR__ . '/connection.php';
$check_stmt = $database->prepare("SELECT * FROM webuser WHERE email=?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_stmt->close();
if ($check_result->num_rows === 0) {
    echo "<script>alert('Email not found. Please register first or contact the administrator.'); window.location.href='forgot-password.php';</script>";
    exit();
}

// Basic rate limiting for OTP sending
if (!otp_allowed_resend()) {
    echo "<script>alert('Please wait before requesting another OTP.'); window.location.href='forgot-password.php';</script>";
    exit();
}

$otp = random_int(100000, 999999);
$_SESSION['otp'] = [
    'code' => $otp,
    'expires' => time() + 300,
    'used' => false,
    'attempts' => 0,
    'last_sent' => time(),
];
$_SESSION['email'] = $email;
$_SESSION['reset'] = true;

$sent = sendEmail($email, $otp);
if ($sent) {
    echo "<script>alert('OTP Sent Successfully'); window.location.href='verify.php';</script>";
} else {
    $_SESSION['otp_visible'] = $otp;
    $_SESSION['otp_fallback'] = true;
    echo "<script>alert('OTP could not be sent by email. Use this code to verify: $otp'); window.location.href='verify.php';</script>";
}

exit();
?>