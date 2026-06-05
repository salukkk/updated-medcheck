
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

// GET APPOINTMENTS FROM DATABASE
$appointsql = "SELECT appointment.appoid, appointment.appodate, appointment.apponum, 
               schedule.scheduleid, schedule.title, schedule.scheduletime,
               patient.pid, patient.pname, patient.pemail, patient.ptel
               FROM appointment
               INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
               INNER JOIN patient ON appointment.pid = patient.pid
               WHERE schedule.docid = '$docid'
               ORDER BY appointment.appodate DESC";

$appointresult = $database->query($appointsql);

$appointments = [];
if($appointresult->num_rows > 0){
    while($row = $appointresult->fetch_assoc()){
        $appointments[] = [
            "patient" => $row['pname'],
            "date" => date('M d, Y', strtotime($row['appodate'])),
            "time" => date('h:i A', strtotime($row['scheduletime'])),
            "type" => $row['title'],
            "status" => "Pending",
            "email" => $row['pemail'],
            "phone" => $row['ptel']
        ];
    }
}

$specialty = "Medical Professional";
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor Appointments</title>

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

.container{
    display:flex;
}

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
    transition:0.3s;
    font-size:15px;
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
    font-weight:500;
    background:transparent;
    border-radius:14px;
    transition:0.3s ease;
}

.logout:hover{
    background:rgba(6,160,212,0.08) !important;
    color:#0f172a !important;
    transform:translateX(3px);
}

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

.welcome{
    background:linear-gradient(135deg,#06b6d4,#0891b2);
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.welcome h1{
    font-size:35px;
    margin-bottom:10px;
}

.welcome p{
    max-width:600px;
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

.search-box{
    display:flex;
    align-items:center;
    background:#f8fafc;
    border:1px solid #dbeafe;
    border-radius:15px;
    padding:14px 18px;
    gap:12px;
}

.search-box input{
    width:100%;
    border:none;
    background:none;
    outline:none;
    font-size:15px;
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

.status-badge{
    padding:8px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}

.confirmed{
    background:#dcfce7;
    color:#166534;
}

.pending{
    background:#fef3c7;
    color:#92400e;
}

.completed{
    background:#dbeafe;
    color:#1d4ed8;
}

.view-btn{
    border:none;
    padding:10px 16px;
    border-radius:12px;
    background:linear-gradient(135deg,#0ea5e9,#0284c7);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.view-btn:hover{
    transform:translateY(-2px);
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
    width:500px;
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
}

.form-group input{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:1px solid #dbeafe;
    background:#f8fafc;
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

    <a class="active" href="appointmentdok.php">
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
                <i class="fa-solid fa-calendar-check"></i>
                Appointment Management
            </h1>

            <p>
                Manage patient schedules, consultations,
                and appointment records professionally.
            </p>

        </div>

        <div class="date">
            <i class="fa-solid fa-calendar"></i>
            <?php echo date('d M, Y'); ?>
        </div>

    </div>

    <div class="section-title">

        <h2>
            <i class="fa-solid fa-list-check"></i>
            Appointment List
        </h2>

    </div>

    <div class="card">

        <div class="search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input type="text"
            id="searchInput"
            placeholder="Search patient, month, day, year, status...">

        </div>

    </div>

    <div class="card table-container">

        <table class="data-table">

            <thead>

                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody id="appointmentTable">

            <?php foreach($appointments as $appointment): ?>

                <tr>

                    <td><?php echo $appointment['patient']; ?></td>
                    <td><?php echo $appointment['date']; ?></td>
                    <td><?php echo $appointment['time']; ?></td>
                    <td><?php echo $appointment['type']; ?></td>

                    <td>

                        <?php
                        $statusClass = strtolower($appointment['status']);
                        ?>

                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $appointment['status']; ?>
                        </span>

                    </td>

                    <td>

                        <button
                        class="view-btn"

                        onclick="openViewModal(
                        '<?php echo $appointment['patient']; ?>',
                        '<?php echo $appointment['email']; ?>',
                        '<?php echo $appointment['phone']; ?>',
                        '<?php echo $appointment['date']; ?>',
                        '<?php echo $appointment['time']; ?>',
                        '<?php echo $appointment['type']; ?>'
                        )">

                            <i class="fa-solid fa-eye"></i>
                            View

                        </button>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

</div>

<div class="modal" id="viewModal">

    <div class="modal-content">

        <div class="modal-header">

            <h2>Appointment Details</h2>

            <button class="close-btn"
            onclick="closeModal()">
                &times;
            </button>

        </div>

        <div class="form-group">
            <label>Patient Name</label>
            <input type="text" id="viewPatient" readonly>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" id="viewEmail" readonly>
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" id="viewPhone" readonly>
        </div>

        <div class="form-group">
            <label>Date</label>
            <input type="text" id="viewDate" readonly>
        </div>

        <div class="form-group">
            <label>Time</label>
            <input type="text" id="viewTime" readonly>
        </div>

        <div class="form-group">
            <label>Appointment Type</label>
            <input type="text" id="viewType" readonly>
        </div>

    </div>

</div>

<script>

const searchInput = document.getElementById("searchInput");
const tableRows = document.querySelectorAll("#appointmentTable tr");

searchInput.addEventListener("keyup", function(){

    let value = searchInput.value.toLowerCase();

    tableRows.forEach(function(row){

        let patient = row.cells[0].textContent.toLowerCase();
        let date = row.cells[1].textContent.toLowerCase();
        let time = row.cells[2].textContent.toLowerCase();
        let type = row.cells[3].textContent.toLowerCase();
        let status = row.cells[4].textContent.toLowerCase();

        let fullText =
        patient + " " +
        date + " " +
        time + " " +
        type + " " +
        status;

        if(fullText.includes(value)){
            row.style.display = "";
        }else{
            row.style.display = "none";
        }

    });

});

function openViewModal(patient,email,phone,date,time,type){

    document.getElementById("viewPatient").value = patient;
    document.getElementById("viewEmail").value = email;
    document.getElementById("viewPhone").value = phone;
    document.getElementById("viewDate").value = date;
    document.getElementById("viewTime").value = time;
    document.getElementById("viewType").value = type;

    document.getElementById("viewModal").style.display = "flex";
}

function closeModal(){
    document.getElementById("viewModal").style.display = "none";
}

window.onclick = function(event){

    let modal = document.getElementById("viewModal");

    if(event.target == modal){
        modal.style.display = "none";
    }

}

</script>

</body>
</html>

