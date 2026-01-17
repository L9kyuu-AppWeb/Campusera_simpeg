<?php
// START OUTPUT BUFFERING - PENTING!

// Check if config.php exists, if not show setup instructions
if (!file_exists('config.php')) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Configuration Required - SIMPEG</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f8f9fa;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .container {
                max-width: 600px;
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            h1 {
                color: #dc3545;
                margin-bottom: 20px;
            }
            .alert {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 15px;
                border-radius: 4px;
                margin: 20px 0;
                text-align: left;
            }
            .code-block {
                background-color: #f1f1f1;
                border-left: 4px solid #007bff;
                padding: 15px;
                margin: 20px 0;
                text-align: left;
                font-family: monospace;
                overflow-x: auto;
            }
            button {
                background-color: #007bff;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                margin: 5px;
            }
            button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Configuration Required</h1>
            <div class="alert">
                <strong>Configuration file missing!</strong> The application cannot run without a configuration file.
            </div>

            <p>Please copy the example configuration file to create your own configuration:</p>

            <div class="code-block">
                cp config-example.php config.php
            </div>

            <p>Or if you are on Windows with Laragon, you can:</p>
            <ol style="text-align: left; margin: 15px 0;">
                <li>Copy the file <strong>config-example.php</strong> and rename it to <strong>config.php</strong></li>
                <li>Edit the new <strong>config.php</strong> file with your database credentials and settings</li>
            </ol>

            <button onclick="copyConfig()">Try to Copy Config Automatically</button>
            <button onclick="location.reload()">Refresh Page</button>

            <script>
                async function copyConfig() {
                    try {
                        const response = await fetch("setup_config.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: "action=copy"
                        });

                        const result = await response.text();
                        alert(result);
                        location.reload();
                    } catch (error) {
                        alert("Error copying config: " + error.message);
                    }
                }
            </script>
        </div>
    </body>
    </html>';
    exit;
}

require_once 'config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? cleanInput($_GET['page']) : 'dashboard';
$allowedPages = ['dashboard', 'profile', 'users', 'settings', 'games', 'roles', 'pegawai', 'dosen', 'dosen_prodi', 'fakultas', 'prodi', 'tendik', 'unit_kerja', 'riwayat_kepegawaian', 'pegawai_keluarga', 'pegawai_pendidikan', 'pegawai_pendidikan_berkas', 'login', 'logout'];
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
    case 'pegawai':
        require_once 'pages/pegawai/index.php';
        break;
    case 'dosen':
        require_once 'pages/dosen/index.php';
        break;
    case 'dosen_prodi':
        require_once 'pages/dosen_prodi/index.php';
        break;
    case 'fakultas':
        require_once 'pages/fakultas/index.php';
        break;
    case 'prodi':
        require_once 'pages/prodi/index.php';
        break;
    case 'tendik':
        require_once 'pages/tendik/index.php';
        break;
    case 'unit_kerja':
        require_once 'pages/unit_kerja/index.php';
        break;
    case 'riwayat_kepegawaian':
        require_once 'pages/riwayat_kepegawaian/index.php';
        break;
    case 'pegawai_keluarga':
        require_once 'pages/pegawai_keluarga/index.php';
        break;
    case 'pegawai_pendidikan':
        require_once 'pages/pegawai_pendidikan/index.php';
        break;
    case 'pegawai_pendidikan_berkas':
        require_once 'pages/pegawai_pendidikan_berkas/index.php';
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