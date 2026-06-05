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
$successMessage = '';
if(isset($_GET['success']) && $_GET['success'] === 'approved'){
    $successMessage = 'Patient approved and added to your list.';
}
if(isset($_SESSION['doctor'])){
    $doctorname = $_SESSION['doctor']['name'] ?? $doctorname;
    $specialty = $_SESSION['doctor']['specialty'] ?? $specialty;
    $doctorid = (int)($_SESSION['doctor']['docid'] ?? 0);
} else {
    $useremail = $_SESSION['user'];
    $sql = "SELECT docid, docname, specialties FROM doctor WHERE docemail='$useremail'";
    $result = $database->query($sql);
    if($row = $result->fetch_assoc()){
        $doctorid = (int)$row['docid'];
        $doctorname = $row['docname'];
        $specialtyid = $row['specialties'];
        $specsql = "SELECT sname FROM specialties WHERE id='$specialtyid'";
        $specresult = $database->query($specsql);
        if($specrow = $specresult->fetch_assoc()){
            $specialty = $specrow['sname'];
        }
    }
}

$patients = [];

$patientSql = "SELECT patient.pid, patient.pname, patient.pemail, patient.ptel, patient.paddress, patient.pdob, patient.pnic AS patient_age,
               GROUP_CONCAT(DISTINCT schedule.title ORDER BY appointment.appodate DESC SEPARATOR ', ') AS session_titles,
               MAX(appointment.appodate) AS last_appointment
               FROM patient
               INNER JOIN appointment ON patient.pid = appointment.pid
               INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
               WHERE schedule.docid = ? AND appointment.status = 'approved'
               GROUP BY patient.pid, patient.pname, patient.pemail, patient.ptel, patient.paddress, patient.pdob, patient.pnic
               ORDER BY last_appointment DESC";
$patientStmt = $database->prepare($patientSql);
$patientStmt->bind_param('i', $doctorid);
$patientStmt->execute();
$patientResult = $patientStmt->get_result();
if ($patientResult) {
    while ($prow = $patientResult->fetch_assoc()) {
        $age = '';
        if (!empty($prow['patient_age']) && is_numeric($prow['patient_age'])) {
            $age = (int)$prow['patient_age'];
        }
        if ($age === '' && !empty($prow['pdob'])) {
            $dob = new DateTime($prow['pdob']);
            $age = $dob->diff(new DateTime())->y;
        }
        $patients[] = [
            'name' => $prow['pname'],
            'age' => $age !== '' ? $age : 'N/A',
            'contact' => $prow['ptel'] ?: 'N/A',
            'history' => 'Sessions: ' . ($prow['session_titles'] ?? 'Consultation'),
            'consultation' => !empty($prow['last_appointment']) ? date('M d, Y', strtotime($prow['last_appointment'])) : 'N/A',
            'status' => 'Active',
            'email' => $prow['pemail'] ?: 'N/A'
        ];
    }
}
$patientStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor My Patients</title>

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

.active-status{
    background:#dcfce7;
    color:#166534;
}

.recovered-status{
    background:#dbeafe;
    color:#1d4ed8;
}

