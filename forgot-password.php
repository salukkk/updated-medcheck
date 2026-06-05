
<?php
include_once __DIR__ . '/init.php';

$_SESSION['reset_flow'] = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>MedCheck Forgot Password</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>

<?php include 'css/login.css'; ?>

body{
    margin:0;
    padding:0;
    font-family:'Inter',sans-serif;
}

.logo-title{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    margin-bottom:5px;
}

.heartbeat-logo{
    display:flex;
    align-items:center;
    justify-content:center;
}

.heartbeat-logo svg{
    width:55px;
    height:55px;
}

.logo-title h1{
    margin:0;
    letter-spacing:1px;
    font-size:34px;
    color:#00bfa6;
}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="logo-title">

            <div class="heartbeat-logo">

                <svg viewBox="0 0 64 64" fill="none">

                    <path 
                        d="M32 56s-20-12-20-28A11 11 0 0 1 32 20a11 11 0 0 1 20 8c0 16-20 28-20 28z" 
                        fill="#00bfa6"/>

                    <path 
                        d="M16 33h8l4-6 6 12 5-8h9" 
                        stroke="white" 
                        stroke-width="3.5" 
                        stroke-linecap="round" 
                        stroke-linejoin="round"/>

                </svg>

            </div>

            <h1>MEDCHECK</h1>

        </div>

        <p class="welcome">Recover your account</p>

        <form action="sendotp.php" method="POST">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit" class="btn">Send OTP</button>
        </form>

        <p class="link" style="margin-top:1rem;">
            Remembered your password? <a href="login.php">Login</a>
        </p>

    </div>

</div>

</body>
</html>

