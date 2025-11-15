<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'admin_panel_db');

// Konfigurasi Aplikasi
define('SITE_NAME', 'AdminPanel');
define('BASE_URL', 'http://localhost/latihan/struktur-ai/');
define('UPLOAD_PATH', __DIR__ . '/assets/uploads/');

// Konfigurasi Session
define('SESSION_TIMEOUT', 3600); // 1 hour

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}