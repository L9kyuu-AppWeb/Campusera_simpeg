<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Masukan_nama_database_anda_di_sini');

// Konfigurasi Aplikasi
define('SITE_NAME', 'SIMPEG');
define('BASE_URL', 'http://localhost/nama_folder_project_anda/');
define('UPLOAD_PATH', __DIR__ . '/assets/uploads/');

// Konfigurasi Session
define('SESSION_TIMEOUT', 3600); // 1 hour

// Timezone
date_default_timezone_set('Asia/Makassar'); //Ganti sesuai zona waktu Anda

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}