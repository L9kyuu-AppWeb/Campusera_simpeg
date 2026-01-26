<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$unitKerjaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$unitKerjaId) {
    redirect('index.php?page=adminRole_unit_kerja');
}

// Get unit_kerja data
$stmt = $pdo->prepare("SELECT u.*, parent.nama_unit as parent_unit, p.nama_lengkap as kepala_unit_nama FROM unit_kerja u LEFT JOIN unit_kerja parent ON u.parent_id = parent.id LEFT JOIN pegawai p ON u.kepala_unit_id = p.id WHERE u.id = ?");
$stmt->execute([$unitKerjaId]);
$unit_kerja = $stmt->fetch();

if (!$unit_kerja) {
    setAlert('error', 'Unit Kerja tidak ditemukan!');
    redirect('index.php?page=adminRole_unit_kerja');
}

// Check if this unit has children
$childCount = $pdo->query("SELECT COUNT(*) FROM unit_kerja WHERE parent_id = $unitKerjaId")->fetchColumn();

// Delete unit_kerja
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Confirm that no children exist before deletion
    if ($childCount > 0) {
        setAlert('error', 'Tidak bisa menghapus unit kerja yang memiliki sub-unit!');
        redirect('index.php?page=adminRole_unit_kerja');
    } else {
        try {
            // Delete the unit_kerja from database
            $stmt = $pdo->prepare("DELETE FROM unit_kerja WHERE id = ?");
            if ($stmt->execute([$unitKerjaId])) {
                logActivity($_SESSION['user_id'], 'delete_unit_kerja', "Deleted unit kerja: " . $unit_kerja['nama_unit']);
                setAlert('success', 'Unit Kerja berhasil dihapus!');
            } else {
                setAlert('error', 'Gagal menghapus unit kerja!');
            }
        } catch (Exception $e) {
            setAlert('error', 'Gagal menghapus unit kerja: ' . $e->getMessage());
        }

        redirect('index.php?page=adminRole_unit_kerja');
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_unit_kerja" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Hapus Unit Kerja</h1>
            <p class="text-gray-500 mt-1">Konfirmasi penghapusan unit kerja</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-800">Konfirmasi Penghapusan</h3>
        
        <?php if ($childCount > 0): ?>
        <p class="mt-1 text-sm text-red-500">Unit ini memiliki <?php echo $childCount; ?> sub-unit. Tidak bisa dihapus sebelum sub-unit dihapus terlebih dahulu.</p>
        <div class="mt-6 bg-gray-50 p-4 rounded-xl">
            <h4 class="text-center mt-3 font-medium text-gray-800"><?php echo htmlspecialchars($unit_kerja['nama_unit']); ?></h4>
            <p class="text-center text-sm text-gray-500">Tipe: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $unit_kerja['tipe_unit']))); ?></p>
            <p class="text-center text-sm text-gray-500">Sub-unit: <?php echo $childCount; ?></p>
        </div>
        <div class="mt-6 flex justify-center">
            <a href="index.php?page=adminRole_unit_kerja" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Kembali
            </a>
        </div>
        <?php else: ?>
        <p class="mt-1 text-sm text-gray-500">Apakah Anda yakin ingin menghapus unit kerja di bawah ini?</p>
        
        <div class="mt-6 bg-gray-50 p-4 rounded-xl">
            <h4 class="text-center mt-3 font-medium text-gray-800"><?php echo htmlspecialchars($unit_kerja['nama_unit']); ?></h4>
            <p class="text-center text-sm text-gray-500">Tipe: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $unit_kerja['tipe_unit']))); ?></p>
            <p class="text-center text-sm text-gray-500">Parent: <?php echo htmlspecialchars($unit_kerja['parent_unit'] ?? '-'); ?></p>
            <p class="text-center text-sm text-gray-500">Kepala Unit: <?php echo htmlspecialchars($unit_kerja['kepala_unit_nama'] ?? '-'); ?></p>
        </div>

        <form method="POST" class="mt-6 flex justify-center space-x-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Hapus
            </button>
            <a href="index.php?page=adminRole_unit_kerja" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </form>
        <?php endif; ?>
    </div>
</div>