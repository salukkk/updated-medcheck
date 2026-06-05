<?php
session_start();

if(isset($_SESSION["user"])){
    if($_SESSION["user"]=="" || $_SESSION['usertype']!='p'){
        header("location: ../login.php");
        exit();
    } else {
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
    exit();
}

include("../connection.php");

/* USER */
$sqlmain= "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch=$userrow->fetch_assoc();

$userid= $userfetch["pid"];
$username=$userfetch["pname"];

include_once(__DIR__ . "/../patient_accessibility.php");
$accessibility = get_patient_accessibility($database, (int) $userid);
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
$safeUserEmail = htmlspecialchars($useremail, ENT_QUOTES, 'UTF-8');
$profileSaved = isset($_GET['success']) && $_GET['success'] === '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings | Medcheck</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="../css/accessibility.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    box-sizing:border-box;
}

body{
    background:#f4f7fb;
}

/* PROFILE */

.profile-card{
    display:flex;
    align-items:center;
    gap:20px;
    padding:30px;
    border-radius:28px;
    background:#fff;
    box-shadow:0 15px 35px rgba(0,0,0,0.06);
    margin-bottom:25px;
    flex-wrap:wrap;
}

.profile-image{
    position:relative;
}

.profile-image img{
    width:115px;
    height:115px;
    border-radius:50%;
    object-fit:cover;
    border:5px solid #fff;
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
}

.upload-btn{
    position:absolute;
    bottom:3px;
    right:3px;
    width:35px;
    height:35px;
    border-radius:50%;
    background:linear-gradient(135deg,#00bcd4,#4facfe);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    border:3px solid #fff;
}

.profile-details h2{
    margin:0;
    font-size:28px;
    color:#0f172a;
}

.profile-details p{
    margin-top:8px;
    color:#64748b;
}

.profile-pill{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:10px 16px;
    border-radius:999px;
    background:#e8f6ff;
    color:#0f4db2;
    margin-top:12px;
    font-size:13px;
    font-weight:600;
}

/* GRID */

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:22px;
}

.grid a{
    text-decoration:none;
}

.stat-card{
    background:#fff;
    border-radius:24px;
    padding:28px;
    box-shadow:0 15px 35px rgba(15,23,42,0.08);
    transition:0.3s;
}

.stat-card:hover{
    transform:translateY(-5px);
}

.stat-top{
    display:flex;
    justify-content:space-between;
    margin-bottom:18px;
}

