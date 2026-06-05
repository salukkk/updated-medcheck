<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] === "" || ($_SESSION['usertype'] ?? '') !== 'p') {
    header("location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("location: appointment.php");
    exit();
}

include("../connection.php");

$useremail = $_SESSION["user"];
$appointmentId = (int) $_GET["id"];

$stmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    header("location: ../logout.php");
    exit();
}

$patientId = (int) $patient["pid"];
$stmt = $database->prepare("DELETE FROM appointment WHERE appoid = ? AND pid = ?");
$stmt->bind_param("ii", $appointmentId, $patientId);
$stmt->execute();

header("location: appointment.php");
exit();
