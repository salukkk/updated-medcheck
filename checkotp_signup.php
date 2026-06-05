<?php
include_once __DIR__ . '/init.php';
include("connection.php");

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
    unset($_SESSION['otp']);
    echo "<script>alert('OTP expired. Please request a new OTP.'); window.location.href='signup.php';</script>";
    exit();
}

if (!hash_equals((string)$otp['code'], (string)$user_otp)) {
    $_SESSION['otp']['attempts'] = ($_SESSION['otp']['attempts'] ?? 0) + 1;
    echo "<script>alert('Invalid OTP'); window.location.href='verify.php';</script>";
    exit();
}

// OTP verified
$_SESSION['otp']['used'] = true;

// GET DATA FROM SESSION (from signup.php)
$data = $_SESSION['personal'];

$fname = $data['fname'];
$lname = $data['lname'];
$name = $fname . " " . $lname;

$email = $data['email'];
$address = $data['address'];
$studentid = $data['studentid'];
$dob = $data['dob'];

$password = password_hash($data['password'], PASSWORD_DEFAULT);

// 🔥 INSERT INTO webuser
$check_stmt = $database->prepare("SELECT * FROM webuser WHERE email=?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check = $check_stmt->get_result();

if($check->num_rows == 0){
    $webuser_stmt = $database->prepare("INSERT INTO webuser(email, usertype) VALUES(?, 'p')");
    $webuser_stmt->bind_param("s", $email);
    $webuser_result = $webuser_stmt->execute();
    
    if(!$webuser_result){
        echo "<script>alert('Error creating user account. Please try again.'); window.location.href='signup.php';</script>";
        exit();
    }
    $webuser_stmt->close();
}

// 🔥 INSERT INTO patient
$patient_stmt = $database->prepare("INSERT INTO patient(pemail, pname, ppassword, paddress, pnic, pdob, ptel)
VALUES(?, ?, ?, ?, ?, ?, '')");
$patient_stmt->bind_param("ssssss", $email, $name, $password, $address, $studentid, $dob);
$patient_result = $patient_stmt->execute();
$patient_stmt->close();

if($patient_result){
    // Clear session data after successful registration
    unset($_SESSION['personal']);
    unset($_SESSION['otp']);
    unset($_SESSION['email']);
    
    echo "<script>alert('Account Created Successfully! Please log in now.'); window.location.href='login.php';</script>";
} else {
    echo "<script>alert('Error creating patient profile. Please contact support.'); window.location.href='signup.php';</script>";
}

?>