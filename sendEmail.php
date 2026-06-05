<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if(isset($_GET['resend'])){
    $email = $_SESSION['email'] ?? null;
    if (!$email) {
        echo "<script>alert('No OTP session found. Please start again.'); window.location.href='verify.php';</script>";
        exit();
    }

    $lastSent = $_SESSION['otp']['last_sent'] ?? 0;
    if (time() - $lastSent < 60) {
        echo "<script>alert('Please wait at least 60 seconds before resending.'); window.location.href='verify.php';</script>";
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

    $sent = sendEmail($email, $otp);
    if ($sent) {
        echo "<script>alert('OTP Resent Successfully'); window.location.href='verify.php';</script>";
    } else {
        $_SESSION['otp_visible'] = $otp;
        $_SESSION['otp_fallback'] = true;
        echo "<script>alert('OTP could not be sent by email. Use this code to verify: $otp'); window.location.href='verify.php';</script>";
    }

    exit();
}
function sendEmail($email, $otp){

    $mail = new PHPMailer(true);

    try {

        // SERVER SETTINGS
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // YOUR GMAIL
        $mail->Username = 'nicolaivonkeicer.glinofria@cvsu.edu.ph';

        // APP PASSWORD
        $mail->Password = 'ylgszomgczoavhym';

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // EMAIL DETAILS
        $mail->setFrom(
            'nicolaivonkeicer.glinofria@cvsu.edu.ph',
            'MedCheck'
        );

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = 'Your OTP Code';

        $mail->Body = "
            <h2>Your OTP Code</h2>
            <h1>$otp</h1>
        ";

        $mail->send();

        return true;

    } catch (Exception $e) {

        echo 'Mailer Error: ' . $mail->ErrorInfo;
        return false;
    }
}
if(isset($_SESSION["personal"])){
    
    $email = $_SESSION["personal"]['email'];

    $otp = rand(100000, 999999);

$_SESSION['otp'] = [
            'code' => $otp,
            'expires' => time() + 300,
            'used' => false,
            'attempts' => 0,
            'last_sent' => time(),
        ];
    $_SESSION['email'] = $email;
    $_SESSION['reset_email'] = $email;

    $mail = new PHPMailer(true);

    try {

        // SERVER SETTINGS
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // YOUR GMAIL
        $mail->Username = 'nicolaivonkeicer.glinofria@cvsu.edu.ph';

        // APP PASSWORD
        $mail->Password = 'ylgszomgczoavhym';

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // EMAIL DETAILS
        $mail->setFrom('nicolaivonkeicer.glinofria@cvsu.edu.ph', 'MedCheck');

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = 'Your OTP Code';

        $mail->Body = "
            <h2>Your OTP Code</h2>
            <h1>$otp</h1>
        ";

        $mail->send();

        echo "
        <script>
            alert('OTP Sent Successfully');
            window.location.href='verify.php';
        </script>
        ";

    } catch (Exception $e) {

        echo "Message could not be sent.";
    }
}
?>
