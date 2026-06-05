<?php
session_start();
include("../connection.php");

if(isset($_GET['logout'])){
    session_destroy();
    header("Location: login.php");
    exit();
}

if(!isset($_SESSION['user'])){
    header('Location: ../login.php'); 
    exit();
}

$useremail = $_SESSION['user'];

$sql = "SELECT docid, docname FROM doctor WHERE docemail='$useremail'";
$result = $database->query($sql);
$doctor = $result->fetch_assoc();

$docid = $doctor['docid'];
$doctorname = $doctor['docname'];

// Handle session creation from doctor UI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $scheduledate = $_POST['scheduledate'] ?? '';
    $scheduletime = $_POST['scheduletime'] ?? '';
    $nop = intval($_POST['nop'] ?? 0);

    if ($title != '' && $scheduledate != '' && $scheduletime != '') {
        $stmt = $database->prepare("INSERT INTO schedule (docid,title,scheduledate,scheduletime,nop) VALUES (?,?,?,?,?)");
        $stmt->bind_param('isssi', $docid, $title, $scheduledate, $scheduletime, $nop);
        if ($stmt->execute()) {
            header("Location: schedule.php?action=session-added&title=" . urlencode($title));
            exit();
        } else {
            $errorMsg = 'Failed to save session.';
        }
    } else {
        $errorMsg = 'Please fill all required fields.';
    }
}

$specsql = "SELECT * FROM specialties WHERE id=(SELECT specialties FROM doctor WHERE docid='$docid')";
$specresult = $database->query($specsql);
$spec = $specresult->fetch_assoc();
$specialty = $spec['sname'] ?? "Medical Professional";

// GET SCHEDULES FROM DATABASE
$schedulesSql = "SELECT scheduleid, title, scheduledate, scheduletime, nop FROM schedule 
                 WHERE docid = '$docid' 
                 ORDER BY scheduledate ASC";
$schedulesResult = $database->query($schedulesSql);

