<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'a'){
    header('location: ../login.php');
    exit();
}
include("../connection.php");

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $database->prepare("SELECT docemail FROM doctor WHERE docid = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($doc) {
    $email = $doc['docemail'];
    $stmt = $database->prepare("DELETE FROM doctor WHERE docid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $database->prepare("DELETE FROM webuser WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
}

header("location: doctors.php");
?>