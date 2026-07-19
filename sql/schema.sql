CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) DEFAULT NULL,
  role ENUM('admin','siswa') NOT NULL DEFAULT 'siswa',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nis VARCHAR(20) NOT NULL UNIQUE,
  nisn VARCHAR(20) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  tempat_lahir VARCHAR(100) DEFAULT NULL,
  tgl_lahir DATE DEFAULT NULL,
  jenis_kelamin ENUM('Laki-laki','Perempuan') DEFAULT NULL,
  agama VARCHAR(20) DEFAULT NULL,
  status_keluarga VARCHAR(50) DEFAULT NULL,
  anak_ke INT DEFAULT NULL,
  alamat TEXT DEFAULT NULL,
  no_telp VARCHAR(20) DEFAULT NULL,
  sekolah_asal VARCHAR(100) DEFAULT NULL,
  diterima_kelas VARCHAR(10) DEFAULT NULL,
  diterima_tanggal DATE DEFAULT NULL,
  nama_ayah VARCHAR(100) DEFAULT NULL,
  nama_ibu VARCHAR(100) DEFAULT NULL,
  pekerjaan_ayah VARCHAR(100) DEFAULT NULL,
  pekerjaan_ibu VARCHAR(100) DEFAULT NULL,
  alamat_ortu TEXT DEFAULT NULL,
  no_telp_ortu VARCHAR(20) DEFAULT NULL,
  nama_wali VARCHAR(100) DEFAULT NULL,
  alamat_wali TEXT DEFAULT NULL,
  no_telp_wali VARCHAR(20) DEFAULT NULL,
  pekerjaan_wali VARCHAR(100) DEFAULT NULL,
  verified TINYINT(1) DEFAULT 0,
  verified_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_nis (nis),
  INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(50) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key, setting_value) VALUES
('kota', 'Cimahi'),
('kepala_sekolah', 'Drs. Deden Musa'),
('nip_kepsek', '196409211988031003'),
('nama_sekolah', 'SMA Negeri 6 Cimahi');

-- Admin default: username=admin, password=admin
INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$QJKBiNDxyX6htxeQysyMNOrBmJkkDXPAdP58G2ciuQPm8wspAiaQy', 'Administrator', 'admin');