.monitoring-status{
    background:#fef3c7;
    color:#92400e;
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

    <a href="appointmentdok.php">
        <i class="fa-solid fa-calendar-check"></i>
        My Appointments
    </a>

    <a class="active" href="mypatient.php">
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
                <i class="fa-solid fa-users"></i>
                Patient Management
            </h1>

            <p>
                Manage patient records, medical history,
                and previous consultations professionally.
            </p>

        </div>

        <div class="date">
            <i class="fa-solid fa-calendar"></i>
            <?php echo date('d M, Y'); ?>
        </div>

    </div>

    <div class="section-title">

        <h2>
            <i class="fa-solid fa-user-group"></i>
            Patient List
        </h2>

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

        <div class="search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input 
            type="text"
            id="searchInput"
            placeholder="Search patient, month, year, date, status...">

        </div>

    </div>

    <div class="card table-container">

        <table class="data-table">

            <thead>

                <tr>
                    <th>Patient</th>
                    <th>Age</th>
                    <th>Contact</th>
                    <th>Medical History</th>
                    <th>Consultation</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody id="patientTable">

            <?php if (count($patients) > 0): ?>
                <?php foreach($patients as $patient): ?>

                <tr>

                    <td><?php echo $patient['name']; ?></td>
                    <td><?php echo $patient['age']; ?></td>
                    <td><?php echo $patient['contact']; ?></td>
                    <td><?php echo $patient['history']; ?></td>
                    <td><?php echo $patient['consultation']; ?></td>

                    <td>

                        <?php
                        $statusClass = "";

                        if($patient['status'] == "Active"){
                            $statusClass = "active-status";
                        }

                        elseif($patient['status'] == "Recovered"){
                            $statusClass = "recovered-status";
                        }

                        else{
                            $statusClass = "monitoring-status";
                        }
                        ?>

                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $patient['status']; ?>
                        </span>

                    </td>

                    <td>

                        <button
                        class="view-btn"

                        onclick="openViewModal(
                        '<?php echo htmlspecialchars($patient['name'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($patient['age'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($patient['contact'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($patient['history'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($patient['consultation'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($patient['status'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($patient['email'], ENT_QUOTES); ?>'
                        )">

                            <i class="fa-solid fa-eye"></i>
                            View

                        </button>

                    </td>

                </tr>

                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:24px; color:#475569;">No approved patients yet. Approve a quick booking from the Notifications page to add patients here.</td>
                </tr>
            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</div>

<div class="modal" id="viewModal">

    <div class="modal-content">

        <div class="modal-header">

            <h2>Patient Details</h2>

            <button class="close-btn"
            onclick="closeModal()">
                &times;
            </button>

        </div>

        <div class="form-group">
            <label>Patient Name</label>
            <input type="text" id="viewName" readonly>
        </div>

        <div class="form-group">
            <label>Age</label>
            <input type="text" id="viewAge" readonly>
        </div>

        <div class="form-group">
            <label>Contact</label>
            <input type="text" id="viewContact" readonly>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" id="viewEmail" readonly>
        </div>

        <div class="form-group">
            <label>Medical History</label>
            <input type="text" id="viewHistory" readonly>
        </div>

        <div class="form-group">
            <label>Previous Consultation</label>
            <input type="text" id="viewConsultation" readonly>
        </div>

        <div class="form-group">
            <label>Status</label>
            <input type="text" id="viewStatus" readonly>
        </div>

    </div>

</div>

<script>

const searchInput =
document.getElementById("searchInput");

function filterPatients(){

    let searchValue =
    searchInput.value.toLowerCase();

    let rows =
    document.querySelectorAll("#patientTable tr");

    rows.forEach(function(row){

        let rowText =
        row.textContent.toLowerCase();

        if(rowText.includes(searchValue)){

            row.style.display = "";

        }else{

            row.style.display = "none";

        }

    });

}

searchInput.addEventListener(
"keyup",
filterPatients
);

function openViewModal(name,age,contact,history,consultation,status,email){

    document.getElementById("viewName").value = name;
    document.getElementById("viewAge").value = age;
    document.getElementById("viewContact").value = contact;
    document.getElementById("viewHistory").value = history;
    document.getElementById("viewConsultation").value = consultation;
    document.getElementById("viewStatus").value = status;
    document.getElementById("viewEmail").value = email;

    document.getElementById("viewModal").style.display = "flex";
}

function closeModal(){

    document.getElementById("viewModal").style.display = "none";

}

window.onclick = function(event){

    let modal =
    document.getElementById("viewModal");

    if(event.target == modal){

        modal.style.display = "none";

    }

}

</script>

</body>
</html>