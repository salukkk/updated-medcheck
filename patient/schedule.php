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

$userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$username=$userfetch["pname"];
$userid=(int)$userfetch["pid"];

include_once(__DIR__ . "/../patient_accessibility.php");
$accessibility = get_patient_accessibility($database, $userid);
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sessions</title>

<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="../css/accessibility.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    .welcome-info .date {
        color: #0f172a;
    }

    .welcome-info .date i {
        color: #4facfe;
    }

    .search-card {
        padding: 22px;
        background: #f8f9fa;
        border-radius: 18px;
        border: none;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        margin-top: 20px;
    }

    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
    }

    .search-container {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
        flex-wrap: wrap;
    }

    .search-container input {
        flex: 1;
        min-width: 220px;
        padding: 12px 14px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background: white;
        font-size: 14px;
        color: #0f172a;
        outline: none;
    }

    .search-container input:focus {
        border-color: #4facfe;
        box-shadow: 0 0 0 4px rgba(79,172,254,0.12);
    }

    .search-container button {
        border: none;
        background: linear-gradient(135deg, #4facfe 0%, #00c6ff 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s ease;
    }

    .search-container button:hover {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        transform: translateY(-2px);
    }

    .search-container button i {
        color: white;
    }

    .session-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 22px;
        margin-top: 24px;
    }

    .session-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 24px;
        border: 1px solid #edf2f7;
        box-shadow: 0 12px 30px rgba(15,23,42,0.06);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .session-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 35px rgba(37,99,235,0.12);
    }

    .session-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        gap: 10px;
    }

    .session-header h3 {
        margin: 0;
        font-size: 22px;
        color: #0f172a;
        word-break: break-word;
    }

    .status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status.today {
        background: #dbeafe;
        color: #2563eb;
    }

    .status.upcoming {
        background: #d1fae5;
        color: #047857;
    }

    .status.passed {
        background: #fee2e2;
        color: #b91c1c;
    }

    .session-card p {
        margin: 10px 0;
        color: #475569;
        font-size: 14px;
    }

    .session-card p b {
        color: #0f172a;
    }

    .book-link {
        text-decoration: none;
        display: block;
        margin-top: 12px;
    }

    .book-btn {
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
        background: linear-gradient(135deg, #4facfe 0%, #00c6ff 100%);
    }

    .book-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(37,99,235,0.2);
    }

    .no-session {
        padding: 30px;
        text-align: center;
        font-size: 16px;
        color: #64748b;
    }
</style>
</head>

<body<?php echo accessibility_body_attributes($accessibility); ?>>

<div class="topbar">
    <div class="logo"><i class="fa-solid fa-heart-pulse"></i> MEDCHECK</div>
    <div class="user"><i class="fa-solid fa-user"></i> Welcome, <?php echo $safeUsername; ?></div>
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
    <a class="active" href="schedule.php"><i class="fa-solid fa-calendar"></i> Sessions</a>
    <a href="appointment.php"><i class="fa-solid fa-book"></i> My Bookings</a>
    <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
    <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

</div>

<div class="main">

<div class="card welcome">
    <div class="welcome-content">
        <h1><i class="fa-solid fa-calendar"></i> Available Sessions</h1>
        <p>Book appointments with verified doctors</p>
    </div>

    <div class="welcome-info">
        <span class="date">
            <i class="fa-solid fa-calendar-day"></i>
            <?php echo date('d/m/Y'); ?>
        </span>
    </div>
</div>

<div class="card search-card">
    <form method="post" class="search-form">
        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="search" placeholder="Search session...">
            <button type="submit">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </div>
    </form>
</div>

<?php

$sql = "SELECT * FROM schedule
INNER JOIN doctor ON schedule.docid=doctor.docid
WHERE schedule.scheduledate>='$today'
ORDER BY schedule.scheduledate ASC";

if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST["search"])){

    $k = mysqli_real_escape_string($database, $_POST["search"]);

    $sql = "SELECT * FROM schedule
    INNER JOIN doctor ON schedule.docid=doctor.docid
    WHERE schedule.scheduledate>='$today'
    AND (
        doctor.docname LIKE '%$k%'
        OR schedule.title LIKE '%$k%'
        OR schedule.scheduledate LIKE '%$k%'
    )
    ORDER BY schedule.scheduledate ASC";
}

$result = $database->query($sql);
?>

<div class="card">
    <h3>Sessions Found: <?php echo $result->num_rows; ?></h3>
</div>

<div class="session-grid">

<?php

if($result->num_rows==0){

    echo "<div class='card no-session'>No Sessions Found</div>";

}else{

while($row=$result->fetch_assoc()){

$id = $row["scheduleid"];
$title = $row["title"];
$doc = $row["docname"];
$date = $row["scheduledate"];
$time = date("h:i A", strtotime($row["scheduletime"]));

$days = (strtotime($date)-strtotime($today))/86400;

if($date==$today){
    $class="today";
    $label="TODAY";
}elseif($days>0){
    $class="upcoming";
    $label="UPCOMING";
}else{
    $class="passed";
    $label="PASSED";
}

?>

<div class="session-card">

    <div class="session-header">
        <h3><?php echo $title; ?></h3>
        <span class="status <?php echo $class; ?>">
            <?php echo $label; ?>
        </span>
    </div>

    <p><b>Doctor:</b> <?php echo $doc; ?></p>
    <p><b>Date:</b> <?php echo $date; ?></p>
    <p><b>Time:</b> <?php echo $time; ?></p>

    <a class="book-link" href="booking.php?id=<?php echo $id; ?>">
        <button type="button" class="book-btn">Quick Book</button>
    </a>

</div>

<?php

}
}
?>

</div>

</div>
</div>

<?php render_accessibility_script($accessibility); ?>

</body>
</html>
