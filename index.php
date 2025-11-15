<?php
// START OUTPUT BUFFERING - PENTING!
ob_start();

require_once 'config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? cleanInput($_GET['page']) : 'dashboard';
$allowedPages = ['dashboard', 'profile', 'users', 'settings', 'games', 'roles', 'login', 'logout'];
$publicPages   = ['login','404']; // Halaman yang tidak butuh login

// Validasi halaman
if (!in_array($page, $allowedPages)) {
    $page = '404';
}

// 1️⃣ Jika halaman bukan "public", maka lakukan cek login terlebih dahulu
if (!in_array($page, $publicPages)) {
    require_once 'includes/auth_check.php'; // Bisa redirect tanpa masalah
}

// 2️⃣ PROSES ROUTING DULU SEBELUM OUTPUT HTML
// Ini memungkinkan redirect dari page content
ob_start(); // Buffer untuk content

switch ($page) {
    case 'login':
        require_once 'pages/auth/login.php';
        break;
    case 'logout':
        require_once 'pages/auth/logout.php';
        break;
    case '404':
        require_once 'pages/errors/404.php';
        break;
    case 'dashboard':
        require_once 'pages/dashboard/index.php';
        break;
    case 'profile':
        require_once 'pages/profile/index.php';
        break;
    case 'users':
        require_once 'pages/users/index.php';
        break;
    case 'settings':
        require_once 'pages/settings/index.php';
        break;
    case 'games':
        require_once 'pages/games/index.php';
        break;
    case 'roles':
        require_once 'pages/roles/index.php';
        break;
    default:
        echo '<h1>404 - Page Not Found</h1>';
}

$pageContent = ob_get_clean(); // Simpan content

// 3️⃣ Sekarang baru tampilkan header & content
if (!in_array($page, $publicPages)) {
    require_once 'includes/header.php';
    require_once 'includes/sidebar.php';
    echo '<main class="lg:ml-64 pt-16 min-h-screen flex flex-col"><div class="p-6 flex-1">';
    echo $pageContent; // Output content yang sudah di-buffer
    echo '</div>';
    require_once 'includes/footer.php';
} else {
    // Untuk public pages (login), langsung output
    echo $pageContent;
}

// END OUTPUT BUFFERING
ob_end_flush();
?>