<?php
include_once __DIR__ . '/init.php';

$action = "checkotp_signup.php"; // default signup

// ✅ FORGOT PASSWORD FLOW
if(isset($_SESSION['reset']) && $_SESSION['reset'] == true){
    $action = "checkotp.php";
}

// ✅ DOCTOR SIGNUP
if(isset($_SESSION['signup_type']) && $_SESSION['signup_type'] == 'doctor'){
    $action = "checkotp_doctor.php";
}

$otp = $_SESSION['otp'] ?? null;
$remaining = 0;
$resend_in = 0;
if ($otp) {
    if (!is_array($otp)) {
        $otp = [
            'code' => $otp,
            'expires' => $_SESSION['otp_expires'] ?? 0,
            'used' => false,
            'attempts' => 0,
            'last_sent' => $_SESSION['otp_last_sent'] ?? 0,
        ];
        $_SESSION['otp'] = $otp;
    }

    $remaining = max(0, ($otp['expires'] ?? 0) - time());
    $last_sent = $otp['last_sent'] ?? 0;
    $resend_in = max(0, 60 - (time() - $last_sent));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MedCheck</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
    <?php include 'css/login.css'; ?>
    </style>
</head>

<body>

<div class="container">
    <div class="card">

        <h1>MEDCHECK</h1>
        <p class="welcome">Verify One-Time Password</p>

        <div style="margin-bottom:12px; text-align:center; color:#d7d7d7;">
            <strong>OTP expires in:</strong> <span id="timer">--:--</span>
        </div>

        <form id="otpForm" action="<?php echo $action; ?>" method="POST">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button id="verifyBtn" type="submit" class="btn">Verify</button>
        </form>

        <p class="link" style="margin-top:1rem;">
            Didn't receive OTP? <a id="resendLink" href="sendEmail.php?resend=1"> Resend</a>
            <span id="resendInfo" style="margin-left:8px;color:#bbb;"></span>
        </p>

        <p class="link">
            Back to <a href="login.php">Login</a>
        </p>

    </div>
</div>

    <script>
    // OTP countdown
    let remaining = <?php echo (int)$remaining; ?>; // seconds
    let resendIn = <?php echo (int)$resend_in; ?>; // seconds until resend allowed

    function fmt(s){
        let m = Math.floor(s/60);
        let sec = s % 60;
        return String(m).padStart(2,'0')+":"+String(sec).padStart(2,'0');
    }

    const timerEl = document.getElementById('timer');
    const verifyBtn = document.getElementById('verifyBtn');
    const resendLink = document.getElementById('resendLink');
    const resendInfo = document.getElementById('resendInfo');

    function tick(){
        if(remaining <= 0){
            timerEl.textContent = '00:00';
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Expired';
        } else {
            timerEl.textContent = fmt(remaining);
            verifyBtn.disabled = false;
        }

        if(resendIn > 0){
            resendLink.style.pointerEvents = 'none';
            resendLink.style.opacity = '0.5';
            resendInfo.textContent = '(resend in ' + fmt(resendIn) + ')';
        } else {
            resendLink.style.pointerEvents = '';
            resendLink.style.opacity = '';
            resendInfo.textContent = '';
        }

        if(remaining > 0) remaining--;
        if(resendIn > 0) resendIn--;
    }

    tick();
    const interval = setInterval(()=>{
        tick();
        if(remaining <= 0 && resendIn <= 0){
            clearInterval(interval);
        }
    }, 1000);

    // Optional: prevent form submit when expired
    document.getElementById('otpForm').addEventListener('submit', function(e){
        if(remaining <= 0){
            e.preventDefault();
            alert('OTP expired. Please resend to get a new one.');
        }
    });
    </script>

    </body>
    </html>