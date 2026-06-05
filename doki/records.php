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

if(isset($_SESSION['doctor'])){
    $doctorname = $_SESSION['doctor']['name'] ?? $doctorname;
    $specialty = $_SESSION['doctor']['specialty'] ?? $specialty;
} else {
    $useremail = $_SESSION['user'];
    $sql = "SELECT docname, specialties FROM doctor WHERE docemail='$useremail'";
    $result = $database->query($sql);
    if($row = $result->fetch_assoc()){
        $doctorname = $row['docname'];
        $specialtyid = $row['specialties'];
        $specsql = "SELECT sname FROM specialties WHERE id='$specialtyid'";
        $specresult = $database->query($specsql);
        if($specrow = $specresult->fetch_assoc()){
            $specialty = $specrow['sname'];
        }
    }
}

$records = [

    [
        "patient" => "Juan Dela Cruz",
        "diagnosis" => "Hypertension",
        "prescription" => "Losartan 50mg",
        "laboratory" => "Blood Test",
        "date" => "March 24, 2026",
        "status" => "Completed"
    ],

    [
        "patient" => "Maria Santos",
        "diagnosis" => "Diabetes",
        "prescription" => "Metformin",
        "laboratory" => "Sugar Test",
        "date" => "April 12, 2026",
        "status" => "Pending"
    ],

    [
        "patient" => "Jose Reyes",
        "diagnosis" => "Fever",
        "prescription" => "Paracetamol",
        "laboratory" => "CBC",
        "date" => "December 5, 2026",
        "status" => "Completed"
    ]

];
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Medical Records</title>

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
    radial-gradient(circle at top left,
    rgba(6,160,212,0.18), transparent 25%),
    radial-gradient(circle at bottom right,
    rgba(54,83,228,0.2), transparent 22%),
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

.completed{
    background:#dcfce7;
    color:#166534;
}

.pending{
    background:#fef3c7;
    color:#92400e;
}

.action-buttons{
    display:flex;
    gap:10px;
}

.view-btn,
.edit-btn,
.delete-btn{
    border:none;
    padding:10px 14px;
    border-radius:12px;
    color:white;
    cursor:pointer;
    font-weight:600;
}

