# Pipeline CC — Content Creator Production Management System

A production management web application built for **Solo Creators**, **Production Teams (Editors)**, and **Administrators** to streamline content ideation, scripting, production pipelines, publishing workflows, and manual performance analytics across multiple social media channels (YouTube, TikTok, Instagram, LinkedIn).

---

## 🛠️ Technology Stack

- **Framework**: [Laravel 12 (PHP 8.2+)]
- **Templating**: Blade Components
- **Styling**: Bootstrap 5.3 & Bootstrap Icons
- **Database**: MySQL

---

## 🚀 Panduan Menjalankan Project (Cara Setup Setelah Clone)

Ikuti langkah-langkah berikut untuk menjalankan project ini di komputer lokal:

### 1. Prasyarat Sistem (Prerequisites)

Device sudah terinstal:

- **PHP >= 8.2**
- **Composer** (versi 2+)
- **Node.js** (versi 18+ atau 20+) & **NPM**
- **MySQL Database Server**

---

### 2. Langkah Instalasi Langkah demi Langkah

#### **Langkah 1: Masuk ke direktori project**

Buka terminal / command prompt:

```bash
cd pipeline_cc
```

#### **Langkah 2: Install dependencies PHP (Composer)**

```bash
composer install
```

#### **Langkah 3: Install dependencies JavaScript (NPM)**

```bash
npm install
```

#### **Langkah 4: Buat File Konfigurasi `.env`**

Salin file template `.env.example` menjadi `.env`:

#### **Langkah 5: Konfigurasi Database di file `.env`**

Buka file `.env`, lalu pastikan pengaturan database sesuai dengan MySQL lokal Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pipeline_cc
DB_USERNAME=root
DB_PASSWORD=
```

#### **Langkah 6: Buat Database di MySQL**

Buat database baru bernama **`pipeline_cc`**

```sql
CREATE DATABASE pipeline_cc;
```

#### **Langkah 7: Generate Application Key**

```bash
php artisan key:generate
```

#### **Langkah 8: Jalankan Migrasi & Seeder Data Awal**

Perintah ini akan membuat semua struktur tabel dan otomatis mengisi data sample (user default, channel, dan kartu konten):

```bash
php artisan migrate --seed
```

#### **Langkah 9: Jalankan Server Aplikasi**

Buka terminal baru lalu jalankan:

```bash
php artisan serve
```

---

## Akun Login Default

1. Email: admin@gmail.com | Password: [PASSWORD]

2. Email: creator@gmail.com | Password: [PASSWORD]

3. Email: editor@gmail.com | Password: [PASSWORD]

Semua akun default menggunakan password: **`password`**

---

## 👥 Matriks Hak Akses & Alur Kerja

Admin

- Delete Channel.
- Read content.
- Mengelola dan mengubah role pengguna.
- Memonitoring sistem.

2. Creator

- Mengelola channel miliknya sendiri (CRUD).
- Membuat konten baru.
- Mengedit judul, naskah, dan deskripsi konten.
- Mengedit jadwal rilis konten (timeline).
- Menugaskan editor untuk mengerjakan konten.
- Mengubah status konten menjadi Ready untuk konten yang dikerjakan sendiri. Jika konten ditugaskan kepada editor, creator menunggu editor menyelesaikan pengerjaannya.
- Memublikasikan konten dengan status Published setelah mengisi URL dan tanggal publikasi.
- Mencatat metrik performa konten secara manual.

3. Editor

- Melihat channel tanpa mengubah data channel.
- Tidak dapat membuat konten baru.
- Mengedit judul, naskah, dan deskripsi hanya pada konten yang ditugaskan kepadanya.
- Tidak dapat mengubah jadwal rilis konten.
- Tidak dapat menugaskan editor lain.
- Mengubah status konten menjadi Ready setelah menyelesaikan pengerjaan.
- Tidak dapat memublikasikan konten.
- Melihat metrik performa konten tanpa mengubahnya.
- Tidak memiliki akses untuk mengelola role pengguna.

### Aturan Bisnis Utama:

1. **Pendaftaran Akun Baru**: dapat memilih mendaftar sebagai **Creator** atau **Editor**. Setelah registrasi berhasil, sistem mengarahkan ke halaman login (tidak langsung login otomatis).
2. **Serah Terima ke Ready**: Jika konten memiliki penugasan editor, Creator **tidak dapat** memotong alur langsung ke status `Ready`. Editor yang ditugaskan harus menyelesaikan pengerjaan dan memindahkannya ke `Ready`.
3. **Publikasi**: Tahap `Published` hanya dapat dilakukan oleh Creator atau Admin dengan mengisi URL postingan media sosial dan tanggal rilis aktual.
4. **Penghapusan Channel**: Jika suatu channel dihapus, seluruh konten yang terkait dengan channel tersebut (beserta naskah, checklist, dan riwayat metrik) akan otomatis ikut terhapus (_cascade delete_).

---
