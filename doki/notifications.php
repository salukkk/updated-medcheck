<?php
session_start();
include("../connection.php");

if(isset($_GET['logout'])){
    session_destroy();
    header("Location: login.php");
    exit();
}

if(!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'd'){
    header("Location: ../login.php?action=logout");
    exit();
}

$doctorname = "Dr. John Doe";
$specialty = "Cardiologist";
$doctorid = 0;

if(isset($_SESSION['doctor'])){
    $doctorname = $_SESSION['doctor']['name'] ?? $doctorname;
    $specialty = $_SESSION['doctor']['specialty'] ?? $specialty;
    $doctorid = (int)($_SESSION['doctor']['docid'] ?? 0);
}

if($doctorid === 0){
    $useremail = $_SESSION['user'];
    $sql = "SELECT docid, docname, specialties FROM doctor WHERE docemail='$useremail'";
    $result = $database->query($sql);
    if($row = $result->fetch_assoc()){
        $doctorid = (int)$row['docid'];
        $_SESSION['doctor']['docid'] = $doctorid;
        $doctorname = $row['docname'];
        $specialtyid = $row['specialties'];
        $specsql = "SELECT sname FROM specialties WHERE id='$specialtyid'";
        $specresult = $database->query($specsql);
        if($specrow = $specresult->fetch_assoc()){
            $specialty = $specrow['sname'];
        }
    }
}

$pendingBookings = [];
$successMessage = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'declined') {
        $successMessage = 'Booking request declined successfully.';
    } elseif ($_GET['success'] === 'approved') {
        $successMessage = 'Booking request approved successfully.';
    }
}

// Handle approve/decline actions for pending quick bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_action'])) {
    $action = $_POST['booking_action'];
    $appoid = (int)($_POST['appoid'] ?? 0);
    if ($appoid > 0) {
        if ($action === 'approve') {
            $stmt = $database->prepare("UPDATE appointment SET status = 'approved' WHERE appoid = ?");
            $stmt->bind_param('i', $appoid);
            $stmt->execute();
            $stmt->close();
            header('Location: mypatient.php?success=approved');
            exit();
        } elseif ($action === 'decline') {
            $stmt = $database->prepare("UPDATE appointment SET status = 'declined' WHERE appoid = ?");
            $stmt->bind_param('i', $appoid);
            $stmt->execute();
            $stmt->close();
            header('Location: notifications.php?success=declined');
            exit();
        }
    }
    header('Location: notifications.php');
    exit();
}

$pendingSql = "SELECT appointment.appoid, appointment.apponum, appointment.appodate, schedule.title, schedule.scheduledate, schedule.scheduletime, patient.pname, patient.pemail, patient.ptel
               FROM appointment
               INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
               INNER JOIN patient ON appointment.pid = patient.pid
               WHERE schedule.docid = ? AND appointment.status = 'pending'
               ORDER BY schedule.scheduledate ASC";
$pendingStmt = $database->prepare($pendingSql);
$pendingStmt->bind_param('i', $doctorid);
$pendingStmt->execute();
$pendingResult = $pendingStmt->get_result();
$pendingBookings = $pendingResult ? $pendingResult->fetch_all(MYSQLI_ASSOC) : [];
$pendingStmt->close();
$pendingCount = count($pendingBookings);

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Notifications</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:"Inter",sans-serif;
    background:#eef4fb;
    color:#1f2937;
}

/* TOPBAR */
.topbar{
    background:white;
    padding:18px 24px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid #dbeafe;
}

.logo{
    font-size:28px;
    font-weight:700;
    color:#0f4c81;
    display:flex;
    align-items:center;
    gap:10px;
}

.user{
    background:#f8fafc;
    padding:10px 18px;
    border-radius:30px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
}

/* LAYOUT */
.container{
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:250px;
    min-height:100vh;
    background:white;
    border-right:1px solid #dbeafe;
    padding:25px 15px;
}

.profile{
    text-align:center;
    margin-bottom:25px;
    border-bottom:1px solid #e5e7eb;
    padding-bottom:20px;
}

.profile img{
    width:90px;
    height:90px;
    border-radius:50%;
    margin-bottom:12px;
    border:4px solid #dbeafe;
}

.profile h3{
    font-size:22px;
}

.profile p{
    color:#64748b;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    text-decoration:none;
    color:#334155;
    padding:14px 16px;
    border-radius:14px;
    margin-bottom:8px;
    transition:0.3s;
}

.sidebar a:hover{
    background:#f1f5f9;
}

