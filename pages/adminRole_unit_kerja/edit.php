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
$stmt = $pdo->prepare("SELECT * FROM unit_kerja WHERE id = ?");
$stmt->execute([$unitKerjaId]);
$unit_kerja = $stmt->fetch();

if (!$unit_kerja) {
    setAlert('error', 'Unit Kerja tidak ditemukan!');
    redirect('index.php?page=adminRole_unit_kerja');
}

$error = '';

// Get all unit_kerja for parent selection (excluding the one being edited) and all pegawai for kepala unit
$unitKerjaList = $pdo->query("SELECT id, nama_unit, tipe_unit FROM unit_kerja WHERE id != $unitKerjaId ORDER BY nama_unit ASC")->fetchAll();
$pegawaiList = $pdo->query("SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_unit = cleanInput($_POST['nama_unit']);
    $tipe_unit = cleanInput($_POST['tipe_unit']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $kepala_unit_id = !empty($_POST['kepala_unit_id']) ? (int)$_POST['kepala_unit_id'] : null;

    // Validation
    if (empty($nama_unit) || empty($tipe_unit)) {
        $error = 'Nama unit dan tipe unit harus diisi!';
    } else {
        // Check if nama_unit already exists for other records
        $stmt = $pdo->prepare("SELECT id FROM unit_kerja WHERE nama_unit = ? AND id != ?");
        $stmt->execute([$nama_unit, $unitKerjaId]);

        if ($stmt->fetch()) {
            $error = 'Nama unit sudah digunakan!';
        } else {
            // Update unit_kerja in database
            $stmt = $pdo->prepare("
                UPDATE unit_kerja
                SET nama_unit = ?, tipe_unit = ?, parent_id = ?, kepala_unit_id = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$nama_unit, $tipe_unit, $parent_id, $kepala_unit_id, $unitKerjaId])) {
                logActivity($_SESSION['user_id'], 'update_unit_kerja', "Updated unit kerja: $nama_unit");
                setAlert('success', 'Unit kerja berhasil diperbarui!');
                redirect('index.php?page=adminRole_unit_kerja');
            } else {
                $error = 'Gagal memperbarui unit kerja!';
            }
        }
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
            <h1 class="text-3xl font-bold text-gray-800">Edit Unit Kerja</h1>
            <p class="text-gray-500 mt-1">Perbarui informasi unit kerja</p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Unit *</label>
                <input type="text" name="nama_unit" required
                       value="<?php echo isset($_POST['nama_unit']) ? htmlspecialchars($_POST['nama_unit']) : htmlspecialchars($unit_kerja['nama_unit']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Unit *</label>
                <select name="tipe_unit" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Tipe</option>
                    <option value="fakultas" <?php echo (isset($_POST['tipe_unit']) ? $_POST['tipe_unit'] : $unit_kerja['tipe_unit']) === 'fakultas' ? 'selected' : ''; ?>>Fakultas</option>
                    <option value="prodi" <?php echo (isset($_POST['tipe_unit']) ? $_POST['tipe_unit'] : $unit_kerja['tipe_unit']) === 'prodi' ? 'selected' : ''; ?>>Prodi</option>
                    <option value="biro" <?php echo (isset($_POST['tipe_unit']) ? $_POST['tipe_unit'] : $unit_kerja['tipe_unit']) === 'biro' ? 'selected' : ''; ?>>Biro</option>
                    <option value="pusat" <?php echo (isset($_POST['tipe_unit']) ? $_POST['tipe_unit'] : $unit_kerja['tipe_unit']) === 'pusat' ? 'selected' : ''; ?>>Pusat</option>
                    <option value="lembaga" <?php echo (isset($_POST['tipe_unit']) ? $_POST['tipe_unit'] : $unit_kerja['tipe_unit']) === 'lembaga' ? 'selected' : ''; ?>>Lembaga</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Parent Unit</label>
                <select name="parent_id"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Parent Unit</option>
                    <?php foreach ($unitKerjaList as $unit): ?>
                        <option value="<?php echo $unit['id']; ?>"
                                <?php echo (isset($_POST['parent_id']) ? $_POST['parent_id'] : $unit_kerja['parent_id']) == $unit['id'] ? 'selected' : ''; ?>>
                            [<?php echo ucfirst($unit['tipe_unit']); ?>] <?php echo htmlspecialchars($unit['nama_unit']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kepala Unit</label>
                <select name="kepala_unit_id"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Kepala Unit</option>
                    <?php foreach ($pegawaiList as $pegawai): ?>
                        <option value="<?php echo $pegawai['id']; ?>"
                                <?php echo (isset($_POST['kepala_unit_id']) ? $_POST['kepala_unit_id'] : $unit_kerja['kepala_unit_id']) == $pegawai['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Update Unit Kerja
            </button>
            <a href="index.php?page=adminRole_unit_kerja" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>
</div>