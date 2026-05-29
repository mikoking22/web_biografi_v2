# 📖 PANDUAN SETUP — Web Biografi Kelompok 4

---

## 1. STRUKTUR FOLDER YANG DIREKOMENDASIKAN

```
web_biografi/
├── 📄 indexbiografi.php        ← Halaman utama publik
├── 📄 admin.php                ← Dashboard admin (dilindungi session)
├── 📄 login.php                ← Halaman login admin
├── 📄 cetak.php                ← Generator PDF (dilindungi session)
├── 📄 koneksi.php              ← Shim kompatibilitas (include includes/koneksi.php)
│
├── 📁 includes/
│   └── koneksi.php             ← Koneksi database (sumber tunggal)
│
├── 📁 assets/
│   ├── css/
│   │   └── style.css           ← Custom CSS
│   └── js/
│       └── app.js              ← JavaScript utama
│
├── 📁 images/                  ← Foto anggota tim
│   ├── ms_saif.jpeg
│   ├── novi.jpeg
│   └── ...
│
└── 📁 lib/
    └── fpdf/
        └── fpdf.php            ← Library FPDF (download manual, lihat poin 4)
```

---

## 2. BUAT DATABASE & TABEL 'team'

Buka **phpMyAdmin → SQL** dan jalankan:

```sql
-- Buat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS db_biografi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_biografi;

-- Buat tabel team
CREATE TABLE IF NOT EXISTS `team` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `photo`       VARCHAR(255) DEFAULT NULL,
  `role`        VARCHAR(100) DEFAULT NULL,
  `short_bio`   TEXT         DEFAULT NULL,
  `full_bio`    TEXT         DEFAULT NULL,
  `skills`      VARCHAR(500) DEFAULT NULL  COMMENT 'Pisah dengan koma: PHP,MySQL,HTML',
  `quote`       TEXT         DEFAULT NULL,
  `bg_color`    VARCHAR(7)   DEFAULT '#f1f0eb',
  `card_color`  VARCHAR(7)   DEFAULT '#fffef9',
  `text_color`  VARCHAR(7)   DEFAULT '#0f172a',
  `font_family` VARCHAR(50)  DEFAULT 'Outfit',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 3. BUAT TABEL 'users' (Akun Admin)

```sql
USE db_biografi;

-- Buat tabel users untuk akun admin
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL  COMMENT 'Hash bcrypt dari password_hash()',
  `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 🔑 Insert Akun Admin

**CARA YANG BENAR** — gunakan `password_hash()` di PHP:

```php
<?php
// Jalankan file ini SATU KALI untuk membuat hash password
// Misalnya simpan sebagai: buat_admin.php (hapus setelah dipakai!)

include 'includes/koneksi.php';

$username = 'admin';         // ← Ganti sesuai keinginan
$password = 'password123';   // ← Ganti dengan password yang kuat!

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hash);
$stmt->execute();
echo "Akun admin berhasil dibuat! Hapus file ini sekarang.";
?>
```

**⚠️ JANGAN** simpan password plain-text di database.  
**⚠️ HAPUS** file `buat_admin.php` setelah berhasil dieksekusi.

---

## 4. PASANG LIBRARY FPDF (untuk cetak.php)

**Langkah-langkah:**

1. Buka browser, pergi ke → http://www.fpdf.org
2. Klik **"Download"** → pilih versi terbaru (misal FPDF 1.86)
3. Download file ZIP, ekstrak
4. Di dalam ZIP akan ada file bernama `fpdf.php` (dan folder `font/`)
5. Salin **seluruh isi** folder hasil ekstrak ke dalam: `lib/fpdf/`

**Hasil akhir folder harus seperti ini:**
```
lib/
└── fpdf/
    ├── fpdf.php       ← ✅ File utama yang di-require oleh cetak.php
    ├── font/          ← ✅ Folder font wajib ikut!
    │   ├── courier.php
    │   ├── helvetica.php
    │   └── ...
    └── ...
```

**Verifikasi:** Akses `cetak.php?id=1` — jika muncul PDF → berhasil!  
Jika muncul pesan error kuning → cek apakah `lib/fpdf/fpdf.php` sudah ada.

---

## 5. CEK KONEKSI DATABASE

Edit file `includes/koneksi.php` sesuai setting XAMPP/server kamu:

```php
$host = "localhost";    // biasanya localhost
$user = "root";         // user MySQL kamu
$pass = "";             // password MySQL (kosong jika XAMPP default)
$db   = "db_biografi";  // nama database
```

---

## 6. CARA MENGHUBUNGKAN FILE KE indexbiografi.php

Di `indexbiografi.php`, file-file dikoneksikan seperti ini:

```php
<?php
// 1. Include database (PHP)
include __DIR__ . '/includes/koneksi.php';

// 2. Ambil data & inject ke JS
$query = $conn->query("SELECT * FROM team ORDER BY id ASC");
$data  = [];
while ($row = $query->fetch_assoc()) {
    $row['skills'] = array_map('trim', explode(',', $row['skills']));
    $data[] = $row;
}
$teamDataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html>
<head>
  <!-- 3. Link CSS custom -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <!-- ... HTML ... -->

  <!--
    4. Inject data PHP → JavaScript (HARUS sebelum app.js!)
       Ini jembatan antara PHP dan app.js
  -->
  <script>
    const teamData = <?= $teamDataJson ?>;
  </script>

  <!-- 5. Load JavaScript utama -->
  <script src="assets/js/app.js"></script>
</body>
</html>
```

**Urutan yang benar:**
1. `includes/koneksi.php` (PHP include) — paling atas
2. `assets/css/style.css` (link tag) — di dalam `<head>`
3. `<script>const teamData = ...;</script>` — sebelum app.js
4. `assets/js/app.js` (script tag) — paling bawah body

---

## 7. RINGKASAN ALUR KEAMANAN

```
Pengunjung tembak URL admin.php
        ↓
session_start() + cek $_SESSION['admin_logged_in']
        ↓
Tidak ada / false?
        ↓
header('Location: login.php') + exit   ← ditendang!
        ↓
User isi form login
        ↓
Prepared Statement cari username di tabel users
        ↓
password_verify($input, $hash_dari_db)
        ↓
Cocok? → set $_SESSION + session_regenerate_id(true)
        ↓
Redirect ke admin.php ← baru bisa akses!
```

---

## 8. CHECKLIST SEBELUM GO-LIVE

- [ ] Edit `includes/koneksi.php` — sesuaikan kredensial database
- [ ] Jalankan SQL tabel `team` dan `users`
- [ ] Buat akun admin via `buat_admin.php` → langsung hapus filenya
- [ ] Download & taruh FPDF di `lib/fpdf/`
- [ ] Upload semua file sesuai struktur folder di atas
- [ ] Test login di `login.php`
- [ ] Test edit anggota di `admin.php`
- [ ] Test cetak PDF di `cetak.php?id=1`
- [ ] Pastikan `indexbiografi.php` tampil normal

---

*Dibuat untuk: Web Biografi Kelompok 4 — PHP Native + MySQL + Tailwind CSS*
