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
$userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$username=$userfetch["pname"];
$userid=(int)$userfetch["pid"];

include_once(__DIR__ . "/../patient_accessibility.php");
$accessibility = get_patient_accessibility($database, $userid);
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings - Medcheck</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="../css/accessibility.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* PAGE HEADER (BLUE GRADIENT LIKE ADMIN PATIENT PAGE) */
.page-header{
    background: linear-gradient(135deg, #4facfe 0%, #00c6ff 100%);
    border-radius: 22px;
    padding: 35px;
    color: white;
    margin-bottom: 22px;
    box-shadow: 0 14px 30px rgba(37,99,235,0.18);
}

.page-header h1{
    margin: 0;
    font-size: 32px;
    font-weight: 700;
}

.page-header p{
    margin-top: 10px;
    opacity: 0.95;
    font-size: 15px;
    color: rgba(255,255,255,0.85);
}

/* ===== UPDATED SEARCH (MATCH ADMIN PATIENT PAGE) ===== */

.search-card{
    padding: 22px;
    background: #f8f9fa;
    border-radius: 18px;
    border: none;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    margin-top: 20px;
}

.search-form{
    width: 100%;
}

.search-container{
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.search-container > i{
    color: #2563eb;
    font-size: 18px;
}

.search-container button i{
    color: white;
}

.search-container input{
    flex: 1;
    min-width: 220px;
    padding: 12px 14px;
    border: 1px solid #ddd;
    border-radius: 10px;
    outline: none;
    background: white;
    font-size: 14px;
    color: #0f172a;
}

.search-container input:focus{
    border-color: #4facfe;
    box-shadow: 0 0 0 4px rgba(79,172,254,0.12);
}

.search-container button{
    border: none;
    background: linear-gradient(135deg, #4facfe 0%, #00c6ff 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.search-container button:hover{
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    transform: translateY(-2px);
}

/* TOTAL TEXT */
.total-bookings{
    margin: 18px 5px;
    font-size: 16px;
    color: #333;
    font-weight: 600;
}

.total-bookings b{
    color: #2563eb;
}

/* SESSION GRID */
.session-grid{
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(320px,1fr));
    gap: 24px;
    margin-top: 20px;
}

/* SESSION CARD (unchanged) */
.session-card{
    background: #ffffff;
    border-radius: 18px;
    padding: 24px;
    border: 1px solid #edf2f7;
    box-shadow: 0 12px 30px rgba(15,23,42,0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.session-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 18px 35px rgba(37,99,235,0.12);
}

.session-header{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.booking-id{
    background: #e8f6ff;
    color: #2563eb;
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
}

.session-card h3{
    margin-bottom: 18px;
    color: #0f172a;
    font-size: 22px;
}

.session-card p{
    margin: 10px 0;
    color: #475569;
    font-size: 14px;
}

.session-card h3 i,
.session-card p i{
    color: #2563eb;
    margin-right: 8px;
}

.session-card p b{
    color: #0f172a;
}

/* BUTTONS */
.book-btn{
    width: 100%;
    border: none;
    outline: none;
    padding: 15px;
    border-radius: 14px;
    color: white;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-top: 8px;
}

.book-btn:hover{
    transform: translateY(-2px);
}

.cancel-btn{
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.back-btn{
    background: linear-gradient(135deg, #4facfe 0%, #00c6ff 100%);
}

/* POPUP (unchanged) */
.popup{
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
    padding: 20px;
}

.popup-content{
    width: 100%;
    max-width: 430px;
    background: #ffffff;
    border-radius: 24px;
    padding: 30px;
    box-shadow: 0 25px 45px rgba(0,0,0,0.18);
}

</style>

</head>

<body<?php echo accessibility_body_attributes($accessibility); ?>>

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

<div class="sidebar">

    <div class="profile">
        <img src="../img/user.png">
        <h3><?php echo $safeUsername; ?></h3>
        <p>Patient</p>
    </div>

    <a href="index.php"><i class="fa-solid fa-house"></i>Home</a>
    <a href="doctors.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
    <a href="schedule.php"><i class="fa-solid fa-calendar-check"></i> Sessions</a>
    <a class="active" href="appointment.php"><i class="fa-solid fa-book"></i> My Bookings</a>
    <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
    <a class="logout" href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

</div>

<div class="main">

<div class="card welcome">
    <div class="welcome-content">
        <h1><i class="fa-solid fa-book"></i> My Bookings</h1>
        <p>View and manage all your scheduled appointments</p>
    </div>
    <div class="welcome-info">
        <span class="date"><i class="fa-solid fa-calendar"></i> <?php echo date('d/m/Y'); ?></span>
    </div>
</div>

<div class="card search-card">

    <form method="POST" class="search-form">

        <div class="search-container">

            <i class="fa-solid fa-calendar-days"></i>

            <input type="date" name="sheduledate">

            <button type="submit">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>

        </div>

    </form>

</div>

<?php

$sqlmain = "select appointment.appoid,schedule.scheduleid,schedule.title,doctor.docname,
schedule.scheduledate,schedule.scheduletime,
appointment.apponum,appointment.appodate
from schedule
inner join appointment on schedule.scheduleid=appointment.scheduleid
inner join doctor on schedule.docid=doctor.docid
where appointment.pid=$userfetch[pid]
order by appointment.appodate desc";

$result = $database->query($sqlmain);

?>

<p class="total-bookings">
    Total Bookings: <b><?php echo $result->num_rows; ?></b>
</p>

<div class="session-grid">

<?php

if($result->num_rows == 0){
    echo "<div class='session-card'><h3>No Bookings Found</h3></div>";
}else{

while($row=$result->fetch_assoc()){

$appoid=$row["appoid"];
$title=$row["title"];
$docname=$row["docname"];
$date=$row["scheduledate"];
$time=$row["scheduletime"];
$num=$row["apponum"];
$appodate=$row["appodate"];

echo "

<div class='session-card'>

    <div class='session-header'>
        <span class='booking-id'>OC-000-$appoid</span>
    </div>

    <h3>$title</h3>

    <p><b>Doctor:</b> $docname</p>
    <p><b>No:</b> $num</p>
    <p><b>Date:</b> $date</p>
    <p><b>Time:</b> $time</p>

    <a href='delete-appointment.php?id=$appoid'>
        <button class='book-btn cancel-btn'>Cancel</button>
    </a>

</div>

";

}

}

?>

</div>

</div>
</div>

<?php render_accessibility_script($accessibility); ?>

</body>
</html>
