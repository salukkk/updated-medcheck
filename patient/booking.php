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
$userfetch = $userrow->fetch_assoc();

$userid = $userfetch['pid'];
$username = $userfetch['pname'];

include_once(__DIR__ . "/../patient_accessibility.php");
$accessibility = get_patient_accessibility($database, (int) $userid);
$safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

if(!isset($_GET['id'])){
    header("location: schedule.php");
    exit();
}

$scheduleid = (int) $_GET['id'];

$stmt = $database->prepare("SELECT * FROM schedule
INNER JOIN doctor ON schedule.docid=doctor.docid
WHERE schedule.scheduleid=?");
$stmt->bind_param("i", $scheduleid);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    header("location: schedule.php");
    exit();
}

$row = $result->fetch_assoc();

$title = $row['title'];
$doctor = $row['docname'];
$date = $row['scheduledate'];
$time = date("h:i A", strtotime($row['scheduletime']));
$docid = $row['docid'];

$message = "";

if(isset($_POST['booknow'])){

    $stmt = $database->prepare("SELECT appoid FROM appointment WHERE pid=? AND scheduleid=?");
    $stmt->bind_param("ii", $userid, $scheduleid);
    $stmt->execute();
    $check = $stmt->get_result();

    if($check->num_rows > 0){

        $message = "already";

    }else{

        $appointmentnum = rand(100000,999999);

        $stmt = $database->prepare("INSERT INTO appointment(pid, apponum, scheduleid, appodate, status) VALUES(?,?,?,?,?)");
        $status = 'pending';
        $stmt->bind_param("iiiss", $userid, $appointmentnum, $scheduleid, $date, $status);
        $stmt->execute();

        $message = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Session</title>

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

    .booking-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }

    .booking-card {
        width: 100%;
        max-width: 700px;
        background: #ffffff;
        border-radius: 22px;
        padding: 35px;
        border: 1px solid #edf2f7;
        box-shadow: 0 12px 30px rgba(15,23,42,0.08);
    }

    .booking-header {
        text-align: center;
        margin-bottom: 28px;
    }

    .booking-header h2 {
        margin: 0;
        font-size: 30px;
        color: #0f172a;
    }

    .booking-header p {
        margin-top: 8px;
        color: #64748b;
        font-size: 15px;
    }

    .booking-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 18px;
        margin-top: 25px;
    }

    .detail-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #e2e8f0;
    }

    .detail-box span {
        display: block;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .detail-box h3 {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        word-break: break-word;
    }

    .booking-actions {
        display: flex;
        gap: 15px;
        margin-top: 35px;
        flex-wrap: wrap;
    }

    .book-btn {
        flex: 1;
        min-width: 200px;
        border: none;
        outline: none;
        padding: 15px;
        border-radius: 14px;
        color: white;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s ease;
        background: linear-gradient(135deg, #4facfe 0%, #00c6ff 100%);
    }

    .book-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(37,99,235,0.2);
    }

    .cancel-btn {
        flex: 1;
        min-width: 200px;
        text-decoration: none;
        text-align: center;
        padding: 15px;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 600;
        transition: 0.3s ease;
        background: #f1f5f9;
        color: #0f172a;
    }

    .cancel-btn:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
    }

    .alert {
        margin-top: 20px;
        padding: 15px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
    }

    .alert.success {
        background: #dcfce7;
        color: #166534;
    }

    .alert.error {
        background: #fee2e2;
        color: #b91c1c;
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
        <h1><i class="fa-solid fa-calendar-check"></i> Session Booking</h1>
        <p>Review and confirm your medical appointment</p>
    </div>

    <div class="welcome-info">
        <span class="date">
            <i class="fa-solid fa-calendar-day"></i>
            <?php echo date('d/m/Y'); ?>
        </span>
    </div>
</div>

<div class="booking-wrapper">

    <div class="booking-card">

        <div class="booking-header">
            <h2><?php echo $title; ?></h2>
            <p>Please confirm the session details before booking.</p>
        </div>

        <div class="booking-details">

            <div class="detail-box">
                <span>Doctor Name</span>
                <h3><?php echo $doctor; ?></h3>
            </div>

            <div class="detail-box">
                <span>Session Date</span>
                <h3><?php echo $date; ?></h3>
            </div>

            <div class="detail-box">
                <span>Session Time</span>
                <h3><?php echo $time; ?></h3>
            </div>

            <div class="detail-box">
                <span>Patient Name</span>
                <h3><?php echo $safeUsername; ?></h3>
            </div>

        </div>

        <?php
        if($message == "success"){
            echo "<div class='alert success'>Appointment booked successfully!</div>";
        }

        if($message == "already"){
            echo "<div class='alert error'>You already booked this session.</div>";
        }
        ?>

        <form method="POST">
            <div class="booking-actions">

                <button type="submit" name="booknow" class="book-btn">
                    <i class="fa-solid fa-check"></i> Confirm Booking
                </button>

                <a href="schedule.php" class="cancel-btn">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </a>

            </div>
        </form>

    </div>

</div>

</div>
</div>

<?php render_accessibility_script($accessibility); ?>

</body>
</html>
