<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get all employees for admin to select from
$pegawai_sql = "SELECT * FROM pegawai ORDER BY nama_lengkap ASC";
$pegawai_stmt = $pdo->prepare($pegawai_sql);
$pegawai_stmt->execute();
$pegawai_list = $pegawai_stmt->fetchAll();

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // For admin role, get any family data
    $sql = "SELECT * FROM pegawai_keluarga WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $keluarga = $stmt->fetch();

    if (!$keluarga) {
        require_once __DIR__ . '/../errors/404.php';
        exit;
    }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $pegawai_id = cleanInput($_POST['pegawai_id']);
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $hubungan = cleanInput($_POST['hubungan']);
    $nomor_induk = cleanInput($_POST['nomor_induk']);
    $nik = cleanInput($_POST['nik']);
    $jenis_kelamin = cleanInput($_POST['jenis_kelamin']);
    $tempat_lahir = cleanInput($_POST['tempat_lahir']);
    $tanggal_lahir = cleanInput($_POST['tanggal_lahir']);
    $status_hidup = cleanInput($_POST['status_hidup']);
    $pekerjaan = cleanInput($_POST['pekerjaan']);
    $pendidikan_terakhir = cleanInput($_POST['pendidikan_terakhir']);
    $tanggal_nikah = cleanInput($_POST['tanggal_nikah']);
    $status_bpjs = isset($_POST['status_bpjs']) ? 1 : 0;

    // Validation
    $errors = [];

    if (empty($pegawai_id)) {
        $errors['pegawai_id'] = 'Pegawai wajib dipilih';
    }

    if (empty($nama_lengkap)) {
        $errors['nama_lengkap'] = 'Nama lengkap wajib diisi';
    }

    if (empty($hubungan)) {
        $errors['hubungan'] = 'Hubungan keluarga wajib dipilih';
    }

    if (empty($nik)) {
        $errors['nik'] = 'NIK wajib diisi';
    } else {
        // Check if NIK already exists for another family member (excluding current record)
        $checkSql = "SELECT id FROM pegawai_keluarga WHERE nik = :nik AND pegawai_id = :pegawai_id AND id != :id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':nik', $nik);
        $checkStmt->bindValue(':pegawai_id', $pegawai_id);
        $checkStmt->bindValue(':id', $id);
        $checkStmt->execute();
        if ($checkStmt->rowCount() > 0) {
            $errors['nik'] = 'NIK sudah digunakan untuk anggota keluarga lain';
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

    if (empty($status_hidup)) {
        $errors['status_hidup'] = 'Status hidup wajib dipilih';
    }

    // Handle file upload if exists
    $foto = $keluarga['foto']; // Keep existing photo if no new upload

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/uploads/pegawai_keluarga/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($_FILES['foto']['size'] > $maxSize) {
            $errors['foto'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
        } elseif (!in_array($_FILES['foto']['type'], $allowedTypes)) {
            $errors['foto'] = 'Format file tidak didukung. Hanya JPEG, PNG, dan GIF yang diperbolehkan.';
        } else {
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . $pegawai_id . '_' . $nik . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
                // Delete old photo if it exists
                if ($keluarga['foto'] && file_exists($uploadDir . $keluarga['foto'])) {
                    unlink($uploadDir . $keluarga['foto']);
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
            $sql = "UPDATE pegawai_keluarga SET
                       pegawai_id = :pegawai_id,
                       nama_lengkap = :nama_lengkap,
                       hubungan = :hubungan,
                       nomor_induk = :nomor_induk,
                       nik = :nik,
                       jenis_kelamin = :jenis_kelamin,
                       tempat_lahir = :tempat_lahir,
                       tanggal_lahir = :tanggal_lahir,
                       status_hidup = :status_hidup,
                       pekerjaan = :pekerjaan,
                       pendidikan_terakhir = :pendidikan_terakhir,
                       tanggal_nikah = :tanggal_nikah,
                       status_bpjs = :status_bpjs,
                       foto = :foto,
                       updated_at = NOW()
                   WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pegawai_id', $pegawai_id);
            $stmt->bindValue(':nama_lengkap', $nama_lengkap);
            $stmt->bindValue(':hubungan', $hubungan);
            $stmt->bindValue(':nomor_induk', $nomor_induk);
            $stmt->bindValue(':nik', $nik);
            $stmt->bindValue(':jenis_kelamin', $jenis_kelamin);
            $stmt->bindValue(':tempat_lahir', $tempat_lahir);
            $stmt->bindValue(':tanggal_lahir', $tanggal_lahir);
            $stmt->bindValue(':status_hidup', $status_hidup);
            $stmt->bindValue(':pekerjaan', $pekerjaan);
            $stmt->bindValue(':pendidikan_terakhir', $pendidikan_terakhir);
            $stmt->bindValue(':tanggal_nikah', $tanggal_nikah);
            $stmt->bindValue(':status_bpjs', $status_bpjs);
            $stmt->bindValue(':foto', $foto);
            $stmt->bindValue(':id', $id);

            if ($stmt->execute()) {
                header("Location: index.php?page=pegawai_keluarga&action=detail&id=$id&success=Data keluarga berhasil diperbarui");
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
    <h1 class="text-3xl font-bold text-gray-800">Edit Keluarga Pegawai</h1>
    <p class="text-gray-500 mt-1">Ubah data anggota keluarga</p>
</div>

<form method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm p-6">
    <?php if (isset($errors['general'])): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <?php echo $errors['general']; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai *</label>
            <select name="pegawai_id"
                    class="w-full px-4 py-2 border <?php echo isset($errors['pegawai_id']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih pegawai</option>
                <?php foreach ($pegawai_list as $pegawai_item): ?>
                    <option value="<?php echo $pegawai_item['id']; ?>" <?php echo (($_POST['pegawai_id'] ?? $keluarga['pegawai_id']) == $pegawai_item['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pegawai_item['nama_lengkap']); ?> (<?php echo htmlspecialchars($pegawai_item['email']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['pegawai_id'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['pegawai_id']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? $keluarga['nama_lengkap']); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['nama_lengkap']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan nama lengkap">
            <?php if (isset($errors['nama_lengkap'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['nama_lengkap']; ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan *</label>
            <select name="hubungan"
                    class="w-full px-4 py-2 border <?php echo isset($errors['hubungan']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih hubungan</option>
                <option value="Suami" <?php echo ((($_POST['hubungan'] ?? $keluarga['hubungan']) === 'Suami') ? 'selected' : ''); ?>>Suami</option>
                <option value="Istri" <?php echo ((($_POST['hubungan'] ?? $keluarga['hubungan']) === 'Istri') ? 'selected' : ''); ?>>Istri</option>
                <option value="Anak" <?php echo ((($_POST['hubungan'] ?? $keluarga['hubungan']) === 'Anak') ? 'selected' : ''); ?>>Anak</option>
                <option value="Ayah" <?php echo ((($_POST['hubungan'] ?? $keluarga['hubungan']) === 'Ayah') ? 'selected' : ''); ?>>Ayah</option>
                <option value="Ibu" <?php echo ((($_POST['hubungan'] ?? $keluarga['hubungan']) === 'Ibu') ? 'selected' : ''); ?>>Ibu</option>
                <option value="Lainnya" <?php echo ((($_POST['hubungan'] ?? $keluarga['hubungan']) === 'Lainnya') ? 'selected' : ''); ?>>Lainnya</option>
            </select>
            <?php if (isset($errors['hubungan'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['hubungan']; ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Induk</label>
            <input type="text" name="nomor_induk" value="<?php echo htmlspecialchars($_POST['nomor_induk'] ?? $keluarga['nomor_induk']); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['nomor_induk']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan nomor induk">
            <?php if (isset($errors['nomor_induk'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['nomor_induk']; ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">NIK *</label>
            <input type="text" name="nik" value="<?php echo htmlspecialchars($_POST['nik'] ?? $keluarga['nik']); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['nik']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan NIK">
            <?php if (isset($errors['nik'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['nik']; ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin *</label>
            <select name="jenis_kelamin"
                    class="w-full px-4 py-2 border <?php echo isset($errors['jenis_kelamin']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih jenis kelamin</option>
                <option value="L" <?php echo ((($_POST['jenis_kelamin'] ?? $keluarga['jenis_kelamin']) === 'L') ? 'selected' : ''); ?>>Laki-laki</option>
                <option value="P" <?php echo ((($_POST['jenis_kelamin'] ?? $keluarga['jenis_kelamin']) === 'P') ? 'selected' : ''); ?>>Perempuan</option>
            </select>
            <?php if (isset($errors['jenis_kelamin'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['jenis_kelamin']; ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir *</label>
            <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($_POST['tempat_lahir'] ?? $keluarga['tempat_lahir']); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['tempat_lahir']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan tempat lahir">
            <?php if (isset($errors['tempat_lahir'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['tempat_lahir']; ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir *</label>
            <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? $keluarga['tanggal_lahir']); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['tanggal_lahir']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <?php if (isset($errors['tanggal_lahir'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['tanggal_lahir']; ?></p>
            <?php endif; ?>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status Hidup *</label>
            <select name="status_hidup"
                    class="w-full px-4 py-2 border <?php echo isset($errors['status_hidup']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih Status Hidup</option>
                <option value="Hidup" <?php echo ((($_POST['status_hidup'] ?? $keluarga['status_hidup']) === 'Hidup') ? 'selected' : ''); ?>>Hidup</option>
                <option value="Meninggal" <?php echo ((($_POST['status_hidup'] ?? $keluarga['status_hidup']) === 'Meninggal') ? 'selected' : ''); ?>>Meninggal</option>
            </select>
            <?php if (isset($errors['status_hidup'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['status_hidup']; ?></p>
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
            
            <?php if ($keluarga['foto']): ?>
                <div class="mt-3">
                    <p class="text-sm text-gray-500">Foto saat ini:</p>
                    <img src="../../assets/uploads/pegawai_keluarga/<?php echo htmlspecialchars($keluarga['foto']); ?>" 
                         class="w-24 h-24 object-cover rounded-xl border border-gray-200 mt-1" 
                         alt="Foto Keluarga">
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan</label>
            <input type="text" name="pekerjaan" value="<?php echo htmlspecialchars($_POST['pekerjaan'] ?? $keluarga['pekerjaan']); ?>"
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan pekerjaan">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pendidikan Terakhir</label>
            <input type="text" name="pendidikan_terakhir" value="<?php echo htmlspecialchars($_POST['pendidikan_terakhir'] ?? $keluarga['pendidikan_terakhir']); ?>"
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan pendidikan terakhir">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Nikah</label>
            <input type="date" name="tanggal_nikah" value="<?php echo htmlspecialchars($_POST['tanggal_nikah'] ?? $keluarga['tanggal_nikah']); ?>"
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        
        <div class="flex items-center pt-6">
            <input type="checkbox" name="status_bpjs" id="status_bpjs" value="1" <?php echo (($keluarga['status_bpjs'] ?? 0) == 1) ? 'checked' : ''; ?>
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="status_bpjs" class="ml-2 block text-sm text-gray-700">
                Status BPJS Aktif
            </label>
        </div>
    </div>
    
    <div class="flex justify-end space-x-3">
        <a href="index.php?page=pegawai_keluarga&action=detail&id=<?php echo $keluarga['id']; ?>" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            Batal
        </a>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
            Simpan Perubahan
        </button>
    </div>
</form>