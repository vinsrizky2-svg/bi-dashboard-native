# BI Dashboard — Performa Alat Berat

Sistem Business Intelligence (BI) Dashboard berbasis web menggunakan PHP Native, untuk memantau performa operasional alat berat lewat empat KPI utama: **Mechanical Availability (MA)**, **Physical Availability (PA)**, **Use of Availability (UA)**, dan **Effective Utilization (EU)**.

Aplikasi ini berfungsi sebagai portal akses terpusat yang menangani autentikasi pengguna, manajemen akun berbasis role, dan menyematkan visualisasi dashboard dari **Tableau Online** ke dalam antarmuka web yang terproteksi login.

---

## Fitur Utama

- 🔐 **Autentikasi & Session Management** — login aman dengan hashing password (BCRYPT)
- 👥 **Manajemen Akun Berbasis Role** — Admin, Manager, dan Staff dengan kewenangan CRUD berbeda
- 📊 **Dashboard BI Interaktif** — embed visualisasi Tableau langsung di aplikasi
- 🔍 **Cascading Filter** — filter data dinamis (tahun → lokasi → bulan → jenis alat)
- 🛡️ **Validasi Data di Level Database** — trigger PostgreSQL menjaga integritas nilai KPI sebelum data masuk
- 📝 **Error Handling Aman** — detail error teknis dicatat via `error_log()`, user hanya melihat pesan generik (tidak ada kebocoran info internal)

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP Native (tanpa framework MVC) |
| Database | PostgreSQL (data warehouse — Star Schema) |
| Konfigurasi | `vlucas/phpdotenv` (baca kredensial dari `.env`) |
| Visualisasi | Tableau Online (Embedding API v3) |
| Frontend | HTML, CSS custom, SweetAlert2 |

---

## Struktur Folder

```
bi-project/
├── public/                 # Entry point — diakses langsung lewat browser
│   ├── api/
│   │   └── filter_options.php
│   ├── assets/
│   │   └── style.css
│   ├── dashboard_ma.php    # Dashboard "Performa Alat" (Mechanical Availability)
│   ├── dashboard_pa.php
│   ├── dashboard_ua.php
│   ├── dashboard_eu.php
│   ├── index.php
│   ├── layout.php
│   ├── login.php
│   ├── logout.php
│   └── manage_users.php
├── src/                     # Logika internal, tidak diakses langsung
│   ├── auth.php             # require_login(), require_role(), current_user()
│   ├── config.php
│   └── db.php                # getDB() — koneksi PDO ke PostgreSQL
├── database/                 # Source code database (function, trigger, schema)
│   ├── functions/
│   ├── triggers/
│   └── procedures/
├── vendor/                   # Dependency Composer (jangan di-edit manual)
├── .env                       # Kredensial (JANGAN di-commit)
├── .gitignore
├── composer.json
└── composer.lock
```

---

## Prasyarat

- PHP 8.0 atau lebih baru
- PostgreSQL 13 atau lebih baru
- [Composer](https://getcomposer.org/)
- Git

---

## Cara Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/vinsrizky2-svg/bi-dashboard-native.git
cd bi-dashboard-native
```

### 2. Install Dependency
```bash
composer install
```

### 3. Buat File `.env`
Buat file `.env` di root project (sejajar dengan folder `public/` dan `src/`), isi sesuai konfigurasi database lokal kalian:
```env
APP_NAME=BI Dashboard

DB_HOST=localhost
DB_PORT=5432
DB_NAME=db_operasional
DB_USER=postgres
DB_PASS=isi_password_kalian
```
> ⚠️ File `.env` sudah masuk `.gitignore` — jangan pernah commit file ini karena berisi kredensial database.
> ℹ️ Semua variabel di atas dibaca lewat `vlucas/phpdotenv` di `src/config.php`. Kalau folder `vendor/` belum ada (belum `composer install`), sistem otomatis fallback ke parser `.env` manual — tetap berfungsi, tapi disarankan tetap jalankan `composer install` dulu.

### 4. Setup Database
Jalankan skema dan objek database lewat pgAdmin Query Tool atau `psql`, urutannya:
```sql
-- 1. Buat tabel (kalau belum ada)
\i database/schema.sql

-- 2. Function (harus sebelum trigger)
\i database/functions/fn_validate_kpi_ketersediaan.sql

-- 3. Trigger
\i database/triggers/trg_fact_ketersediaan_validate_kpi.sql
```

### 5. Jalankan Aplikasi (Development Server)
```bash
cd public
php -S localhost:8000
```
Buka `http://localhost:8000` di browser.

---

## Role & Kewenangan

| Role | Kewenangan |
|---|---|
| **Admin** | Akses penuh — tambah/reset password/nonaktifkan/hapus akun apa pun |
| **Manager** | Hanya bisa mengelola akun dengan role Staff |
| **Staff** | Hanya bisa melihat dashboard, tidak ada akses Kelola Akun |

---

## Alur Kontribusi (Git Workflow)

```
main        → versi stabil, siap demo/sidang
dev         → integrasi fitur sebelum masuk main
feature/*   → satu branch per fitur
```

```bash
git checkout dev
git checkout -b feature/nama-fitur
# ...kerjakan perubahan...
git add .
git commit -m "feat: deskripsi singkat perubahan"
git push -u origin feature/nama-fitur
```
Lalu buka **Pull Request** ke branch `dev` di GitHub untuk direview sebelum di-merge.

### Konvensi Commit Message
```
feat:     fitur baru
fix:      perbaikan bug
docs:     perubahan dokumentasi
refactor: perubahan kode tanpa mengubah perilaku
chore:    perubahan konfigurasi/setup, tidak menyentuh logika aplikasi
```

---

## Roadmap / Pengembangan Selanjutnya

- [ ] Terapkan `fn_protect_last_admin` & `fn_normalize_email` (trigger) ke database — saat ini baru `fn_validate_kpi_ketersediaan` yang aktif
- [ ] Terapkan procedure `sp_deactivate_dormant_staff` untuk pembersihan akun dormant
- [ ] Tambah proteksi CSRF token pada form CRUD di `manage_users.php`
- [ ] Refactor `manage_users.php` agar memakai `require_role(['admin','manager'])` alih-alih pengecekan role manual

---

## Troubleshooting

**Muncul pesan "Koneksi database gagal. Silakan coba lagi nanti."**

Pesan ke browser memang sengaja dibuat generik (demi keamanan — lihat bagian Error Handling). Untuk lihat detail error aslinya, cek PHP error log:

```powershell
# Windows (PowerShell)
Get-Content C:\xampp\php\logs\php_error_log -Wait -Tail 20

# Linux/Mac
tail -f /path/ke/php_error_log
```
Penyebab umum: `.env` belum dibuat/salah isi, PostgreSQL belum jalan, atau nama database di `DB_NAME` tidak sesuai dengan yang ada di PostgreSQL.

**Dashboard Tableau tidak muncul/kosong**

Buka Chrome DevTools (`F12`) → tab **Console** dan **Network**, cari request ke domain `*.online.tableau.com`. Kalau statusnya bukan `200`, kemungkinan workbook belum di-publish/private, atau URL `src` di `dashboard_ma.php` sudah tidak sesuai dengan nama workbook terbaru di Tableau Online.

---

## Kontributor

- [vinsrizky2-svg](https://github.com/vinsrizky2-svg)

---

## Lisensi

Belum ditentukan — untuk keperluan akademik/sertifikasi.
