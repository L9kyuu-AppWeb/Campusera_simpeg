<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$prodiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$prodiId) {
    redirect('index.php?page=adminRole_prodi');
}

// Get prodi data
$stmt = $pdo->prepare("SELECT * FROM prodi WHERE id = ?");
$stmt->execute([$prodiId]);
$prodi = $stmt->fetch();

if (!$prodi) {
    setAlert('error', 'Program Studi tidak ditemukan!');
    redirect('index.php?page=adminRole_prodi');
}

$error = '';

// Get all fakultas and dosen for dropdown
$fakultasList = $pdo->query("SELECT id, nama_fakultas FROM fakultas ORDER BY nama_fakultas ASC")->fetchAll();
$dosenList = $pdo->query("SELECT d.id, p.nama_lengkap as pegawai_nama FROM dosen d JOIN pegawai p ON d.pegawai_id = p.id ORDER BY p.nama_lengkap ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fakultas_id = (int)$_POST['fakultas_id'];
    $kode_prodi = cleanInput($_POST['kode_prodi']);
    $nama_prodi = cleanInput($_POST['nama_prodi']);
    $jenjang = cleanInput($_POST['jenjang']);
    $kaprodi_id = !empty($_POST['kaprodi_id']) ? (int)$_POST['kaprodi_id'] : null;
    $akreditasi = !empty($_POST['akreditasi']) ? cleanInput($_POST['akreditasi']) : null;
    $kuota_mahasiswa = (int)$_POST['kuota_mahasiswa'];
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    // Validation
    if (empty($fakultas_id) || empty($kode_prodi) || empty($nama_prodi) || empty($jenjang)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } else {
        // Check if kode_prodi already exists for other records
        $stmt = $pdo->prepare("SELECT id FROM prodi WHERE kode_prodi = ? AND id != ?");
        $stmt->execute([$kode_prodi, $prodiId]);

        if ($stmt->fetch()) {
            $error = 'Kode program studi sudah digunakan!';
        } else {
            // Update prodi in database
            $stmt = $pdo->prepare("
                UPDATE prodi
                SET fakultas_id = ?, kode_prodi = ?, nama_prodi = ?, jenjang = ?, kaprodi_id = ?, 
                    akreditasi = ?, kuota_mahasiswa = ?, status_aktif = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$fakultas_id, $kode_prodi, $nama_prodi, $jenjang, $kaprodi_id, $akreditasi, $kuota_mahasiswa, $status_aktif, $prodiId])) {
                logActivity($_SESSION['user_id'], 'update_prodi', "Updated prodi: $nama_prodi");
                setAlert('success', 'Program studi berhasil diperbarui!');
                redirect('index.php?page=adminRole_prodi');
            } else {
                $error = 'Gagal memperbarui program studi!';
            }
        }
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_prodi" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Program Studi</h1>
            <p class="text-gray-500 mt-1">Perbarui informasi program studi</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas *</label>
                <select name="fakultas_id" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Fakultas</option>
                    <?php foreach ($fakultasList as $fakultas): ?>
                        <option value="<?php echo $fakultas['id']; ?>"
                                <?php echo (isset($_POST['fakultas_id']) ? $_POST['fakultas_id'] : $prodi['fakultas_id']) == $fakultas['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($fakultas['nama_fakultas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Prodi *</label>
                <input type="text" name="kode_prodi" required
                       value="<?php echo isset($_POST['kode_prodi']) ? htmlspecialchars($_POST['kode_prodi']) : htmlspecialchars($prodi['kode_prodi']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Prodi *</label>
                <input type="text" name="nama_prodi" required
                       value="<?php echo isset($_POST['nama_prodi']) ? htmlspecialchars($_POST['nama_prodi']) : htmlspecialchars($prodi['nama_prodi']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenjang *</label>
                <select name="jenjang" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jenjang</option>
                    <option value="D3" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $prodi['jenjang']) === 'D3' ? 'selected' : ''; ?>>D3</option>
                    <option value="D4" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $prodi['jenjang']) === 'D4' ? 'selected' : ''; ?>>D4</option>
                    <option value="S1" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $prodi['jenjang']) === 'S1' ? 'selected' : ''; ?>>S1</option>
                    <option value="S2" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $prodi['jenjang']) === 'S2' ? 'selected' : ''; ?>>S2</option>
                    <option value="S3" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $prodi['jenjang']) === 'S3' ? 'selected' : ''; ?>>S3</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ketua Prodi</label>
                <select name="kaprodi_id"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Ketua Prodi</option>
                    <?php foreach ($dosenList as $dosen): ?>
                        <option value="<?php echo $dosen['id']; ?>"
                                <?php echo (isset($_POST['kaprodi_id']) ? $_POST['kaprodi_id'] : $prodi['kaprodi_id']) == $dosen['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dosen['pegawai_nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Akreditasi</label>
                <input type="text" name="akreditasi"
                       value="<?php echo isset($_POST['akreditasi']) ? htmlspecialchars($_POST['akreditasi']) : htmlspecialchars($prodi['akreditasi']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kuota Mahasiswa</label>
                <input type="number" name="kuota_mahasiswa" min="0"
                       value="<?php echo isset($_POST['kuota_mahasiswa']) ? htmlspecialchars($_POST['kuota_mahasiswa']) : htmlspecialchars($prodi['kuota_mahasiswa']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status Aktif</label>
            <div class="flex items-center space-x-3 mt-3">
                <input type="checkbox" name="status_aktif" id="status_aktif" value="1"
                       <?php echo (isset($_POST['status_aktif']) ? $_POST['status_aktif'] : $prodi['status_aktif']) ? 'checked' : ''; ?>
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="status_aktif" class="text-sm text-gray-700">Program Studi Aktif</label>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Update Prodi
            </button>
            <a href="index.php?page=adminRole_prodi" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>
</div>