$schedules = [];
if($schedulesResult->num_rows > 0){
    while($row = $schedulesResult->fetch_assoc()){
        $schedules[] = [
            "scheduleid" => $row['scheduleid'],
            "day" => date('l', strtotime($row['scheduledate'])),
            "date" => $row['scheduledate'],
            "title" => $row['title'],
            "time" => date('h:i A', strtotime($row['scheduletime'])),
            "nop" => $row['nop'],
            "status" => "Available"
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor Schedule</title>

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
    background:
    radial-gradient(circle at top left, rgba(6,160,212,0.18), transparent 25%),
    radial-gradient(circle at bottom right, rgba(54,83,228,0.2), transparent 22%),
    #eff7ff;
    color:#1f2937;
}

.topbar{
    background:rgba(255,255,255,0.92);
    backdrop-filter:blur(10px);
    padding:20px 34px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid rgba(15,23,42,0.08);
    position:sticky;
    top:0;
    z-index:100;
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
    background:rgba(15,23,42,0.04);
    padding:10px 18px;
    border-radius:30px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
}

.container{
    display:flex;
}

.sidebar{
    width:260px;
    min-height:100vh;
    background:rgba(255,255,255,0.88);
    padding:24px 20px;
    border-right:1px solid rgba(15,23,42,0.08);
}

.profile{
    text-align:center;
    margin-bottom:30px;
    padding-bottom:20px;
    border-bottom:1px solid rgba(15,23,42,0.08);
}

.profile img{
    width:90px;
    height:90px;
    border-radius:50%;
    border:4px solid rgba(6,160,212,0.15);
    padding:4px;
    object-fit:cover;
    margin-bottom:14px;
}

.profile h3{
    font-size:22px;
    margin-bottom:5px;
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
    transition:0.3s ease;
    font-size:15px;
    font-weight:500;
}

.sidebar a:hover{
    background:rgba(6,160,212,0.08);
    transform:translateX(3px);
}

.sidebar .active{
    background:linear-gradient(135deg,#0d9488,#0ea5e9);
    color:white;
}

.logout{
    color:#334155 !important;
    font-weight:500;
}

.main{
    flex:1;
    padding:35px;
}

.card{
    background:white;
    border-radius:24px;
    padding:25px;
    margin-bottom:22px;
    box-shadow:0 18px 50px rgba(15,23,42,0.08);
}

.welcome{
    background:linear-gradient(135deg,#00bcd4,#0097a7);
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.welcome h1{
    font-size:34px;
    margin-bottom:10px;
}

.welcome p{
    max-width:650px;
    line-height:1.7;
}

.date{
    background:rgba(255,255,255,0.2);
    padding:12px 18px;
    border-radius:14px;
}

.section-title{
    margin-bottom:15px;
}

.section-title h2{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:25px;
}

.top-actions{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    gap:15px;
    flex-wrap:wrap;
}

.search-box{
    flex:1;
    display:flex;
    align-items:center;
    background:#f8fafc;
    border:1px solid #dbeafe;
    border-radius:16px;
    padding:15px 20px;
    gap:12px;
}

.search-box i{
    color:#0ea5e9;
}

.search-box input{
    width:100%;
    border:none;
    background:none;
    outline:none;
    font-size:15px;
}

.add-btn{
    border:none;
    background:linear-gradient(135deg,#0ea5e9,#0284c7);
    color:white;
    padding:14px 20px;
    border-radius:14px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.add-btn:hover{
    transform:translateY(-2px);
}

.table-container{
    overflow-x:auto;
}

.data-table{
    width:100%;
    border-collapse:collapse;
}

.data-table thead{
    background:#f8fafc;
}

.data-table th{
    padding:18px;
    text-align:left;
    font-size:13px;
    color:#64748b;
    text-transform:uppercase;
}

.data-table td{
    padding:18px;
    border-bottom:1px solid #f1f5f9;
}

.data-table tbody tr:hover{
    background:#f8fbff;
}

.status{
    padding:8px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}

.available{
    background:#dcfce7;
    color:#166534;
}

.unavailable{
    background:#fee2e2;
    color:#991b1b;
}

.action-buttons{
    display:flex;
    gap:10px;
}

.edit-btn{
    border:none;
    padding:10px 14px;
    border-radius:12px;
    background:linear-gradient(135deg,#f59e0b,#d97706);
    color:white;
    cursor:pointer;
    font-weight:600;
}

.delete-btn{
    border:none;
    padding:10px 14px;
    border-radius:12px;
    background:linear-gradient(135deg,#ef4444,#dc2626);
    color:white;
    cursor:pointer;
    font-weight:600;
}

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
    width:450px;
    max-width:95%;
    border-radius:24px;
    padding:30px;
}

.modal-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.close-btn{
    border:none;
    background:none;
    font-size:28px;
    cursor:pointer;
}

.form-group{
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#334155;
}

.form-group input,
.form-group select{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:1px solid #dbeafe;
    background:#f8fafc;
    outline:none;
}

.save-btn{
    width:100%;
    border:none;
    padding:14px;
    border-radius:14px;
    background:linear-gradient(135deg,#0ea5e9,#0284c7);
    color:white;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
}

@media(max-width:900px){

    .container{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
        min-height:auto;
    }

    .welcome{
        flex-direction:column;
        gap:20px;
        align-items:flex-start;
    }

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

<div class="sidebar">

    <div class="profile">

        <img src="https://i.imgur.com/6VBx3io.png">

        <h3><?php echo $doctorname; ?></h3>
        <p><?php echo $specialty; ?></p>

    </div>

    <a href="dashboard.php">
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

    <a class="active" href="schedule.php">
        <i class="fa-solid fa-clock"></i>
        Schedule / Sessions
    </a>

    <a href="records.php">
        <i class="fa-solid fa-file-medical"></i>
        Medical Records
    </a>

    <a href="notifications.php">
        <i class="fa-solid fa-bell"></i>
        Notifications
    </a>

    <a href="profilesettings.php">
        <i class="fa-solid fa-user-gear"></i>
        Profile Settings
    </a>

    <a class="logout" href="?logout=true">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</div>

<div class="main">

    <div class="card welcome">

        <div>

            <h1>
                <i class="fa-solid fa-clock"></i>
                Schedule & Sessions
            </h1>

            <p>
                Manage your doctor availability,
                clinic schedules, and session hours professionally.
            </p>

        </div>

        <div class="date">
            <i class="fa-solid fa-calendar"></i>
            <?php echo date('d M, Y'); ?>
        </div>

    </div>

    <div class="section-title">

        <h2>
            <i class="fa-solid fa-calendar-days"></i>
            Weekly Schedule
        </h2>

    </div>

    <div class="card">

        <div class="top-actions">

            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input type="text"
                id="searchInput"
                placeholder="Search schedule...">

            </div>

            <button class="add-btn"
            onclick="openModal()">

                <i class="fa-solid fa-plus"></i>
                Add Session

            </button>

        </div>

        <div class="table-container">

            <table class="data-table">

                <thead>

                    <tr>
                        <th>Session Title</th>
                        <th>Date & Time</th>
                        <th>Slots</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody id="scheduleTable">

                <?php foreach($schedules as $schedule): ?>

                    <tr>

                        <td><?php echo $schedule['title']; ?></td>

                        <td><?php echo date('M d, Y', strtotime($schedule['date'])) . ' at ' . $schedule['time']; ?></td>

                        <td><?php echo $schedule['nop']; ?></td>

                        <td>

                            <?php
                            $statusClass =
                            strtolower($schedule['status']);
                            ?>

                            <span class="status <?php echo $statusClass; ?>">
                                <?php echo $schedule['status']; ?>
                            </span>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

<div class="modal" id="sessionModal">

    <div class="modal-content">

        <div class="modal-header">

            <h2 id="modalTitle">
                Add Session
            </h2>

            <button class="close-btn" onclick="closeModal()">&times;</button>

        </div>

        <form method="POST">

            <div class="form-group">
                <label>Session Title</label>
                <input type="text" name="title" required placeholder="e.g., General Consultation">
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="scheduledate" required>
            </div>

            <div class="form-group">
                <label>Time</label>
                <input type="time" name="scheduletime" required>
            </div>

            <div class="form-group">
                <label>Slots (Number of Patients)</label>
                <input type="number" name="nop" min="1" value="1">
            </div>

            <button type="submit" class="save-btn">Save Session</button>

        </form>

    </div>

</div>

<script>

const searchInput =
document.getElementById("searchInput");

let editingRow = null;

searchInput.addEventListener("keyup", function(){

    let value = this.value.toLowerCase();

    let rows =
    document.querySelectorAll("#scheduleTable tr");

    rows.forEach(function(row){

        let text =
        row.textContent.toLowerCase();

        if(text.includes(value)){
            row.style.display = "";
        }else{
            row.style.display = "none";
        }

    });

});

function openModal(){

    editingRow = null;

    document.getElementById("modalTitle")
    .innerText = "Add Session";

    // reset form fields
    const form = document.querySelector('#sessionModal form');
    if(form){
        form.reset();
    }

    document.getElementById("sessionModal").style.display = "flex";

}

function closeModal(){

    document.getElementById("sessionModal")
    .style.display = "none";

}

// Note: session creation is handled server-side via POST form submission.

window.onclick = function(event){

    let modal =
    document.getElementById("sessionModal");

    if(event.target == modal){

        modal.style.display = "none";

    }

}

</script>

</body>
</html>
```
