<?php
/**
 * CLI: Import data siswa dari file teks hasil ekstraksi PDF
 * Usage: php import.php
 * 
 * Panggil ulang jika ada perubahan: docker compose exec -T web php import.php
 * (data duplikat otomatis dilewati)
 */

require_once __DIR__ . '/init.php';

$data_dir = '/data/pdf';
$txt_files = glob("$data_dir/*.txt");

if (empty($txt_files)) {
    echo "[ERROR] Tidak ada file .txt di $data_dir\n";
    exit(1);
}

echo "=== IMPORT DATA SISWA DARI PDF ===\n\n";
echo "Ditemukan " . count($txt_files) . " file teks\n\n";

$total_imported = 0;

foreach ($txt_files as $file) {
    $filename = basename($file);
    echo "Memproses: $filename... ";
    $content = file_get_contents($file);
    if ($content === false) { echo "GAGAL\n"; continue; }
    $imported = process_file($content);
    $total_imported += $imported;
    echo "$imported siswa\n";
}

echo "\n=== SELESAI ===\nTotal diimport: $total_imported siswa\n";

function process_file($content) {
    $sections = preg_split('/\f?IDENTITAS PESERTA DIDIK\s*/', $content);
    array_shift($sections);
    $count = 0;
    foreach ($sections as $section) {
        $data = parse_section($section);
        if (!empty($data['nis']) && !empty($data['nama'])) {
            if (import_siswa($data)) $count++;
        }
    }
    return $count;
}

function parse_section($text) {
    $data = [
        'nama' => '', 'nis' => '', 'nisn' => '',
        'tempat_lahir' => '', 'tgl_lahir' => '',
        'jenis_kelamin' => '', 'agama' => '',
        'status_keluarga' => '', 'anak_ke' => null,
        'alamat' => '', 'no_telp' => '',
        'sekolah_asal' => '',
        'diterima_kelas' => '', 'diterima_tanggal' => '',
        'nama_ayah' => '', 'nama_ibu' => '',
        'pekerjaan_ayah' => '', 'pekerjaan_ibu' => '',
        'alamat_ortu' => '', 'no_telp_ortu' => '',
        'nama_wali' => '', 'alamat_wali' => '',
        'no_telp_wali' => '', 'pekerjaan_wali' => '',
    ];

    // Map of label → [field, handler]
    // Each label is matched independently (not sequential)
    $label_map = [
        'Nama Lengkap Peserta Didik' => ['nama', 'text'],
        'Nomor Induk/NISN' => ['nis', 'nis'],
        'Tempat ,Tanggal Lahir' => ['tempat_lahir', 'ttl'],
        'Jenis Kelamin' => ['jenis_kelamin', 'text'],
        'Agama' => ['agama', 'text'],
        'Status dalam Keluarga' => ['status_keluarga', 'text'],
        'Anak ke' => ['anak_ke', 'number'],
        'Alamat Peserta Didik' => ['alamat', 'text'],
        'Sekolah Asal' => ['sekolah_asal', 'text'],
        'Di kelas' => ['diterima_kelas', 'text'],
        'Pada tanggal' => ['diterima_tanggal', 'date'],
        'Alamat Orang Tua' => ['alamat_ortu', 'text'],
        'Nama Wali Siswa' => ['nama_wali', 'text'],
        'Alamat Wali Peserta Didik' => ['alamat_wali', 'text'],
        'Pekerjaan Wali Peserta Didik' => ['pekerjaan_wali', 'text'],
    ];

    // Track which labels we need to skip (for second occurrences)
    // "a. Ayah" and "b. Ibu" appear twice: once for nama, once for pekerjaan
    // "Nomor Telepon Rumah" appears twice: once for siswa, once for wali
    $skip_tracker = ['a. Ayah' => 0, 'b. Ibu' => 0, 'Nomor Telepon Rumah' => 0];
    $seen_pekerjaan_section = false;
    $seen_ortu_section = false;

    $lines = explode("\n", $text);
    $current_label = '';
    $in_pekerjaan = false;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line === ':' || $line === '.' || $line === '...................................................') continue;
        
        // Skip numeric list items like "1.", "2.", etc.
        if (preg_match('/^\d+\.$/', $line)) continue;
        
        // Skip headers and non-data lines
        if (strpos($line, 'KEMENTERIAN') === 0 || strpos($line, 'REPUBLIK') === 0) continue;
        if (strpos($line, 'SEKOLAH') === 0 || $line === '( SMA )') continue;
        if (preg_match('/^(Nama Sekolah|NPSN|NIS\/NSS|Alamat Sekolah|Kelurahan|Kecamatan|Kota|Provinsi|Website|E-mail|Nama Peserta Didik|KETERANGAN)/', $line)) continue;
        if ($line === 'Nama Orang Tua' || $line === 'Nama Orang Tua :') continue;
        
        // Detect section changes
        if ($line === 'Pekerjaan Orang Tua :' || $line === 'Pekerjaan Orang Tua') {
            $in_pekerjaan = true;
            continue;
        }

        // If line starts with colon, it's a value line
        if ($line[0] === ':') {
            $value = ltrim($line, ': ');
            if (empty($value) || $value === ':') continue;

            if (empty($current_label)) continue;

            // Map the current label + context to the right field
            if ($current_label === 'a. Ayah') {
                $field = $in_pekerjaan ? 'pekerjaan_ayah' : 'nama_ayah';
            } elseif ($current_label === 'b. Ibu') {
                $field = $in_pekerjaan ? 'pekerjaan_ibu' : 'nama_ibu';
            } elseif ($current_label === 'Nomor Telepon Rumah') {
                // First occurrence is no_telp (siswa), second is no_telp_wali
                $field = in_array('nama_wali', array_keys(array_filter($data))) ? 'no_telp_wali' : 'no_telp';
            } else {
                $field = $current_label;
            }

            // Parse and store
            switch ($field) {
                case 'nis':
                    if (preg_match('/^(\S+)\s*\/\s*(\S+)$/', $value, $m)) {
                        $data['nis'] = trim($m[1]);   // First = NIS
                        $data['nisn'] = trim($m[2]);  // Second = NISN
                    }
                    break;
                case 'tempat_lahir':
                    if (preg_match('/^(.+?),\s*(\d+\s+\w+\s+\d+)$/', $value, $m)) {
                        $data['tempat_lahir'] = trim($m[1]);
                        $data['tgl_lahir'] = parse_date_indonesia(trim($m[2]));
                    } elseif (!empty($value)) {
                        $data['tempat_lahir'] = $value;
                    }
                    break;
                case 'anak_ke':
                    if (is_numeric($value)) $data['anak_ke'] = (int)$value;
                    break;
                case 'diterima_tanggal':
                    if (!empty($value)) $data['diterima_tanggal'] = parse_date_indonesia($value);
                    break;
                default:
                    if (!empty($value)) $data[$field] = $value;
                    break;
            }

            $current_label = '';
            continue;
        }

        // This is a label line (no colon)
        // Check if it matches any known label
        if (isset($label_map[$line])) {
            $current_label = $label_map[$line][0];
        } elseif ($line === 'a. Ayah') {
            $current_label = 'a. Ayah';
        } elseif ($line === 'b. Ibu') {
            $current_label = 'b. Ibu';
        } elseif ($line === 'Nomor Telepon Rumah') {
            $current_label = 'Nomor Telepon Rumah';
        } elseif ($line === 'Diterima di sekolah ini') {
            // Skip this header line, no action needed
        }
    }

    return $data;
}

