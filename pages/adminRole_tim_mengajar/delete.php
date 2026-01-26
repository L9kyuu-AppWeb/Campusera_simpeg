<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$dosenProdiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$dosenProdiId) {
    redirect('index.php?page=adminRole_tim_mengajar');
}

// Get dosen_prodi data
$stmt = $pdo->prepare("SELECT dp.*, pe.nama_lengkap as nama_dosen, p.nama_prodi FROM dosen_prodi dp JOIN dosen d ON dp.dosen_id = d.id JOIN pegawai pe ON d.pegawai_id = pe.id JOIN prodi p ON dp.prodi_id = p.id WHERE dp.id = ?");
$stmt->execute([$dosenProdiId]);
$dosen_prodi = $stmt->fetch();

if (!$dosen_prodi) {
    setAlert('error', 'Hubungan Dosen dan Prodi tidak ditemukan!');
    redirect('index.php?page=adminRole_tim_mengajar');
}

// Delete dosen_prodi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete the dosen_prodi from database
        $stmt = $pdo->prepare("DELETE FROM dosen_prodi WHERE id = ?");
        if ($stmt->execute([$dosenProdiId])) {
            logActivity($_SESSION['user_id'], 'delete_dosen_prodi', "Deleted dosen_prodi: " . $dosen_prodi['nama_dosen'] . " - " . $dosen_prodi['nama_prodi']);
            setAlert('success', 'Hubungan Dosen dan Prodi berhasil dihapus!');
        } else {
            setAlert('error', 'Gagal menghapus hubungan dosen dan prodi!');
        }
    } catch (Exception $e) {
        setAlert('error', 'Gagal menghapus hubungan dosen dan prodi: ' . $e->getMessage());
    }

    redirect('index.php?page=adminRole_tim_mengajar');
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_tim_mengajar" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Hapus Hubungan Dosen-Prodi</h1>
            <p class="text-gray-500 mt-1">Konfirmasi penghapusan hubungan dosen dan program studi</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-800">Konfirmasi Penghapusan</h3>
        <p class="mt-1 text-sm text-gray-500">Apakah Anda yakin ingin menghapus hubungan dosen dan prodi di bawah ini?</p>

        <div class="mt-6 bg-gray-50 p-4 rounded-xl">
            <h4 class="text-center mt-3 font-medium text-gray-800"><?php echo htmlspecialchars($dosen_prodi['nama_dosen']); ?></h4>
            <p class="text-center text-sm text-gray-500"><?php echo htmlspecialchars($dosen_prodi['nama_prodi']); ?></p>
            <p class="text-center text-sm text-gray-500"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dosen_prodi['status_hubungan']))); ?></p>
            <p class="text-center text-sm text-gray-500">Kaprodi: <?php echo $dosen_prodi['is_kaprodi'] ? 'Ya' : 'Tidak'; ?></p>
        </div>

        <form method="POST" class="mt-6 flex justify-center space-x-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Hapus
            </button>
            <a href="index.php?page=adminRole_tim_mengajar" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </form>
    </div>
</div>