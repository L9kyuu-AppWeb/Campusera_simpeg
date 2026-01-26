<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$pegawaiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pegawaiId) {
    redirect('index.php?page=adminRole_pegawai');
}

// Get pegawai data
$stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->execute([$pegawaiId]);
$pegawai = $stmt->fetch();

if (!$pegawai) {
    setAlert('error', 'Pegawai tidak ditemukan!');
    redirect('index.php?page=adminRole_pegawai');
}

// Delete pegawai
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete image file if exists
        if (!empty($pegawai['foto'])) {
            deleteImage($pegawai['foto'], 'pegawai');
        }

        // Delete the pegawai from database
        $stmt = $pdo->prepare("DELETE FROM pegawai WHERE id = ?");
        if ($stmt->execute([$pegawaiId])) {
            logActivity($_SESSION['user_id'], 'delete_pegawai', "Deleted pegawai: " . $pegawai['nama_lengkap']);
            setAlert('success', 'Pegawai berhasil dihapus!');
        } else {
            setAlert('error', 'Gagal menghapus pegawai!');
        }
    } catch (Exception $e) {
        setAlert('error', 'Gagal menghapus pegawai: ' . $e->getMessage());
    }

    redirect('index.php?page=adminRole_pegawai');
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_pegawai" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Hapus Pegawai</h1>
            <p class="text-gray-500 mt-1">Konfirmasi penghapusan pegawai</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-800">Konfirmasi Penghapusan</h3>
        <p class="mt-1 text-sm text-gray-500">Apakah Anda yakin ingin menghapus pegawai di bawah ini?</p>

        <div class="mt-6 bg-gray-50 p-4 rounded-xl">
            <?php if (!empty($pegawai['foto'])): ?>
            <div class="flex justify-center">
                <img src="<?php echo getImageUrl($pegawai['foto'], 'pegawai'); ?>" alt="<?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>" class="w-24 h-24 object-cover rounded border">
            </div>
            <?php endif; ?>
            <h4 class="text-center mt-3 font-medium text-gray-800"><?php echo htmlspecialchars($pegawai['nama_lengkap']); ?></h4>
            <p class="text-center text-sm text-gray-500"><?php echo htmlspecialchars($pegawai['email']); ?></p>
            <p class="text-center text-sm text-gray-500">
                <?php echo htmlspecialchars($pegawai['tipe_pegawai'] === 'dosen_luar' ? 'Dosen Luar' : (strpos($pegawai['tipe_pegawai'], 'dosen') !== false ? 'Dosen Tetap' : 'Tendik')); ?>
                | 
                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pegawai['status_aktif']))); ?>
            </p>
        </div>

        <form method="POST" class="mt-6 flex justify-center space-x-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Hapus
            </button>
            <a href="index.php?page=adminRole_pegawai" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </form>
    </div>
</div>