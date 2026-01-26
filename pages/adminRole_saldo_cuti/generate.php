<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get available years
$currentYear = date('Y');
$availableYears = [];
for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
    $availableYears[] = $i;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun = (int)$_POST['tahun'];
    
    // Validate input
    if (empty($tahun)) {
        $error = "Tahun wajib dipilih.";
    } else {
        // Get all employees
        $pegawai_stmt = $pdo->query("
            SELECT p.id as pegawai_id
            FROM pegawai p
        ");
        $pegawai_list = $pegawai_stmt->fetchAll();
        
        $success_count = 0;
        $skip_count = 0;
        
        foreach ($pegawai_list as $pegawai) {
            // Check if saldo cuti already exists for this employee and year
            $check_stmt = $pdo->prepare("SELECT id FROM saldo_cuti WHERE pegawai_id = ? AND tahun = ?");
            $check_stmt->execute([$pegawai['pegawai_id'], $tahun]);
            $existing = $check_stmt->fetch();

            if ($existing) {
                // Skip if already exists
                $skip_count++;
                continue;
            }

            // Since we can't determine role from the database structure, use default value
            $total_cuti = 12; // Default allocation

            // Insert new saldo cuti with default values
            $sisa_cuti = $total_cuti;
            $sumber = 'default';

            $insert_stmt = $pdo->prepare("INSERT INTO saldo_cuti (pegawai_id, tahun, total_cuti, sisa_cuti, sumber) VALUES (?, ?, ?, ?, ?)");
            $result = $insert_stmt->execute([$pegawai['pegawai_id'], $tahun, $total_cuti, $sisa_cuti, $sumber]);

            if ($result) {
                $success_count++;
            }
        }
        
        if ($success_count > 0) {
            $_SESSION['success_message'] = "Berhasil generate saldo cuti untuk {$success_count} pegawai pada tahun {$tahun}. {$skip_count} pegawai dilewati karena sudah memiliki saldo cuti.";
        } else {
            if ($skip_count > 0) {
                $_SESSION['success_message'] = "Semua pegawai sudah memiliki saldo cuti untuk tahun {$tahun}. Tidak ada saldo cuti baru yang ditambahkan.";
            } else {
                $_SESSION['success_message'] = "Tidak ada pegawai yang ditemukan untuk tahun {$tahun}.";
            }
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php?page=adminRole_saldo_cuti" class="text-gray-600 hover:text-gray-800 flex items-center">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
        </svg>
        Kembali ke Daftar Saldo Cuti
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Generate Saldo Cuti</h1>
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
                <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun *</label>
                <select name="tahun" id="tahun" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        required>
                    <option value="">Pilih Tahun</option>
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?php echo $year; ?>" 
                            <?php echo (isset($_POST['tahun']) && $_POST['tahun'] == $year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <h3 class="text-sm font-medium text-blue-800 mb-2">Proses Generate Saldo Cuti</h3>
                <ul class="text-sm text-blue-700 list-disc pl-5 space-y-1">
                    <li>Sistem akan mengecek semua pegawai yang belum memiliki saldo cuti untuk tahun yang dipilih</li>
                    <li>Untuk setiap pegawai yang belum memiliki saldo cuti, sistem akan memberikan alokasi default 12 hari</li>
                    <li>Total cuti dan sisa cuti akan disetel sama besarnya</li>
                    <li>Sumber akan disetel sebagai "default"</li>
                </ul>
            </div>

            <div class="flex justify-end pt-4">
                <a href="index.php?page=adminRole_saldo_cuti" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                    Batal
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                    Generate Saldo Cuti
                </button>
            </div>
        </div>
    </form>
</div>