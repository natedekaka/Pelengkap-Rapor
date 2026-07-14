<?php
session_start();
require_once __DIR__ . '/../init.php';
require_admin();

$user = current_user();
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid!";
    } else {
        $old = $_POST['password_lama'] ?? '';
        $new = $_POST['password_baru'] ?? '';
        $confirm = $_POST['password_konfirmasi'] ?? '';

        if (empty($old) || empty($new) || empty($confirm)) {
            $error = "Semua kolom harus diisi!";
        } elseif ($new !== $confirm) {
            $error = "Password baru dan konfirmasi tidak cocok!";
        } elseif (strlen($new) < 4) {
            $error = "Password baru minimal 4 karakter!";
        } else {
            $stmt = conn()->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if (!$row || !password_verify($old, $row['password'])) {
                $error = "Password lama salah!";
            } else {
                $hash = password_hash($new, PASSWORD_BCRYPT);
                $upd = conn()->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $hash, $user['id']);
                if ($upd->execute()) {
                    $_SESSION['success'] = "Password berhasil diubah!";
                    header("Location: ganti_password.php");
                    exit;
                } else {
                    $error = "Gagal menyimpan: " . $upd->error;
                }
            }
        }
    }
}

require_once __DIR__ . '/../init.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Ganti Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: system-ui, -apple-system, sans-serif; }
        .sidebar { background: #1a1a2e; min-height: 100vh; padding: 1rem 0; }
        .sidebar a { color: rgba(255,255,255,0.7); text-decoration: none; padding: 0.6rem 1.2rem; display: flex; align-items: center; gap: 0.7rem; font-size: 0.9rem; border-radius: 0; transition: all 0.2s; }
        .sidebar a:hover, .sidebar a.active { color: white; background: rgba(255,255,255,0.1); }
        .sidebar .brand { color: white; font-weight: 700; padding: 0.8rem 1.2rem; font-size: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 0.5rem; }
        .content { padding: 1.5rem; }
        .card { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; font-weight: 600; padding: 1rem 1.25rem; }
        .topbar { background: white; border-radius: 16px; padding: 0.8rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .form-control { border-radius: 10px; border: 2px solid #e9ecef; padding: 0.6rem 0.75rem; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.12); }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1040; }
        .sidebar-overlay.show { display: block; }
        @media (max-width: 767.98px) {
            .sidebar { position: fixed; top: 0; left: -260px; width: 240px !important; z-index: 1050; transition: left 0.3s ease; }
            .sidebar.open { left: 0; }
            .content { padding: 1rem; }
            .topbar { padding: 0.6rem 1rem; flex-wrap: wrap; gap: 0.5rem; }
            .topbar h5 { font-size: 0.95rem; }
        }
    </style>
</head>
<body>
    <nav class="d-md-none bg-white shadow-sm px-3 py-2 d-flex align-items-center justify-content-between" style="position: sticky; top: 0; z-index: 1030;">
        <button class="btn btn-sm btn-outline-secondary border-0" type="button" onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('show')">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <span class="fw-bold"><i class="fas fa-shield-alt me-1 text-primary"></i>Admin Panel</span>
        <a href="../logout.php" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-sign-out-alt"></i></a>
    </nav>
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="this.classList.remove('show');document.getElementById('sidebar').classList.remove('open')"></div>
    <div class="container-fluid">
        <div class="row">
            <div id="sidebar" class="col-auto p-0" style="width: 220px;">
                <div class="sidebar">
                    <div class="brand"><i class="fas fa-shield-alt me-2"></i>Admin Panel</div>
                    <a href="index.php?page=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="index.php?page=siswa"><i class="fas fa-users"></i> Data Siswa</a>
                    <a href="index.php?page=export"><i class="fas fa-download"></i> Export Data</a>
                    <a href="ganti_password.php" class="active"><i class="fas fa-key"></i> Ganti Password</a>
                    <hr style="border-color: rgba(255,255,255,0.1); margin: 0.5rem 1rem;">
                    <a href="../verifikasi.php"><i class="fas fa-user-check"></i> Data Per-Siswa</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                </div>
            </div>
            <div class="col p-0">
                <div class="content">
                    <div class="topbar">
                        <div><h5 class="m-0 fw-bold"><i class="fas fa-key me-2"></i>Ganti Password</h5></div>
                        <span class="text-muted small"><?= htmlspecialchars($user['username']) ?></span>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="row justify-content-center">
                        <div class="col-12 col-md-6 col-lg-5">
                            <div class="card">
                                <div class="card-body p-4">
                                    <form method="POST">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-secondary small">Password Lama</label>
                                            <input type="password" name="password_lama" class="form-control" required autofocus>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-secondary small">Password Baru</label>
                                            <input type="password" name="password_baru" class="form-control" required minlength="4">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold text-secondary small">Konfirmasi Password Baru</label>
                                            <input type="password" name="password_konfirmasi" class="form-control" required minlength="4">
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save me-2"></i> Simpan Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
