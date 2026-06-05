<?php 
session_start();
if(isset($_SESSION['user'])){
    if($_SESSION['user']=='' || $_SESSION['usertype']!='a'){
        header('location: ../login.php'); exit();
    }
}else{ header('location: ../login.php'); exit(); }

include('../connection.php');
$username='Administrator';
$today=date('F d, Y');

/* ===== TOGGLE STATUS HANDLER ===== */
if(isset($_POST['toggle_status'])){
    $did = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if($did > 0){
        if($action === 'deactivate'){
            $stmt = $database->prepare("UPDATE doctor SET status = 'deactivated', deactivate_until = DATE_ADD(NOW(), INTERVAL 6 MONTH) WHERE docid = ?");
            $stmt->bind_param("i", $did);
            $stmt->execute();
            $stmt->close();
        } elseif($action === 'reactivate'){
            $stmt = $database->prepare("UPDATE doctor SET status = 'active', deactivate_until = NULL WHERE docid = ?");
            $stmt->bind_param("i", $did);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: doctors.php"); exit();
}

/* ===== SPECIALTY FIX ===== */
function displaySpecialty($value) {
    global $database;
    if(empty($value)) return 'Not Selected';
    $stmt = $database->prepare("SELECT sname FROM specialties WHERE id = ?");
    $stmt->bind_param("i", $value);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        $stmt->close();
        return $row['sname'];
    }
    $stmt->close();
    return $value;
}

/* ADD */
if(isset($_POST['adddoctor'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $specialty = (int) $_POST['spec'];
    $docnic = $_POST['docnic'] ?? '';
    $doctel = $_POST['doctel'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $database->prepare("INSERT INTO doctor (docname, docemail, docpassword, docnic, doctel, specialties) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $name, $email, $password, $docnic, $doctel, $specialty);
    $stmt->execute();
    $stmt->close();

    header("Location: doctors.php"); exit();
}

/* DELETE */
if(isset($_GET['delete'])){
    $deleteId = (int) $_GET['delete'];
    $stmt = $database->prepare("DELETE FROM doctor WHERE docid = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: doctors.php"); exit();
}

/* UPDATE */
if(isset($_POST['update'])){
    $id = (int) $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $spec = (int) $_POST['spec'];

    $stmt = $database->prepare("UPDATE doctor SET docname = ?, docemail = ?, specialties = ? WHERE docid = ?");
    $stmt->bind_param("ssii", $name, $email, $spec, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: doctors.php"); exit();
}

/* SEARCH */
if(isset($_POST['searchbtn'])){
    $search = '%' . trim($_POST['search']) . '%';
    $stmt = $database->prepare("SELECT * FROM doctor WHERE docname LIKE ? OR docemail LIKE ? ORDER BY docid DESC");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $database->query("SELECT * FROM doctor ORDER BY docid DESC");
}

$specialtyOptions = $database->query("SELECT id, sname FROM specialties ORDER BY sname ASC");
$totalDoctors = $result->num_rows;
?>

<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Doctors</title>

<link rel='stylesheet' href='../css/index.css'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'>

<style>

/* ===== COLOR SYSTEM ===== */
:root{
    --primary:#2f80ed;
    --primary-dark:#1c6ed5;
    --green:#27ae60;
    --red:#e74c3c;
    --orange:#f39c12;
    --bg:#f4f7fb;
}

/* ===== GLOBAL ===== */
body{background:var(--bg); font-family:Segoe UI;}
.card{
    border-radius:14px;
    box-shadow:0 4px 20px rgba(0,0,0,0.05);
}

/* ===== SEARCH ===== */
.search-bar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.search-bar input{
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
    min-width:250px;
}
.search-bar input:focus{
    border-color:var(--primary);
    outline:none;
}

/* ===== BUTTONS ===== */
.btn{
    padding:10px 16px;
    border:none;
    border-radius:10px;
    color:white;
    cursor:pointer;
    font-size:14px;
}
.primary{background:var(--primary);}
.primary:hover{background:var(--primary-dark);}
.green{background:var(--green);}
.red{background:var(--red);}
.orange{background:var(--orange);}

/* ===== TABLE ===== */
.data-table{
    width:100%;
    border-collapse:collapse;
}
.data-table th{
    background:var(--primary);
    color:white;
    padding:14px;
}
.data-table td{
    padding:14px;
    border-bottom:1px solid #eee;
}
.data-table tr:hover{
    background:#eef5ff;
}

/* ===== STATUS BADGE ===== */
.status-badge{
    display:inline-block;
    padding:6px 10px;
    border-radius:8px;
    color:#fff;
    font-weight:700;
    font-size:13px;
}
.status-active{background:var(--green);}
.status-deactivated{background:var(--red);}

/* ===== ACTION BUTTONS ===== */
.actions{
    display:flex;
    gap:6px;
}
.actions button{
    padding:6px 10px;
    border-radius:6px;
    border:none;
    color:white;
    cursor:pointer;
    font-size:12px;
}

/* ===== MODAL ===== */
.modal{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.4);
    justify-content:center;
    align-items:center;
}
.modal-box{
    background:white;
    padding:25px;
    border-radius:12px;
    width:400px;
}

/* ===== FORM ALIGNMENT ===== */
.form-group{
    margin-bottom:12px;
}
.form-group label{
    font-size:13px;
    color:#555;
    display:block;
    margin-bottom:4px;
}
.form-group input{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}
.form-group input:focus{
    border-color:var(--primary);
    outline:none;
}

.modal-box button{
    width:100%;
    margin-top:10px;
}

</style>

</head>
<body>

<div class='topbar'>
 <div class='logo'><i class='fa-solid fa-heart-pulse'></i> MEDCHECK</div>
 <div class='user'><i class='fa-solid fa-user-shield'></i> <?php echo $username; ?></div>
</div>

<div class='container'>

<div class='sidebar'>
<div class='profile'>
<img src='../img/user.png'>
<h3><?php echo $username; ?></h3>
<p>Administrator</p>
</div>

<a href='index.php'><i class='fa-solid fa-gauge'></i> Dashboard</a>
<a class='active' href='doctors.php'><i class='fa-solid fa-user-doctor'></i> Doctors</a>
<a href='schedule.php'><i class='fa-solid fa-calendar-days'></i> Schedule</a>
<a href='appointment.php'><i class='fa-solid fa-calendar-check'></i> Appointment</a>
<a href='patient.php'><i class='fa-solid fa-users'></i> Patients</a>
<a class='logout' href='../logout.php'><i class='fa-solid fa-right-from-bracket'></i> Logout</a>
</div>

<div class='main'>

<div class='card welcome'>
<h1><i class='fa-solid fa-user-doctor'></i> Doctors Management</h1>
<p><?php echo $today; ?></p>
</div>

<div class='grid'>
<div class='card stat-card'>
<div class='stat-top'>
<i class='fa-solid fa-stethoscope stat-icon'></i>
<h2><?php echo $totalDoctors; ?></h2>
</div>
<p>Total Doctors</p>
</div>
</div>

<div class='card' style='padding:15px;'>
<form method='POST' class='search-bar'>
<input type='text' name='search' placeholder='Search doctor name or email'>
<button name='searchbtn' class='btn primary'><i class='fa fa-search'></i></button>
<button type='button' onclick='openAdd()' class='btn green'><i class='fa fa-plus'></i> Add</button>
</form>
</div>

<div class='card' style='margin-top:15px;'>
<table class='data-table'>
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Specialty</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>
<?php while($row=$result->fetch_assoc()){ ?>
<tr>
<td><?php echo $row['docname']; ?></td>
<td><?php echo $row['docemail']; ?></td>
<td><?php echo displaySpecialty($row['specialties']); ?></td>
<td>
    <span class="status-badge <?php echo (isset($row['status']) && $row['status'] === 'deactivated') ? 'status-deactivated' : 'status-active'; ?>">
        <?php echo ucfirst($row['status'] ?? 'active'); ?>
    </span>
</td>
<td class='actions'>
<button class='primary' onclick="viewDoc('<?php echo $row['docname']; ?>','<?php echo $row['docemail']; ?>','<?php echo displaySpecialty($row['specialties']); ?>','<?php echo $row['status'] ?? 'active'; ?>','<?php echo $row['deactivate_until'] ?? ''; ?>')">View</button>
<button class='orange' onclick="editDoc('<?php echo $row['docid']; ?>','<?php echo $row['docname']; ?>','<?php echo $row['docemail']; ?>','<?php echo $row['specialties']; ?>')">Edit</button>

<form method='POST' style='display:inline-block; margin:0 4px;'>
    <input type='hidden' name='id' value='<?php echo $row['docid']; ?>'>
    <?php if(isset($row['status']) && $row['status'] === 'deactivated'){ ?>
        <input type='hidden' name='action' value='reactivate'>
        <button type='submit' name='toggle_status' class='btn green'>Reactivate</button>
    <?php } else { ?>
        <input type='hidden' name='action' value='deactivate'>
        <button type='submit' name='toggle_status' class='btn red'>Deactivate</button>
    <?php } ?>
</form>

<button class='red' onclick="deleteDoc(<?php echo $row['docid']; ?>)">Delete</button>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

</div>
</div>

<!-- ADD MODAL -->
<div class='modal' id='addModal'>
<div class='modal-box'>
<h3>Add Doctor</h3>
<form method='POST'>
<div class='form-group'><label>Name</label><input name='name' required></div>
<div class='form-group'><label>Email</label><input name='email' required></div>
<div class='form-group'><label>NIC</label><input name='docnic'></div>
<div class='form-group'><label>Phone</label><input name='doctel'></div>
<div class='form-group'><label>Password</label><input type='password' name='password' required></div>

<div class='form-group'>
<label>Specialty</label>
<select name='spec' required style='width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;'>
<option value=''>Select Specialty</option>
<?php
    if($specialtyOptions){
        while($opt = $specialtyOptions->fetch_assoc()){
            echo "<option value='".$opt['id']."'>".htmlspecialchars($opt['sname'])."</option>";
        }
        $specialtyOptions->data_seek(0);
    }
?>
</select>
</div>

<button class='btn green' name='adddoctor'>Save</button>
</form>
<button onclick='closeModal()' class='btn primary'>Close</button>
</div>
</div>

<!-- EDIT MODAL -->
<div class='modal' id='editModal'>
<div class='modal-box'>
<h3>Edit Doctor</h3>
<form method='POST'>
<input type='hidden' name='id' id='eid'>
<div class='form-group'><label>Name</label><input name='name' id='ename'></div>
<div class='form-group'><label>Email</label><input name='email' id='eemail'></div>

<div class='form-group'>
<label>Specialty</label>
<select name='spec' id='espec' style='width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;'>
<option value=''>Select Specialty</option>
<?php
    if($specialtyOptions){
        while($opt = $specialtyOptions->fetch_assoc()){
            echo "<option value='".$opt['id']."'>".htmlspecialchars($opt['sname'])."</option>";
        }
        $specialtyOptions->data_seek(0);
    }
?>
</select>
</div>

<button class='btn orange' name='update'>Update</button>
</form>
<button onclick='closeModal()' class='btn primary'>Close</button>
</div>
</div>

<!-- VIEW MODAL -->
<div class='modal' id='viewModal'>
<div class='modal-box'>
<h3>Doctor Info</h3>
<p id='viewData'></p>
<button onclick='closeModal()' class='btn primary'>Close</button>
</div>
</div>

<script>
const specialtyMap = {
 '1':'Pediatrics',
 '2':'Cardiology',
 '3':'Orthopedics',
 '4':'Neurology',
 '5':'Dermatology',
 '6':'Dentistry',
 '7':'General Practice'
};

function getSpec(val){
 return specialtyMap[val] ? specialtyMap[val] : val;
}

function openAdd(){ document.getElementById('addModal').style.display='flex'; }
function closeModal(){ document.querySelectorAll('.modal').forEach(m=>m.style.display='none'); }

function viewDoc(n,e,s,st,du){
 document.getElementById('viewData').innerHTML =
 "<b>Name:</b> "+n+"<br><b>Email:</b> "+e+"<br><b>Specialty:</b> "+getSpec(s)+"<br><b>Status:</b> "+st + (du ? ("<br><b>Deactivated Until:</b> "+du) : "");
 document.getElementById('viewModal').style.display='flex';
}

function editDoc(id,n,e,s){
 document.getElementById('eid').value=id;
 document.getElementById('ename').value=n;
 document.getElementById('eemail').value=e;
 document.getElementById('espec').value=s;
 document.getElementById('editModal').style.display='flex';
}

function deleteDoc(id){
 if(confirm("Delete doctor?")){
  window.location.href="doctors.php?delete="+id;
 }
}
</script>

</body>
</html>