
<?php
session_start();

if(isset($_SESSION["user"])){

    if(($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'a'){
        header("location: ../login.php");
        exit();

    }else{
        $useremail = $_SESSION["user"];
    }

}else{
    header("location: ../login.php");
    exit();
}

include("../connection.php");

$username = "Administrator";
date_default_timezone_set('Asia/Manila');
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Schedule</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#f1f5f9;
    font-family:Arial, Helvetica, sans-serif;
}

/* =========================
   MAIN CONTENT
========================= */

.main{
    width:100%;
}

/* =========================
   OVERLAY
========================= */

.overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100vh;
    background:rgba(15,23,42,0.70);
    backdrop-filter:blur(5px);

    display:flex;
    justify-content:center;
    align-items:center;

    z-index:9999;
    padding:20px;
}

/* =========================
   POPUP
========================= */

.popup{
    width:100%;
    max-width:600px;
    background:#fff;
    border-radius:30px;
    padding:45px;

    
    margin:auto;
    position:relative;

    box-shadow:0 25px 70px rgba(0,0,0,0.18);

    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;

    animation:popupShow .25s ease;
}

@keyframes popupShow{
    from{
        opacity:0;
        transform:translateY(10px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* =========================
   TITLE
========================= */

.popup-title{
    width:100%;
    text-align:center;
    font-size:34px;
    font-weight:700;
    color:#111827;
    margin-bottom:10px;
}

/* =========================
   SUBTITLE
========================= */

.popup-subtitle{
    width:100%;
    max-width:430px;
    text-align:center;
    color:#6b7280;
    font-size:14px;
    line-height:1.8;
    margin-bottom:35px;
}

/* =========================
   FORM
========================= */

.popup form{
    width:100%;
    max-width:430px;
    margin:auto;
}

/* =========================
   FORM GROUP
========================= */

.form-group{
    width:100%;
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:700;
    color:#374151;
}

/* =========================
   ROW
========================= */

.form-row{
    width:100%;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

/* =========================
   INPUTS
========================= */

.form-group input,
.form-group select{
    width:100%;
    height:58px;
    border:none;
    outline:none;
    background:#f8fafc;
    border-radius:16px;
    padding:0 18px;
    font-size:14px;
    color:#111827;
    border:1px solid transparent;
}

.form-group input:focus,
.form-group select:focus{
    background:#fff;
    border:1px solid #8b5cf6;
    box-shadow:0 0 0 4px rgba(139,92,246,0.10);
}

/* =========================
   VIEW SECTION
========================= */

.view-section{
    width:100%;
    max-width:430px;

    /* CENTER */
    margin:auto;

    text-align:center;
}

.view-box{
    background:#f8fafc;
    border-radius:18px;
    padding:18px;
    margin-bottom:15px;
    text-align:center;
}

.view-label{
    font-size:12px;
    font-weight:700;
    color:#6b7280;
    margin-bottom:7px;
    text-transform:uppercase;
}

.view-value{
    font-size:16px;
    font-weight:600;
    color:#111827;
}

/* =========================
   BUTTONS
========================= */

.popup-footer{
    width:100%;
    margin-top:28px;
}

.btn{
    width:100%;
    height:58px;
    border:none;
    border-radius:16px;
    display:flex;
    justify-content:center;
    align-items:center;
    text-decoration:none;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
    transition:.3s;
}

.btn-primary{
    background:#8b5cf6;
    color:white;
}

.btn-primary:hover{
    background:#7c3aed;
}

.btn-secondary{
    background:#111827;
    color:white;
    margin-top:12px;
}

.btn-secondary:hover{
    background:#000;
}

/* =========================
   DELETE POPUP
========================= */

.delete-popup{
    width:100%;
    max-width:420px;
    background:#fff;
    border-radius:26px;
    padding:40px;

    text-align:center;

    /* CENTER */
    margin:auto;

    box-shadow:0 25px 70px rgba(0,0,0,0.18);

    animation:popupShow .25s ease;
}

.delete-title{
    font-size:30px;
    font-weight:700;
    color:#111827;
    margin-bottom:10px;
}

.delete-text{
    color:#6b7280;
    line-height:1.8;
    margin-bottom:28px;
}

/* =========================
   TABLE
========================= */

.card.table-card{
    margin-top:20px;
}

.table-container{
    overflow-x:auto;
}

.data-table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:18px;
    overflow:hidden;
}

.data-table th,
.data-table td{
    padding:18px;
    border-bottom:1px solid #eee;
}

.data-table th{
    background:#f8fafc;
}

.action-buttons{
    display:flex;
    gap:10px;
}

.btn-view{
    background:#3b82f6;
    color:white;
    padding:10px 15px;
    border-radius:10px;
    text-decoration:none;
}

.btn-delete{
    background:#ef4444;
    color:white;
    padding:10px 15px;
    border-radius:10px;
    text-decoration:none;
}

.btn-primary-main{
    background:#8b5cf6;
    color:white;
    padding:13px 22px;
    border-radius:12px;
    text-decoration:none;
    font-weight:600;
}

/* =========================
   MOBILE
========================= */

@media(max-width:700px){

    .popup{
        padding:28px;
    }

    .form-row{
        grid-template-columns:1fr;
    }

    .popup-title{
        font-size:28px;
    }

}

</style>

</head>

<body>

<?php
$list11 = $database->query("select * from schedule;");
?>

<div class="topbar">

<div class="logo">
<i class="fa-solid fa-heart-pulse"></i> MEDCHECK
</div>

<div class="user">
<i class="fa-solid fa-user-shield"></i>
<?php echo $username; ?>
</div>

</div>

<div class="container">

<div class="sidebar">

<div class="profile">
<img src="../img/user.png" alt="">
<h3><?php echo $username; ?></h3>
<p>Administrator</p>
</div>

<a href="index.php">
<i class="fa-solid fa-gauge"></i> Dashboard
</a>

<a href="doctors.php">
<i class="fa-solid fa-user-doctor"></i> Doctors
</a>

<a class="active" href="schedule.php">
<i class="fa-solid fa-calendar-days"></i> Schedule
</a>

<a href="appointment.php">
<i class="fa-solid fa-calendar-check"></i> Appointment
</a>

<a href="patient.php">
<i class="fa-solid fa-users"></i> Patients
</a>

<a class="logout" href="../logout.php">
<i class="fa-solid fa-right-from-bracket"></i> Logout
</a>

</div>

<div class="main">

<div class="card welcome">

<div class="welcome-content">

<h1>
<i class="fa-solid fa-calendar-days"></i>
Schedule Manager
</h1>

<p>
Manage sessions professionally.
</p>

</div>

</div>

<div class="card table-card">

<div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">

<h2>
All Sessions (<?php echo $list11->num_rows; ?>)
</h2>

<a href="?action=add-session" class="btn-primary-main">
Add Session
</a>

</div>

<?php

$sqlmain = "select schedule.scheduleid,schedule.title,doctor.docname,schedule.scheduledate,schedule.scheduletime,schedule.nop 
from schedule 
inner join doctor on schedule.docid=doctor.docid 
order by schedule.scheduledate desc";

$result = $database->query($sqlmain);

?>

<div class="table-container">

<table class="data-table">

<thead>

<tr>
<th>Session</th>
<th>Doctor</th>
<th>Date & Time</th>
<th>Patients</th>
<th>Actions</th>
</tr>

</thead>

<tbody>

<?php

if($result->num_rows==0){

echo "
<tr>
<td colspan='5' style='text-align:center;padding:40px;'>
No sessions found.
</td>
</tr>";

}else{

while($row = $result->fetch_assoc()){

echo "

<tr>

<td>".$row['title']."</td>

<td>".$row['docname']."</td>

<td>".$row['scheduledate']." | ".substr($row['scheduletime'],0,5)."</td>

<td>".$row['nop']."</td>

<td>

<div class='action-buttons'>

<a href='?action=view&id=".$row['scheduleid']."' class='btn-view'>
View
</a>

<a href='?action=delete&id=".$row['scheduleid']."' class='btn-delete'>
Delete
</a>

</div>

</td>

</tr>

";

}

}

?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<?php

if($_GET){

$action = $_GET['action'];

/* =========================
   ADD SESSION
========================= */

if($action=="add-session"){

?>

<div class="overlay">

<div class="popup">

<div class="popup-title">
Add New Session
</div>

<div class="popup-subtitle">
Create and organize new medical schedules professionally.
</div>

<form action="add-session.php" method="POST">

<div class="form-group">

<label>Session Title</label>

<input type="text" name="title" placeholder="Enter session title" required>

</div>

<div class="form-group">

<label>Select Doctor</label>

<select name="docid" required>

<option value="" hidden selected>
Choose Doctor
</option>

<?php

$list11 = $database->query("select * from doctor order by docname asc");

while($doc = $list11->fetch_assoc()){

echo "<option value='".$doc['docid']."'>".$doc['docname']."</option>";

}

?>

</select>

</div>

<div class="form-row">

<div class="form-group">

<label>Patient Limit</label>

<input type="number" name="nop" placeholder="Enter patient limit" required>

</div>

<div class="form-group">

<label>Session Date</label>

<input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>

</div>

</div>

<div class="form-group">

<label>Session Time</label>

<input type="time" name="time" required>

</div>

<div class="popup-footer">

<button type="submit" name="shedulesubmit" class="btn btn-primary">
Create Session
</button>

<a href="schedule.php" class="btn btn-secondary">
Close
</a>

</div>

</form>

</div>

</div>

<?php
}

/* =========================
   VIEW SESSION
========================= */

elseif($action=="view"){

$id = $_GET['id'];

$sqlmain = "select schedule.scheduleid,schedule.title,doctor.docname,schedule.scheduledate,schedule.scheduletime,schedule.nop 
from schedule 
inner join doctor on schedule.docid=doctor.docid 
where schedule.scheduleid=$id";

$result = $database->query($sqlmain);

$row = $result->fetch_assoc();

?>

<div class="overlay">

<div class="popup">

<div class="view-section">

<div class="popup-title">
Session Details
</div>

<div class="popup-subtitle">
Complete information and schedule overview.
</div>

<div class="view-box">
<div class="view-label">Session Title</div>
<div class="view-value"><?php echo $row['title']; ?></div>
</div>

<div class="view-box">
<div class="view-label">Doctor Name</div>
<div class="view-value"><?php echo $row['docname']; ?></div>
</div>

<div class="view-box">
<div class="view-label">Session Date</div>
<div class="view-value"><?php echo $row['scheduledate']; ?></div>
</div>

<div class="view-box">
<div class="view-label">Session Time</div>
<div class="view-value"><?php echo substr($row['scheduletime'],0,5); ?></div>
</div>

<div class="view-box">
<div class="view-label">Maximum Patients</div>
<div class="view-value"><?php echo $row['nop']; ?> Patients</div>
</div>

<div class="popup-footer">

<a href="schedule.php" class="btn btn-secondary">
Close
</a>

</div>

</div>

</div>

</div>

<?php
}

/* =========================
   DELETE SESSION
========================= */

elseif($action=="delete"){

$id = $_GET['id'];

?>

<div class="overlay">

<div class="delete-popup">

<div class="delete-title">
Delete Session?
</div>

<div class="delete-text">
Are you sure you want to permanently delete this session?
</div>

<a href="delete-session.php?id=<?php echo $id; ?>" class="btn btn-primary" style="background:#ef4444;">
Delete
</a>

<a href="schedule.php" class="btn btn-secondary">
Cancel
</a>

</div>

</div>

<?php
}

}
?>

</body>
</html>

