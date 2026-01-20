<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('index.php?page=jenis_izin');
}

// Get jenis izin data
$stmt = $pdo->prepare("SELECT * FROM jenis_izin WHERE id_jenis_izin = ?");
$stmt->execute([$id]);
$jenis_izin = $stmt->fetch();

if (!$jenis_izin) {
    redirect('index.php?page=jenis_izin');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_izin = cleanInput($_POST['nama_izin']);
    $keterangan = cleanInput($_POST['keterangan']);
    $is_potong_cuti = isset($_POST['is_potong_cuti']) ? 1 : 0;

    // Validate input
    if (empty($nama_izin)) {
        $error = "Nama izin wajib diisi.";
    } else {
        // Check if jenis izin with same name already exists for other records
        $stmt = $pdo->prepare("SELECT id_jenis_izin FROM jenis_izin WHERE nama_izin = ? AND id_jenis_izin != ?");
        $stmt->execute([$nama_izin, $id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $error = "Jenis izin dengan nama tersebut sudah ada.";
        } else {
            // Update jenis izin
            $stmt = $pdo->prepare("UPDATE jenis_izin SET nama_izin = ?, keterangan = ?, is_potong_cuti = ? WHERE id_jenis_izin = ?");
            $result = $stmt->execute([$nama_izin, $keterangan, $is_potong_cuti, $id]);

            if ($result) {
                $_SESSION['success_message'] = "Jenis izin berhasil diperbarui.";
                redirect('index.php?page=jenis_izin');
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
            }
        }
    }
} else {
    // Pre-populate form with existing data
    $nama_izin = $jenis_izin['nama_izin'];
    $keterangan = $jenis_izin['keterangan'];
    $is_potong_cuti = $jenis_izin['is_potong_cuti'];
}
?>

<div class="mb-6">
    <a href="index.php?page=jenis_izin" class="text-gray-600 hover:text-gray-800 flex items-center">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
        </svg>
        Kembali ke Daftar Jenis Izin
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Edit Jenis Izin</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 max-w-2xl">
    <?php if (isset($error)): ?>
        <div class="mb-6 bg-red-50 text-red-700 px-4 py-3 rounded-xl">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="space-y-6">
            <div>
                <label for="nama_izin" class="block text-sm font-medium text-gray-700 mb-1">Nama Izin *</label>
                <input type="text" name="nama_izin" id="nama_izin" value="<?php echo isset($nama_izin) ? htmlspecialchars($nama_izin) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       required>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="4"
                          class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo isset($keterangan) ? htmlspecialchars($keterangan) : ''; ?></textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_potong_cuti" id="is_potong_cuti" value="1"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                       <?php echo isset($is_potong_cuti) && $is_potong_cuti ? 'checked' : ''; ?>>
                <label for="is_potong_cuti" class="ml-2 block text-sm text-gray-700">
                    Potong Cuti
                </label>
            </div>

            <div class="flex justify-end pt-4">
                <a href="index.php?page=jenis_izin" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>