.view-btn{
    background:linear-gradient(135deg,#0ea5e9,#0284c7);
}

.edit-btn{
    background:linear-gradient(135deg,#f59e0b,#d97706);
}

.delete-btn{
    background:linear-gradient(135deg,#ef4444,#dc2626);
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
    width:650px;
    max-width:95%;
    border-radius:24px;
    padding:30px;
    max-height:90vh;
    overflow-y:auto;
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
.form-group textarea{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:1px solid #dbeafe;
    background:#f8fafc;
    outline:none;
    font-size:14px;
}

.form-group textarea{
    resize:none;
    height:110px;
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
    margin-top:10px;
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

    <a href="schedule.php">
        <i class="fa-solid fa-clock"></i>
        Schedule / Sessions
    </a>

    <a class="active" href="records.php">
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
                <i class="fa-solid fa-file-medical"></i>
                Medical Records
            </h1>

            <p>
                Manage diagnosis, prescriptions,
                laboratory results, consultation notes,
                and patient medical files professionally.
            </p>

        </div>

        <div class="date">
            <i class="fa-solid fa-calendar"></i>
            <?php echo date('d M, Y'); ?>
        </div>

    </div>

    <div class="section-title">

        <h2>
            <i class="fa-solid fa-notes-medical"></i>
            Patient Records
        </h2>

    </div>

    <div class="card">

        <div class="top-actions">

            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input type="text"
                id="searchInput"
                placeholder="Search patient, month, day, year...">

            </div>

            <button class="add-btn">

                <i class="fa-solid fa-plus"></i>
                Add Record

            </button>

        </div>

        <div class="table-container">

            <table class="data-table">

                <thead>

                    <tr>
                        <th>Patient</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                        <th>Laboratory</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody id="recordTable">

                <?php foreach($records as $record): ?>

                    <tr>

                        <td><?php echo $record['patient']; ?></td>

                        <td><?php echo $record['diagnosis']; ?></td>

                        <td><?php echo $record['prescription']; ?></td>

                        <td><?php echo $record['laboratory']; ?></td>

                        <td><?php echo $record['date']; ?></td>

                        <td>

                            <?php
                            $statusClass =
                            strtolower($record['status']);
                            ?>

                            <span class="status <?php echo $statusClass; ?>">
                                <?php echo $record['status']; ?>
                            </span>

                        </td>

                        <td>

                            <div class="action-buttons">

                                <button class="view-btn"
                                onclick="viewRecord(this)">

                                    <i class="fa-solid fa-eye"></i>

                                </button>

                                <button class="edit-btn"
                                onclick="editRecord(this)">

                                    <i class="fa-solid fa-pen"></i>

                                </button>

                                <button class="delete-btn"
                                onclick="deleteRecord(this)">

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

<div class="modal"
id="viewModal">

    <div class="modal-content">

        <div class="modal-header">

            <h2 id="modalTitle">
                Medical Record Details
            </h2>

            <button class="close-btn"
            onclick="closeViewModal()">
                &times;
            </button>

        </div>

        <div class="form-group">
            <label>Patient Name</label>
            <input type="text" id="viewPatient">
        </div>

        <div class="form-group">
            <label>Diagnosis</label>
            <textarea id="viewDiagnosis"></textarea>
        </div>

        <div class="form-group">
            <label>Prescription</label>
            <textarea id="viewPrescription"></textarea>
        </div>

        <div class="form-group">
            <label>Laboratory</label>
            <input type="text" id="viewLaboratory">
        </div>

        <div class="form-group">
            <label>Date</label>
            <input type="text" id="viewDate">
        </div>

        <div class="form-group">
            <label>Status</label>
            <input type="text" id="viewStatus">
        </div>

        <button
        class="save-btn"
        id="saveBtn"
        onclick="saveRecord()">
            Save Changes
        </button>

    </div>

</div>

<script>

const searchInput =
document.getElementById("searchInput");

let currentEditRow = null;

searchInput.addEventListener(
"keyup",
function(){

    let value =
    this.value.toLowerCase();

    let rows =
    document.querySelectorAll("#recordTable tr");

    rows.forEach(function(row){

        let patient =
        row.cells[0].innerText.toLowerCase();

        let diagnosis =
        row.cells[1].innerText.toLowerCase();

        let date =
        row.cells[4].innerText.toLowerCase();

        let fullText =
        patient + " " +
        diagnosis + " " +
        date;

        if(fullText.includes(value)){

            row.style.display = "";

        }else{

            row.style.display = "none";

        }

    });

});

function viewRecord(button){

    let row =
    button.closest("tr");

    document.getElementById("viewPatient").value =
    row.cells[0].innerText;

    document.getElementById("viewDiagnosis").value =
    row.cells[1].innerText;

    document.getElementById("viewPrescription").value =
    row.cells[2].innerText;

    document.getElementById("viewLaboratory").value =
    row.cells[3].innerText;

    document.getElementById("viewDate").value =
    row.cells[4].innerText;

    document.getElementById("viewStatus").value =
    row.cells[5].innerText;

    document.getElementById("viewPatient").readOnly = true;
    document.getElementById("viewDiagnosis").readOnly = true;
    document.getElementById("viewPrescription").readOnly = true;
    document.getElementById("viewLaboratory").readOnly = true;
    document.getElementById("viewDate").readOnly = true;
    document.getElementById("viewStatus").readOnly = true;

    document.getElementById("saveBtn").style.display =
    "none";

    document.getElementById("modalTitle").innerText =
    "Medical Record Details";

    document.getElementById("viewModal")
    .style.display = "flex";

}

function editRecord(button){

    currentEditRow =
    button.closest("tr");

    document.getElementById("viewPatient").value =
    currentEditRow.cells[0].innerText;

    document.getElementById("viewDiagnosis").value =
    currentEditRow.cells[1].innerText;

    document.getElementById("viewPrescription").value =
    currentEditRow.cells[2].innerText;

    document.getElementById("viewLaboratory").value =
    currentEditRow.cells[3].innerText;

    document.getElementById("viewDate").value =
    currentEditRow.cells[4].innerText;

    document.getElementById("viewStatus").value =
    currentEditRow.cells[5].innerText;

    document.getElementById("viewPatient").readOnly = false;
    document.getElementById("viewDiagnosis").readOnly = false;
    document.getElementById("viewPrescription").readOnly = false;
    document.getElementById("viewLaboratory").readOnly = false;
    document.getElementById("viewDate").readOnly = false;
    document.getElementById("viewStatus").readOnly = false;

    document.getElementById("saveBtn").style.display =
    "block";

    document.getElementById("modalTitle").innerText =
    "Edit Medical Record";

    document.getElementById("viewModal")
    .style.display = "flex";

}

function saveRecord(){

    if(currentEditRow){

        currentEditRow.cells[0].innerText =
        document.getElementById("viewPatient").value;

        currentEditRow.cells[1].innerText =
        document.getElementById("viewDiagnosis").value;

        currentEditRow.cells[2].innerText =
        document.getElementById("viewPrescription").value;

        currentEditRow.cells[3].innerText =
        document.getElementById("viewLaboratory").value;

        currentEditRow.cells[4].innerText =
        document.getElementById("viewDate").value;

        let statusValue =
        document.getElementById("viewStatus").value;

        let statusClass =
        statusValue.toLowerCase();

        currentEditRow.cells[5].innerHTML =
        '<span class="status ' +
        statusClass +
        '">' +
        statusValue +
        '</span>';

        alert("Medical Record Updated Successfully");

        closeViewModal();

    }

}

function closeViewModal(){

    document.getElementById("viewModal")
    .style.display = "none";

}

function deleteRecord(button){

    if(confirm("Delete this record?")){

        button.closest("tr").remove();

    }

}

window.onclick = function(event){

    let modal =
    document.getElementById("viewModal");

    if(event.target == modal){

        modal.style.display = "none";

    }

}

</script>

</body>a
</html>