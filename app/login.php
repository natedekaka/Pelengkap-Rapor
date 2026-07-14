<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
require_once 'init.php';

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Verifikasi Data Siswa Pelengkap Biodata Rapor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, -apple-system, sans-serif; }
        .login-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px; padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); }
        .login-card h1 { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; }
        .login-card p { color: #6c757d; font-size: 0.9rem; }
        .form-control { border-radius: 12px; padding: 0.75rem 1rem; border: 2px solid #e9ecef; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.15); }
        .btn-login { border-radius: 12px; padding: 0.75rem; font-weight: 600; }
        .logo-icon { width: 70px; height: 70px; background: linear-gradient(135deg, #0d6efd, #6610f2); border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .logo-icon i { font-size: 1.8rem; color: white; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="logo-icon"><i class="fas fa-user-check"></i></div>
            <h1>Verifikasi Data Siswa Pelengkap Biodata Rapor</h1>
            <p>Masuk untuk memverifikasi data pelengkap biodata rapor Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="proses_login.php">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary small">Username (NIS)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-primary"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-2" placeholder="Masukkan NIS" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary small">Password (NIS)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-primary"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-2" placeholder="Masukkan NIS" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login w-full w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Masuk
            </button>
            <div class="text-center">
                <small class="text-muted">Login: NIS (username & password)</small>
            </div>
        </form>
    </div>

    <div style="max-width: 420px; width: 100%; margin-top: 1rem;">
        <div class="text-center" style="color: rgba(255,255,255,0.8); font-size: 0.85rem;">
            <a href="#" onclick="document.getElementById('infoSiswa').classList.toggle('d-none'); return false;" style="color: rgba(255,255,255,0.8); text-decoration: underline; text-underline-offset: 3px;">
                <i class="fas fa-question-circle me-1"></i> Panduan untuk Siswa
            </a>
        </div>
        <div id="infoSiswa" class="d-none" style="background: rgba(255,255,255,0.95); border-radius: 16px; padding: 1.25rem; margin-top: 0.75rem; font-size: 0.9rem; color: #1a1a2e;">
            <h6 class="fw-bold mb-2"><i class="fas fa-list-check me-2 text-primary"></i>Langkah-langkah:</h6>
            <ol class="ps-3 mb-2" style="line-height: 1.8;">
                <li><strong>Login</strong> — Masukkan NIS kamu sebagai Username dan Password, lalu klik <strong>Masuk</strong></li>
                <li><strong>Cek Data</strong> — Periksa seluruh data yang sudah terisi. Lengkapi yang masih kosong.</li>
                <li><strong>Simpan Sementara</strong> — Klik tombol <strong>Simpan Data</strong> jika masih ingin mengubah nanti.</li>
                <li><strong>Verifikasi</strong> — Jika semua data sudah benar, klik <strong>Simpan & Verifikasi</strong> sebagai tanda data sudah final.</li>
            </ol>
            <p class="mb-0 small text-muted border-top pt-2 mt-1">
                <i class="fas fa-info-circle me-1 text-primary"></i> Setelah verifikasi, data masih bisa diubah dengan <strong>Simpan</strong>, 
                tapi status verifikasi akan direset. Verifikasi ulang setelah selesai memperbaiki.
            </p>
        </div>
    </div>
</body>
</html>
