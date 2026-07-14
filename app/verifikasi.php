<?php
session_start();
require_once 'init.php';
require_login();

$user = current_user();
$siswa = null;
$pesan = $_SESSION['pesan'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['pesan'], $_SESSION['error']);
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

if ($user['role'] === 'siswa') {
    $stmt = conn()->prepare("SELECT * FROM siswa WHERE nis = ?");
    $stmt->bind_param("s", $user['username']);
    $stmt->execute();
    $siswa = $stmt->get_result()->fetch_assoc();

    if (!$siswa) {
        $error = "Data siswa tidak ditemukan. Hubungi admin.";
    }
} elseif (is_admin() && isset($_GET['nis'])) {
    $stmt = conn()->prepare("SELECT * FROM siswa WHERE nis = ?");
    $stmt->bind_param("s", $_GET['nis']);
    $stmt->execute();
    $siswa = $stmt->get_result()->fetch_assoc();
}

if (!$siswa && $user['role'] === 'siswa') {
    $error = "Data siswa tidak ditemukan untuk NIS: " . htmlspecialchars($user['username']);
    $siswa_ditemukan = false;
} else {
    $siswa_ditemukan = true;
}

$progress = 0;
$total_fields = 18;
$filled = 0;
$fields_check = [];

if ($siswa) {
    $check_map = [
        'tempat_lahir' => 'Tempat Lahir',
        'tgl_lahir' => 'Tanggal Lahir',
        'jenis_kelamin' => 'Jenis Kelamin',
        'agama' => 'Agama',
        'status_keluarga' => 'Status Keluarga',
        'anak_ke' => 'Anak ke',
        'alamat' => 'Alamat',
        'no_telp' => 'No. Telepon',
        'sekolah_asal' => 'Sekolah Asal',
        'nama_ayah' => 'Nama Ayah',
        'nama_ibu' => 'Nama Ibu',
        'pekerjaan_ayah' => 'Pekerjaan Ayah',
        'pekerjaan_ibu' => 'Pekerjaan Ibu',
        'alamat_ortu' => 'Alamat Orang Tua',
        'nama_wali' => 'Nama Wali',
        'alamat_wali' => 'Alamat Wali',
        'no_telp_wali' => 'No. Telp Wali',
        'pekerjaan_wali' => 'Pekerjaan Wali',
    ];

    foreach ($check_map as $key => $label) {
        $isi = !empty($siswa[$key]) && $siswa[$key] !== '00:00:00';
        if ($key === 'anak_ke') $isi = ($siswa[$key] !== null && $siswa[$key] !== 0);
        if ($key === 'tgl_lahir') $isi = !empty($siswa[$key]) && $siswa[$key] !== '0000-00-00';
        if ($isi) $filled++;
        $fields_check[$key] = ['label' => $label, 'filled' => $isi, 'value' => $siswa[$key] ?? ''];
    }
    $total_fields = count($check_map);
    $progress = $total_fields > 0 ? round(($filled / $total_fields) * 100) : 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: system-ui, -apple-system, sans-serif; }
        .header-bar { background: linear-gradient(135deg, #0d6efd, #6610f2); color: white; padding: 0.7rem 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-bar h5 { margin: 0; font-weight: 600; font-size: 1rem; }
        .header-bar .title-text { font-size: 0.9rem; }
        @media (min-width: 768px) { .header-bar { padding: 1rem 0; } .header-bar h5 { font-size: 1.25rem; } }
        .card { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1rem; }
        .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; font-weight: 600; padding: 0.8rem 1rem; }
        .card-body { padding: 1rem; }
        @media (min-width: 768px) { .card-header { padding: 1rem 1.25rem; } .card-body { padding: 1.25rem; } }
        .form-label { font-size: 0.75rem; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.3rem; }
        .form-control, .form-select { border-radius: 10px; border: 2px solid #e9ecef; padding: 0.55rem 0.7rem; font-size: 0.9rem; }
        .btn-save { border-radius: 12px; padding: 0.7rem 2rem; font-weight: 600; }
        @media (min-width: 768px) { .btn-save:not(.w-100) { width: auto !important; } }
        .progress-bar-custom { height: 10px; border-radius: 10px; background: #e9ecef; overflow: hidden; }
        .progress-bar-custom .bar { height: 100%; border-radius: 10px; background: linear-gradient(90deg, #0d6efd, #6610f2); transition: width 0.5s ease; }
        .stat-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 1.1rem; font-weight: 700; }
        .field-status { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .field-status.filled { background: #198754; }
        .field-status.empty { background: #dc3545; }
        .navbar-brand { font-weight: 700; }
        .user-info { background: rgba(255,255,255,0.15); border-radius: 10px; padding: 0.25rem 0.6rem; font-size: 0.75rem; white-space: nowrap; max-width: 140px; overflow: hidden; text-overflow: ellipsis; }
        @media (min-width: 768px) { .user-info { padding: 0.4rem 0.8rem; font-size: 0.85rem; max-width: none; } }
    </style>
</head>
<body>
    <nav class="header-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <?php if (is_admin()): ?>
                    <a href="admin/index.php" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left"></i></a>
                <?php endif; ?>
                <h5 class="title-text mb-0"><i class="fas fa-user-check me-2"></i>Verifikasi Data Siswa</h5>
            </div>
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <span class="user-info">
                    <i class="fas fa-user me-1"></i> <?= htmlspecialchars($siswa['nama'] ?? $user['nama'] ?? '') ?>
                </span>
                <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if ($user['role'] === 'siswa'): ?>
        <div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-3 small" role="alert" style="border-radius: 12px;">
            <i class="fas fa-info-circle"></i>
            <span>Isi data yang masih kosong, perbaiki jika ada yang salah, lalu klik <strong>Simpan & Verifikasi</strong> jika sudah yakin benar.</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center gap-2"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($siswa): ?>
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card text-center p-4">
                    <div class="mb-3">
                        <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-graduate text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold"><?= htmlspecialchars($siswa['nama']) ?></h5>
                    <p class="text-muted small mb-2">NIS: <?= htmlspecialchars($siswa['nis']) ?> | NISN: <?= htmlspecialchars($siswa['nisn']) ?></p>
                    <hr>
                    <div class="text-start">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="stat-label">Kelengkapan Data</span>
                            <span class="stat-value small"><?= $filled ?>/<?= $total_fields ?></span>
                        </div>
                        <div class="progress-bar-custom mb-3">
                            <div class="bar" style="width: <?= $progress ?>%"></div>
                        </div>
                        <?php if ($siswa['verified']): ?>
                            <span class="badge bg-success bg-opacity-10 text-success w-100 py-2">
                                <i class="fas fa-check-circle me-1"></i> Terverifikasi
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning bg-opacity-10 text-warning w-100 py-2">
                                <i class="fas fa-clock me-1"></i> Belum Verifikasi
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Status Data</div>
                    <div class="card-body p-2">
                        <?php foreach ($fields_check as $key => $f): ?>
                            <div class="d-flex justify-content-between align-items-center px-2 py-1 small">
                                <span class="<?= $f['filled'] ? 'text-success' : 'text-danger' ?>">
                                    <span class="field-status <?= $f['filled'] ? 'filled' : 'empty' ?> me-2"></span>
                                    <?= $f['label'] ?>
                                </span>
                                <span class="text-muted"><?= $f['filled'] ? '✓' : '✗' ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <form method="POST" action="verifikasi_save.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="siswa_id" value="<?= $siswa['id'] ?>">

                    <div class="card">
                        <div class="card-header"><i class="fas fa-user me-2 text-primary"></i>Identitas Peserta Didik</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['nama']) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">NIS</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['nis']) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">NISN</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['nisn']) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($siswa['tempat_lahir'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" name="tgl_lahir" class="form-control" value="<?= $siswa['tgl_lahir'] ?? '' ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-select">
                                        <option value="">-- Pilih --</option>
                                        <option value="Laki-laki" <?= ($siswa['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="Perempuan" <?= ($siswa['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Agama</label>
                                    <select name="agama" class="form-select">
                                        <option value="">-- Pilih --</option>
                                        <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'] as $a): ?>
                                            <option value="<?= $a ?>" <?= ($siswa['agama'] ?? '') === $a ? 'selected' : '' ?>><?= $a ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status Keluarga</label>
                                    <select name="status_keluarga" class="form-select">
                                        <option value="">-- Pilih --</option>
                                        <option value="Anak Kandung" <?= ($siswa['status_keluarga'] ?? '') === 'Anak Kandung' ? 'selected' : '' ?>>Anak Kandung</option>
                                        <option value="Anak Tiri" <?= ($siswa['status_keluarga'] ?? '') === 'Anak Tiri' ? 'selected' : '' ?>>Anak Tiri</option>
                                        <option value="Anak Angkat" <?= ($siswa['status_keluarga'] ?? '') === 'Anak Angkat' ? 'selected' : '' ?>>Anak Angkat</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Anak ke</label>
                                    <input type="number" name="anak_ke" class="form-control" value="<?= htmlspecialchars($siswa['anak_ke'] ?? '') ?>" min="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Alamat & Kontak</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($siswa['alamat'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($siswa['no_telp'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sekolah Asal</label>
                                    <input type="text" name="sekolah_asal" class="form-control" value="<?= htmlspecialchars($siswa['sekolah_asal'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Diterima Kelas</label>
                                    <input type="text" name="diterima_kelas" class="form-control" value="<?= htmlspecialchars($siswa['diterima_kelas'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Diterima Tanggal</label>
                                    <input type="date" name="diterima_tanggal" class="form-control" value="<?= $siswa['diterima_tanggal'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><i class="fas fa-users me-2 text-primary"></i>Orang Tua</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Ayah</label>
                                    <input type="text" name="nama_ayah" class="form-control" value="<?= htmlspecialchars($siswa['nama_ayah'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Ibu</label>
                                    <input type="text" name="nama_ibu" class="form-control" value="<?= htmlspecialchars($siswa['nama_ibu'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pekerjaan Ayah</label>
                                    <input type="text" name="pekerjaan_ayah" class="form-control" value="<?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pekerjaan Ibu</label>
                                    <input type="text" name="pekerjaan_ibu" class="form-control" value="<?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Orang Tua</label>
                                    <textarea name="alamat_ortu" class="form-control" rows="2"><?= htmlspecialchars($siswa['alamat_ortu'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon Orang Tua</label>
                                    <input type="text" name="no_telp_ortu" class="form-control" value="<?= htmlspecialchars($siswa['no_telp_ortu'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><i class="fas fa-user-tie me-2 text-primary"></i>Wali Siswa</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Wali</label>
                                    <input type="text" name="nama_wali" class="form-control" value="<?= htmlspecialchars($siswa['nama_wali'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon Wali</label>
                                    <input type="text" name="no_telp_wali" class="form-control" value="<?= htmlspecialchars($siswa['no_telp_wali'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Wali</label>
                                    <textarea name="alamat_wali" class="form-control" rows="2"><?= htmlspecialchars($siswa['alamat_wali'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pekerjaan Wali</label>
                                    <input type="text" name="pekerjaan_wali" class="form-control" value="<?= htmlspecialchars($siswa['pekerjaan_wali'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 gap-md-3 mb-4">
                        <?php if ($user['role'] === 'siswa'): ?>
                            <button type="submit" name="action" value="save" class="btn btn-primary btn-save w-100">
                                <i class="fas fa-save me-2"></i> Simpan Data
                            </button>
                            <button type="submit" name="action" value="verify" class="btn btn-success btn-save w-100">
                                <i class="fas fa-check-circle me-2"></i> Simpan & Verifikasi
                            </button>
                        <?php else: ?>
                            <button type="submit" name="action" value="save" class="btn btn-primary btn-save w-100">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan (Admin)
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
