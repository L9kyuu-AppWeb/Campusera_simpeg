<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$riwayatId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$riwayatId) {
    redirect('index.php?page=riwayat_kepegawaian');
}

// Get riwayat data
$stmt = $pdo->prepare("SELECT rk.*, p.nama_lengkap, ds.dokumen_sk FROM riwayat_kepegawaian rk JOIN pegawai p ON rk.pegawai_id = p.id LEFT JOIN dokumen_sk ds ON rk.dokumen_sk_id = ds.id WHERE rk.id = ?");
$stmt->execute([$riwayatId]);
$riwayat = $stmt->fetch();

if (!$riwayat) {
    setAlert('error', 'Riwayat Kepegawaian tidak ditemukan!');
    redirect('index.php?page=riwayat_kepegawaian');
}

// Delete riwayat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete document file if exists
        if (isset($riwayat['dokumen_sk']) && !empty($riwayat['dokumen_sk'])) {
            deleteDocument($riwayat['dokumen_sk'], 'riwayat');
        }

        $pdo->beginTransaction(); // Begin transaction for data consistency

        // Delete the main record in riwayat_kepegawaian
        $stmt = $pdo->prepare("DELETE FROM riwayat_kepegawaian WHERE id = ?");
        if (!$stmt->execute([$riwayatId])) {
            throw new Exception('Gagal menghapus riwayat kepegawaian!');
        }

        // Delete the corresponding relationship in dokumen_sk_pegawai if exists
        if (isset($riwayat['dokumen_sk_id']) && $riwayat['dokumen_sk_id']) {
            $stmt_rel = $pdo->prepare("DELETE FROM dokumen_sk_pegawai WHERE dokumen_sk_id = ? AND pegawai_id = ?");
            if (!$stmt_rel->execute([$riwayat['dokumen_sk_id'], $riwayat['pegawai_id']])) {
                throw new Exception('Gagal menghapus relasi dokumen SK dan pegawai!');
            }

            // Check if there are other relationships to this dokumen_sk
            // If not, also remove the dokumen_sk entry to keep database clean
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM dokumen_sk_pegawai WHERE dokumen_sk_id = ?");
            $stmt_count->execute([$riwayat['dokumen_sk_id']]);
            $relCount = $stmt_count->fetchColumn();

            // If no more relationships to this dokumen_sk, remove the dokumen_sk entry too
            if ($relCount == 0) {
                $stmt_doc = $pdo->prepare("DELETE FROM dokumen_sk WHERE id = ?");
                if (!$stmt_doc->execute([$riwayat['dokumen_sk_id']])) {
                    throw new Exception('Gagal menghapus dokumen SK!');
                }
            }
        }

        $pdo->commit(); // Commit the transaction
        logActivity($_SESSION['user_id'], 'delete_riwayat_kepegawaian', "Deleted riwayat_kepegawaian for: " . $riwayat['nama_lengkap']);
        setAlert('success', 'Riwayat Kepegawaian berhasil dihapus!');
    } catch (Exception $e) {
        setAlert('error', 'Gagal menghapus riwayat kepegawaian: ' . $e->getMessage());
    }

    redirect('index.php?page=riwayat_kepegawaian');
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=riwayat_kepegawaian" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Hapus Riwayat Kepegawaian</h1>
            <p class="text-gray-500 mt-1">Konfirmasi penghapusan riwayat kepegawaian</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-800">Konfirmasi Penghapusan</h3>
        <p class="mt-1 text-sm text-gray-500">Apakah Anda yakin ingin menghapus riwayat kepegawaian di bawah ini?</p>

        <div class="mt-6 bg-gray-50 p-4 rounded-xl">
            <h4 class="text-center mt-3 font-medium text-gray-800"><?php echo htmlspecialchars($riwayat['nama_lengkap']); ?></h4>
            <p class="text-center text-sm text-gray-500">Jenis Perubahan: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $riwayat['jenis_perubahan']))); ?></p>
            <p class="text-center text-sm text-gray-500">Tanggal Efektif: <?php echo htmlspecialchars(date('d M Y', strtotime($riwayat['tanggal_efektif']))); ?></p>
            <p class="text-center text-sm text-gray-500">Keterangan: <?php echo htmlspecialchars($riwayat['keterangan'] ?? '-'); ?></p>
            <?php if (isset($riwayat['dokumen_sk']) && !empty($riwayat['dokumen_sk'])): ?>
            <p class="text-center text-sm text-gray-500">Dokumen SK: <?php echo htmlspecialchars($riwayat['dokumen_sk']); ?></p>
            <?php endif; ?>
        </div>

        <form method="POST" class="mt-6 flex justify-center space-x-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Hapus
            </button>
            <a href="index.php?page=riwayat_kepegawaian" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </form>
    </div>
</div>