.sidebar .active{
    background:linear-gradient(135deg,#0ea5e9,#14b8a6);
    color:white;
}

.logout{
    color:#334155 !important;
}

/* MAIN */
.main{
    flex:1;
    padding:28px;
}

.card{
    background:white;
    border-radius:22px;
    padding:24px;
    margin-bottom:22px;
    box-shadow:0 5px 18px rgba(0,0,0,0.05);
}

/* HEADER */
.welcome{
    background:linear-gradient(135deg,#06b6d4,#0891b2);
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.welcome h1{
    font-size:32px;
}

.date{
    background:rgba(255,255,255,0.2);
    padding:12px 18px;
    border-radius:14px;
}

/* NOTIFICATIONS */
.notification-item{
    padding:16px;
    border-bottom:1px solid #e5e7eb;
    cursor:pointer;
    transition:0.3s;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
}

.notification-item:hover{
    background:#f8fbff;
}

.notification-left{
    display:flex;
    gap:12px;
    align-items:flex-start;
}

.notification-icon{
    font-size:18px;
    color:#0ea5e9;
    margin-top:3px;
}

.notification-title{
    font-weight:700;
}

.notification-message{
    font-size:14px;
    color:#64748b;
}

.notification-date{
    font-size:12px;
    color:#94a3b8;
}

/* STATUS */
.badge{
    padding:6px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:600;
}

.notify-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:24px;
    height:24px;
    border-radius:999px;
    background:#ef4444;
    color:white;
    font-size:12px;
    padding:0 8px;
    margin-left:6px;
}

.approve-btn,
.decline-btn{
    border:none;
    padding:10px 14px;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
    transition:0.2s ease;
}

.approve-btn{
    background:#22c55e;
    color:white;
}

.decline-btn{
    background:#ef4444;
    color:white;
}

.approve-btn:hover{
    opacity:.9;
}

.decline-btn:hover{
    opacity:.9;
}

.unread{
    background:#fee2e2;
    color:#b91c1c;
}

.read{
    background:#dcfce7;
    color:#166534;
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
    z-index:999;
}

.modal-content{
    background:white;
    width:500px;
    border-radius:22px;
    padding:25px;
}

.close-btn{
    float:right;
    border:none;
    background:none;
    font-size:24px;
    cursor:pointer;
}

.modal h2{
    margin-bottom:10px;
}

.modal p{
    margin-bottom:8px;
    color:#475569;
}

</style>

</head>

<body>

<div class="topbar">
    <div class="logo">
        <i class="fa-solid fa-heart-pulse"></i>
        MEDCHECK
    </div>

    <div class="user">
        <i class="fa-solid fa-user-doctor"></i>
        Welcome, <?php echo $doctorname; ?>
    </div>
</div>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="profile">
        <img src="https://i.imgur.com/6VBx3io.png">
        <h3><?php echo $doctorname; ?></h3>
        <p><?php echo $specialty; ?></p>
    </div>

    <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="appointmentdok.php"><i class="fa-solid fa-calendar-check"></i> My Appointments</a>
    <a href="mypatient.php"><i class="fa-solid fa-users"></i> My Patients</a>
    <a href="schedule.php"><i class="fa-solid fa-clock"></i> Schedule / Sessions</a>
    <a href="records.php"><i class="fa-solid fa-file-medical"></i> Medical Records</a>
    <a class="active" href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a>
    <a href="profilesettings.php"><i class="fa-solid fa-user-gear"></i> Profile Settings</a>
    <a class="logout" href="?logout=true"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

</div>

<!-- MAIN -->
<div class="main">

<div class="card welcome">
    <div>
        <h1><i class="fa-solid fa-bell"></i> Notifications</h1>
        <p>Manage all system alerts, patient updates, and schedule changes.</p>
    </div>

    <div class="date">
        <?php echo date('d M, Y'); ?>
    </div>
</div>

<?php if (!empty($successMessage)): ?>
    <div class="card" style="border-left:4px solid #22c55e; background:#ecfdf5; color:#166534; margin-bottom:16px;">
        <p style="margin:0; font-weight:600; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo htmlspecialchars($successMessage, ENT_QUOTES); ?>
        </p>
    </div>
<?php endif; ?>

<div class="card">

    <div class="section-title">
        <h2><i class="fa-solid fa-hourglass-half"></i> Pending Booking Requests<?php if(!empty($pendingCount)){ echo ' <span class="notify-count">'.$pendingCount.'</span>'; } ?></h2>
    </div>

    <?php if (!empty($pendingBookings)): ?>
        <?php foreach ($pendingBookings as $booking): ?>
            <div class="notification-item">
                <div class="notification-left">
                    <i class="fa-solid fa-user-clock notification-icon"></i>
                    <div>
                        <div class="notification-title">
                            Quick booking request from <?php echo htmlspecialchars($booking['pname']); ?>
                        </div>
                        <div class="notification-message">
                            Session: <?php echo htmlspecialchars($booking['title']); ?> on <?php echo date('M d, Y', strtotime($booking['scheduledate'])); ?> at <?php echo date('g:i A', strtotime($booking['scheduletime'])); ?>
                        </div>
                    </div>
                </div>

                <div style="text-align:right; display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
                    <div class="notification-date">
                        Requested: <?php echo date('M d, Y', strtotime($booking['appodate'])); ?>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="appoid" value="<?php echo (int)$booking['appoid']; ?>">
                            <button type="submit" name="booking_action" value="approve" class="approve-btn">Approve</button>
                        </form>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="appoid" value="<?php echo (int)$booking['appoid']; ?>">
                            <button type="submit" name="booking_action" value="decline" class="decline-btn">Decline</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="notification-item" style="justify-content:center;">
            <div class="notification-message">No pending booking requests at this time.</div>
        </div>
    <?php endif; ?>

</div>

</div>
</div>

<!-- MODAL -->
<div class="modal" id="notifModal">

    <div class="modal-content">

        <button class="close-btn" onclick="closeModal()">&times;</button>

        <h2 id="title"></h2>
        <p id="message"></p>
        <p id="date"></p>

    </div>

</div>

<script>

function openNotif(title,message,date,el){

    document.getElementById("title").innerText = title;
    document.getElementById("message").innerText = message;
    document.getElementById("date").innerText = date;

    document.getElementById("notifModal").style.display = "flex";

    // MARK AS READ
    let badge = el.querySelector(".badge");
    badge.classList.remove("unread");
    badge.classList.add("read");
    badge.innerText = "READ";
}

function closeModal(){
    document.getElementById("notifModal").style.display = "none";
}

window.onclick = function(e){
    let modal = document.getElementById("notifModal");
    if(e.target == modal){
        modal.style.display = "none";
    }
}

</script>

</body>
</html>