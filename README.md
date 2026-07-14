# Pelengkap Biodata Rapor

Aplikasi verifikasi data siswa untuk kelengkapan biodata rapor.

## Fitur

- **Login Siswa** — login pakai NIS (username & password = NIS)
- **Verifikasi Data** — siswa mengisi/memperbaiki 18 field data pribadi (identitas, alamat, orang tua, wali)
- **Progress Tracking** — progress bar kelengkapan data + status per-field
- **Admin Dashboard** — lihat daftar siswa, search/filter, edit data
- **Export CSV & SQL** — download data verified untuk diimport ke database absensi
- **Ganti Password Admin**
- **Responsive Mobile**

## Cara Pakai

```bash
docker compose up -d
docker compose exec web php init_db.php       # buat tabel
docker compose exec web php import.php        # import 1006 siswa (optional)
docker compose exec web php create_accounts.php # buat akun siswa
```

### Akses

| Role   | URL                        | Login                  |
|--------|----------------------------|------------------------|
| Siswa  | http://localhost:9092      | NIS / NIS              |
| Admin  | http://localhost:9092      | admin / admin          |
| DB     | http://localhost:9093      | root / rootpass        |

## Struktur Data

Field yang diverifikasi siswa: Tempat Lahir, Tgl Lahir, JK, Agama, Status Keluarga, Anak ke, Alamat, No Telp, Sekolah Asal, Nama Ayah, Nama Ibu, Pekerjaan Ayah, Pekerjaan Ibu, Alamat Ortu, Nama Wali, Alamat Wali, No Telp Wali, Pekerjaan Wali.

## Sumber Data

PDF rapor diekstrak ke txt, diparsing oleh `app/import.php`, dan disimpan ke MariaDB.
