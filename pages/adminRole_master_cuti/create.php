<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get roles data for dropdown
$roles_list = $pdo->query("SELECT id, role_name FROM roles ORDER BY role_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_id = (int)$_POST['role_id'];
    $total_cuti = (int)$_POST['total_cuti'];
    
    // Validate input
    if (empty($role_id) || $total_cuti < 0) {
        $error = "Role dan jumlah cuti wajib diisi dengan benar.";
    } else {
        // Check if role already has master cuti entry
        $stmt = $pdo->prepare("SELECT id FROM master_cuti WHERE role_id = ?");
        $stmt->execute([$role_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $error = "Role ini sudah memiliki pengaturan master cuti.";
        } else {
            // Insert new master cuti
            $stmt = $pdo->prepare("INSERT INTO master_cuti (role_id, total_cuti) VALUES (?, ?)");
            $result = $stmt->execute([$role_id, $total_cuti]);

            if ($result) {
                $_SESSION['success_message'] = "Master cuti berhasil ditambahkan.";
                redirect('index.php?page=adminRole_master_cuti');
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
            }
        }
    }
}
?>

<div class="mb-6">
    <a href="index.php?page=adminRole_master_cuti" class="text-gray-600 hover:text-gray-800 flex items-center">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
        </svg>
        Kembali ke Daftar Master Cuti
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Tambah Master Cuti Baru</h1>
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
                <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                <select name="role_id" id="role_id" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        required>
                    <option value="">Pilih Role</option>
                    <?php foreach ($roles_list as $role): ?>
                        <option value="<?php echo $role['id']; ?>" 
                            <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="total_cuti" class="block text-sm font-medium text-gray-700 mb-1">Total Cuti (hari) *</label>
                <input type="number" name="total_cuti" id="total_cuti" 
                       value="<?php echo isset($_POST['total_cuti']) ? htmlspecialchars($_POST['total_cuti']) : '12'; ?>"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       required>
            </div>

            <div class="flex justify-end pt-4">
                <a href="index.php?page=adminRole_master_cuti" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</div>