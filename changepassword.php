<?php
include_once __DIR__ . '/init.php';
include("connection.php");

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_csrf_or_die();

    $newPassword = mysqli_real_escape_string($database, $_POST['new_password']);
    $confirmPassword = mysqli_real_escape_string($database, $_POST['confirm_password']);

    if ($newPassword != $confirmPassword) {

        $message = "Passwords do not match!";

    } else {

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        $stmt = $database->prepare("SELECT usertype FROM webuser WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows != 1) {
            $message = "Account not found.";
        } else {
            $usertype = $result->fetch_assoc()['usertype'];

            // `webuser` table doesn't store passwords (passwords live in role tables).
            // Skip updating `webuser` and mark webuser update as successful.
            $webuserOk = true;
            $updateOk = true;

            if ($usertype == 'p') {
                $patientStmt = $database->prepare("UPDATE patient SET ppassword = ? WHERE pemail = ?");
                $patientStmt->bind_param("ss", $hashedPassword, $email);
                $updateOk = $patientStmt->execute();
                $patientStmt->close();
            } elseif ($usertype == 'd') {
                $doctorStmt = $database->prepare("UPDATE doctor SET docpassword = ? WHERE docemail = ?");
                $doctorStmt->bind_param("ss", $hashedPassword, $email);
                $updateOk = $doctorStmt->execute();
                $doctorStmt->close();
            } elseif ($usertype == 'a') {
                $adminStmt = $database->prepare("UPDATE admin SET apassword = ? WHERE aemail = ?");
                $hashedAdmin = password_hash($newPassword, PASSWORD_DEFAULT);
                $adminStmt->bind_param("ss", $hashedAdmin, $email);
                $updateOk = $adminStmt->execute();
                $adminStmt->close();
            }

            if ($webuserOk && $updateOk) {
                session_destroy();

                echo "
                <script>
                    alert('Password changed successfully!');
                    window.location.href='login.php';
                </script>
                ";

                exit();
            } else {
                $message = "Something went wrong!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

 <style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
}

.container{
    width:420px;
}

.card{
    background:#374955;
    padding:45px;
    border-radius:28px;
    text-align:center;
    box-shadow:0 20px 50px rgba(0,0,0,0.45);
}

h1{
    color:white;
    margin-bottom:10px;
    font-size:50px;
    font-weight:700;
}

p{
    color:#d7d7d7;
    margin-bottom:28px;
    font-size:16px;
}

.input-group{
    position:relative;
    margin-bottom:18px;
}

.input-group input{
    width:100%;
    padding:16px;
    padding-right:55px;
    border-radius:15px;
    border:none;
    outline:none;
    background:#5b6972;
    color:white;
    font-size:16px;
}

.input-group input::placeholder{
    color:#d7d7d7;
}

.toggle-password{
    position:absolute;
    right:18px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:white;
    font-size:18px;
    user-select:none;
}

button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:15px;
    background:#66d9ef;
    color:black;
    font-size:20px;
    font-weight:700;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    opacity:0.9;
    transform:scale(1.01);
}

.message{
    color:#ffb3b3;
    margin-bottom:15px;
}

</style>

</head>
<body>

<div class="container">

    <div class="card">

        <h1>MEDCHECK</h1>

        <p>Create New Password</p>

        <?php if($message != "") { ?>
            <div class="message"><?php echo $message; ?></div>
        <?php } ?>

        <form method="POST">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <div class="input-group">
        <input type="password" id="new_password" name="new_password" placeholder="New Password" required>
        <span class="toggle-password" onclick="togglePassword('new_password', this)">👁</span>
    </div>

    <div class="input-group">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁</span>
    </div>

    <button type="submit">Change Password</button>

</form>

    </div>

</div>
<script>

function togglePassword(id, element){

    const input = document.getElementById(id);

    if(input.type === "password"){

        input.type = "text";
        element.innerHTML = "🙈";

    }else{

        input.type = "password";
        element.innerHTML = "👁";
    }
}

</script>
</body>
</html>