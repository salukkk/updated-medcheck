
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
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userfetch = $stmt->get_result()->fetch_assoc();

if(!$userfetch){
    header("location: ../logout.php");
    exit();
}

$username = $userfetch["pname"];
$userid = (int) $userfetch["pid"];
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

include_once(__DIR__ . "/../patient_accessibility.php");
$accessibility = get_patient_accessibility($database, $userid);
$doctorCount = $database->query("SELECT * FROM doctor")->num_rows;

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $keyword = trim($_POST["search"] ?? "");
    $sqlmain = "SELECT * FROM doctor WHERE docemail=? OR docname LIKE ? ORDER BY docid DESC";
    $stmt2 = $database->prepare($sqlmain);
    $like = "%$keyword%";
    $stmt2->bind_param("ss", $keyword, $like);
    $stmt2->execute();
    $result = $stmt2->get_result();
} else {
    $sqlmain = "SELECT * FROM doctor ORDER BY docid DESC";
    $result = $database->query($sqlmain);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctors - Medcheck</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="../css/accessibility.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* SEARCH BAR DESIGN ONLY */


.search-card{
    background: #ffffff;
    border-radius: 18px;
    padding: 22px 25px;
    margin-top: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    border: 1px solid #eef2f7;
}

.search-form{
    width: 100%;
}

.search-container{
    width: 100%;
    max-width: 580px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f9fbff;
    border: 2px solid #dcecff;
    border-radius: 15px;
    padding: 10px 14px;
    transition: all 0.3s ease;
}

.search-container:hover{
    border-color: #4da3ff;
    box-shadow: 0 6px 16px rgba(77,163,255,0.14);
}

.search-container:focus-within{
    background: #ffffff;
    border-color: #4da3ff;
    box-shadow: 0 8px 20px rgba(77,163,255,0.18);
}

.search-container i{
    color: #4da3ff;
    font-size: 16px;
}

.search-container input{
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 14px;
    color: #2d3748;
    font-weight: 500;
}

.search-container input::placeholder{
    color: #94a3b8;
    font-weight: 400;
}

.search-container button{
    border: none;
    outline: none;
    background: linear-gradient(135deg,#4da3ff,#2196f3);
    color: white;
    padding: 11px 22px;
    border-radius: 11px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
    display: flex;
    align-items: center;
    gap: 7px;
}

.search-container button:hover{
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(77,163,255,0.24);
}

.search-container button:active{
    transform: scale(0.98);
}

@media(max-width:768px){

    .search-container{
        max-width: 100%;
        flex-direction: column;
        align-items: stretch;
        padding: 15px;
    }

    .search-container button{
        width: 100%;
        justify-content: center;
    }

}

</style>
</head>

<body<?php echo accessibility_body_attributes($accessibility); ?>>

<!-- TOPBAR -->
<div class="topbar">
    <div class="logo">
        <i class="fa-solid fa-heart-pulse"></i> MEDCHECK
    </div>
    <div class="user">
        <i class="fa-solid fa-user"></i>
        Welcome back, <?php echo $safeUsername; ?>
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

    <a href="index.php"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a class="active" href="doctors.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
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
        <h1><i class="fa-solid fa-user-doctor"></i> Doctors</h1>
        <p>Find licensed medical professionals and book consultations instantly.</p>
    </div>
</div>

<!-- SEARCH -->
<div class="card search-card">
    <form method="POST" class="search-form">
        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass"></i>

            <input 
                type="search" 
                name="search" 
                placeholder="Search doctor name or email..." 
                required
            >

            <button type="submit">
                <i class="fa-solid fa-search"></i>
                Search
            </button>
        </div>
    </form>
</div>

<!-- TABLE -->
<div class="card table-card">

<h3><i class="fa-solid fa-hospital-user"></i> Available Doctors (<?php echo $doctorCount; ?>)</h3>

<table class="data-table">
    <thead>
        <tr>
            <th><i class="fa-solid fa-user-doctor"></i> Name</th>
            <th><i class="fa-solid fa-envelope"></i> Email</th>
            <th><i class="fa-solid fa-stethoscope"></i> Specialty</th>
            <th><i class="fa-solid fa-circle-info"></i> Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if($result && $result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $docid = $row["docid"];
                $name = htmlspecialchars($row["docname"]);
                $email = htmlspecialchars($row["docemail"]);
                $spe = $row["specialties"];

                $specialty = "General";
                if($spe !== null && $spe !== ""){
                    $sp = $database->query("SELECT sname FROM specialties WHERE id='" . $database->real_escape_string($spe) . "'");
                    $spn = $sp ? $sp->fetch_assoc() : null;
                    if($spn && isset($spn["sname"])){
                        $specialty = htmlspecialchars($spn["sname"]);
                    }
                }

                echo "
                <tr>
                    <td>$name</td>
                    <td>$email</td>
                    <td>$specialty</td>
                    <td>
                        <a href='?action=view&id=$docid'>
                            <button class='view-btn'>
                                <i class='fa-solid fa-eye'></i> View
                            </button>
                        </a>
                    </td>
                </tr>
                ";
            }
        } else {
            echo "
                <tr>
                    <td colspan='4' class='empty-state'>No doctors were found. Please try a different search term.</td>
                </tr>
            ";
        }
        ?>
    </tbody>
</table>

</div>

</div>
</div>

<?php
// POPUP
if(isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $doc = $database->query("SELECT * FROM doctor WHERE docid='$id'");
    if($doc && $doc->num_rows > 0){
        $d = $doc->fetch_assoc();
        $specialtyLabel = "General";
        if(!empty($d["specialties"])){
            $sp = $database->query("SELECT sname FROM specialties WHERE id='" . $database->real_escape_string($d["specialties"]) . "'");
            $spn = $sp ? $sp->fetch_assoc() : null;
            if($spn && isset($spn["sname"])){
                $specialtyLabel = htmlspecialchars($spn["sname"]);
            }
        }
?>

<div class="popup">
    <div class="popup-content">

        <h2><i class="fa-solid fa-user-doctor"></i> Doctor Profile</h2>

        <div class="popup-info">
            <p><i class="fa-solid fa-user"></i> <b>Name:</b> <?php echo htmlspecialchars($d['docname']); ?></p>
            <p><i class="fa-solid fa-envelope"></i> <b>Email:</b> <?php echo htmlspecialchars($d['docemail']); ?></p>
            <p><i class="fa-solid fa-stethoscope"></i> <b>Specialty:</b> <?php echo $specialtyLabel; ?></p>
        </div>

        <p class="note">
        <?php
        if(strtolower($specialtyLabel) == "pediatrics"){
            echo "<i class='fa-solid fa-baby'></i> Pediatric Specialist - Child healthcare & development";
        } elseif(strtolower($specialtyLabel) == "cardiology"){
            echo "<i class='fa-solid fa-heart-pulse'></i> Cardiology Specialist - Heart & cardiovascular care";
        } else {
            echo "<i class='fa-solid fa-stethoscope'></i> Available for medical consultation";
        }
        ?>
        </p>

        <a href="doctors.php">
            <button class="close-btn">
                <i class="fa-solid fa-xmark"></i> Close
            </button>
        </a>

    </div>
</div>

<?php }
} ?>

<?php render_accessibility_script($accessibility); ?>

</body>
</html>
