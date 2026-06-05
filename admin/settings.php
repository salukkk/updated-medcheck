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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings | Medcheck</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

:root{
    --bg:#f4f7fb;
    --card:#ffffff;
    --text:#0f172a;
    --text2:#64748b;
    --border:#dbe2ea;
    --input:#f8fafc;

    --main:#00bcd4;
    --main2:#4facfe;

    --danger:#ef4444;

    --shadow:0 15px 35px rgba(0,0,0,0.06);

    --font-size:16px;
}

body.dark-mode{
    --bg:#0f172a;
    --card:#1e293b;
    --text:#ffffff;
    --text2:#cbd5e1;
    --border:#334155;
    --input:#273449;

    --shadow:0 15px 35px rgba(0,0,0,0.35);
}

html{
    font-size:var(--font-size);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    transition:0.3s;
}

body{
    background:var(--bg);
    color:var(--text);
    font-family:Arial, Helvetica, sans-serif;
}

/* TOPBAR */

.topbar{
    width:100%;
    height:75px;
    background:var(--card);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 30px;
    box-shadow:var(--shadow);
    position:sticky;
    top:0;
    z-index:1000;
}

.logo{
    font-size:1.6rem;
    font-weight:700;
    color:var(--main);
}

.user{
    color:var(--text);
    font-weight:600;
}

/* CONTAINER */

.container{
    display:flex;
    min-height:calc(100vh - 75px);
}

/* SIDEBAR */

.sidebar{
    width:270px;
    background:var(--card);
    padding:25px 18px;
    box-shadow:var(--shadow);
}

.profile{
    text-align:center;
    margin-bottom:35px;
}

.profile img{
    width:100px;
    height:100px;
    border-radius:50%;
    object-fit:cover;
    margin-bottom:14px;
}

.profile h3{
    color:var(--text);
    margin-bottom:6px;
}

.profile p{
    color:var(--text2);
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:14px;
    padding:15px 18px;
    border-radius:14px;
    text-decoration:none;
    color:var(--text);
    font-weight:600;
    margin-bottom:12px;
}

.sidebar a:hover{
    background:#dff9ff;
}

.sidebar a.active{
    background:linear-gradient(135deg,var(--main),var(--main2));
    color:#fff;
}

.logout{
    margin-top:20px;
    background:#fee2e2;
    color:#dc2626 !important;
}

/* MAIN */

.main{
    flex:1;
    padding:30px;
}

/* WELCOME */

.welcome{
    background:var(--card);
    padding:30px;
    border-radius:28px;
    box-shadow:var(--shadow);
    margin-bottom:25px;
}

.welcome h1{
    color:var(--text);
    margin-bottom:10px;
}

.welcome p{
    color:var(--text2);
}

/* ACCESSIBILITY */

.settings-control{
    background:var(--card);
    padding:28px;
    border-radius:28px;
    box-shadow:var(--shadow);
    margin-bottom:25px;
}

.settings-control h2{
    color:var(--text);
    margin-bottom:20px;
}

.control-grid{
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}

.control-btn{
    border:none;
    padding:14px 22px;
    border-radius:14px;
    background:linear-gradient(135deg,var(--main),var(--main2));
    color:#fff;
    font-size:1rem;
    font-weight:600;
    cursor:pointer;
}

.switch-box{
    display:flex;
    align-items:center;
    gap:14px;
    background:var(--input);
    padding:14px 20px;
    border-radius:14px;
    border:1px solid var(--border);
    color:var(--text);
}

.switch{
    position:relative;
    width:60px;
    height:30px;
}

.switch input{
    opacity:0;
    width:0;
    height:0;
}

.slider{
    position:absolute;
    inset:0;
    background:#ccc;
    border-radius:999px;
    cursor:pointer;
}

.slider::before{
    content:"";
    position:absolute;
    width:24px;
    height:24px;
    left:3px;
    top:3px;
    border-radius:50%;
    background:#fff;
    transition:0.3s;
}

.switch input:checked + .slider{
    background:var(--main);
}

.switch input:checked + .slider::before{
    transform:translateX(30px);
}

/* PROFILE */

.profile-card{
    display:flex;
    align-items:center;
    gap:20px;
    padding:30px;
    border-radius:28px;
    background:var(--card);
    box-shadow:var(--shadow);
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
    color:var(--text);
}

.profile-details p{
    margin-top:8px;
    color:var(--text2);
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
    background:var(--card);
    border-radius:24px;
    padding:28px;
    box-shadow:var(--shadow);
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
    color:var(--text);
}

.stat-card p{
    color:var(--text2);
    line-height:1.7;
    margin-top:10px;
}

/* DANGER */

.danger-card{
    margin-top:28px;
    padding:28px;
    border-radius:24px;
    background:var(--card);
    border-left:6px solid #ef4444;
    box-shadow:0 15px 35px rgba(239,68,68,0.10);
}

.danger-card h2{
    margin:0;
    color:#dc2626;
}

.danger-card p{
    margin-top:12px;
    color:var(--text2);
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
    background:var(--card);
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
    color:var(--text);
}

.popup-header p{
    color:var(--text2);
    margin-top:10px;
}

