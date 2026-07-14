<?php
session_start();
require_once __DIR__ . '/../init.php';
require_admin();

$total = conn()->query("SELECT COUNT(*) as c FROM siswa")->fetch_assoc()['c'];
$verified = conn()->query("SELECT COUNT(*) as c FROM siswa WHERE verified = 1")->fetch_assoc()['c'];
$unverified = $total - $verified;
$complete = conn()->query("SELECT COUNT(*) as c FROM siswa WHERE verified = 1 AND alamat IS NOT NULL AND alamat != '' AND nama_ayah IS NOT NULL AND nama_ayah != ''")->fetch_assoc()['c'];

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$page = $_GET['page'] ?? 'dashboard';

function render_header($title) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - <?= $title ?></title>
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
            .stat-card { border-radius: 16px; padding: 1.5rem; color: white; }
            .stat-card h3 { font-size: 2rem; font-weight: 700; margin: 0; }
            .stat-card p { margin: 0; opacity: 0.85; font-size: 0.85rem; }
            .table { font-size: 0.9rem; }
            .badge-success { background: #d1e7dd; color: #0f5132; }
            .badge-warning { background: #fff3cd; color: #664d03; }
            .btn-action { border-radius: 8px; padding: 0.25rem 0.75rem; font-size: 0.8rem; }
            .topbar { background: white; border-radius: 16px; padding: 0.8rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1040; }
            .sidebar-overlay.show { display: block; }
            @media (max-width: 767.98px) {
                .sidebar { position: fixed; top: 0; left: -260px; width: 240px !important; z-index: 1050; transition: left 0.3s ease; }
                .sidebar.open { left: 0; }
                .content { padding: 1rem; }
                .stat-card { padding: 1rem; }
                .stat-card h3 { font-size: 1.5rem; }
                .topbar { padding: 0.6rem 1rem; flex-wrap: wrap; gap: 0.5rem; }
                .topbar h5 { font-size: 0.95rem; }
            }
        </style>
    </head>
    <body>
    <!-- Mobile top bar -->
    <nav class="d-md-none bg-white shadow-sm px-3 py-2 d-flex align-items-center justify-content-between" style="position: sticky; top: 0; z-index: 1030;">
        <button class="btn btn-sm btn-outline-secondary border-0" type="button" onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('show')">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <span class="fw-bold"><i class="fas fa-shield-alt me-1 text-primary"></i>Admin Panel</span>
        <a href="../logout.php" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-sign-out-alt"></i></a>
    </nav>

    <!-- Sidebar overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="this.classList.remove('show');document.getElementById('sidebar').classList.remove('open')"></div>

    <div class="container-fluid">
        <div class="row">
            <div id="sidebar" class="col-auto p-0" style="width: 220px;">
                <div class="sidebar">
                    <div class="brand"><i class="fas fa-shield-alt me-2"></i>Admin Panel</div>
                    <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="?page=siswa" class="<?= $page === 'siswa' ? 'active' : '' ?>"><i class="fas fa-users"></i> Data Siswa</a>
                    <a href="?page=export" class="<?= $page === 'export' ? 'active' : '' ?>"><i class="fas fa-download"></i> Export Data</a>
                    <a href="ganti_password.php"><i class="fas fa-key"></i> Ganti Password</a>
                    <hr style="border-color: rgba(255,255,255,0.1); margin: 0.5rem 1rem;">
                    <a href="../verifikasi.php"><i class="fas fa-user-check"></i> Data Per-Siswa</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                </div>
            </div>
            <div class="col p-0">
                <div class="content">
    <?php
}

function render_footer() {
    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}

switch ($page) {
    case 'siswa':
        render_header('Data Siswa');

        $search = $_GET['search'] ?? '';
        $filter = $_GET['filter'] ?? 'all';
        $page_num = max(1, (int)($_GET['p'] ?? 1));
        $limit = 50;
        $offset = ($page_num - 1) * $limit;

        $where = "WHERE 1=1";
        $params = [];
        $types = '';

        if ($search) {
            $where .= " AND (nama LIKE ? OR nis LIKE ? OR nisn LIKE ?)";
            $s = "%$search%";
            $params = [$s, $s, $s];
            $types = 'sss';
        }

        if ($filter === 'verified') $where .= " AND verified = 1";
        elseif ($filter === 'unverified') $where .= " AND verified = 0";

        $count_q = conn()->prepare("SELECT COUNT(*) as c FROM siswa $where");
        if ($params) $count_q->bind_param($types, ...$params);
        $count_q->execute();
        $total_rows = $count_q->get_result()->fetch_assoc()['c'];
        $total_pages = ceil($total_rows / $limit);

        $sql = "SELECT * FROM siswa $where ORDER BY nama LIMIT $limit OFFSET $offset";
        $q = conn()->prepare($sql);
        if ($params) $q->bind_param($types, ...$params);
        $q->execute();
        $siswa_list = $q->get_result();

        if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="topbar">
            <div><h5 class="m-0 fw-bold"><i class="fas fa-users me-2"></i>Data Siswa</h5></div>
            <div>
                <span class="text-muted small">Total: <?= $total_rows ?> siswa</span>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="siswa">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama / NIS / NISN..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="filter" class="form-select">
                            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Semua</option>
                            <option value="verified" <?= $filter === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
                            <option value="unverified" <?= $filter === 'unverified' ? 'selected' : '' ?>>Belum Verifikasi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100"><i class="fas fa-search"></i> Cari</button>
                    </div>
                    <div class="col-md-2">
                        <a href="?page=siswa" class="btn btn-outline-secondary w-100"><i class="fas fa-redo"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS/NISN</th>
                            <th>Nama</th>
                            <th>Kelamin</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset + 1; while ($s = $siswa_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><small><?= htmlspecialchars($s['nis']) ?><br><?= htmlspecialchars($s['nisn']) ?></small></td>
                            <td class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></td>
                            <td><?= $s['jenis_kelamin'] ?? '-' ?></td>
                            <td><small><?= htmlspecialchars($s['no_telp'] ?? '-') ?></small></td>
                            <td>
                                <?php if ($s['verified']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="../verifikasi.php?nis=<?= urlencode($s['nis']) ?>" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($siswa_list->num_rows === 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="card-body border-top">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page_num ? 'active' : '' ?>">
                                <a class="page-link" href="?page=siswa&p=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
        <?php
        render_footer();
        break;

    case 'export':
        render_header('Export Data');

        $format = $_GET['format'] ?? '';
        $filter_export = $_GET['filter_export'] ?? 'all';

        if ($format === 'csv') {
            $where = $filter_export === 'verified' ? "WHERE verified = 1" : ($filter_export === 'unverified' ? "WHERE verified = 0" : "");
            $q = conn()->query("SELECT * FROM siswa $where ORDER BY nama");

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=data_siswa_verified.csv');
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($f, ['NIS', 'NISN', 'Nama', 'Tempat Lahir', 'Tgl Lahir', 'JK', 'Agama', 'Status Keluarga', 'Anak ke',
                'Alamat', 'No Telp', 'Sekolah Asal', 'Diterima Kelas', 'Diterima Tanggal',
                'Nama Ayah', 'Nama Ibu', 'Pekerjaan Ayah', 'Pekerjaan Ibu', 'Alamat Ortu', 'No Telp Ortu',
                'Nama Wali', 'Alamat Wali', 'No Telp Wali', 'Pekerjaan Wali', 'Verified']);

            while ($r = $q->fetch_assoc()) {
                fputcsv($f, [
                    $r['nis'], $r['nisn'], $r['nama'], $r['tempat_lahir'], $r['tgl_lahir'],
                    $r['jenis_kelamin'], $r['agama'], $r['status_keluarga'], $r['anak_ke'],
                    $r['alamat'], $r['no_telp'], $r['sekolah_asal'], $r['diterima_kelas'], $r['diterima_tanggal'],
                    $r['nama_ayah'], $r['nama_ibu'], $r['pekerjaan_ayah'], $r['pekerjaan_ibu'],
                    $r['alamat_ortu'], $r['no_telp_ortu'],
                    $r['nama_wali'], $r['alamat_wali'], $r['no_telp_wali'], $r['pekerjaan_wali'],
                    $r['verified'] ? 'Ya' : 'Tidak'
                ]);
            }
            fclose($f);
            exit;
        }

        if ($format === 'sql_update') {
            $where = $filter_export === 'verified' ? "WHERE verified = 1" : ($filter_export === 'unverified' ? "WHERE verified = 0" : "");
            $q = conn()->query("SELECT * FROM siswa $where ORDER BY nis");

            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename=update_data_siswa.sql');

            while ($r = $q->fetch_assoc()) {
                $cols = ['tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'agama', 'status_keluarga', 'anak_ke',
                    'alamat', 'no_telp', 'sekolah_asal', 'diterima_kelas', 'diterima_tanggal',
                    'nama_ayah', 'nama_ibu', 'pekerjaan_ayah', 'pekerjaan_ibu', 'alamat_ortu', 'no_telp_ortu',
                    'nama_wali', 'alamat_wali', 'no_telp_wali', 'pekerjaan_wali'];
                $sets = [];
                foreach ($cols as $c) {
                    $v = $r[$c] ?? '';
                    if ($v === null || $v === '' || $v === '0000-00-00') continue;
                    $ev = conn()->real_escape_string($v);
                    $sets[] = "$c = '$ev'";
                }
                if (!empty($sets)) {
                    echo "UPDATE siswa SET " . implode(', ', $sets) . " WHERE nis = '{$r['nis']}';\n";
                }
            }
            exit;
        }
        ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <div class="topbar">
            <div><h5 class="m-0 fw-bold"><i class="fas fa-download me-2"></i>Export Data Siswa</h5></div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-csv text-success mb-3" style="font-size: 3rem;"></i>
                        <h6>Export CSV</h6>
                        <p class="text-muted small">Download data siswa dalam format CSV, bisa dibuka di Excel</p>
                        <form method="GET" class="d-flex gap-2 justify-content-center">
                            <input type="hidden" name="page" value="export">
                            <input type="hidden" name="format" value="csv">
                            <select name="filter_export" class="form-select form-select-sm w-auto">
                                <option value="all">Semua</option>
                                <option value="verified">Terverifikasi</option>
                                <option value="unverified">Belum Verifikasi</option>
                            </select>
                            <button class="btn btn-success btn-sm"><i class="fas fa-download"></i> Download</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-database text-primary mb-3" style="font-size: 3rem;"></i>
                        <h6>Export SQL Update</h6>
                        <p class="text-muted small">Generate query UPDATE untuk update database absensi-siswa</p>
                        <form method="GET" class="d-flex gap-2 justify-content-center">
                            <input type="hidden" name="page" value="export">
                            <input type="hidden" name="format" value="sql_update">
                            <select name="filter_export" class="form-select form-select-sm w-auto">
                                <option value="all">Semua</option>
                                <option value="verified">Terverifikasi</option>
                                <option value="unverified">Belum Verifikasi</option>
                            </select>
                            <button class="btn btn-primary btn-sm"><i class="fas fa-download"></i> Download</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card p-4 text-center">
                    <i class="fas fa-print text-secondary mb-3" style="font-size: 2.5rem;"></i>
                    <h6>Cetak Laporan</h6>
                    <p class="text-muted small">Cetak data siswa per halaman verifikasi langsung dari browser</p>
                    <a href="?page=siswa" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> Ke Data Siswa</a>
                </div>
            </div>
        </div>
        <?php
        render_footer();
        break;

    default:
        render_header('Dashboard');

        $unverified_list = conn()->query("SELECT * FROM siswa WHERE verified = 0 ORDER BY nama LIMIT 10");
        $recent_verified = conn()->query("SELECT * FROM siswa WHERE verified = 1 AND verified_at IS NOT NULL ORDER BY verified_at DESC LIMIT 5");
        ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="topbar">
            <div><h5 class="m-0 fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h5></div>
            <div class="text-muted small"><?= date('d F Y') ?></div>
        </div>

        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #0d6efd, #6610f2);">
                    <p>Total Siswa</p>
                    <h3><?= $total ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #198754, #157347);">
                    <p>Terverifikasi</p>
                    <h3><?= $verified ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <p>Belum Verifikasi</p>
                    <h3><?= $unverified ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #dc3545, #b02a37);">
                    <p>Data Lengkap</p>
                    <h3><?= $complete ?></h3>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header"><i class="fas fa-clock me-2 text-warning"></i>Belum Verifikasi (10 terakhir)</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>NIS</th><th>Nama</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php while ($s = $unverified_list->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['nis']) ?></td>
                                    <td><?= htmlspecialchars($s['nama']) ?></td>
                                    <td><a href="../verifikasi.php?nis=<?= urlencode($s['nis']) ?>" class="btn btn-sm btn-outline-primary btn-action">Edit</a></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header"><i class="fas fa-check-circle me-2 text-success"></i>Terbaru Diverifikasi</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>NIS</th><th>Nama</th><th>Tanggal</th></tr></thead>
                            <tbody>
                                <?php while ($s = $recent_verified->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['nis']) ?></td>
                                    <td><?= htmlspecialchars($s['nama']) ?></td>
                                    <td><small><?= $s['verified_at'] ? date('d/m/Y H:i', strtotime($s['verified_at'])) : '-' ?></small></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
        render_footer();
        break;
}
