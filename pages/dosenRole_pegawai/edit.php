<?php
// Check permission - allow only dosen role
if (!hasRole(['dosen'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// For dosen role, only allow editing their own data
if (hasRole(['dosen'])) {
    // Get user info first
    $user_info_sql = "SELECT * FROM users WHERE id = :user_id";
    $user_stmt = $pdo->prepare($user_info_sql);
    $user_stmt->bindValue(':user_id', $current_user_id);
    $user_stmt->execute();
    $user_info = $user_stmt->fetch();

    // Try to find the pegawai record associated with the current user
    // Only get pegawai records that are linked to dosen
    $sql = "SELECT p.* FROM pegawai p
            JOIN dosen d ON p.id = d.pegawai_id
            WHERE p.email = :email AND p.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $user_info['email']);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $pegawai = $stmt->fetch();

    if (!$pegawai) {
        require_once __DIR__ . '/../errors/404.php';
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $email = cleanInput($_POST['email']);
    $nomor_induk = cleanInput($_POST['nomor_induk']);
    $nik = cleanInput($_POST['nik']);
    $jenis_kelamin = cleanInput($_POST['jenis_kelamin']);
    $tempat_lahir = cleanInput($_POST['tempat_lahir']);
    $tanggal_lahir = cleanInput($_POST['tanggal_lahir']);
    $alamat = cleanInput($_POST['alamat']);
    $no_hp = cleanInput($_POST['no_hp']);

    // Validation
    $errors = [];

    if (empty($nama_lengkap)) {
        $errors['nama_lengkap'] = 'Nama lengkap wajib diisi';
    }

    if (empty($email)) {
        $errors['email'] = 'Email wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    } else {
        // Check if email already exists for another user
        $checkSql = "SELECT id FROM pegawai WHERE email = :email AND id != :id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':email', $email);
        $checkStmt->bindValue(':id', $id);
        $checkStmt->execute();
        if ($checkStmt->rowCount() > 0) {
            $errors['email'] = 'Email sudah digunakan oleh pengguna lain';
        }
    }

    // Only validate admin-only fields if user is admin
    if (hasRole(['admin'])) {
        if (empty($nomor_induk)) {
            $errors['nomor_induk'] = 'Nomor induk wajib diisi';
        } else {
            // Check if nomor induk already exists for another user
            $checkSql = "SELECT id FROM pegawai WHERE nomor_induk = :nomor_induk AND id != :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindValue(':nomor_induk', $nomor_induk);
            $checkStmt->bindValue(':id', $id);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                $errors['nomor_induk'] = 'Nomor induk sudah digunakan oleh pengguna lain';
            }
        }

        if (empty($nik)) {
            $errors['nik'] = 'NIK wajib diisi';
        } else {
            // Check if NIK already exists for another user
            $checkSql = "SELECT id FROM pegawai WHERE nik = :nik AND id != :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindValue(':nik', $nik);
            $checkStmt->bindValue(':id', $id);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                $errors['nik'] = 'NIK sudah digunakan oleh pengguna lain';
            }
        }

        if (empty($jenis_kelamin)) {
            $errors['jenis_kelamin'] = 'Jenis kelamin wajib dipilih';
        }

        if (empty($tempat_lahir)) {
            $errors['tempat_lahir'] = 'Tempat lahir wajib diisi';
        }

        if (empty($tanggal_lahir)) {
            $errors['tanggal_lahir'] = 'Tanggal lahir wajib diisi';
        }
    }

    if (empty($no_hp)) {
        $errors['no_hp'] = 'Nomor HP wajib diisi';
    }

    // Handle file upload if exists
    $foto = $pegawai['foto']; // Keep existing photo if no new upload

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/uploads/pegawai/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($_FILES['foto']['size'] > $maxSize) {
            $errors['foto'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
        } elseif (!in_array($_FILES['foto']['type'], $allowedTypes)) {
            $errors['foto'] = 'Format file tidak didukung. Hanya JPEG, PNG, dan GIF yang diperbolehkan.';
        } else {
            // Generate unique filename
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . $id . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
                // Delete old photo if it exists
                if ($pegawai['foto'] && file_exists($uploadDir . $pegawai['foto'])) {
                    unlink($uploadDir . $pegawai['foto']);
                }

                $foto = $filename;
            } else {
                $errors['foto'] = 'Gagal mengunggah file.';
            }
        }
    }

    // If no errors, update the data
    if (empty($errors)) {
        try {
            if (hasRole(['admin'])) {
                // Admin can update all fields
                $sql = "UPDATE pegawai SET
                           nama_lengkap = :nama_lengkap,
                           email = :email,
                           nomor_induk = :nomor_induk,
                           nik = :nik,
                           jenis_kelamin = :jenis_kelamin,
                           tempat_lahir = :tempat_lahir,
                           tanggal_lahir = :tanggal_lahir,
                           alamat = :alamat,
                           no_hp = :no_hp,
                           foto = :foto,
                           updated_at = NOW()
                       WHERE id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama_lengkap', $nama_lengkap);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':nomor_induk', $nomor_induk);
                $stmt->bindValue(':nik', $nik);
                $stmt->bindValue(':jenis_kelamin', $jenis_kelamin);
                $stmt->bindValue(':tempat_lahir', $tempat_lahir);
                $stmt->bindValue(':tanggal_lahir', $tanggal_lahir);
                $stmt->bindValue(':alamat', $alamat);
                $stmt->bindValue(':no_hp', $no_hp);
                $stmt->bindValue(':foto', $foto);
                $stmt->bindValue(':id', $id);
            } else {
                // Dosen can only update certain fields
                $sql = "UPDATE pegawai SET
                           nama_lengkap = :nama_lengkap,
                           email = :email,
                           alamat = :alamat,
                           no_hp = :no_hp,
                           foto = :foto,
                           updated_at = NOW()
                       WHERE id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':nama_lengkap', $nama_lengkap);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':alamat', $alamat);
                $stmt->bindValue(':no_hp', $no_hp);
                $stmt->bindValue(':foto', $foto);
                $stmt->bindValue(':id', $id);
            }

            if ($stmt->execute()) {
                header("Location: index.php?page=dosenRole_pegawai&id=$id&success=Data berhasil diperbarui");
                exit;
            } else {
                $errors['general'] = 'Terjadi kesalahan saat menyimpan data';
            }
        } catch (Exception $e) {
            $errors['general'] = 'Terjadi kesalahan saat menyimpan data';
        }
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Edit Pegawai</h1>
    <p class="text-gray-500 mt-1">Ubah informasi data pegawai</p>
