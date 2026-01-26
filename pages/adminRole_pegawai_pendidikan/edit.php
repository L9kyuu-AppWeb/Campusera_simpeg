<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$pendidikanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pendidikanId) {
    redirect('index.php?page=adminRole_pegawai_pendidikan');
}

// Get education data
$stmt = $pdo->prepare("SELECT * FROM pendidikan WHERE id = ?");
$stmt->execute([$pendidikanId]);
$pendidikan = $stmt->fetch();

if (!$pendidikan) {
    setAlert('error', 'Data pendidikan tidak ditemukan!');
    redirect('index.php?page=adminRole_pegawai_pendidikan');
}

// Get all employees for the dropdown
$stmt = $pdo->query("SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap ASC");
$employees = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pegawai_id = cleanInput($_POST['pegawai_id']);
    $jenjang = cleanInput($_POST['jenjang']);
    $nama_institusi = cleanInput($_POST['nama_institusi']);
    $jurusan = !empty($_POST['jurusan']) ? cleanInput($_POST['jurusan']) : null;
    $tahun_masuk = !empty($_POST['tahun_masuk']) ? cleanInput($_POST['tahun_masuk']) : null;
    $tahun_lulus = !empty($_POST['tahun_lulus']) ? cleanInput($_POST['tahun_lulus']) : null;
    $no_ijazah = !empty($_POST['no_ijazah']) ? cleanInput($_POST['no_ijazah']) : null;
    $tanggal_ijazah = !empty($_POST['tanggal_ijazah']) ? cleanInput($_POST['tanggal_ijazah']) : null;
    $gelar_depan = !empty($_POST['gelar_depan']) ? cleanInput($_POST['gelar_depan']) : null;
    $gelar_belakang = !empty($_POST['gelar_belakang']) ? cleanInput($_POST['gelar_belakang']) : null;
    $status_terakhir = isset($_POST['status_terakhir']) ? 1 : 0;

    // Validation
    if (empty($pegawai_id) || empty($jenjang) || empty($nama_institusi)) {
        $error = 'Pegawai, jenjang, dan nama institusi harus diisi!';
    } else {
        // Check if employee exists
        $stmt = $pdo->prepare("SELECT id FROM pegawai WHERE id = ?");
        $stmt->execute([$pegawai_id]);
        if (!$stmt->fetch()) {
            $error = 'Pegawai tidak ditemukan!';
        } else {
            // If status_terakhir is checked, uncheck all other records for this employee
            if ($status_terakhir) {
                $stmt = $pdo->prepare("UPDATE pendidikan SET status_terakhir = 0 WHERE pegawai_id = ? AND id != ?");
                $stmt->execute([$pegawai_id, $pendidikanId]);
            }
            
            // Update education record
            $stmt = $pdo->prepare("
                UPDATE pendidikan 
                SET pegawai_id = ?, jenjang = ?, nama_institusi = ?, jurusan = ?, tahun_masuk = ?, tahun_lulus = ?, 
                    no_ijazah = ?, tanggal_ijazah = ?, gelar_depan = ?, gelar_belakang = ?, status_terakhir = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$pegawai_id, $jenjang, $nama_institusi, $jurusan, $tahun_masuk, $tahun_lulus,
                               $no_ijazah, $tanggal_ijazah, $gelar_depan, $gelar_belakang, $status_terakhir, $pendidikanId])) {
                logActivity($_SESSION['user_id'], 'update_education', "Updated education for employee: $pegawai_id");
                setAlert('success', 'Data pendidikan berhasil diperbarui!');

                // Preserve search parameters when redirecting back
                $redirect_url = 'index.php?page=adminRole_pegawai_pendidikan';
                if (isset($_SESSION['pegawai_pendidikan_search'])) {
                    $session_search = $_SESSION['pegawai_pendidikan_search'] ?? [];
                    $pegawai_id_param = $session_search['pegawai_id'] ?? null;
                    $search = $session_search['search'] ?? '';
                    $jenjangFilter = $session_search['jenjang'] ?? '';
                    $tahunLulusFilter = $session_search['tahun_lulus'] ?? '';

                    $params = [];
                    if ($pegawai_id_param) $params[] = 'pegawai_id=' . urlencode($pegawai_id_param);
                    if ($search) $params[] = 'search=' . urlencode($search);
                    if ($jenjangFilter) $params[] = 'jenjang=' . urlencode($jenjangFilter);
                    if ($tahunLulusFilter) $params[] = 'tahun_lulus=' . urlencode($tahunLulusFilter);

                    if (!empty($params)) {
                        $redirect_url .= '&' . implode('&', $params);
                    }
                }

                redirect($redirect_url);
            } else {
                $error = 'Gagal memperbarui data pendidikan!';
            }
        }
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <?php
        // Build back URL with preserved search parameters
        $backUrl = 'index.php?page=adminRole_pegawai_pendidikan';
        if (isset($_SESSION['pegawai_pendidikan_search'])) {
            $session_search = $_SESSION['pegawai_pendidikan_search'] ?? [];
            $pegawai_id_param = $session_search['pegawai_id'] ?? null;
            $search = $session_search['search'] ?? '';
            $jenjangFilter = $session_search['jenjang'] ?? '';
            $tahunLulusFilter = $session_search['tahun_lulus'] ?? '';

            $params = [];
            if ($pegawai_id_param) $params[] = 'pegawai_id=' . urlencode($pegawai_id_param);
            if ($search) $params[] = 'search=' . urlencode($search);
            if ($jenjangFilter) $params[] = 'jenjang=' . urlencode($jenjangFilter);
            if ($tahunLulusFilter) $params[] = 'tahun_lulus=' . urlencode($tahunLulusFilter);

            if (!empty($params)) {
                $backUrl .= '&' . implode('&', $params);
            }
        }
        ?>
        <a href="<?php echo $backUrl; ?>" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Data Pendidikan</h1>
            <p class="text-gray-500 mt-1">Perbarui data pendidikan</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai *</label>
                <select name="pegawai_id" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Pegawai</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?php echo $employee['id']; ?>" 
                                <?php echo (isset($_POST['pegawai_id']) ? $_POST['pegawai_id'] : $pendidikan['pegawai_id']) == $employee['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($employee['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenjang *</label>
                <select name="jenjang" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jenjang</option>
                    <option value="SD" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'SD' ? 'selected' : ''; ?>>SD</option>
                    <option value="SMP" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'SMP' ? 'selected' : ''; ?>>SMP</option>
                    <option value="SMA" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'SMA' ? 'selected' : ''; ?>>SMA</option>
                    <option value="D1" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'D1' ? 'selected' : ''; ?>>D1</option>
                    <option value="D2" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'D2' ? 'selected' : ''; ?>>D2</option>
                    <option value="D3" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'D3' ? 'selected' : ''; ?>>D3</option>
                    <option value="D4" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'D4' ? 'selected' : ''; ?>>D4</option>
                    <option value="S1" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'S1' ? 'selected' : ''; ?>>S1</option>
                    <option value="S2" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'S2' ? 'selected' : ''; ?>>S2</option>
                    <option value="S3" <?php echo (isset($_POST['jenjang']) ? $_POST['jenjang'] : $pendidikan['jenjang']) === 'S3' ? 'selected' : ''; ?>>S3</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Institusi *</label>
                <input type="text" name="nama_institusi" required
                       value="<?php echo isset($_POST['nama_institusi']) ? htmlspecialchars($_POST['nama_institusi']) : htmlspecialchars($pendidikan['nama_institusi']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                <input type="text" name="jurusan"
                       value="<?php echo isset($_POST['jurusan']) ? htmlspecialchars($_POST['jurusan']) : htmlspecialchars($pendidikan['jurusan'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Masuk</label>
                <input type="number" name="tahun_masuk" min="1900" max="2100"
                       value="<?php echo isset($_POST['tahun_masuk']) ? htmlspecialchars($_POST['tahun_masuk']) : htmlspecialchars($pendidikan['tahun_masuk'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Lulus</label>
                <input type="number" name="tahun_lulus" min="1900" max="2100"
                       value="<?php echo isset($_POST['tahun_lulus']) ? htmlspecialchars($_POST['tahun_lulus']) : htmlspecialchars($pendidikan['tahun_lulus'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No Ijazah</label>
                <input type="text" name="no_ijazah"
                       value="<?php echo isset($_POST['no_ijazah']) ? htmlspecialchars($_POST['no_ijazah']) : htmlspecialchars($pendidikan['no_ijazah'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Ijazah</label>
                <input type="date" name="tanggal_ijazah"
                       value="<?php echo isset($_POST['tanggal_ijazah']) ? htmlspecialchars($_POST['tanggal_ijazah']) : htmlspecialchars($pendidikan['tanggal_ijazah'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gelar Depan</label>
                <input type="text" name="gelar_depan"
                       value="<?php echo isset($_POST['gelar_depan']) ? htmlspecialchars($_POST['gelar_depan']) : htmlspecialchars($pendidikan['gelar_depan'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gelar Belakang</label>
                <input type="text" name="gelar_belakang"
                       value="<?php echo isset($_POST['gelar_belakang']) ? htmlspecialchars($_POST['gelar_belakang']) : htmlspecialchars($pendidikan['gelar_belakang'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Terakhir</label>
                <div class="mt-1">
                    <input type="checkbox" id="status_terakhir" name="status_terakhir" value="1"
                           <?php echo (isset($_POST['status_terakhir']) ? $_POST['status_terakhir'] : $pendidikan['status_terakhir']) == 1 ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="status_terakhir" class="ml-2 text-sm text-gray-700">Jadikan sebagai pendidikan terakhir</label>
                </div>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <a href="index.php?page=adminRole_pegawai_pendidikan_berkas&pendidikan_id=<?php echo $pendidikanId; ?>" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Kelola Berkas</span>
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Update Pendidikan
            </button>
            <a href="<?php echo $backUrl; ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>