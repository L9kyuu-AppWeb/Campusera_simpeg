<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('index.php?page=adminRole_jenis_izin');
}

// Get jenis izin data
$stmt = $pdo->prepare("SELECT * FROM jenis_izin WHERE id_jenis_izin = ?");
$stmt->execute([$id]);
$jenis_izin = $stmt->fetch();

if (!$jenis_izin) {
    redirect('index.php?page=adminRole_jenis_izin');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete jenis izin
    $stmt = $pdo->prepare("DELETE FROM jenis_izin WHERE id_jenis_izin = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        $_SESSION['success_message'] = "Jenis izin berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat menghapus data.";
    }
    
    redirect('index.php?page=adminRole_jenis_izin');
}
?>

<div class="mb-6">
    <a href="index.php?page=adminRole_jenis_izin" class="text-gray-600 hover:text-gray-800 flex items-center">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
        </svg>
        Kembali ke Daftar Jenis Izin
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Hapus Jenis Izin</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 max-w-2xl">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    Anda yakin ingin menghapus jenis izin <strong>"<?php echo htmlspecialchars($jenis_izin['nama_izin']); ?>"</strong>? 
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="bg-gray-50 rounded-xl p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Nama Izin</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($jenis_izin['nama_izin']); ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Potong Cuti</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo $jenis_izin['is_potong_cuti'] ? 'Ya' : 'Tidak'; ?></p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-500">Keterangan</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($jenis_izin['keterangan']); ?></p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="index.php?page=adminRole_jenis_izin" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                Batal
            </a>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Hapus Permanen
            </button>
        </div>
    </form>
</div>