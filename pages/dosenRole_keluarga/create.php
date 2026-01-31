<?php
// Check permission
if (!hasRole(['dosen'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

// Get user info first
$user_info_sql = "SELECT * FROM users WHERE id = :user_id";
$user_stmt = $pdo->prepare($user_info_sql);
$user_stmt->bindValue(':user_id', $current_user_id);
$user_stmt->execute();
$user_info = $user_stmt->fetch();

// Get pegawai info based on user's email
$pegawai_sql = "SELECT * FROM pegawai WHERE email = :email";
$pegawai_stmt = $pdo->prepare($pegawai_sql);
$pegawai_stmt->bindValue(':email', $user_info['email']);
$pegawai_stmt->execute();
$pegawai = $pegawai_stmt->fetch();

if (!$pegawai) {
    // If no pegawai record found, show error
    require_once __DIR__ . '/../errors/404.php';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $nama = cleanInput($_POST['nama']);
    $hubungan = cleanInput($_POST['hubungan']);
    $no_ktp = cleanInput($_POST['no_ktp']);
    $jenis_kelamin = cleanInput($_POST['jenis_kelamin']);
    $tempat_lahir = cleanInput($_POST['tempat_lahir']);
    $tanggal_lahir = cleanInput($_POST['tanggal_lahir']);
    $status_hidup = cleanInput($_POST['status_hidup']);
    $pekerjaan = cleanInput($_POST['pekerjaan']);
    $pendidikan_terakhir = cleanInput($_POST['pendidikan_terakhir']);
    $status_tanggungan = isset($_POST['status_tanggungan']) ? 1 : 0;
    $no_kk = cleanInput($_POST['no_kk']);

    // Validation
    $errors = [];

    if (empty($nama)) {
        $errors['nama'] = 'Nama lengkap wajib diisi';
    }

    if (empty($hubungan)) {
        $errors['hubungan'] = 'Hubungan keluarga wajib dipilih';
    }

    if (empty($no_ktp)) {
        $errors['no_ktp'] = 'NIK wajib diisi';
    } else {
        // Check if NIK already exists for another family member
        $checkSql = "SELECT id FROM pegawai_keluarga WHERE no_ktp = :no_ktp AND pegawai_id = :pegawai_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':no_ktp', $no_ktp);
        $checkStmt->bindValue(':pegawai_id', $pegawai['id']);
        $checkStmt->execute();
        if ($checkStmt->rowCount() > 0) {
            $errors['no_ktp'] = 'NIK sudah digunakan untuk anggota keluarga lain';
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


    // If no errors, insert the data
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO pegawai_keluarga (
                       pegawai_id, nama, hubungan,
                       no_ktp, jenis_kelamin, tempat_lahir, tanggal_lahir,
                       status_hidup, pekerjaan, pendidikan_terakhir,
                       status_tanggungan, no_kk, created_at
                   ) VALUES (
                       :pegawai_id, :nama, :hubungan,
                       :no_ktp, :jenis_kelamin, :tempat_lahir, :tanggal_lahir,
                       :status_hidup, :pekerjaan, :pendidikan_terakhir,
                       :status_tanggungan, :no_kk, NOW()
                   )";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pegawai_id', $pegawai['id']);
            $stmt->bindValue(':nama', $nama);
            $stmt->bindValue(':hubungan', $hubungan);
            $stmt->bindValue(':no_ktp', $no_ktp);
            $stmt->bindValue(':jenis_kelamin', $jenis_kelamin);
            $stmt->bindValue(':tempat_lahir', $tempat_lahir);
            $stmt->bindValue(':tanggal_lahir', $tanggal_lahir);
            $stmt->bindValue(':status_hidup', $status_hidup);
            $stmt->bindValue(':pekerjaan', $pekerjaan);
            $stmt->bindValue(':pendidikan_terakhir', $pendidikan_terakhir);
            $stmt->bindValue(':status_tanggungan', $status_tanggungan);
            $stmt->bindValue(':no_kk', $no_kk);

            if ($stmt->execute()) {
                header("Location: index.php?page=dosenRole_keluarga&success=Data keluarga berhasil ditambahkan");
                exit;
            } else {
                $errors['general'] = 'Terjadi kesalahan saat menyimpan data: ' . implode(', ', $stmt->errorInfo());
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
        }
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Tambah Keluarga Saya</h1>
    <p class="text-gray-500 mt-1">Tambahkan data anggota keluarga Anda</p>
</div>

<form method="POST" class="bg-white rounded-2xl shadow-sm p-6">
    <?php if (isset($errors['general'])): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <?php echo $errors['general']; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['nama']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan nama lengkap">
            <?php if (isset($errors['nama'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['nama']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan *</label>
            <select name="hubungan"
                    class="w-full px-4 py-2 border <?php echo isset($errors['hubungan']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih hubungan</option>
                <option value="Suami" <?php echo (($_POST['hubungan'] ?? '') === 'Suami') ? 'selected' : ''; ?>>Suami</option>
                <option value="Istri" <?php echo (($_POST['hubungan'] ?? '') === 'Istri') ? 'selected' : ''; ?>>Istri</option>
                <option value="Anak" <?php echo (($_POST['hubungan'] ?? '') === 'Anak') ? 'selected' : ''; ?>>Anak</option>
                <option value="Ayah" <?php echo (($_POST['hubungan'] ?? '') === 'Ayah') ? 'selected' : ''; ?>>Ayah</option>
                <option value="Ibu" <?php echo (($_POST['hubungan'] ?? '') === 'Ibu') ? 'selected' : ''; ?>>Ibu</option>
                <option value="Lainnya" <?php echo (($_POST['hubungan'] ?? '') === 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
            </select>
            <?php if (isset($errors['hubungan'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['hubungan']; ?></p>
            <?php endif; ?>
        </div>


        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">NIK *</label>
            <input type="text" name="no_ktp" value="<?php echo htmlspecialchars($_POST['no_ktp'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['no_ktp']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan NIK">
            <?php if (isset($errors['no_ktp'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['no_ktp']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin *</label>
            <select name="jenis_kelamin"
                    class="w-full px-4 py-2 border <?php echo isset($errors['jenis_kelamin']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih jenis kelamin</option>
                <option value="L" <?php echo (($_POST['jenis_kelamin'] ?? '') === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="P" <?php echo (($_POST['jenis_kelamin'] ?? '') === 'P') ? 'selected' : ''; ?>>Perempuan</option>
            </select>
            <?php if (isset($errors['jenis_kelamin'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['jenis_kelamin']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir *</label>
            <input type="text" name="tempat_lahir" value="<?php echo htmlspecialchars($_POST['tempat_lahir'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['tempat_lahir']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan tempat lahir">
            <?php if (isset($errors['tempat_lahir'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['tempat_lahir']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir *</label>
            <input type="date" name="tanggal_lahir" value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['tanggal_lahir']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <?php if (isset($errors['tanggal_lahir'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['tanggal_lahir']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status Hidup *</label>
            <select name="status_hidup"
                    class="w-full px-4 py-2 border <?php echo isset($errors['status_hidup']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih status hidup</option>
                <option value="Hidup" <?php echo (($_POST['status_hidup'] ?? '') === 'Hidup') ? 'selected' : ''; ?>>Hidup</option>
                <option value="Meninggal" <?php echo (($_POST['status_hidup'] ?? '') === 'Meninggal') ? 'selected' : ''; ?>>Meninggal</option>
            </select>
            <?php if (isset($errors['status_hidup'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['status_hidup']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan</label>
            <input type="text" name="pekerjaan" value="<?php echo htmlspecialchars($_POST['pekerjaan'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['pekerjaan']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan pekerjaan">
            <?php if (isset($errors['pekerjaan'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['pekerjaan']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pendidikan Terakhir</label>
            <select name="pendidikan_terakhir"
                    class="w-full px-4 py-2 border <?php echo isset($errors['pendidikan_terakhir']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <option value="">Pilih pendidikan terakhir</option>
                <option value="SD" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'SD') ? 'selected' : ''; ?>>SD</option>
                <option value="SMP" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'SMP') ? 'selected' : ''; ?>>SMP</option>
                <option value="SMA" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'SMA') ? 'selected' : ''; ?>>SMA</option>
                <option value="D1" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'D1') ? 'selected' : ''; ?>>D1</option>
                <option value="D2" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'D2') ? 'selected' : ''; ?>>D2</option>
                <option value="D3" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'D3') ? 'selected' : ''; ?>>D3</option>
                <option value="D4/S1" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'D4/S1') ? 'selected' : ''; ?>>D4/S1</option>
                <option value="S2" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'S2') ? 'selected' : ''; ?>>S2</option>
                <option value="S3" <?php echo (($_POST['pendidikan_terakhir'] ?? '') === 'S3') ? 'selected' : ''; ?>>S3</option>
            </select>
            <?php if (isset($errors['pendidikan_terakhir'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['pendidikan_terakhir']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Nikah</label>
            <input type="date" name="tanggal_nikah" value="<?php echo htmlspecialchars($_POST['tanggal_nikah'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['tanggal_nikah']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <?php if (isset($errors['tanggal_nikah'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['tanggal_nikah']; ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-500 mb-2">No KK</label>
            <input type="text" name="no_kk" value="<?php echo htmlspecialchars($_POST['no_kk'] ?? ''); ?>"
                   class="w-full px-4 py-2 border <?php echo isset($errors['no_kk']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   placeholder="Masukkan nomor kartu keluarga">
            <?php if (isset($errors['no_kk'])): ?>
                <p class="mt-1 text-sm text-red-600"><?php echo $errors['no_kk']; ?></p>
            <?php endif; ?>
        </div>

        <div class="md:col-span-2">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="status_tanggungan" name="status_tanggungan" type="checkbox" <?php echo (isset($_POST['status_tanggungan']) || !empty($_POST)) ? 'checked' : ''; ?>
                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="status_tanggungan" class="font-medium text-gray-700">Status Tanggungan</label>
                    <p class="text-gray-500">Centang jika anggota keluarga ini menjadi tanggungan</p>
                </div>
            </div>
        </div>

    </div>

    <div class="flex justify-end space-x-3">
        <a href="index.php?page=dosenRole_keluarga" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
            Batal
        </a>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
            Simpan Data
        </button>
    </div>
</form>