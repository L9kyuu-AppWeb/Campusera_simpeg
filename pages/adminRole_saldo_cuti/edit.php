<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('index.php?page=adminRole_saldo_cuti');
}

// Get saldo cuti data
$stmt = $pdo->prepare("SELECT sc.*, p.nama_lengkap
                      FROM saldo_cuti sc
                      LEFT JOIN pegawai p ON sc.pegawai_id = p.id
                      WHERE sc.id = ?");
$stmt->execute([$id]);
$saldo_cuti = $stmt->fetch();

if (!$saldo_cuti) {
    redirect('index.php?page=adminRole_saldo_cuti');
}

// Get available years
$currentYear = date('Y');
$availableYears = [];
for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
    $availableYears[] = $i;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_cuti = (int)$_POST['total_cuti'];
    $sisa_cuti = (int)$_POST['sisa_cuti'];
    
    // Validate input
    if ($total_cuti < 0 || $sisa_cuti < 0 || $sisa_cuti > $total_cuti) {
        $error = "Jumlah cuti tidak valid. Sisa cuti tidak boleh lebih besar dari total cuti.";
    } else {
        // Update saldo cuti and set sumber to custom
        $sumber = 'custom';
        
        $stmt = $pdo->prepare("UPDATE saldo_cuti SET total_cuti = ?, sisa_cuti = ?, sumber = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$total_cuti, $sisa_cuti, $sumber, $id]);

        if ($result) {
            $_SESSION['success_message'] = "Saldo cuti berhasil diperbarui.";
            redirect('index.php?page=adminRole_saldo_cuti');
        } else {
            $error = "Terjadi kesalahan saat menyimpan data.";
        }
    }
} else {
    // Pre-populate form with existing data
    $total_cuti = $saldo_cuti['total_cuti'];
    $sisa_cuti = $saldo_cuti['sisa_cuti'];
}
?>

<div class="mb-6">
    <a href="index.php?page=adminRole_saldo_cuti" class="text-gray-600 hover:text-gray-800 flex items-center">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
        </svg>
        Kembali ke Daftar Saldo Cuti
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Edit Saldo Cuti</h1>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai</label>
                <div class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50">
                    <?php echo htmlspecialchars($saldo_cuti['nama_lengkap']); ?>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <div class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50">
                    <?php echo $saldo_cuti['tahun']; ?>
                </div>
            </div>

            <div>
                <label for="total_cuti" class="block text-sm font-medium text-gray-700 mb-1">Total Cuti (hari) *</label>
                <input type="number" name="total_cuti" id="total_cuti" 
                       value="<?php echo isset($total_cuti) ? htmlspecialchars($total_cuti) : ''; ?>"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       required>
            </div>

            <div>
                <label for="sisa_cuti" class="block text-sm font-medium text-gray-700 mb-1">Sisa Cuti (hari) *</label>
                <input type="number" name="sisa_cuti" id="sisa_cuti" 
                       value="<?php echo isset($sisa_cuti) ? htmlspecialchars($sisa_cuti) : ''; ?>"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       required>
                <p class="mt-1 text-sm text-gray-500">Sisa cuti tidak boleh lebih besar dari total cuti.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sumber</label>
                <div class="px-4 py-2 border border-gray-200 rounded-xl bg-gray-50">
                    <?php echo ucfirst($saldo_cuti['sumber']); ?>
                </div>
                <p class="mt-1 text-sm text-gray-500">Catatan: Mengubah data akan mengubah sumber menjadi "custom"</p>
            </div>

            <div class="flex justify-end pt-4">
                <a href="index.php?page=adminRole_saldo_cuti" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>