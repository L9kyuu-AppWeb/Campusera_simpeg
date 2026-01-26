<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$riwayatId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$riwayatId) {
    redirect('index.php?page=adminRole_pegawai_riwayat');
}

// Get riwayat data
$stmt = $pdo->prepare("SELECT rk.*, p.nama_lengkap FROM riwayat_kepegawaian rk JOIN pegawai p ON rk.pegawai_id = p.id WHERE rk.id = ?");
$stmt->execute([$riwayatId]);
$riwayat = $stmt->fetch();

if (!$riwayat) {
    setAlert('error', 'Riwayat Kepegawaian tidak ditemukan!');
    redirect('index.php?page=adminRole_pegawai_riwayat');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keterangan = !empty($_POST['keterangan']) ? cleanInput($_POST['keterangan']) : null;

    // Update keterangan in database
    $stmt = $pdo->prepare("UPDATE riwayat_kepegawaian SET keterangan = ? WHERE id = ?");
    
    if ($stmt->execute([$keterangan, $riwayatId])) {
        logActivity($_SESSION['user_id'], 'update_keterangan_riwayat', "Updated keterangan for riwayat_kepegawaian ID: $riwayatId");
        setAlert('success', 'Keterangan berhasil diperbarui!');
        redirect('index.php?page=adminRole_pegawai_riwayat');
    } else {
        $error = 'Gagal memperbarui keterangan!';
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_pegawai_riwayat" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Keterangan Riwayat Kepegawaian</h1>
            <p class="text-gray-500 mt-1">Perbarui keterangan untuk <?php echo htmlspecialchars($riwayat['nama_lengkap']); ?></p>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pegawai</label>
            <input type="text" value="<?php echo htmlspecialchars($riwayat['nama_lengkap']); ?>" readonly
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Perubahan</label>
            <input type="text" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $riwayat['jenis_perubahan']))); ?>" readonly
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Efektif</label>
            <input type="date" value="<?php echo htmlspecialchars($riwayat['tanggal_efektif']); ?>" readonly
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Dokumen Pendukung</label>
            <input type="text" value="<?php echo htmlspecialchars($riwayat['nomor_sk'] ?? '-'); ?>" readonly
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl bg-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan *</label>
            <textarea name="keterangan" rows="4" required
                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : htmlspecialchars($riwayat['keterangan'] ?? ''); ?></textarea>
            <p class="text-xs text-gray-500 mt-1">Keterangan spesifik untuk pegawai ini</p>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Perbarui Keterangan
            </button>
            <a href="index.php?page=adminRole_pegawai_riwayat" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>