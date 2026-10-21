# Pipeline CC — Content Creator Production Management System

A production management web application built for **Solo Creators**, **Production Teams (Editors)**, and **Administrators** to streamline content ideation, scripting, production pipelines, publishing workflows, and manual performance analytics across multiple social media channels (YouTube, TikTok, Instagram, LinkedIn).

---

## 🛠️ Technology Stack

- **Framework**: [Laravel 12 (PHP 8.2+)](https://laravel.com)
- **Interactive UI**: [Livewire v4](https://livewire.laravel.com) (Real-time Kanban Drag-and-Drop & Interactive Filterable Table)
- **Templating**: Blade Components
- **Styling**: Bootstrap 5.3 & Bootstrap Icons
- **Asset Bundling**: Vite 7
- **Database**: MySQL 8.0+ (with strict relational foreign keys and cascade rules)

---

## 🚀 Panduan Menjalankan Project (Cara Setup Setelah Clone)

Ikuti langkah-langkah berikut untuk menjalankan project ini di komputer lokal:

### 1. Prasyarat Sistem (Prerequisites)

Device sudah terinstal:

- **PHP >= 8.2**
- **Composer** (versi 2+)
- **Node.js** (versi 18+ atau 20+) & **NPM**
- **MySQL Database Server** (bisa menggunakan Laragon, XAMPP)

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

_(Catatan pengguna Windows: jika ada pembatasan script execution policy, gunakan perintah `npm.cmd install`)_

#### **Langkah 4: Buat File Konfigurasi `.env`**

Salin file template `.env.example` menjadi `.env`:

- **Di Windows (CMD / PowerShell):**
    ```bash
    copy .env.example .env
    ```
- **Di Linux / macOS / Git Bash:**
    ```bash
    cp .env.example .env
    ```

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

Buat database baru bernama **`pipeline_cc`** di MySQL Anda (melalui phpMyAdmin, HeidiSQL, DBeaver, atau terminal MySQL):

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

#### **Langkah 9: Compile Asset Frontend**

- **Untuk Production (sekali build):**
    ```bash
    npm run build
    ```
- **Untuk Development (Hot-Reloading):**
    ```bash
    npm run dev
    ```

#### **Langkah 10: Jalankan Server Aplikasi**

Buka terminal baru lalu jalankan:

```bash
php artisan serve
```

Buka browser dan akses aplikasi di: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## Akun Login Default (Seed Credentials)

Semua akun default menggunakan password: **`password`**

1. Admin

- Email: admin@pipeline.test
- Password: password

2. Creator

- Email: creator@pipeline.test
- Password: password

3. Editor

- Email: editor@pipeline.test
- Password: password

---

## Matriks Hak Akses & Alur Kerja

1. Admin

- Kelola Channel: cuman bisa delete channel creator
- monitoring Metrik: cuman bisa read d metrik content
- Manajemen Role User: Dapat mengelola role pengguna, termasuk Creator dan Editor.

2. Creator

- Kelola Channel: Dapat membuat, melihat, mengubah, dan menghapus channel miliknya sendiri.
- Buat Konten Baru: Dapat membuat konten baru pada channel yang dimiliki atau dikelolanya.
- Edit Konten: Dapat mengubah judul, naskah, dan deskripsi konten miliknya.
- Kelola Jadwal Rilis: Dapat mengatur dan mengubah jadwal rilis konten.
- Tugaskan Editor: Dapat menugaskan editor untuk mengerjakan konten miliknya.
- Kelola Status Konten:
    - Dapat memindahkan konten solo menjadi Ready.
    - Jika konten ditugaskan kepada Editor, Creator menunggu Editor menyelesaikan pengerjaan sebelum konten dilanjutkan ke tahap berikutnya.
- Publikasikan Konten: Dapat mengubah status konten menjadi Published dengan wajib mengisi URL publikasi dan tanggal publikasi.
- Catat Metrik: Dapat mencatat metrik performa konten secara manual.

3. Editor

- Akses Channel: Hanya dapat melihat channel dan tidak memiliki izin untuk mengelolanya.
- Buat Konten Baru: Tidak memiliki izin untuk membuat konten baru.
- Edit Konten: Dapat mengubah judul, naskah, dan deskripsi hanya pada konten yang ditugaskan kepadanya.
- Kelola Jadwal Rilis: Tidak memiliki izin untuk mengubah jadwal rilis konten.
- Tugaskan Editor: Tidak memiliki izin untuk menugaskan editor lain.
- Kelola Status Konten: Dapat memindahkan status konten menjadi Ready setelah menyelesaikan pengerjaannya.
- Publikasikan Konten: Tidak memiliki izin untuk memublikasikan konten.
- Catat Metrik: Hanya dapat melihat metrik performa konten dan tidak dapat mengubahnya.
- Manajemen Role User: Tidak memiliki izin untuk mengelola role pengguna.

### Aturan Bisnis Utama:

1. **Pendaftaran Akun Baru**: Pengunjung publik dapat memilih mendaftar sebagai **Creator** atau **Editor**. Setelah registrasi berhasil, sistem mengarahkan ke halaman login (tidak langsung login otomatis).
2. **Serah Terima ke Ready**: Jika konten memiliki penugasan editor, Creator **tidak dapat** memotong alur langsung ke status `Ready`. Editor yang ditugaskan harus menyelesaikan pengerjaan dan memindahkannya ke `Ready`.
3. **Publikasi**: Tahap `Published` hanya dapat dilakukan oleh Creator atau Admin dengan mengisi URL postingan media sosial dan tanggal rilis aktual.
4. **Penghapusan Channel**: Jika suatu channel dihapus, seluruh konten yang terkait dengan channel tersebut (beserta naskah, checklist, dan riwayat metrik) akan otomatis ikut terhapus (_cascade delete_).

---

## 📁 Struktur Direktori Utama

```
app/
├── Http/Controllers/
│   ├── AdminUserController.php     # Manajemen role user oleh admin
│   ├── AuthController.php          # Login, Register, Logout
│   ├── ChannelController.php       # CRUD Channel medsos
│   ├── ContentController.php       # CRUD Konten, penugasan editor, checklist, publish
│   ├── DashboardController.php     # Dashboard berdasarkan role
│   └── MetricController.php        # Pencatatan statistik metrik manual
├── Livewire/
│   ├── PipelineKanban.php          # Komponen papan Kanban drag-and-drop
│   └── PipelineTable.php           # Komponen tabel interaktif, filter & pagination
├── Models/
│   ├── Channel.php                 # Model Channel medsos
│   ├── Content.php                 # Model Konten & mesin validasi transisi status
│   ├── ContentAssignment.php       # Pivot relasi penugasan editor
│   ├── ContentChecklist.php        # Sub-tugas checklist produksi
│   ├── ContentMetric.php           # Riwayat performa harian
│   └── User.php                    # Model user dengan helper role
└── Policies/
    ├── ChannelPolicy.php           # Kebijakan kepemilikan channel
    └── ContentPolicy.php           # Kebijakan otorisasi alur produksi & peran
resources/views/
├── auth/                           # Halaman Login & Register
├── contents/                       # Halaman Create, Edit, Show, Index, Kanban
├── dashboard/                      # Dashboard Creator, Editor, Admin
├── livewire/                       # View Blade Livewire (Tabel & Kanban)
└── metrics/                        # Halaman ringkasan analitik metrik
```

---
