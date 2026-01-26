<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pegawai_id'])) {
    $pegawaiId = cleanInput($_POST['pegawai_id']);

    // Get employee data to get NIK/Nomor Induk for username
    $stmt = $pdo->prepare("SELECT id, nik, nomor_induk, tipe_pegawai, nama_lengkap, email, no_hp FROM pegawai WHERE id = ?");
    $stmt->execute([$pegawaiId]);
    $pegawai = $stmt->fetch();

    if ($pegawai) {
        $username = !empty($pegawai['nik']) ? $pegawai['nik'] : $pegawai['nomor_induk'];

        if (!empty($username)) {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // User exists, just reset the password
                $defaultPassword = password_hash('1234567', PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
                $result = $stmt->execute([$defaultPassword, $username]);

                if ($result) {
                    logActivity($_SESSION['user_id'], 'reset_password', "Reset password for user: $username (pegawai: {$pegawai['nama_lengkap']})");
                    setAlert('success', 'Password berhasil direset ke default!');
                } else {
                    setAlert('error', 'Gagal mereset password!');
                }
            } else {
                // User doesn't exist, create new user and set default password
                $defaultPassword = password_hash('1234567', PASSWORD_DEFAULT);
                $firstName = explode(' ', $pegawai['nama_lengkap'])[0]; // Get first word as first name
                $lastName = implode(' ', array_slice(explode(' ', $pegawai['nama_lengkap']), 1)); // Remaining words as last name

                // Determine role based on employee type (case-insensitive search)
                if (stripos($pegawai['tipe_pegawai'], 'dosen') !== false) {
                    // For dosen types, use 'dosen' role
                    $roleId = 2;
                } else {
                    // For tendik types, use 'tendik' role
                    $roleId = 3;
                }

                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, phone, role_id, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $result = $stmt->execute([
                    $username,
                    $pegawai['email'],
                    $defaultPassword,
                    $firstName,
                    $lastName,
                    $pegawai['no_hp'],
                    $roleId
                ]);

                if ($result) {
                    logActivity($_SESSION['user_id'], 'create_user', "Created user account during password reset for: $username (pegawai: {$pegawai['nama_lengkap']})");
                    setAlert('success', 'User account berhasil dibuat dan password direset ke default!');
                } else {
                    setAlert('error', 'Gagal membuat user account!');
                }
            }
        } else {
            setAlert('error', 'Username tidak ditemukan untuk pegawai ini!');
        }
    } else {
        setAlert('error', 'Pegawai tidak ditemukan!');
    }

    redirect('index.php?page=adminRole_pegawai');
} else {
    // Redirect if accessed directly
    redirect('index.php?page=adminRole_pegawai');
}