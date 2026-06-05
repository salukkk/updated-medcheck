<?php
session_start();

include("../connection.php");

$message = "";
if(isset($_GET['error'])){
    if($_GET['error'] === 'wrongpass'){
        $message = 'Incorrect password. Please try again.';
    } elseif($_GET['error'] === 'deactivate_invalid_password'){
        $message = 'Password is required and must be correct to deactivate your account.';
    }
}
if(isset($_GET['success']) && $_GET['success'] === '1'){
    $message = 'Profile updated successfully.';
}

if(isset($_GET['logout'])){
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Load doctor data from database
if(isset($_SESSION['user']) && $_SESSION['usertype'] == 'd'){
    $email = $_SESSION['user'];
    $stmt = $database->prepare("SELECT d.*, s.sname FROM doctor d LEFT JOIN specialties s ON d.specialties = s.id WHERE d.docemail=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1){
        $row = $result->fetch_assoc();
        $_SESSION['doctor'] = [
            'docid' => $row['docid'],
            'name' => $row['docname'],
            'email' => $row['docemail'],
            'phone' => $row['doctel'],
            'nic' => $row['docnic'],
            'specialty_id' => $row['specialties'],
            'specialty' => $row['sname'] ?: 'Not Selected',
            'password' => $row['docpassword'],
            'status' => $row['status'] ?? 'active',
            'deactivate_until' => $row['deactivate_until'] ?? ''
        ];
    }
    $stmt->close();
} else {
    header("Location: ../login.php");
    exit();
}

$doctor = $_SESSION['doctor'];

if(isset($_POST['save'])){
    $doctor = $_SESSION['doctor'];
    $oldPasswordInput = trim($_POST['old_password'] ?? '');
    $newPasswordInput = trim($_POST['new_password'] ?? '');

    if(!empty($newPasswordInput)){
        if(empty($oldPasswordInput) || (!password_verify($oldPasswordInput, $doctor['password']) && $oldPasswordInput !== $doctor['password'])){
            header("Location: ".$_SERVER['PHP_SELF']."?error=wrongpass");
            exit();
        }
    }

    // Update doctor information in database
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialty = $_POST['specialty'];
    $docid = $doctor['docid'];
    $oldEmail = $doctor['email'];
    
    $update_stmt = $database->prepare("UPDATE doctor SET docname=?, docemail=?, doctel=?, specialties=? WHERE docid=?");
    $update_stmt->bind_param("sssii", $name, $email, $phone, $specialty, $docid);
    $update_stmt->execute();
    $update_stmt->close();

    if ($email !== $oldEmail) {
        $webuser_stmt = $database->prepare("UPDATE webuser SET email = ? WHERE email = ?");
        if ($webuser_stmt) {
            $webuser_stmt->bind_param("ss", $email, $oldEmail);
            $webuser_stmt->execute();
            $webuser_stmt->close();
        }
        $_SESSION['user'] = $email;
    }
    
    // Update session
    $_SESSION['doctor']['name'] = $name;
    $_SESSION['doctor']['email'] = $email;
    $_SESSION['doctor']['phone'] = $phone;
    $_SESSION['doctor']['specialty_id'] = $specialty;
    
    // Get specialty name
    $spec_stmt = $database->prepare("SELECT sname FROM specialties WHERE id=?");
    $spec_stmt->bind_param("i", $specialty);
    $spec_stmt->execute();
    $spec_result = $spec_stmt->get_result();
    if($spec_row = $spec_result->fetch_assoc()){
        $_SESSION['doctor']['specialty'] = $spec_row['sname'];
    }
    $spec_stmt->close();

    // Update password if provided
    if(!empty($newPasswordInput)){
        $hashed_password = password_hash($newPasswordInput, PASSWORD_DEFAULT);
        $pass_stmt = $database->prepare("UPDATE doctor SET docpassword=? WHERE docid=?");
        $pass_stmt->bind_param("si", $hashed_password, $docid);
        $pass_stmt->execute();
        $pass_stmt->close();
        $_SESSION['doctor']['password'] = $hashed_password;
    }

    header("Location: ".$_SERVER['PHP_SELF']."?success=1");
    exit();
}

if(isset($_POST['deactivate'])){
    $password = trim($_POST['deactivate_password'] ?? '');
    $docid = $doctor['docid'];

    if(empty($password) || (!password_verify($password, $doctor['password']) && $password !== $doctor['password'])){
        header("Location: ".$_SERVER['PHP_SELF']."?error=deactivate_invalid_password");
        exit();
    }

    $days = $_POST['duration'];

    if($days == "7"){
        $until = date("Y-m-d", strtotime("+7 days"));
    }elseif($days == "30"){
        $until = date("Y-m-d", strtotime("+1 month"));
    }else{
        $until = date("Y-m-d", strtotime("+3 months"));
    }

    $update_stmt = $database->prepare("UPDATE doctor SET status = 'deactivated', deactivate_until = ? WHERE docid = ?");
    $update_stmt->bind_param("si", $until, $docid);
    $update_stmt->execute();
    $update_stmt->close();

    session_destroy();
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['delete'])){
    if($_POST['confirm_delete'] === "DELETE"){
        session_destroy();
        header("Location: login.php");
        exit();
    }else{
        header("Location: ".$_SERVER['PHP_SELF']."?error=delete");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile Settings</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Inter,sans-serif;}

body{background:#eef4fb;color:#1f2937;}

.topbar{
    background:#fff;
    padding:14px 22px;
    display:flex;
    justify-content:space-between;
    border-bottom:1px solid #e5e7eb;
    align-items:center;
}

.logo{
    font-size:22px;
    font-weight:800;
    color:#0ea5e9;
    display:flex;
    gap:10px;
    align-items:center;
}

.user{
    background:#f8fafc;
    padding:8px 14px;
    border-radius:20px;
    font-size:14px;
}

.container{display:flex;}

.sidebar{
    width:250px;
    min-height:100vh;
    background:#fff;
    border-right:1px solid #e5e7eb;
    padding:20px;
}

.profile-box{
    text-align:center;
    padding-bottom:15px;
    border-bottom:1px solid #e5e7eb;
}

.profile-box img{
    width:85px;height:85px;border-radius:50%;
    border:3px solid #dbeafe;
}

.profile-box h3{margin-top:10px;font-size:18px;}
.profile-box p{font-size:13px;color:#6b7280;}

.sidebar a{
    display:flex;
    gap:10px;
    padding:12px;
    text-decoration:none;
    color:#374151;
    margin-top:6px;
    border-radius:10px;
    font-size:14px;
}

.sidebar a:hover{background:#f1f5f9;}

.sidebar .active{
    background:linear-gradient(135deg,#0ea5e9,#14b8a6);
    color:#fff;
}

.logout{color:#ef4444!important;}

.main{flex:1;padding:25px;}

.card{
    background:#fff;
    border-radius:18px;
    padding:20px;
    margin-bottom:18px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.header{
    background:linear-gradient(135deg,#0ea5e9,#14b8a6);
    color:#fff;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.input-group{margin-bottom:12px;}

label{
    font-size:13px;
    font-weight:600;
    display:block;
    margin-bottom:6px;
}

input,textarea{
    width:100%;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #dbeafe;
    background:#f8fafc;
}

textarea{height:80px;resize:none;}

.save-btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#0ea5e9,#14b8a6);
    color:#fff;
    font-weight:700;
    margin-top:10px;
    cursor:pointer;
}

.actions{
    display:flex;
    gap:10px;
    margin-top:15px;
}

.deactivate{
    flex:1;
    padding:10px;
    border:none;
    border-radius:10px;
    background:#facc15;
    font-weight:600;
    cursor:pointer;
}

.delete{
    flex:1;
    padding:10px;
    border:none;
    border-radius:10px;
    background:#ef4444;
    color:#fff;
    font-weight:600;
    cursor:pointer;
}

.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.55);
    justify-content:center;
    align-items:center;
}

.modal-content{
    background:#fff;
    width:420px;
    border-radius:18px;
    padding:22px;
}

.modal-content h3{
    margin-bottom:10px;
}

.modal-content p{
    font-size:13px;
    color:#6b7280;
    margin-bottom:15px;
    line-height:1.5;
}

.modal-actions{
    display:flex;
    gap:10px;
    margin-top:10px;
}

.modal-actions button{
    flex:1;
    padding:10px;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

.confirm{background:#ef4444;color:#fff;}
.cancel{background:#e5e7eb;}
</style>
</head>

<body>

<div class="topbar">
    <div class="logo"><i class="fa-solid fa-heart-pulse"></i> MEDCHECK</div>
    <div class="user"><i class="fa fa-user-doctor"></i> <?php echo $doctor['name']; ?></div>
</div>

<div class="container">

<div class="sidebar">

    <div class="profile-box">
        <img src="https://i.imgur.com/6VBx3io.png">
        <h3><?php echo $doctor['name']; ?></h3>
        <p><?php echo $doctor['specialty']; ?></p>
    </div>

    <a href="dashboard.php"><i class="fa fa-house"></i> Dashboard</a>
    <a href="appointmentdok.php"><i class="fa fa-calendar"></i> Appointments</a>
    <a href="mypatient.php"><i class="fa fa-users"></i> Patients</a>
    <a href="schedule.php"><i class="fa fa-clock"></i> Schedule</a>
    <a href="records.php"><i class="fa fa-file-medical"></i> Records</a>
    <a href="notifications.php"><i class="fa fa-bell"></i> Notifications</a>
    <a class="active" href="#"><i class="fa fa-user-gear"></i> Profile Settings</a>
    <a class="logout" href="?logout=true"><i class="fa fa-right-from-bracket"></i> Logout</a>

</div>

<div class="main">

<div class="card header">
    <h2><i class="fa fa-user-gear"></i> Profile Settings</h2>
    <p>Manage your account information, security, and settings</p>
</div>

<?php if(!empty($message)): ?>
<div class="card" style="border-left:4px solid #0ea5e9;">
    <p style="color:#0f766e;margin:0;"><?php echo htmlspecialchars($message); ?></p>
</div>
<?php endif; ?>

<div class="card">

<form method="POST">

<div class="form-grid">

<div>

<div class="input-group">
<label>Name</label>
<input type="text" name="name" value="<?php echo $doctor['name']; ?>">
</div>

<div class="input-group">
<label>Email</label>
<input type="email" name="email" value="<?php echo $doctor['email']; ?>">
</div>

<div class="input-group">
<label>Specialty</label>
<select name="specialty" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid #dbeafe;background:#f8fafc;">
    <option value="">Select Your Specialty</option>
    <?php 
        $all_specs = array();
        $spec_list = $database->prepare("SELECT id, sname FROM specialties ORDER BY sname ASC");
        $spec_list->execute();
        $specs_result = $spec_list->get_result();
        while($spec_row = $specs_result->fetch_assoc()){
            $selected = ($doctor['specialty_id'] == $spec_row['id']) ? 'selected' : '';
            echo "<option value='" . $spec_row['id'] . "' $selected>" . $spec_row['sname'] . "</option>";
        }
        $spec_list->close();
    ?>
</select>
</div>

<div class="input-group">
<label>Contact</label>
<input type="text" name="phone" value="<?php echo $doctor['phone']; ?>">
</div>

<div class="input-group">
<label>NIC</label>
<input type="text" name="nic" value="<?php echo $doctor['nic']; ?>" readonly>
</div>

<div class="input-group">
<label>Old Password</label>
<input type="password" name="old_password">
</div>

<div class="input-group">
<label>New Password</label>
<input type="password" name="new_password">
</div>

</div>

<div style="text-align:center;">
<img src="https://i.imgur.com/6VBx3io.png" style="width:120px;border-radius:50%;border:3px solid #dbeafe;">

<div class="actions">
<button type="button" onclick="openDeactivate()" class="deactivate">
<i class="fa fa-ban"></i> Deactivate
</button>

<button type="button" onclick="openDelete()" class="delete">
<i class="fa fa-trash"></i> Delete
</button>
</div>

</div>

</div>

<button class="save-btn" name="save">
<i class="fa fa-save"></i> Save Changes
</button>

</form>

</div>

</div>
</div>

<div class="modal" id="deactivateModal">
<div class="modal-content">

<h3>Deactivate Account</h3>
<p>
If you deactivate your account, your profile will be hidden for the selected duration.
You can reactivate anytime after the period ends.
</p>

<form method="POST">
<div class="input-group" style="margin-bottom:12px;">
<label>Password</label>
<input type="password" name="deactivate_password" placeholder="Enter your password" required>
</div>
<select name="duration" style="width:100%;padding:10px;border-radius:10px;margin-bottom:10px;">
<option value="7">7 Days</option>
<option value="30">1 Month</option>
<option value="90">3 Months</option>
</select>

<div class="modal-actions">
<button class="confirm" name="deactivate">Confirm</button>
<button type="button" class="cancel" onclick="closeModal()">Cancel</button>
</div>
</form>

</div>
</div>

<div class="modal" id="deleteModal">
<div class="modal-content">

<h3>Delete Account</h3>

<p>
This action is permanent. All your data, appointments, and records will be removed.
Please type <b>DELETE</b> to confirm.
</p>

<form method="POST">
<input type="text" name="confirm_delete" placeholder="Type DELETE">

<div class="modal-actions">
<button class="confirm" name="delete">Delete</button>
<button type="button" class="cancel" onclick="closeModal()">Cancel</button>
</div>
</form>

</div>
</div>

<script>
function openDeactivate(){
document.getElementById("deactivateModal").style.display="flex";
}
function openDelete(){
document.getElementById("deleteModal").style.display="flex";
}
function closeModal(){
document.getElementById("deactivateModal").style.display="none";
document.getElementById("deleteModal").style.display="none";
}
window.onclick=function(e){
if(e.target.classList.contains("modal")){
closeModal();
}
}
</script>

</body>
</html>