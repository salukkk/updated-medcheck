<?php
// CLI script to migrate plaintext admin passwords to secure hashes.
// Usage: php migrate_admin_passwords.php

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

echo "*** Admin Password Migration Script ***\n";
echo "This will hash any admin.apassword values that appear to be plaintext.\n";
echo "Make sure you have a database backup before proceeding.\n\n";

// Confirmation
fwrite(STDOUT, "Type 'YES' to proceed: ");
$confirm = trim(fgets(STDIN));
if ($confirm !== 'YES') {
    echo "Aborted by user. No changes made.\n";
    exit(0);
}

// Load DB connection
require_once __DIR__ . '/connection.php';

// Start a transaction
$database->begin_transaction();

try {
    $query = "SELECT aemail, apassword FROM admin";
    $result = $database->query($query);

    if (!$result) {
        throw new Exception('Failed to query admin table: ' . $database->error);
    }

    $updated = 0;
    $skipped = 0;

    $stmt = $database->prepare("UPDATE admin SET apassword = ? WHERE aemail = ?");
    if (!$stmt) throw new Exception('Prepare failed: ' . $database->error);

    while ($row = $result->fetch_assoc()) {
        $email = $row['aemail'];
        $pw = $row['apassword'];

        // Detect common password_hash formats (bcrypt/argon2)
        if (preg_match('/^\$2[ayb]\$|^\$argon2/i', $pw)) {
            $skipped++;
            continue; // already hashed
        }

        // Treat as plaintext — hash and update
        $hashed = password_hash($pw, PASSWORD_DEFAULT);
        if ($hashed === false) throw new Exception('Failed to hash password for ' . $email);

        $stmt->bind_param('ss', $hashed, $email);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update password for ' . $email . ': ' . $stmt->error);
        }

        $updated++;
    }

    $database->commit();

    echo "Migration complete. Updated: $updated, Skipped (already hashed): $skipped\n";
    echo "Please verify logins and advise if rollback needed.\n";

} catch (Exception $e) {
    $database->rollback();
    echo "Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}

?>