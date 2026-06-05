<?php

function ensure_patient_accessibility_columns(mysqli $database): void
{
    $columns = [];
    $result = $database->query("SHOW COLUMNS FROM patient");

    while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = true;
    }

    if (!isset($columns['dark_mode'])) {
        $database->query("ALTER TABLE patient ADD dark_mode TINYINT(1) NOT NULL DEFAULT 0");
    }

    if (!isset($columns['font_size'])) {
        $database->query("ALTER TABLE patient ADD font_size TINYINT UNSIGNED NOT NULL DEFAULT 16");
    }
}

function normalize_font_size($fontSize): int
{
    $fontSize = (int) $fontSize;

    if ($fontSize < 12) {
        return 12;
    }

    if ($fontSize > 24) {
        return 24;
    }

    return $fontSize;
}

function get_patient_accessibility(mysqli $database, int $patientId): array
{
    ensure_patient_accessibility_columns($database);

    $stmt = $database->prepare("SELECT dark_mode, font_size FROM patient WHERE pid = ?");
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc() ?: [];

    return [
        'dark_mode' => !empty($settings['dark_mode']) ? 1 : 0,
        'font_size' => normalize_font_size($settings['font_size'] ?? 16),
    ];
}

function update_patient_accessibility(mysqli $database, int $patientId, int $darkMode, int $fontSize): array
{
    ensure_patient_accessibility_columns($database);

    $darkMode = $darkMode === 1 ? 1 : 0;
    $fontSize = normalize_font_size($fontSize);

    $stmt = $database->prepare("UPDATE patient SET dark_mode = ?, font_size = ? WHERE pid = ?");
    $stmt->bind_param("iii", $darkMode, $fontSize, $patientId);
    $stmt->execute();

    return [
        'dark_mode' => $darkMode,
        'font_size' => $fontSize,
    ];
}

function accessibility_body_attributes(array $settings): string
{
    $class = !empty($settings['dark_mode']) ? ' class="dark-mode"' : '';
    $fontSize = normalize_font_size($settings['font_size'] ?? 16);

    return $class . ' style="--medcheck-font-size:' . $fontSize . 'px"';
}

function render_accessibility_script(array $settings, string $endpoint = 'save-accessibility.php'): void
{
    $darkMode = !empty($settings['dark_mode']) ? 'true' : 'false';
    $fontSize = normalize_font_size($settings['font_size'] ?? 16);
    $endpointJson = json_encode($endpoint);

    echo "<script>window.medcheckAccessibility={darkMode:$darkMode,fontSize:$fontSize,endpoint:$endpointJson};</script>";
    echo '<script src="../js/accessibility.js"></script>';
}
