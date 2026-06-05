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

// USER
$stmt = $database->prepare("SELECT * FROM patient WHERE pemail=?");
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userfetch=$stmt->get_result()->fetch_assoc();

$username=$userfetch["pname"];
$userid=(int)$userfetch["pid"];

include_once(__DIR__ . "/../patient_accessibility.php");
$accessibility = get_patient_accessibility($database, $userid);
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

// STATS
$doctorrow = $database->query("SELECT * FROM doctor");
$patientrow = $database->query("SELECT * FROM patient");
$appointmentrow = $database->query("SELECT * FROM appointment");
$schedulerow = $database->query("SELECT * FROM schedule WHERE scheduledate=CURDATE()");

// UPCOMING APPOINTMENT
$nextAppointmentQuery = "SELECT appointment.appoid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime
FROM appointment
INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
INNER JOIN doctor ON schedule.docid = doctor.docid
WHERE appointment.pid = ? AND schedule.scheduledate >= CURDATE()
ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC
LIMIT 1";
$nextAppointmentStmt = $database->prepare($nextAppointmentQuery);
$nextAppointmentStmt->bind_param("i", $userfetch['pid']);
$nextAppointmentStmt->execute();
$nextAppointment = $nextAppointmentStmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medcheck Dashboard</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="../css/accessibility.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body<?php echo accessibility_body_attributes($accessibility); ?>>

<!-- TOPBAR -->
<div class="topbar">
    <div class="logo">
        <i class="fa-solid fa-heart-pulse"></i> MEDCHECK
    </div>
    <div class="user">
        <i class="fa-solid fa-user"></i>
        Welcome, <?php echo $safeUsername; ?>
    </div>
</div>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="profile">
        <img src="../img/user.png">
        <h3><?php echo $safeUsername; ?></h3>
        <p>Patient</p>
    </div>

    <a class="active" href="index.php"><i class="fa-solid fa-house"></i> Home</a>
    <a href="doctors.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
    <a href="schedule.php"><i class="fa-solid fa-calendar-check"></i> Sessions</a>
    <a href="appointment.php"><i class="fa-solid fa-book"></i> Bookings</a>
    <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>

    <a class="logout" href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<!-- MAIN -->
<div class="main">

<!-- HEADER -->
<div class="card welcome">
    <div class="welcome-content">
        <h1><i class="fa-solid fa-hand-wave"></i> Welcome back, <?php echo $safeUsername; ?></h1>
        <p>Here's your dashboard for today. Stay on top of appointments, notifications, and your health.</p>
    </div>
    <div class="welcome-info">
        <span class="date"><i class="fa-solid fa-calendar"></i> <?php echo date('d M, Y'); ?></span>
    </div>
</div>

<!-- QUICK STATS -->
<div class="section-title">
    <h2><i class="fa-solid fa-chart-simple"></i> Quick Stats</h2>
</div>

<div class="grid">

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-user-doctor stat-icon"></i>
            <h2><?php echo ($doctorrow ? $doctorrow->num_rows : 0); ?></h2>
        </div>
        <p>Doctors</p>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-users stat-icon"></i>
            <h2><?php echo ($patientrow ? $patientrow->num_rows : 0); ?></h2>
        </div>
        <p>Patients</p>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-calendar-check stat-icon"></i>
            <h2><?php echo ($appointmentrow ? $appointmentrow->num_rows : 0); ?></h2>
        </div>
        <p>Total Bookings</p>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <i class="fa-solid fa-clock stat-icon"></i>
            <h2><?php echo ($schedulerow ? $schedulerow->num_rows : 0); ?></h2>
        </div>
        <p>Today's Sessions</p>
    </div>

</div>

<!-- WIDGETS -->
<div class="grid-2">

    <div class="card booknow-card">
        <div class="section-title">
            <h2><i class="fa-solid fa-calendar-plus"></i> Schedule Your Next Appointment</h2>
        </div>
        <p class="booknow-desc">Book your next appointment in seconds and maintain your care routine with confidence.</p>
        <div class="booknow-footer">
            <span class="booknow-badge"><i class="fa-solid fa-star"></i> Express Booking</span>
            <a href="appointment.php" class="action-btn view"><i class="fa-solid fa-arrow-right"></i> Schedule Now</a>
        </div>
    </div>

    <div>
        <div class="card notifications-card">
            <div class="section-title">
                <h2><i class="fa-solid fa-bell"></i> Notifications</h2>
            </div>
            <ul class="notification-list">
                <li class="notification-item"><i class="fa-solid fa-circle-check"></i> Your profile is complete and ready.</li>
                <li class="notification-item"><i class="fa-solid fa-calendar-plus"></i> New sessions are available this week.</li>
                <?php
                // Show next booked appointment (existing behavior)
                if($nextAppointment): ?>
                <li class="notification-item"><i class="fa-solid fa-bell"></i> Next appointment scheduled for <?php echo date('d M', strtotime($nextAppointment['scheduledate'])); ?>.</li>
                <?php else: ?>
                <li class="notification-item"><i class="fa-solid fa-info-circle"></i> You currently have no upcoming appointments scheduled.</li>
                <?php endif; ?>

                <?php
                // Include helper that lists upcoming doctor sessions so they "pop" on patient dashboard
                include_once(__DIR__ . '/../appointment_functions.php');
                render_upcoming_sessions_widget($database, 5);
                ?>
            </ul>
        </div>

        <div class="card health-tip-card">
            <div class="section-title">
                <h2><i class="fa-solid fa-heart-pulse"></i> Health Tip</h2>
            </div>
            <p>Stay hydrated, eat balanced meals, and take short movement breaks throughout the day. Consistent care supports both your body and mind.</p>
        </div>
    </div>

</div>

<?php
// Render a professional upcoming sessions cards area for the patient
include_once(__DIR__ . '/../appointment_functions.php');
render_upcoming_sessions_cards($database, $userfetch['pid'], 6);
?>

</div>
</div>

<!-- JS -->
<script>
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('clicked'));
        card.classList.add('clicked');
    });
});
</script>
<?php render_accessibility_script($accessibility); ?>

</body>
</html>
