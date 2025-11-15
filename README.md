# Struktur-AI - Admin Panel & Game Management System

## 📋 Deskripsi Proyek

Template repository untuk sistem admin panel dengan manajemen game. Proyek ini menyediakan struktur dasar untuk sistem manajemen pengguna dan game dengan otentikasi serta sistem otorisasi.

## 🚀 Instalasi

### Prasyarat
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web server (Apache/Nginx)

### Langkah-langkah Instalasi

1. **Clone repository atau gunakan sebagai template**
   ```
   git clone https://github.com/username/struktur-ai.git
   # Atau gunakan tombol "Use this template" di GitHub
   ```

2. **Konfigurasi Database**
   - Buat database baru di MySQL/MariaDB
   - Import file `database/schema.sql` untuk skema utama
   - Import file `database/game_schema.sql` untuk skema game
   - **Catatan**: File skema mungkin sudah termasuk default admin user:

     -- Insert default admin user
     -- Username: admin
     -- Password: admin123

3. **Konfigurasi Aplikasi**
   - Buka file `config.php`
   - Sesuaikan konfigurasi database:
     ```php
     define('DB_HOST', 'localhost');        // Host database
     define('DB_USER', 'root');             // Username database
     define('DB_PASS', '');                 // Password database
     define('DB_NAME', 'admin_panel_db');   // Nama database
     ```
   - Atur BASE_URL sesuai dengan lokasi proyek Anda:
     ```php
     define('BASE_URL', 'http://localhost/latihan/struktur-ai/');
     ```

4. **Struktur Folder**
   - Buat folder `assets/uploads/avatars/` dan pastikan web server memiliki izin untuk menulis di folder ini

5. **Akses Aplikasi**
   - Buka browser dan akses URL proyek
   - Gunakan kredensial default berikut untuk login pertama:
     - Username: `admin`
     - Password: `admin123`
   - Sebaiknya ubah password default setelah login pertama untuk alasan keamanan

## 📁 Struktur Folder

```
struktur-ai/
│
├── index.php                      # Entry point aplikasi
├── config.php                     # Konfigurasi database & konstanta
├── README.md                      # Dokumentasi proyek
│
├── includes/
│   ├── header.php                 # Header HTML & Navigation
│   ├── footer.php                 # Footer HTML
│   ├── sidebar.php                # Sidebar navigation
│   ├── functions.php              # Fungsi-fungsi helper
│   ├── db_connect.php             # Koneksi database
│   └── auth_check.php             # Cek authentication & authorization
│
├── pages/
│   ├── dashboard/
│   │   └── index.php              # Dashboard
│   │
│   ├── profile/
│   │   └── index.php              # Profile
│   │
│   ├── users/
│   │   ├── index.php              # List users
│   │   ├── create.php             # Create user
│   │   ├── edit.php               # Edit user
│   │   └── delete.php             # Delete user
│   │
│   ├── games/
│   │   ├── index.php              # List games
│   │   ├── create.php             # Create game
│   │   ├── edit.php               # Edit game
│   │   └── delete.php             # Delete game
│   │
│   ├── settings/
│   │   └── index.php              # Settings
│   │
│   ├── auth/
│   │   ├── login.php              # Login
│   │   └── logout.php             # Logout
│   │
│   └── errors/
│       └── 403.php                # Access Denied
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│       └── avatars/
│
└── database/
    ├── schema.sql                 # Skema database utama
    └── game_schema.sql            # Skema database untuk game
```