.stat-icon{
    width:58px;
    height:58px;
    border-radius:18px;
    background:#ecfeff;
    color:#06b6d4;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}

.arrow-icon{
    color:#94a3b8;
}

.stat-card h3{
    margin:0;
    color:#0f172a;
}

.stat-card p{
    color:#64748b;
    line-height:1.7;
    margin-top:10px;
}

/* DANGER */

.danger-card{
    margin-top:28px;
    padding:28px;
    border-radius:24px;
    background:#fff;
    border-left:6px solid #ef4444;
    box-shadow:0 15px 35px rgba(239,68,68,0.10);
}

.danger-card h2{
    margin:0;
    color:#dc2626;
}

.danger-card p{
    margin-top:12px;
    color:#64748b;
}

.btn-danger{
    background:linear-gradient(135deg,#dc2626,#ef4444);
}

/* POPUP */

.popup{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100vh;
    background:rgba(15,23,42,0.55);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:999;
    padding:20px;
}

.popup-content{
    width:100%;
    max-width:620px;
    background:#fff;
    border-radius:26px;
    padding:30px;
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
}

.popup-header{
    text-align:center;
    margin-bottom:24px;
}

.popup-header h2{
    margin:0;
    color:#0f172a;
}

.popup-header p{
    color:#64748b;
    margin-top:10px;
}

    .alert-banner{
        display:flex;
        align-items:center;
        gap:12px;
        background:#def7ec;
        color:#166534;
        border:1px solid #86efac;
        border-radius:18px;
        padding:14px 18px;
        margin:16px 0;
        font-weight:600;
    }

    .alert-banner i{
        font-size:18px;
    }

    .form-row{
        display:grid;
        grid-template-columns:170px 1fr;
        align-items:center;
        gap:15px;
    }

.form-row label{
    font-size:14px;
    font-weight:600;
    color:#0f172a;
}

.form-row input{
    width:100%;
    padding:14px 16px;
    border-radius:14px;
    border:1px solid #dbe2ea;
    background:#f8fafc;
    font-size:14px;
}

.form-row input:focus{
    outline:none;
    border-color:#38bdf8;
    background:#fff;
}

/* VIEW */

.view-box{
    display:flex;
    flex-direction:column;
    gap:14px;
}

.view-item{
    background:#f8fafc;
    border-radius:16px;
    padding:16px;
    display:flex;
    align-items:center;
    gap:14px;
}

.view-item i{
    width:42px;
    height:42px;
    border-radius:12px;
    background:#ecfeff;
    color:#06b6d4;
    display:flex;
    align-items:center;
    justify-content:center;
}

.view-item span{
    display:block;
    font-size:12px;
    color:#64748b;
    margin-bottom:3px;
}

.view-item strong{
    color:#0f172a;
}

/* BUTTONS */

.popup-buttons{
    display:flex;
    justify-content:center;
    gap:12px;
    margin-top:22px;
    flex-wrap:wrap;
}

.book-btn{
    border:none;
    padding:13px 22px;
    border-radius:14px;
    cursor:pointer;
    font-size:14px;
    font-weight:600;
    color:#fff;
    background:linear-gradient(135deg,#00bcd4,#4facfe);
}

.cancel-btn{
    background:#e2e8f0;
    color:#0f172a;
}

.delete-btn{
    background:#ef4444;
}

.orange-btn{
    background:#f59e0b;
}

.manage-option{
    display:flex;
    align-items:center;
    gap:15px;
    padding:18px;
    border-radius:18px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    margin-bottom:14px;
}

.manage-icon{
    width:55px;
    height:55px;
    border-radius:15px;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}

.orange{
    background:#f59e0b;
}

.red{
    background:#ef4444;
}

.blue{
    background:#06b6d4;
}

.manage-text h4{
    margin:0;
    color:#0f172a;
}

.manage-text p{
    margin-top:5px;
    color:#64748b;
    font-size:13px;
    line-height:1.6;
}

.no-underline{
    text-decoration:none;
}

@media(max-width:700px){

    .popup-content{
        max-width:95%;
        padding:22px;
    }

    .form-row{
        grid-template-columns:1fr;
        gap:7px;
    }

}

</style>
</head>

<body<?php echo accessibility_body_attributes($accessibility); ?>>

<div class="topbar">
    <div class="logo">
        <i class="fa-solid fa-heart-pulse"></i> MEDCHECK
    </div>

    <div class="user">
        <i class="fa-solid fa-user"></i> <?php echo $safeUsername; ?>
    </div>
</div>

<div class="container">

<div class="sidebar">

    <div class="profile">
        <img src="../img/user.png">
        <h3><?php echo $safeUsername; ?></h3>
        <p>Patient</p>
    </div>

    <a href="index.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="doctors.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
    <a href="schedule.php"><i class="fa-solid fa-calendar-check"></i> Sessions</a>
    <a href="appointment.php"><i class="fa-solid fa-book"></i> My Bookings</a>
    <a class="active" href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>

    <a href="../logout.php" class="logout">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>

</div>

<div class="main">

<div class="card welcome">

    <div class="welcome-content">
        <h1><i class="fa-solid fa-gear"></i> Account Settings</h1>
        <p>Manage your profile and account security.</p>
    </div>

    <?php if ($profileSaved): ?>
        <div class="alert-banner">
            <i class="fa-solid fa-circle-check"></i>
            <span>Your profile changes were saved successfully.</span>
        </div>
    <?php endif; ?>
        <i class="fa-solid fa-sliders"></i>
        Appearance & Accessibility
    </h2>

    <div class="accessibility-controls">

        <button type="button" class="accessibility-btn" onclick="increaseFont()">A+</button>

        <button type="button" class="accessibility-btn" onclick="decreaseFont()">A-</button>

        <button type="button" class="accessibility-btn" onclick="resetFont()">Reset Font</button>

        <div class="accessibility-switch-box">
            <i class="fa-solid fa-moon"></i>

            <label class="accessibility-switch">
                <input type="checkbox" id="darkModeToggle" <?php echo !empty($accessibility['dark_mode']) ? 'checked' : ''; ?>>
                <span class="accessibility-slider"></span>
            </label>

            <span>Dark Mode</span>
        </div>

    </div>

</div>

<div class="profile-card">

    <div class="profile-image">
        <img src="../img/user.png">

        <label class="upload-btn">
            <i class="fa-solid fa-camera"></i>
        </label>
    </div>

    <div class="profile-details">
        <h2><?php echo $safeUsername; ?></h2>

        <p>
            <i class="fa-solid fa-envelope"></i>
            <?php echo $safeUserEmail; ?>
        </p>

        <div class="profile-pill">
            <i class="fa-solid fa-circle-check"></i>
            Verified Patient Account
        </div>
    </div>

</div>

<div class="grid">

    <!-- EDIT -->
    <a href="?action=edit&id=<?php echo $userid ?>">

        <div class="stat-card">

            <div class="stat-top">
                <div class="stat-icon">
                    <i class="fa-solid fa-user-pen"></i>
                </div>

                <i class="fa-solid fa-arrow-right arrow-icon"></i>
            </div>

            <h3>Edit Profile</h3>

            <p>
                Update your personal information professionally.
            </p>

        </div>

    </a>

    <!-- VIEW -->
    <a href="?action=view&id=<?php echo $userid ?>">

        <div class="stat-card">

            <div class="stat-top">
                <div class="stat-icon">
                    <i class="fa-solid fa-id-card"></i>
                </div>

                <i class="fa-solid fa-arrow-right arrow-icon"></i>
            </div>

            <h3>View Profile</h3>

            <p>
                Review your account information and details.
            </p>

        </div>

    </a>

    <!-- SECURITY -->
    <a href="?action=security&id=<?php echo $userid ?>">

        <div class="stat-card">

            <div class="stat-top">
                <div class="stat-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <i class="fa-solid fa-arrow-right arrow-icon"></i>
            </div>

            <h3>Security</h3>

            <p>
                Manage password, login protection, and security settings.
            </p>

        </div>

    </a>

</div>

<div class="danger-card">

    <h2>
        <i class="fa-solid fa-triangle-exclamation"></i>
        Account Management
    </h2>

    <p>
        Deactivate or permanently delete your account securely.
    </p>

    <a href="?action=drop&id=<?php echo $userid ?>" class="no-underline">

        <button class="book-btn btn-danger" style="margin-top:15px;">
            Manage Account
        </button>

    </a>

</div>

</div>
</div>

<?php render_accessibility_script($accessibility); ?>

<?php

if($_GET){

$id=$userid;
$action=$_GET["action"] ?? '';

/* VIEW */
if($action=='view'){

$stmt = $database->prepare("select * from patient where pid=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row=$stmt->get_result()->fetch_assoc();

echo '

<div class="popup">

    <div class="popup-content">

        <div class="popup-header">
            <h2><i class="fa-solid fa-user"></i> Profile Overview</h2>
            <p>Complete account information.</p>
        </div>

        <div class="view-box">

            <div class="view-item">
                <i class="fa-solid fa-user"></i>

                <div>
                    <span>Full Name</span>
                    <strong>'.$row["pname"].'</strong>
                </div>
            </div>

            <div class="view-item">
                <i class="fa-solid fa-envelope"></i>

                <div>
                    <span>Email Address</span>
                    <strong>'.$row["pemail"].'</strong>
                </div>
            </div>

            <div class="view-item">
                <i class="fa-solid fa-phone"></i>

                <div>
                    <span>Phone Number</span>
                    <strong>'.$row["ptel"].'</strong>
                </div>
            </div>

            <div class="view-item">
                <i class="fa-solid fa-location-dot"></i>

                <div>
                    <span>Address</span>
                    <strong>'.$row["paddress"].'</strong>
                </div>
            </div>

        </div>

        <div class="popup-buttons">

            <a href="settings.php">
                <button class="book-btn">
                    Close
                </button>
            </a>

        </div>

    </div>

</div>

';
}

/* EDIT */
elseif($action=='edit'){

$stmt = $database->prepare("select * from patient where pid=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row=$stmt->get_result()->fetch_assoc();

echo '

<div class="popup">

    <div class="popup-content">

        <div class="popup-header">
            <h2><i class="fa-solid fa-user-pen"></i> Edit Profile</h2>
            <p>Update your personal information professionally.</p>
        </div>

        <form action="edit-user.php" method="POST" class="edit-form">

            <input type="hidden" name="id00" value="'.$id.'">

            <div class="form-row">
                <label>Email Address</label>
                <input type="email" name="email" value="'.$row["pemail"].'" required>
            </div>

            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="name" value="'.$row["pname"].'" required>
            </div>

            <div class="form-row">
                <label>Age</label>
                <input type="number" name="age" value="'.$row["pnic"].'" min="1" required>
            </div>

            <div class="form-row">
                <label>Phone Number</label>
                <input type="text" name="Tele" value="'.$row["ptel"].'" required>
            </div>

            <div class="form-row">
                <label>Address</label>
                <input type="text" name="address" value="'.$row["paddress"].'" required>
            </div>

            <div class="form-row">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Enter new password">
            </div>

            <div class="form-row">
                <label>Confirm Password</label>
                <input type="password" name="cpassword" placeholder="Confirm password">
            </div>

            <div class="popup-buttons">

                <button class="book-btn" type="submit">
                    Save Changes
                </button>

                <a href="settings.php">
                    <button type="button" class="book-btn cancel-btn">
                        Cancel
                    </button>
                </a>

            </div>

        </form>

    </div>

</div>

';
}

/* SECURITY */
elseif($action=='security'){

echo '

<div class="popup">

    <div class="popup-content">

        <div class="popup-header">
            <h2>
                <i class="fa-solid fa-shield-halved"></i>
                Security Settings
            </h2>

            <p>
                Manage your password and account security.
            </p>
        </div>

        <form action="change-password.php" method="POST" class="edit-form">

            <input type="hidden" name="id" value="'.$id.'">

            <div class="form-row">
                <label>Current Password</label>
                <input type="password" name="currentpassword" placeholder="Enter current password" required>
            </div>

            <div class="form-row">
                <label>New Password</label>
                <input type="password" name="newpassword" placeholder="Enter new password" required>
            </div>

            <div class="form-row">
                <label>Confirm Password</label>
                <input type="password" name="confirmpassword" placeholder="Confirm new password" required>
            </div>

            <div class="popup-buttons">

                <button type="submit" class="book-btn">
                    Update Password
                </button>

                <a href="settings.php">
                    <button type="button" class="book-btn cancel-btn">
                        Cancel
                    </button>
                </a>

            </div>

        </form>

    </div>

</div>

';
}

/* ACCOUNT OPTIONS */
elseif($action=='drop'){

$name=$safeUsername;

echo '

<div class="popup">

    <div class="popup-content">

        <div class="popup-header">
            <h2 style="color:#dc2626;">
                <i class="fa-solid fa-user-slash"></i>
                Account Options
            </h2>

            <p>Hello <b>'.$name.'</b>, please choose an action below.</p>
        </div>

        <a href="?action=deactivate&id='.$id.'" class="no-underline">

            <div class="manage-option">

                <div class="manage-icon orange">
                    <i class="fa-solid fa-user-clock"></i>
                </div>

                <div class="manage-text">
                    <h4>Deactivate Account</h4>
                    <p>Your account will be hidden after 6 months. Login anytime to reactivate.</p>
                </div>

            </div>

        </a>

        <a href="?action=delete&id='.$id.'" class="no-underline">

            <div class="manage-option">

                <div class="manage-icon red">
                    <i class="fa-solid fa-trash"></i>
                </div>

                <div class="manage-text">
                    <h4>Delete Account Permanently</h4>
                    <p>Permanently remove your account and all information.</p>
                </div>

            </div>

        </a>

        <div class="popup-buttons">

            <a href="settings.php">
                <button class="book-btn cancel-btn">
                    Cancel
                </button>
            </a>

        </div>

    </div>

</div>

';
}

/* DELETE */
elseif($action=='delete'){

echo '

<div class="popup">

    <div class="popup-content">

        <div class="popup-header">
            <h2 style="color:#ef4444;">
                <i class="fa-solid fa-trash"></i>
                Delete Account
            </h2>

            <p>
                Are you sure you want to permanently delete your account?
            </p>
        </div>

        <div class="popup-buttons">

            <a href="delete-account.php?id='.$id.'">
                <button class="book-btn delete-btn">
                    Delete Account
                </button>
            </a>

            <a href="settings.php">
                <button class="book-btn cancel-btn">
                    Cancel
                </button>
            </a>

        </div>

    </div>

</div>

';
}

/* DEACTIVATE */
elseif($action=='deactivate'){

echo '

<div class="popup">

    <div class="popup-content">

        <div class="popup-header">
            <h2 style="color:#f59e0b;">
                <i class="fa-solid fa-user-clock"></i>
                Deactivate Account
            </h2>

            <p>
                Your account will be deactivated after 6 months.
                Login again anytime to reactivate your account.
            </p>
        </div>

        <form action="deactivate-account.php" method="POST" class="edit-form">

            <input type="hidden" name="id" value="'.$id.'">

            <div class="form-row">
                <label>Enter Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="popup-buttons">

                <button type="submit" class="book-btn orange-btn">
                    Confirm Deactivate
                </button>

                <a href="settings.php">
                    <button type="button" class="book-btn cancel-btn">
                        Cancel
                    </button>
                </a>

            </div>

        </form>

    </div>

</div>

';
}

}
?>

</body>
</html>
