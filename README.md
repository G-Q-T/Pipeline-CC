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

## 🔑 Akun Login Default (Seed Credentials)

Semua akun default menggunakan password: **`password`**

| Peran (Role)      | Email                   | Password   | Hak Akses Utama                                                         |
| ----------------- | ----------------------- | ---------- | ----------------------------------------------------------------------- |
| **Administrator** | `admin@pipeline.test`   | `password` | Pengawasan sistem, ganti role user, akses penuh                         |
| **Creator**       | `creator@pipeline.test` | `password` | Buat konten, kelola channel, tugaskan editor, publikasi postingan       |
| **Editor**        | `editor@pipeline.test`  | `password` | Edit judul, draft naskah & deskripsi, checklist, pindah status ke Ready |

---

## 👥 Matriks Hak Akses & Alur Kerja

| Fitur / Aksi                          |  Admin   |                         Creator                          |              Editor               |
| ------------------------------------- | :------: | :------------------------------------------------------: | :-------------------------------: |
| **Kelola Channel (CRUD)**             | ✅ Penuh |                    ✅ Channel Sendiri                    |           ❌ Baca Saja            |
| **Buat Konten Baru**                  |    ✅    |                            ✅                            |                ❌                 |
| **Edit Judul, Naskah & Deskripsi**    |    ✅    |                            ✅                            | ✅ (Hanya konten yang ditugaskan) |
| **Edit Jadwal Rilis (Timeline)**      |    ✅    |                            ✅                            |     ❌ (Hanya Creator/Admin)      |
| **Tugaskan Editor**                   |    ✅    |                            ✅                            |                ❌                 |
| **Pindahkan Status ke `Ready`**       |    ✅    | ✅ (Konten solo)<br>⏳ Menunggu Editor (Jika ditugaskan) |  ✅ (Setelah selesai pengerjaan)  |
| **Publikasikan Konten (`Published`)** |    ✅    |               ✅ (Wajib isi URL & Tanggal)               |     ❌ (Hanya Creator/Admin)      |
| **Catat Metrik Manual**               |    ✅    |                            ✅                            |           ❌ Baca Saja            |
| **Manajemen Role User**               |    ✅    |                            ❌                            |                ❌                 |

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

## 📄 Lisensi

Open-source berlisensi [MIT License](https://opensource.org/licenses/MIT).
