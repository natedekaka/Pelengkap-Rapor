<?php
session_start();
require_once 'init.php';
require_login();

$user = current_user();

if ($user['role'] === 'admin') {
    header('Location: admin/index.php');
    exit;
}

header('Location: verifikasi.php');
exit;
