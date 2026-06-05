<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'edoc';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$database = new mysqli($servername, $username, $password, $dbname);
$database->set_charset('utf8mb4');

if ($database->connect_error) {
    error_log('Database connection failed: ' . $database->connect_error);
    http_response_code(500);
    exit('Database unavailable.');
}

$columnResult = $database->query("SHOW COLUMNS FROM patient LIKE 'status'");
if ($columnResult && $columnResult->num_rows === 0) {
    $database->query("ALTER TABLE patient ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
}

$patientDeactivateUntilResult = $database->query("SHOW COLUMNS FROM patient LIKE 'deactivate_until'");
if ($patientDeactivateUntilResult && $patientDeactivateUntilResult->num_rows === 0) {
    $database->query("ALTER TABLE patient ADD COLUMN deactivate_until DATE DEFAULT NULL");
}

$doctorStatusResult = $database->query("SHOW COLUMNS FROM doctor LIKE 'status'");
if ($doctorStatusResult && $doctorStatusResult->num_rows === 0) {
    $database->query("ALTER TABLE doctor ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
}

$doctorDeactivateUntilResult = $database->query("SHOW COLUMNS FROM doctor LIKE 'deactivate_until'");
if ($doctorDeactivateUntilResult && $doctorDeactivateUntilResult->num_rows === 0) {
    $database->query("ALTER TABLE doctor ADD COLUMN deactivate_until DATE DEFAULT NULL");
}

// Ensure appointment has a status column (used for pending/approved/declined)
$appointmentStatusResult = $database->query("SHOW COLUMNS FROM appointment LIKE 'status'");
if ($appointmentStatusResult && $appointmentStatusResult->num_rows === 0) {
    // Default to 'approved' for existing rows; new bookings will set 'pending'
    $database->query("ALTER TABLE appointment ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'approved'");
}
?>