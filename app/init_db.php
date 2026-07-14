<?php
/**
 * Inisialisasi database
 * Panggil: php init_db.php
 */

require_once __DIR__ . '/init.php';

$conn = conn();

$schema = file_get_contents('/var/www/sql/schema.sql');
$statements = explode(';', $schema);

$success = 0;
$errors = 0;

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt)) continue;
    
    if ($conn->query($stmt)) {
        $success++;
    } else {
        echo "Error: " . $conn->error . "\n";
        $errors++;
    }
}

echo "Database initialized: $success statements OK, $errors errors\n";
