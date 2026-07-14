<?php
session_start();
require_once 'init.php';
require_login();

if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
    $_SESSION['error'] = "Token keamanan tidak valid!";
    header('Location: verifikasi.php');
    exit;
}

$user = current_user();
$siswa_id = (int)($_POST['siswa_id'] ?? 0);

$cek = conn()->query("SELECT * FROM siswa WHERE id = $siswa_id");
if (!$cek || $cek->num_rows === 0) {
    $_SESSION['error'] = "Data siswa tidak ditemukan!";
    header('Location: verifikasi.php');
    exit;
}

$siswa = $cek->fetch_assoc();

if ($user['role'] === 'siswa' && $user['username'] !== $siswa['nis']) {
    $_SESSION['error'] = "Akses ditolak!";
    header('Location: verifikasi.php');
    exit;
}

$fields = [
    'tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'agama',
    'status_keluarga', 'anak_ke', 'alamat', 'no_telp', 'sekolah_asal',
    'diterima_kelas', 'diterima_tanggal',
    'nama_ayah', 'nama_ibu', 'pekerjaan_ayah', 'pekerjaan_ibu',
    'alamat_ortu', 'no_telp_ortu',
    'nama_wali', 'alamat_wali', 'no_telp_wali', 'pekerjaan_wali'
];

$sets = [];
$params = [];
$types = '';

foreach ($fields as $f) {
    $val = $_POST[$f] ?? '';
    if ($f === 'anak_ke') {
        $val = $val !== '' ? (int)$val : null;
        if ($val === null) {
            $sets[] = "anak_ke = NULL";
        } else {
            $sets[] = "anak_ke = ?";
            $params[] = $val;
            $types .= 'i';
        }
        continue;
    }
    if ($f === 'tgl_lahir' || $f === 'diterima_tanggal') {
        if (empty($val) || $val === '0000-00-00') {
            $sets[] = "$f = NULL";
        } else {
            $sets[] = "$f = ?";
            $params[] = $val;
            $types .= 's';
        }
        continue;
    }
    $sets[] = "$f = ?";
    $params[] = $val;
    $types .= 's';
}

$action = $_POST['action'] ?? 'save';
if ($action === 'verify') {
    $sets[] = "verified = 1";
    $sets[] = "verified_at = NOW()";
} elseif ($siswa['verified']) {
    $sets[] = "verified = 0";
    $sets[] = "verified_at = NULL";
}

$params[] = $siswa_id;
$types .= 'i';

$sql = "UPDATE siswa SET " . implode(', ', $sets) . " WHERE id = ?";
$stmt = conn()->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if ($action === 'verify') {
        $_SESSION['success'] = "Data berhasil diverifikasi! Terima kasih.";
    } else {
        $_SESSION['success'] = "Data berhasil disimpan.";
    }
} else {
    $_SESSION['error'] = "Gagal menyimpan: " . $stmt->error;
}

$redirect = $user['role'] === 'siswa' ? 'verifikasi.php' : 'admin/index.php';
header("Location: $redirect");
exit;
