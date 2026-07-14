<?php
/**
 * Buat akun siswa (username=nis, password=nis) untuk semua siswa
 * Panggil: php create_accounts.php
 */

require_once __DIR__ . '/init.php';

$result = conn()->query("SELECT id, nis, nama FROM siswa WHERE nis NOT IN (SELECT username FROM users WHERE role = 'siswa')");

if ($result->num_rows === 0) {
    echo "Semua akun sudah dibuat.\n";
    exit;
}

$created = 0;
$stmt = conn()->prepare("INSERT IGNORE INTO users (username, password, nama, role) VALUES (?, ?, ?, 'siswa')");

while ($row = $result->fetch_assoc()) {
    $password = password_hash($row['nis'], PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $row['nis'], $password, $row['nama']);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $created++;
    }
}

echo "Akun siswa dibuat: $created\n";
