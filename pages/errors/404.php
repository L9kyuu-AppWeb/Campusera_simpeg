<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
    exit;
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex items-center justify-center min-h-screen">

    <div class="text-center">
        <h1 class="text-9xl font-extrabold text-red-500">404</h1>
        <h2 class="text-3xl font-semibold mt-4">Page Not Found</h2>
        <p class="text-gray-600 mt-2">Halaman yang Anda cari tidak ditemukan.</p>

        <div class="mt-6 space-x-4">
            <a href="javascript:history.back()"
               class="px-5 py-2 bg-gray-300 hover:bg-gray-400 rounded-md transition">
                ← Kembali
            </a>
            <a href="index.php?page=dashboard"
               class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                Ke Dashboard
            </a>
        </div>
    </div>

</body>
</html>