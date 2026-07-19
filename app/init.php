<?php
require_once __DIR__ . '/config.php';

function conn() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Koneksi database gagal: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        $_SESSION['error'] = 'Akses ditolak';
        header('Location: index.php');
        exit;
    }
}

function asset($path) {
    return BASE_URL . ltrim($path, '/');
}

function format_tgl($date) {
    if (!$date) return '-';
    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $d = explode('-', $date);
    return (int)$d[2] . ' ' . $months[(int)$d[1]] . ' ' . $d[0];
}

function get_setting($key, $default = '-') {
    $q = conn()->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $q->bind_param("s", $key);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    return $r['setting_value'] ?? $default;
}

function set_setting($key, $value) {
    $q = conn()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $q->bind_param("sss", $key, $value, $value);
    return $q->execute();
}