function parse_date_indonesia($str) {
    $months = [
        'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
        'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
        'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
    ];
    if (preg_match('/^(\d+)\s+(\w+)\s+(\d+)$/', $str, $m)) {
        return $m[3] . '-' . ($months[$m[2]] ?? '01') . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    }
    return $str;
}

function import_siswa($data) {
    $conn = conn();
    $data['nis'] = trim($data['nis']);
    $data['nisn'] = trim($data['nisn']);
    if (empty($data['nis']) || empty($data['nama'])) return false;

    $stmt = $conn->prepare("SELECT id FROM siswa WHERE nis = ?");
    $stmt->bind_param("s", $data['nis']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) return false;

    $stmt = $conn->prepare("INSERT INTO siswa (nis, nisn, nama, tempat_lahir, tgl_lahir, jenis_kelamin, agama, 
        status_keluarga, anak_ke, alamat, no_telp, sekolah_asal, diterima_kelas, diterima_tanggal,
        nama_ayah, nama_ibu, pekerjaan_ayah, pekerjaan_ibu, alamat_ortu, no_telp_ortu,
        nama_wali, alamat_wali, no_telp_wali, pekerjaan_wali) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $n = function($v) { return ($v !== null && $v !== '' && $v !== '0000-00-00') ? $v : null; };

    $p1  = $data['nis'];                $p2  = $data['nisn'];
    $p3  = $data['nama'];               $p4  = $n($data['tempat_lahir']);
    $p5  = $n($data['tgl_lahir']);      $p6  = $n($data['jenis_kelamin']);
    $p7  = $n($data['agama']);          $p8  = $n($data['status_keluarga']);
    $p9  = $data['anak_ke'];            $p10 = $n($data['alamat']);
    $p11 = $n($data['no_telp']);        $p12 = $n($data['sekolah_asal']);
    $p13 = $n($data['diterima_kelas']); $p14 = $n($data['diterima_tanggal']);
    $p15 = $n($data['nama_ayah']);      $p16 = $n($data['nama_ibu']);
    $p17 = $n($data['pekerjaan_ayah']); $p18 = $n($data['pekerjaan_ibu']);
    $p19 = $n($data['alamat_ortu']);    $p20 = $n($data['no_telp_ortu']);
    $p21 = $n($data['nama_wali']);      $p22 = $n($data['alamat_wali']);
    $p23 = $n($data['no_telp_wali']);   $p24 = $n($data['pekerjaan_wali']);

    $stmt->bind_param("ssssssssssssssssssssssss",
        $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10,
        $p11, $p12, $p13, $p14, $p15, $p16, $p17, $p18, $p19, $p20,
        $p21, $p22, $p23, $p24
    );

    if ($stmt->execute()) return true;
    echo "  Error: " . $stmt->error . " (NIS: {$data['nis']})\n";
    return false;
}
