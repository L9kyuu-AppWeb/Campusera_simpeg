<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$dosenProdiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$dosenProdiId) {
    redirect('index.php?page=dosen_prodi');
}

// Get dosen_prodi data
$stmt = $pdo->prepare("SELECT * FROM dosen_prodi WHERE id = ?");
$stmt->execute([$dosenProdiId]);
$dosen_prodi = $stmt->fetch();

if (!$dosen_prodi) {
    setAlert('error', 'Hubungan Dosen dan Prodi tidak ditemukan!');
    redirect('index.php?page=dosen_prodi');
}

$error = '';

// Get all dosen and prodi for dropdown
$dosenList = $pdo->query("SELECT d.id, p.nama_lengkap FROM dosen d JOIN pegawai p ON d.pegawai_id = p.id ORDER BY p.nama_lengkap ASC")->fetchAll();
$prodiList = $pdo->query("SELECT id, nama_prodi FROM prodi ORDER BY nama_prodi ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dosen_id = (int)$_POST['dosen_id'];
    $prodi_id = (int)$_POST['prodi_id'];
    $status_hubungan = cleanInput($_POST['status_hubungan']);
    $is_kaprodi = isset($_POST['is_kaprodi']) ? 1 : 0;
    $tanggal_mulai = cleanInput($_POST['tanggal_mulai']);
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? cleanInput($_POST['tanggal_selesai']) : null;

    // Validation
    if (empty($dosen_id) || empty($prodi_id) || empty($status_hubungan) || empty($tanggal_mulai)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } else {
        // Check if this combination already exists for other records
        $stmt = $pdo->prepare("SELECT id FROM dosen_prodi WHERE dosen_id = ? AND prodi_id = ? AND status_hubungan = ? AND id != ?");
        $stmt->execute([$dosen_id, $prodi_id, $status_hubungan, $dosenProdiId]);

        if ($stmt->fetch()) {
            $error = 'Hubungan dosen dan prodi dengan status ini sudah terdaftar!';
        } else {
            // Update dosen_prodi in database
            $stmt = $pdo->prepare("
                UPDATE dosen_prodi
                SET dosen_id = ?, prodi_id = ?, status_hubungan = ?, is_kaprodi = ?, tanggal_mulai = ?, tanggal_selesai = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$dosen_id, $prodi_id, $status_hubungan, $is_kaprodi, $tanggal_mulai, $tanggal_selesai, $dosenProdiId])) {
                logActivity($_SESSION['user_id'], 'update_dosen_prodi', "Updated dosen_prodi: ID $dosenProdiId");
                setAlert('success', 'Hubungan dosen dan prodi berhasil diperbarui!');
                redirect('index.php?page=dosen_prodi');
            } else {
                $error = 'Gagal memperbarui hubungan dosen dan prodi!';
            }
        }
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=dosen_prodi" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Hubungan Dosen-Prodi</h1>
            <p class="text-gray-500 mt-1">Perbarui informasi hubungan dosen dan program studi</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Dosen *</label>
                <select name="dosen_id" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Dosen</option>
                    <?php foreach ($dosenList as $dosen): ?>
                        <option value="<?php echo $dosen['id']; ?>"
                                <?php echo (isset($_POST['dosen_id']) ? $_POST['dosen_id'] : $dosen_prodi['dosen_id']) == $dosen['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dosen['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi *</label>
                <select name="prodi_id" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Program Studi</option>
                    <?php foreach ($prodiList as $prodi): ?>
                        <option value="<?php echo $prodi['id']; ?>"
                                <?php echo (isset($_POST['prodi_id']) ? $_POST['prodi_id'] : $dosen_prodi['prodi_id']) == $prodi['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prodi['nama_prodi']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Hubungan *</label>
                <select name="status_hubungan" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Status</option>
                    <option value="homebase" <?php echo (isset($_POST['status_hubungan']) ? $_POST['status_hubungan'] : $dosen_prodi['status_hubungan']) === 'homebase' ? 'selected' : ''; ?>>Homebase</option>
                    <option value="pengampu" <?php echo (isset($_POST['status_hubungan']) ? $_POST['status_hubungan'] : $dosen_prodi['status_hubungan']) === 'pengampu' ? 'selected' : ''; ?>>Pengampu</option>
                    <option value="tamu" <?php echo (isset($_POST['status_hubungan']) ? $_POST['status_hubungan'] : $dosen_prodi['status_hubungan']) === 'tamu' ? 'selected' : ''; ?>>Tamu</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                <input type="date" name="tanggal_mulai" required
                       value="<?php echo isset($_POST['tanggal_mulai']) ? htmlspecialchars($_POST['tanggal_mulai']) : htmlspecialchars($dosen_prodi['tanggal_mulai']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai"
                       value="<?php echo isset($_POST['tanggal_selesai']) ? htmlspecialchars($_POST['tanggal_selesai']) : htmlspecialchars($dosen_prodi['tanggal_selesai']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika masih aktif</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kaprodi</label>
            <div class="flex items-center space-x-3 mt-3">
                <input type="checkbox" name="is_kaprodi" id="is_kaprodi" value="1"
                       <?php echo (isset($_POST['is_kaprodi']) ? $_POST['is_kaprodi'] : $dosen_prodi['is_kaprodi']) ? 'checked' : ''; ?>
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_kaprodi" class="text-sm text-gray-700">Dosen ini adalah kaprodi</label>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Update Hubungan
            </button>
            <a href="index.php?page=dosen_prodi" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>
</div>