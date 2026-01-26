<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get pegawai data for dropdown
$pegawai_list = $pdo->query("
    SELECT p.id, p.nama_lengkap
    FROM pegawai p
    ORDER BY p.nama_lengkap
")->fetchAll();

// Get available years
$currentYear = date('Y');
$availableYears = [];
for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
    $availableYears[] = $i;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pegawai_id = (int)$_POST['pegawai_id'];
    $tahun = (int)$_POST['tahun'];
    $total_cuti = (int)$_POST['total_cuti'];
    
    // Validate input
    if (empty($pegawai_id) || empty($tahun) || $total_cuti < 0) {
        $error = "Pegawai, tahun dan jumlah cuti wajib diisi dengan benar.";
    } else {
        // Check if saldo cuti already exists for this employee and year
        $stmt = $pdo->prepare("SELECT id FROM saldo_cuti WHERE pegawai_id = ? AND tahun = ?");
        $stmt->execute([$pegawai_id, $tahun]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $error = "Saldo cuti untuk pegawai ini pada tahun tersebut sudah ada.";
        } else {
            // Insert new saldo cuti with sisa_cuti equal to total_cuti
            $sisa_cuti = $total_cuti;
            $sumber = 'custom';
            
            $stmt = $pdo->prepare("INSERT INTO saldo_cuti (pegawai_id, tahun, total_cuti, sisa_cuti, sumber) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([$pegawai_id, $tahun, $total_cuti, $sisa_cuti, $sumber]);

            if ($result) {
                $_SESSION['success_message'] = "Saldo cuti berhasil ditambahkan.";
                redirect('index.php?page=adminRole_saldo_cuti');
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
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
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Tambah Saldo Cuti Manual</h1>
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
                <label for="pegawai_id" class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai *</label>
                <select name="pegawai_id" id="pegawai_id" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        required>
                    <option value="">Pilih Pegawai</option>
                    <?php foreach ($pegawai_list as $pegawai): ?>
                        <option value="<?php echo $pegawai['id']; ?>"
                            <?php echo (isset($_POST['pegawai_id']) && $_POST['pegawai_id'] == $pegawai['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

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

            <div>
                <label for="total_cuti" class="block text-sm font-medium text-gray-700 mb-1">Total Cuti (hari) *</label>
                <input type="number" name="total_cuti" id="total_cuti" 
                       value="<?php echo isset($_POST['total_cuti']) ? htmlspecialchars($_POST['total_cuti']) : ''; ?>"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       required>
                <p class="mt-1 text-sm text-gray-500">Sisa cuti akan otomatis disetel sama dengan total cuti.</p>
            </div>

            <div class="flex justify-end pt-4">
                <a href="index.php?page=adminRole_saldo_cuti" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</div>