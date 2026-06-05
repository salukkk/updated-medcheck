<?php  
session_start();

if(isset($_SESSION["user"])){
    if($_SESSION["user"]=="" || $_SESSION['usertype']!='a'){
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

// ADMIN
$username = "Administrator";

// STATS
$doctorrow = $database->query("SELECT * FROM doctor");
$patientrow = $database->query("SELECT * FROM patient");
$appointmentrow = $database->query("SELECT * FROM appointment");
$schedulerow = $database->query("SELECT * FROM schedule WHERE scheduledate=CURDATE()");

// SYSTEM INSIGHTS
$today = date('Y-m-d');

$pendingAppointments = $database->query("SELECT * FROM appointment WHERE appodate >= '$today'");
$completedAppointments = $database->query("SELECT * FROM appointment WHERE appodate < '$today'");
$cancelledAppointments = $database->query("SELECT * FROM appointment WHERE appodate < '$today'");

// UPCOMING APPOINTMENTS
$upcomingAppointments = $database->query("
    SELECT 
        appointment.appoid,
        appointment.apponum,
        appointment.appodate,
        patient.pname,
        doctor.docname,
        schedule.title as session_title,
        schedule.scheduledate,
        schedule.scheduletime
    FROM appointment
    INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
    INNER JOIN patient ON appointment.pid = patient.pid
    INNER JOIN doctor ON schedule.docid = doctor.docid
    WHERE schedule.scheduledate >= '$today' 
    AND schedule.scheduledate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medcheck Dashboard</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.show-all-btn{
    background: linear-gradient(135deg, #4facfe, #00c6ff);
    color:white;
    padding:10px 18px;
    border-radius:25px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:6px;
    transition:0.3s;
}
.show-all-btn:hover{
    transform:scale(1.05);
    background: linear-gradient(135deg, #43e97b, #38f9d7);
}
</style>

</head>

<body>

<!-- TOPBAR -->
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

<!-- SIDEBAR (ICONS FIXED) -->
<div class="sidebar">

    <div class="profile">
        <img src="../img/user.png">
        <h3><?php echo $username; ?></h3>
        <p>Administrator</p>
    </div>

    <a class="active" href="index.php">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    <a href="doctors.php">
        <i class="fa-solid fa-user-doctor"></i> Doctors
    </a>

    <a href="schedule.php">
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

<!-- MAIN -->
<div class="main">

<!-- HEADER -->
<div class="card welcome">
    <div class="welcome-content">
        <h1><i class="fa-solid fa-chart-line"></i> Dashboard</h1>
        <p>System Overview and Activity Summary</p>
    </div>
    <div class="welcome-info">
        <span class="date"><i class="fa-solid fa-calendar"></i> <?php echo date('F d, Y'); ?></span>
    </div>
</div>

<!-- STATS -->
<div class="grid">

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-stethoscope stat-icon"></i>
            <h2><?php echo ($doctorrow ? $doctorrow->num_rows : 0); ?></h2>
        </div>
        <p>Doctors</p>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-user-injured stat-icon"></i>
            <h2><?php echo ($patientrow ? $patientrow->num_rows : 0); ?></h2>
        </div>
        <p>Patients</p>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-calendar-check stat-icon"></i>
            <h2><?php echo ($appointmentrow ? $appointmentrow->num_rows : 0); ?></h2>
        </div>
        <p>Appointments</p>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-clock stat-icon"></i>
            <h2><?php echo ($schedulerow ? $schedulerow->num_rows : 0); ?></h2>
        </div>
        <p>Today Sessions</p>
    </div>

</div>

<!-- INSIGHTS -->
<div class="section-title">
    <h2><i class="fa-solid fa-chart-pie"></i> System Insights</h2>
</div>

<div class="grid">

    <div class="card stat-card">
        <h2><?php echo ($pendingAppointments ? $pendingAppointments->num_rows : 0); ?></h2>
        <p>Pending</p>
    </div>

    <div class="card stat-card">
        <h2><?php echo ($completedAppointments ? $completedAppointments->num_rows : 0); ?></h2>
        <p>Completed</p>
    </div>

    <div class="card stat-card">
        <h2><?php echo ($cancelledAppointments ? $cancelledAppointments->num_rows : 0); ?></h2>
        <p>Cancelled</p>
    </div>

</div>

<!-- BUTTON -->
<div style="display:flex; justify-content:flex-end; margin:20px 0;">
    <a href="appointment.php" class="show-all-btn">
        <i class="fa-solid fa-calendar-check"></i>
        Show All Appointments
    </a>
</div>

<!-- TABLE -->
<div class="card">

<?php if($upcomingAppointments && $upcomingAppointments->num_rows > 0): ?>

<table class="data-table">
<thead>
<tr>
<th>#</th>
<th>Patient</th>
<th>Doctor</th>
<th>Date</th>
<th>Time</th>
</tr>
</thead>

<tbody>
<?php while($appt = $upcomingAppointments->fetch_assoc()): ?>
<tr>
<td><?php echo $appt['apponum']; ?></td>
<td><?php echo $appt['pname']; ?></td>
<td><?php echo $appt['docname']; ?></td>
<td><?php echo date('M d, Y', strtotime($appt['scheduledate'])); ?></td>
<td><?php echo date('h:i A', strtotime($appt['scheduletime'])); ?></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>

<?php else: ?>
<p style="padding:20px;">No upcoming appointments.</p>
<?php endif; ?>

</div>

</div>
</div>

</body>
</html>