</div>

<form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm p-6">
    <?php if (isset($errors['general'])): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <?php echo $errors['general']; ?>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($pegawai['nama_lengkap'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['nama_lengkap']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan nama lengkap">
            <?php if (isset($errors['nama_lengkap'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['nama_lengkap']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($pegawai['email'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan email">
            <?php if (isset($errors['email'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['email']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Induk *</label>
            <input type="text" name="nomor_induk" value="<?php echo htmlspecialchars($pegawai['nomor_induk'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['nomor_induk']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan nomor induk" <?php echo hasRole(['admin']) ? '' : 'readonly'; ?>>
            <?php if (isset($errors['nomor_induk'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['nomor_induk']; ?></p>
            <?php endif; ?>
            <?php if (!hasRole(['admin'])): ?>
                <p class="mt-1 text-sm text-gray-500">Field ini hanya bisa diedit oleh admin</p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">NIK *</label>
            <input type="text" name="nik" value="<?php echo htmlspecialchars($pegawai['nik'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['nik']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan NIK" <?php echo hasRole(['admin']) ? '' : 'readonly'; ?>>
            <?php if (isset($errors['nik'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['nik']; ?></p>
            <?php endif; ?>
            <?php if (!hasRole(['admin'])): ?>
                <p class="mt-1 text-sm text-gray-500">Field ini hanya bisa diedit oleh admin</p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin *</label>
            <select name="jenis_kelamin"
                    class="w-full px-4 py-2 border <?php echo isset($errors['jenis_kelamin']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" <?php echo hasRole(['admin']) ? '' : 'disabled'; ?>>
                <option value="">Pilih jenis kelamin</option>
                <option value="L" <?php echo (isset($pegawai['jenis_kelamin']) && $pegawai['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="P" <?php echo (isset($pegawai['jenis_kelamin']) && $pegawai['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan</option>
            </select>
            <?php if (isset($errors['jenis_kelamin'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['jenis_kelamin']; ?></p>
            <?php endif; ?>
            <?php if (!hasRole(['admin'])): ?>
                <p class="mt-1 text-sm text-gray-500">Field ini hanya bisa diedit oleh admin</p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir *</label>
            <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($pegawai['tempat_lahir'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['tempat_lahir']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan tempat lahir" <?php echo hasRole(['admin']) ? '' : 'readonly'; ?>>
            <?php if (isset($errors['tempat_lahir'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['tempat_lahir']; ?></p>
            <?php endif; ?>
            <?php if (!hasRole(['admin'])): ?>
                <p class="mt-1 text-sm text-gray-500">Field ini hanya bisa diedit oleh admin</p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir *</label>
            <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($pegawai['tanggal_lahir'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['tanggal_lahir']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   <?php echo hasRole(['admin']) ? '' : 'readonly'; ?>>
            <?php if (isset($errors['tanggal_lahir'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['tanggal_lahir']; ?></p>
            <?php endif; ?>
            <?php if (!hasRole(['admin'])): ?>
                <p class="mt-1 text-sm text-gray-500">Field ini hanya bisa diedit oleh admin</p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
            <input type="file" name="foto" accept="image/*"
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <p class="mt-1 text-sm text-gray-500">Format: JPEG, PNG, GIF. Maksimal 5MB.</p>
            <?php if (isset($errors['foto'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['foto']; ?></p>
            <?php endif; ?>
            
            <?php if ($pegawai['foto']): ?>
                <div class="mt-3">
                    <p class="text-sm text-gray-500">Foto saat ini:</p>
                    <img src="<?php echo getImageUrl($pegawai['foto'], 'pegawai'); ?>" 
                         class="w-24 h-24 object-cover rounded-xl border border-gray-200 mt-1" 
                         alt="Foto Pegawai">
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
            <textarea name="alamat" rows="4"
                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                      placeholder="Masukkan alamat"><?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
            <input type="text" name="no_hp" value="<?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['no_hp']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan nomor HP">
            <?php if (isset($errors['no_hp'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['no_hp']; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex justify-end space-x-3">
        <a href="index.php?page=dosenRole_pegawai" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            Batal
        </a>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
            Simpan Perubahan
        </button>
    </div>
</form>