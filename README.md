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

## Cara Pakai (Docker)

```bash
docker compose up -d
docker compose exec web php init_db.php       # buat tabel
docker compose exec web php import.php        # import 1006 siswa (optional)
docker compose exec web php create_accounts.php # buat akun siswa
```

### Cara Pakai (Podman di Debian)

Instal podman & podman-compose:

```bash
apt install podman podman-compose
```

Sesuaikan path volume di `docker-compose.yml` lalu jalankan:

```bash
podman-compose up -d
podman-compose exec web php init_db.php
podman-compose exec web php import.php        # optional
podman-compose exec web php create_accounts.php
```

> **Catatan:** Podman kadang butuh `podman machine init` dulu (kalo pakai WSL/macOS). Di Debian native biasanya langsung jalan. Pastikan direktori PDF (`~/Documents/Pelengkap-Rapor`) sudah ada atau sesuaikan volume mount di compose.

Atau tanpa compose — jalankan container satu per satu:

```bash
# Network
podman network create pelengkap

# Database
podman run -d --name db --network pelengkap -e MYSQL_ROOT_PASSWORD=rootpass -e MYSQL_DATABASE=pelengkap_rapor -v db_data:/var/lib/mysql docker.io/library/mariadb:10

# phpMyAdmin
podman run -d --name phpmyadmin --network pelengkap -p 9093:80 -e PMA_HOST=db docker.io/library/phpmyadmin

# Web
podman run -d --name web --network pelengkap -p 9092:80 -v ./app:/var/www/html -v ./sql:/var/www/sql docker.io/library/php:8.2-apache bash -c "docker-php-ext-install mysqli && a2enmod rewrite && echo 'ServerName localhost' >> /etc/apache2/apache2.conf && apache2-foreground"
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