/* FORM */

.edit-form{
    display:flex;
    flex-direction:column;
    gap:16px;
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
    color:var(--text);
}

.form-row input{
    width:100%;
    padding:14px 16px;
    border-radius:14px;
    border:1px solid var(--border);
    background:var(--input);
    font-size:14px;
    color:var(--text);
}

.form-row input:focus{
    outline:none;
    border-color:#38bdf8;
    background:var(--card);
}

/* VIEW */

.view-box{
    display:flex;
    flex-direction:column;
    gap:14px;
}

.view-item{
    background:var(--input);
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
    color:var(--text2);
    margin-bottom:3px;
}

.view-item strong{
    color:var(--text);
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
    background:var(--input);
    border:1px solid var(--border);
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
    color:var(--text);
}

.manage-text p{
    margin-top:5px;
    color:var(--text2);
    font-size:13px;
    line-height:1.6;
}

.no-underline{
    text-decoration:none;
}

@media(max-width:900px){

    .container{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
    }

}

@media(max-width:700px){

    .main{
        padding:20px;
    }

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

<body>

<div class="topbar">
    <div class="logo">
        <i class="fa-solid fa-heart-pulse"></i> MEDCHECK
    </div>

    <div class="user">
        <i class="fa-solid fa-user"></i> <?php echo $username; ?>
    </div>
</div>

<div class="container">

<div class="sidebar">

    <div class="profile">
        <img src="../img/user.png">
        <h3><?php echo $username; ?></h3>
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

<div class="welcome">

    <h1>
        <i class="fa-solid fa-gear"></i>
        Account Settings
    </h1>

    <p>
        Manage your profile and account security.
    </p>

</div>

<!-- ACCESSIBILITY -->

<div class="settings-control">

    <h2>
        <i class="fa-solid fa-sliders"></i>
        Appearance & Accessibility
    </h2>

    <div class="control-grid">

        <button class="control-btn" onclick="increaseFont()">
            A+
        </button>

        <button class="control-btn" onclick="decreaseFont()">
            A-
        </button>

        <button class="control-btn" onclick="resetFont()">
            Reset Font
        </button>

        <div class="switch-box">

            <i class="fa-solid fa-moon"></i>

            <label class="switch">

                <input type="checkbox" id="darkModeToggle">

                <span class="slider"></span>

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
        <h2><?php echo $username; ?></h2>

        <p>
            <i class="fa-solid fa-envelope"></i>
            <?php echo $useremail; ?>
        </p>

        <div class="profile-pill">
            <i class="fa-solid fa-circle-check"></i>
            Verified Patient Account
        </div>
    </div>

</div>

<div class="grid">

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

    <a href="?action=drop&id=<?php echo $userid ?>&name=<?php echo $username ?>" class="no-underline">

        <button class="book-btn btn-danger" style="margin-top:15px;">
            Manage Account
        </button>

    </a>

</div>

</div>
</div>

<script>

/* DARK MODE */

const darkToggle = document.getElementById("darkModeToggle");

function applyDarkMode(){

    const mode = localStorage.getItem("medcheck-dark-mode");

    if(mode === "enabled"){

        document.body.classList.add("dark-mode");

        if(darkToggle){
            darkToggle.checked = true;
        }

    }else{

        document.body.classList.remove("dark-mode");

    }

}

applyDarkMode();

if(darkToggle){

    darkToggle.addEventListener("change", function(){

        if(this.checked){

            localStorage.setItem(
                "medcheck-dark-mode",
                "enabled"
            );

        }else{

            localStorage.setItem(
                "medcheck-dark-mode",
                "disabled"
            );

        }

        applyDarkMode();

    });

}

/* FONT SIZE */

function applyFontSize(){

    let size = localStorage.getItem(
        "medcheck-font-size"
    );

    if(!size){

        size = 16;

        localStorage.setItem(
            "medcheck-font-size",
            16
        );

    }

    document.documentElement.style.setProperty(
        "--font-size",
        size + "px"
    );

}

applyFontSize();

function increaseFont(){

    let size = parseInt(
        localStorage.getItem(
            "medcheck-font-size"
        )
    );

    if(size < 24){

        size++;

        localStorage.setItem(
            "medcheck-font-size",
            size
        );

        applyFontSize();

    }

}

function decreaseFont(){

    let size = parseInt(
        localStorage.getItem(
            "medcheck-font-size"
        )
    );

    if(size > 12){

        size--;

        localStorage.setItem(
            "medcheck-font-size",
            size
        );

        applyFontSize();

    }

}

function resetFont(){

    localStorage.setItem(
        "medcheck-font-size",
        16
    );

    applyFontSize();

}

</script>

<?php

if($_GET){

$id=$_GET["id"];
$action=$_GET["action"];

/* VIEW */
if($action=='view'){

$row=$database->query("select * from patient where pid='$id'")->fetch_assoc();

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

$row=$database->query("select * from patient where pid='$id'")->fetch_assoc();

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
                <label>NIC</label>
                <input type="text" name="nic" value="'.$row["pnic"].'" required>
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

}
?>

</body>
</html>