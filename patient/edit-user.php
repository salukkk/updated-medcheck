<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] === "" || ($_SESSION['usertype'] ?? '') !== 'p') {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("location: settings.php");
    exit();
}

$useremail = $_SESSION["user"];
$id = (int) ($_POST['id00'] ?? 0);
$name = trim($_POST['name'] ?? '');
$age = trim($_POST['age'] ?? '');
$address = trim($_POST['address'] ?? '');
$email = trim($_POST['email'] ?? '');
$tele = trim($_POST['Tele'] ?? '');
$password = $_POST['password'] ?? '';
$cpassword = $_POST['cpassword'] ?? '';

$stmt = $database->prepare("SELECT pid, pemail, ppassword FROM patient WHERE pemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$currentPatient = $stmt->get_result()->fetch_assoc();

if (!$currentPatient || (int) $currentPatient['pid'] !== $id) {
    header("location: settings.php?action=edit&error=unauthorized&id=" . $id);
    exit();
}

if ($password !== $cpassword) {
    header("location: settings.php?action=edit&error=2&id=" . $id);
    exit();
}

if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("location: settings.php?action=edit&error=invalid&id=" . $id);
    exit();
}

$stmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ? AND pid <> ?");
$stmt->bind_param("si", $email, $id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    header("location: settings.php?action=edit&error=1&id=" . $id);
    exit();
}

$database->begin_transaction();

try {
    if ($password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $database->prepare("UPDATE patient SET pemail = ?, pname = ?, ppassword = ?, pnic = ?, ptel = ?, paddress = ? WHERE pid = ?");
        $stmt->bind_param("ssssssi", $email, $name, $hashedPassword, $age, $tele, $address, $id);
    } else {
        $stmt = $database->prepare("UPDATE patient SET pemail = ?, pname = ?, pnic = ?, ptel = ?, paddress = ? WHERE pid = ?");
        $stmt->bind_param("sssssi", $email, $name, $age, $tele, $address, $id);
    }

    $stmt->execute();

    $stmt = $database->prepare("UPDATE webuser SET email = ? WHERE email = ?");
    $stmt->bind_param("ss", $email, $currentPatient['pemail']);
    $stmt->execute();

    $database->commit();
    $_SESSION["user"] = $email;

    header("location: settings.php?success=1");
    exit();
} catch (Throwable $e) {
    $database->rollback();
    error_log("Patient profile update failed: " . $e->getMessage());
    header("location: settings.php?action=edit&error=server&id=" . $id);
    exit();
}
