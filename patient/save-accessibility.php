<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user'] === '' || ($_SESSION['usertype'] ?? '') !== 'p') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include("../connection.php");
include_once(__DIR__ . "/../patient_accessibility.php");

$useremail = $_SESSION['user'];
$stmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
    exit();
}

$darkMode = isset($_POST['dark_mode']) ? (int) $_POST['dark_mode'] : 0;
$fontSize = isset($_POST['font_size']) ? (int) $_POST['font_size'] : 16;

$settings = update_patient_accessibility($database, (int) $patient['pid'], $darkMode, $fontSize);

echo json_encode([
    'success' => true,
    'settings' => $settings,
]);
