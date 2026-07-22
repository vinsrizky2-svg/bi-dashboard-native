# BI Dashboard — Checklist Setup

## Struktur Folder
```
dashboard-bi/
├── .env                  ✅ sudah ada — isi kredensial DB
├── composer.json         ✅ sudah ada
├── vendor/               ❌ BELUM ADA — wajib di-generate
├── src/
│   ├── config.php        ✅ sudah ada
│   ├── db.php            ✅ sudah ada
│   └── auth.php          ✅ sudah ada
└── public/
    ├── api/
    │   └── filter_options.php  ✅ sudah ada
    ├── assets/
    │   ├── style.css           ✅ sudah ada
    │   ├── logo_sbprmvbg.png   ❌ BELUM ADA — copy dari project lama
    │   └── bcgrdnlgn.jpg       ❌ BELUM ADA — copy dari project lama
    ├── dashboard_ma.php  ✅ sudah ada
    ├── dashboard_pa.php  ✅ sudah ada
    ├── dashboard_ua.php  ✅ sudah ada
    ├── dashboard_eu.php  ✅ sudah ada
    ├── index.php         ✅ sudah ada
    ├── layout.php        ✅ sudah ada
    ├── login.php         ✅ sudah ada
    └── logout.php        ✅ sudah ada
```

---

## Yang WAJIB Dilakukan Sebelum Bisa Jalan

### 1. Install vendor/ (PALING PENTING)
Buka terminal di folder root project, jalankan:
```cmd
cd C:\Project\dashboard-bi
composer install
```
Kalau composer belum ada, download di: https://getcomposer.org/download/
Setelah install, akan muncul folder `vendor/` otomatis.

### 2. Copy file assets dari project lama
Copy 2 file ini ke folder `public/assets/`:
- `logo_sbprmvbg.png`
- `bcgrdnlgn.jpg`

### 3. Cek .env
Pastikan isi `.env` sudah benar:
```
DB_HOST=localhost
DB_PORT=5433
DB_NAME=Db_cascade
DB_USER=postgres
DB_PASS=Prfrmhe2025
```
Ganti DB_PASS jika password berbeda.

### 4. Aktifkan extension PHP PostgreSQL
Buka `C:\php-8.1.33\php.ini`, uncomment:
```ini
extension=pdo_pgsql
extension=pgsql
```
Simpan lalu restart server PHP.

### 5. Jalankan server dari folder yang BENAR
```cmd
cd C:\Project\dashboard-bi
php -S localhost:8000 -t public
```
Wajib pakai flag `-t public` agar root server = folder public.

---

## Urutan Test Setelah Setup

### Step 1 — Test koneksi DB
Buka browser:
```
http://localhost:8000/api/filter_options.php?type=tahun
```
Hasil yang benar: `[2022,2023,2024]`
Kalau error: lihat field "detail" di JSON untuk pesan lengkapnya.

### Step 2 — Test login
```
http://localhost:8000/login.php
```
Login dengan: `admin@demo.local` / `admin123`

### Step 3 — Test dashboard
```
http://localhost:8000/dashboard_ma.php
```
Filter dropdown harus terisi dari database.

---

## Kredensial Login (sementara)
| Email | Password |
|---|---|
| admin@demo.local | admin123 |
| manager@demo.local | manager123 |
| staff@demo.local | staff123 |
