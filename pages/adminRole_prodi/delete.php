<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$prodiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$prodiId) {
    redirect('index.php?page=adminRole_prodi');
}

// Get prodi data
$stmt = $pdo->prepare("SELECT p.*, f.nama_fakultas, pe.nama_lengkap as nama_kaprodi FROM prodi p LEFT JOIN fakultas f ON p.fakultas_id = f.id LEFT JOIN dosen d ON p.kaprodi_id = d.id LEFT JOIN pegawai pe ON d.pegawai_id = pe.id WHERE p.id = ?");
$stmt->execute([$prodiId]);
$prodi = $stmt->fetch();

if (!$prodi) {
    setAlert('error', 'Program Studi tidak ditemukan!');
    redirect('index.php?page=adminRole_prodi');
}

// Delete prodi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete the prodi from database
        $stmt = $pdo->prepare("DELETE FROM prodi WHERE id = ?");
        if ($stmt->execute([$prodiId])) {
            logActivity($_SESSION['user_id'], 'delete_prodi', "Deleted prodi: " . $prodi['nama_prodi']);
            setAlert('success', 'Program Studi berhasil dihapus!');
        } else {
            setAlert('error', 'Gagal menghapus program studi!');
        }
    } catch (Exception $e) {
        setAlert('error', 'Gagal menghapus program studi: ' . $e->getMessage());
    }

    redirect('index.php?page=adminRole_prodi');
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_prodi" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Hapus Program Studi</h1>
            <p class="text-gray-500 mt-1">Konfirmasi penghapusan program studi</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-800">Konfirmasi Penghapusan</h3>
        <p class="mt-1 text-sm text-gray-500">Apakah Anda yakin ingin menghapus program studi di bawah ini?</p>

        <div class="mt-6 bg-gray-50 p-4 rounded-xl">
            <h4 class="text-center mt-3 font-medium text-gray-800"><?php echo htmlspecialchars($prodi['nama_prodi']); ?></h4>
            <p class="text-center text-sm text-gray-500">Kode: <?php echo htmlspecialchars($prodi['kode_prodi']); ?></p>
            <p class="text-center text-sm text-gray-500">Fakultas: <?php echo htmlspecialchars($prodi['nama_fakultas']); ?></p>
            <p class="text-center text-sm text-gray-500">Kaprodi: <?php echo htmlspecialchars($prodi['nama_kaprodi'] ?? 'Belum ditentukan'); ?></p>
            <p class="text-center text-sm text-gray-500">Jenjang: <?php echo htmlspecialchars($prodi['jenjang']); ?></p>
            <p class="text-center text-sm text-gray-500">Status: <?php echo $prodi['status_aktif'] ? 'Aktif' : 'Tidak Aktif'; ?></p>
        </div>

        <form method="POST" class="mt-6 flex justify-center space-x-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Hapus
            </button>
            <a href="index.php?page=adminRole_prodi" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </form>
    </div>
</div>