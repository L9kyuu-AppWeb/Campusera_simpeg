<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$success = false;

// Get all dosen for dropdown
$dosenList = $pdo->query("SELECT d.id, p.nama_lengkap as pegawai_nama FROM dosen d JOIN pegawai p ON d.pegawai_id = p.id ORDER BY p.nama_lengkap ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_fakultas = cleanInput($_POST['kode_fakultas']);
    $nama_fakultas = cleanInput($_POST['nama_fakultas']);
    $dekan_id = !empty($_POST['dekan_id']) ? (int)$_POST['dekan_id'] : null;

    // Validation
    if (empty($kode_fakultas) || empty($nama_fakultas)) {
        $error = 'Kode fakultas dan nama fakultas harus diisi!';
    } else {
        // Check if kode_fakultas already exists
        $stmt = $pdo->prepare("SELECT id FROM fakultas WHERE kode_fakultas = ?");
        $stmt->execute([$kode_fakultas]);

        if ($stmt->fetch()) {
            $error = 'Kode fakultas sudah digunakan!';
        } else {
            // Insert fakultas into database
            $stmt = $pdo->prepare("
                INSERT INTO fakultas (kode_fakultas, nama_fakultas, dekan_id)
                VALUES (?, ?, ?)
            ");

            if ($stmt->execute([$kode_fakultas, $nama_fakultas, $dekan_id])) {
                logActivity($_SESSION['user_id'], 'create_fakultas', "Created new fakultas: $nama_fakultas");
                setAlert('success', 'Fakultas berhasil ditambahkan!');
                redirect('index.php?page=fakultas');
            } else {
                $error = 'Gagal menambahkan fakultas!';
            }
        }
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=fakultas" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Fakultas Baru</h1>
            <p class="text-gray-500 mt-1">Tambahkan data fakultas baru</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Fakultas *</label>
                <input type="text" name="kode_fakultas" required
                       value="<?php echo isset($_POST['kode_fakultas']) ? htmlspecialchars($_POST['kode_fakultas']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Fakultas *</label>
                <input type="text" name="nama_fakultas" required
                       value="<?php echo isset($_POST['nama_fakultas']) ? htmlspecialchars($_POST['nama_fakultas']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dekan</label>
                <select name="dekan_id"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Dekan</option>
                    <?php foreach ($dosenList as $dosen): ?>
                        <option value="<?php echo $dosen['id']; ?>"
                                <?php echo (isset($_POST['dekan_id']) && $_POST['dekan_id'] == $dosen['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dosen['pegawai_nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Simpan Fakultas
            </button>
            <a href="index.php?page=fakultas" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>
</div>