<?php
session_start();


include("../connection.php");



if(!isset($_SESSION['user'])){
    header('Location: ../login.php?action=logout'); 
    exit();
}

/*
|--------------------------------------------------------------------------
| GET LOGGED-IN DOCTOR
|--------------------------------------------------------------------------
*/

$useremail = $_SESSION['user'];

$sql = "SELECT docid, docname, specialties FROM doctor WHERE docemail='$useremail'";

$result = $database->query($sql);

$doctor = $result->fetch_assoc();

$docid = $doctor['docid'];

// Handle approve/decline actions from doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_action'])) {
    $action = $_POST['booking_action'];
    $appoid = (int)($_POST['appoid'] ?? 0);
    if ($appoid > 0) {
        if ($action === 'approve') {
            $stmt = $database->prepare("UPDATE appointment SET status = 'approved' WHERE appoid = ?");
            $stmt->bind_param('i', $appoid);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'decline') {
            $stmt = $database->prepare("UPDATE appointment SET status = 'declined' WHERE appoid = ?");
            $stmt->bind_param('i', $appoid);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: dashboard.php'); exit();
}

/*
|--------------------------------------------------------------------------
| DOCTOR INFO FROM DATABASE
|--------------------------------------------------------------------------
*/

$doctorname = $doctor['docname'];

/*
|--------------------------------------------------------------------------
| SPECIALTY
|--------------------------------------------------------------------------
*/

$specialtyid = $doctor['specialties'];

$specsql = "SELECT * FROM specialties WHERE id='$specialtyid'";

$specresult = $database->query($specsql);

$spec = $specresult ? $specresult->fetch_assoc() : null;

$specialty = $spec['sname'] ?? 'Unknown';

/*
|--------------------------------------------------------------------------
| GET REAL STATS FROM DATABASE
|--------------------------------------------------------------------------
*/

// Total Patients (count unique patients who have appointments with this doctor)
$patientsSql = "SELECT COUNT(DISTINCT appointment.pid) as count FROM appointment 
               INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid 
               WHERE schedule.docid = '$docid'";
$patientsResult = $database->query($patientsSql);
$patientsRow = $patientsResult->fetch_assoc();
$totalPatients = $patientsRow['count'];

// Today's Appointments
$today = date('Y-m-d');
$todayAppointmentsSql = "SELECT COUNT(*) as count FROM appointment 
                        INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid 
                        WHERE schedule.docid = '$docid' AND appointment.appodate = '$today'";
$todayAppointmentsResult = $database->query($todayAppointmentsSql);
$todayAppointmentsRow = $todayAppointmentsResult->fetch_assoc();
$todayAppointments = $todayAppointmentsRow['count'];

// Pending booking requests (need doctor approval)
$pendingAppointmentsSql = "SELECT COUNT(*) as count FROM appointment 
                          INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid 
                          WHERE schedule.docid = '$docid' AND appointment.status = 'pending'";
$pendingAppointmentsResult = $database->query($pendingAppointmentsSql);
$pendingAppointmentsRow = $pendingAppointmentsResult->fetch_assoc();
$pendingAppointments = $pendingAppointmentsRow['count'];

// Completed Appointments (past dates)
$completedAppointmentsSql = "SELECT COUNT(*) as count FROM appointment 
                            INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid 
                            WHERE schedule.docid = '$docid' AND appointment.appodate < '$today'";
$completedAppointmentsResult = $database->query($completedAppointmentsSql);
$completedAppointmentsRow = $completedAppointmentsResult->fetch_assoc();
$completedAppointments = $completedAppointmentsRow['count'];

// Upcoming Sessions (distinct schedules in future)
$upcomingSessionsSql = "SELECT COUNT(DISTINCT schedule.scheduleid) as count FROM schedule 
                       WHERE schedule.docid = '$docid' AND schedule.scheduledate >= '$today'";
$upcomingSessionsResult = $database->query($upcomingSessionsSql);
$upcomingSessionsRow = $upcomingSessionsResult->fetch_assoc();
$upcomingSessions = $upcomingSessionsRow['count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor Dashboard</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- CONNECT CSS -->
<link rel="stylesheet" href="doki.css">

</head>

<body>

<!-- TOPBAR -->
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

    <a class="active" href="#">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <a href="appointmentdok.php">
        <i class="fa-solid fa-calendar-check"></i>
        My Appointments
    </a>

    <a href="mypatient.php">
        <i class="fa-solid fa-users"></i>
        My Patients
    </a>

    <a href="schedule.php">
        <i class="fa-solid fa-clock"></i>
        Schedule / Sessions
    </a>

    <a href="records.php">
        <i class="fa-solid fa-file-medical"></i>
        Medical Records
    </a>

    <a href="notifications.php">
        <i class="fa-solid fa-bell"></i>
        Notifications<?php if(!empty($pendingAppointments)){ echo ' <span class="notify-count">'.$pendingAppointments.'</span>'; } ?>
    </a>

    <a href="profilesettings.php">
        <i class="fa-solid fa-user-gear"></i>
        Profile Settings
    </a>

 <a class="logout" href="/totga/mecheck/logout.php">
    <i class="fa-solid fa-right-from-bracket"></i>
    Logout
</a>

</div>

<!-- MAIN -->
<div class="main">

    <!-- WELCOME -->
    <div class="card welcome">

        <div class="welcome-content">

            <h1>
                <i class="fa-solid fa-stethoscope"></i>
                Welcome back, <?php echo $doctorname; ?>
            </h1>

            <p>
                Here's your doctor dashboard for today. Manage appointments,
                patients, schedules, and notifications easily.
            </p>

        </div>

        <div class="welcome-info">

            <span class="date">
                <i class="fa-solid fa-calendar"></i>
                <?php echo date('d M, Y'); ?>
            </span>

        </div>

    </div>

    <!-- QUICK STATS -->
    <div class="section-title">
        <h2>
            <i class="fa-solid fa-chart-simple"></i>
            Quick Stats
        </h2>
    </div>

    <div class="grid">

        <div class="card stat-card">

            <div class="stat-top">
                <i class="fa-solid fa-users stat-icon"></i>
                <h2><?php echo $totalPatients; ?></h2>
            </div>

            <p>Total Patients</p>

        </div>

        <div class="card stat-card">

            <div class="stat-top">
                <i class="fa-solid fa-calendar-day stat-icon"></i>
                <h2><?php echo $todayAppointments; ?></h2>
            </div>

            <p>Today's Appointments</p>

        </div>

        <div class="card stat-card">

            <div class="stat-top">
                <i class="fa-solid fa-hourglass-half stat-icon"></i>
                <h2><?php echo $pendingAppointments; ?></h2>
            </div>

            <p>Pending Requests</p>

        </div>

        <div class="card stat-card">

            <div class="stat-top">
                <i class="fa-solid fa-circle-check stat-icon"></i>
                <h2><?php echo $completedAppointments; ?></h2>
            </div>

            <p>Completed Appointments</p>

        </div>

        <div class="card stat-card">

            <div class="stat-top">
                <i class="fa-solid fa-stethoscope stat-icon"></i>
                <h2><?php echo $upcomingSessions; ?></h2>
            </div>

            <p>Upcoming Sessions</p>

        </div>

    </div>

    <!-- CONTENT -->
    <div class="grid-2">

        <!-- RECENT APPOINTMENTS -->
        <div class="card">

            <div class="section-title">
                <h2>
                    <i class="fa-solid fa-calendar-check"></i>
                    Recent Appointments
                </h2>
            </div>

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    // GET RECENT APPOINTMENTS FOR TABLE
                    $recentAppointsSql = "SELECT appointment.appodate, patient.pname 
                                         FROM appointment
                                         INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid 
                                         INNER JOIN patient ON appointment.pid = patient.pid
                                         WHERE schedule.docid = '$docid'
                                         ORDER BY appointment.appodate DESC LIMIT 5";
                    $recentAppointsResult = $database->query($recentAppointsSql);
                    
                    if($recentAppointsResult->num_rows > 0){
                        while($row = $recentAppointsResult->fetch_assoc()){
                            $appointDate = $row['appodate'];
                            $patientName = $row['pname'];
                            
                            // Determine status based on date
                            $today = date('Y-m-d');
                            if($appointDate < $today){
                                $statusBadge = 'completed';
                                $statusText = 'Completed';
                            } elseif($appointDate == $today){
                                $statusBadge = 'pending';
                                $statusText = 'Today';
                            } else {
                                $statusBadge = 'pending';
                                $statusText = 'Pending';
                            }
                            
                            echo "
                            <tr>
                                <td>$patientName</td>
                                <td>".date('M d, Y', strtotime($appointDate))."</td>
                                <td>
                                    <span class='status-badge $statusBadge'>
                                        $statusText
                                    </span>
                                </td>
                            </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;'>No appointments yet</td></tr>";
                    }
                    ?>

                </tbody>

            </table>

        </div>
        <!-- PENDING QUICK BOOKINGS -->
        <div class="card">
            <div class="section-title">
                <h2><i class="fa-solid fa-hourglass-half"></i> Pending Quick Bookings</h2>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Session</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pendingSql = "SELECT appointment.appoid, appointment.apponum, appointment.appodate, appointment.pid, patient.pname, schedule.title, schedule.scheduledate, schedule.scheduletime
                                   FROM appointment
                                   INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                                   INNER JOIN patient ON appointment.pid = patient.pid
                                   WHERE schedule.docid = ? AND appointment.status = 'pending'
                                   ORDER BY schedule.scheduledate ASC";
                    $pst = $database->prepare($pendingSql);
                    $pst->bind_param('i', $docid);
                    $pst->execute();
                    $pres = $pst->get_result();
                    if ($pres && $pres->num_rows > 0) {
                        while ($prow = $pres->fetch_assoc()) {
                            $pname = htmlspecialchars($prow['pname']);
                            $title = htmlspecialchars($prow['title']);
                            $sdate = $prow['scheduledate'];
                            $stime = date('g:i A', strtotime($prow['scheduletime']));
                            $appoid = (int)$prow['appoid'];
                            echo "<tr>";
                            echo "<td>$pname</td>";
                            echo "<td>$title</td>";
                            echo "<td>".date('M d, Y', strtotime($sdate))."</td>";
                            echo "<td>$stime</td>";
                            echo "<td>";
                            echo "<form method='POST' style='display:inline-block;margin-right:6px;'>";
                            echo "<input type='hidden' name='appoid' value='$appoid'>";
                            echo "<button type='submit' name='booking_action' value='approve' class='btn green'>Approve</button>";
                            echo "</form>";
                            echo "<form method='POST' style='display:inline-block;margin-right:6px;'>";
                            echo "<input type='hidden' name='appoid' value='$appoid'>";
                            echo "<button type='submit' name='booking_action' value='decline' class='btn red'>Decline</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No pending quick bookings</td></tr>";
                    }
                    $pst->close();
                    ?>
                </tbody>
            </table>
        </div>
        <!-- RIGHT SIDE -->
        <div>

            <!-- NOTIFICATIONS -->
            <div class="card">

                <div class="section-title">
                    <h2>
                        <i class="fa-solid fa-bell"></i>
                        Notifications
                    </h2>
                </div>

                <ul class="notification-list">

                    <li class="notification-item">
                        <i class="fa-solid fa-calendar-check"></i>
                        You have new appointments today.
                    </li>

                    <li class="notification-item">
                        <i class="fa-solid fa-user-doctor"></i>
                        Update your doctor schedule this week.
                    </li>

                    <li class="notification-item">
                        <i class="fa-solid fa-heart-pulse"></i>
                        Patient records need review.
                    </li>

                </ul>

            </div>

            <!-- HEALTH CARD -->
            <div class="card health-tip-card">

                <div class="section-title">
                    <h2>
                        <i class="fa-solid fa-notes-medical"></i>
                        Doctor Reminder
                    </h2>
                </div>

                <p>
                    Keep your patient consultations updated regularly and
                    maintain accurate medical records for better healthcare service.
                </p>

            </div>

        </div>

    </div>

</div>

</div>

<script>

document.querySelectorAll('.stat-card').forEach(card => {

    card.addEventListener('click', () => {

        document.querySelectorAll('.stat-card').forEach(c => {
            c.style.background = "";
            c.style.color = "";
        });

        card.style.background =
        "linear-gradient(135deg,#0ea5e9 0%,#0d9488 100%)";

        card.style.color = "white";

    });

});

</script>

</body>
</html>