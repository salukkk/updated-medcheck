
<?php   
session_start();

include("connection.php");

$_SESSION["user"]="";
$_SESSION["usertype"]="";

date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');
$_SESSION["date"]=$date;

$error = "";

if($_POST){

    if($_POST['password'] !== $_POST['confirm_password']){
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        
        $email = trim(strtolower($_POST['email']));
        
        // Check if email already exists
        $check_stmt = $database->prepare("SELECT * FROM webuser WHERE email=?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();
        
        if($check_result->num_rows > 0){
            $error = "Email already registered! Please use a different email or login.";
        } else {

            $_SESSION["personal"]=array(
                'fname'=>$_POST['fname'],
                'lname'=>$_POST['lname'],
                'address'=>$_POST['address'],
                'email'=>$email,
                'password'=>$_POST['password'],
                'dob'=>$_POST['dob']
            );
    
            header("location: sendemail.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MedCheck Sign Up</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>

<?php include 'css/signup.css'; ?>

body{
    margin:0;
    padding:0;
    font-family:'Inter',sans-serif;
}

/* =========================
   MEDCHECK LOGO
========================= */

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

/* =========================
   PASSWORD
========================= */

.password-wrapper{
    position:relative;
    width:100%;
}

.password-wrapper input{
    width:100%;
    box-sizing:border-box;
    padding-right:65px;
}

.show-password{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    font-size:13px;
    cursor:pointer;
    color:#00bfa6;
    font-weight:600;
    user-select:none;
}

</style>

</head>
<body>

<div class="container">

    <div class="card">

        <!-- LOGO -->
        <div class="logo-title">

            <div class="heartbeat-logo">

                <!-- HEART ONLY WITH HEARTBEAT -->
                <svg viewBox="0 0 64 64" fill="none">

                    <!-- HEART -->
                    <path 
                        d="M32 56s-20-12-20-28A11 11 0 0 1 32 20a11 11 0 0 1 20 8c0 16-20 28-20 28z" 
                        fill="#00bfa6"/>

                    <!-- HEARTBEAT LINE -->
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

        <p class="welcome">Create Your Account</p>
        
        <?php if($error != ""){ ?>
            <div style="background-color: #ffcccc; color: #cc0000; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold;">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <form method="POST">

            <div class="row">
                <input type="text" name="fname" placeholder="First Name" required>
                <input type="text" name="lname" placeholder="Last Name" required>
            </div>

            <input type="text" name="address" placeholder="Address" required>

            <input type="email" name="email" placeholder="Email Address" required>

            <!-- CREATE PASSWORD -->
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Create Password" required>
                <span class="show-password" onclick="togglePassword()">Show</span>
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <span class="show-password" onclick="togglePassword()">Show</span>
            </div>

            <input type="date" name="dob" required>

            <div class="buttons">
                <input type="reset" value="Reset" class="btn reset">
                <input type="submit" value="Next" class="btn submit">
            </div>

        </form>

        <p class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </p>

        <p class="login-link">
             Are you a doctor? <a href="doctor_signup.php">Sign up as Doctor</a>
        </p>

    </div>

</div>

<script>

function togglePassword() {

    let pass = document.getElementById("password");
    let confirm = document.getElementById("confirm_password");

    if (pass.type === "password") {

        pass.type = "text";
        confirm.type = "text";

        document.querySelectorAll(".show-password").forEach(btn=>{
            btn.innerHTML = "Hide";
        });

    } else {

        pass.type = "password";
        confirm.type = "password";

        document.querySelectorAll(".show-password").forEach(btn=>{
            btn.innerHTML = "Show";
        });
    }
}

// PASSWORD STRENGTH CHECKER
document.getElementById("password").addEventListener("input", function() {

    let password = this.value;
    let strengthText = document.getElementById("strength");

    if(strengthText){

        if(password.length < 6){
            strengthText.innerHTML = "Weak password";
            strengthText.style.color = "red";
        }
        else if(password.match(/[A-Z]/) && password.match(/[0-9]/)){
            strengthText.innerHTML = "Strong password";
            strengthText.style.color = "lightgreen";
        }
        else{
            strengthText.innerHTML = "Medium password";
            strengthText.style.color = "orange";
        }

    }

});

</script>

</body>
</html>

