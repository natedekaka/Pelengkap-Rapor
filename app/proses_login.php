<?php
session_start();
require_once 'init.php';

if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
    $_SESSION['error'] = "Token keamanan tidak valid!";
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Username dan password harus diisi!";
    header('Location: login.php');
    exit;
}

$stmt = conn()->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    }
}

$_SESSION['error'] = "Username atau password salah!";
header('Location: login.php');
exit;
