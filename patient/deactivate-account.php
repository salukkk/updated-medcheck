<?php

session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'p') {
        header("location: ../login.php");
        exit();
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
    exit();
}

include("../connection.php");
$sqlmain = "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["pid"];

if ($_POST) {
    $id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    if ($id !== $userid) {
        http_response_code(403);
        exit("Forbidden");
    }

    if (!password_verify($password, $userfetch['ppassword'])) {
        header("location: settings.php?action=deactivate&error=invalid_password");
        exit();
    }

    $deactivateUntil = date('Y-m-d', strtotime('+6 months'));
    $sqlmain = "UPDATE patient SET status = 'deactivated', deactivate_until = ? WHERE pid = ?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("si", $deactivateUntil, $id);
    $stmt->execute();

    header("location: ../logout.php");
    exit();
}

header("location: settings.php");
exit();
