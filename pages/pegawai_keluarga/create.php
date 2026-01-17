<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$pegawaiId = isset($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : 0;

// Get all employees for the dropdown
$stmt = $pdo->query("SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap ASC");
$employees = $stmt->fetchAll();

// If accessed from employee detail page, pre-select that employee
$preselectedEmployee = null;
if ($pegawaiId > 0) {
    $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM pegawai WHERE id = ?");
    $stmt->execute([$pegawaiId]);
    $preselectedEmployee = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pegawai_id = cleanInput($_POST['pegawai_id']);
    $nama = cleanInput($_POST['nama']);
    $hubungan = cleanInput($_POST['hubungan']);
    $jenis_kelamin = cleanInput($_POST['jenis_kelamin']);
    $tempat_lahir = !empty($_POST['tempat_lahir']) ? cleanInput($_POST['tempat_lahir']) : null;
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? cleanInput($_POST['tanggal_lahir']) : null;
    $pendidikan_terakhir = !empty($_POST['pendidikan_terakhir']) ? cleanInput($_POST['pendidikan_terakhir']) : null;
    $pekerjaan = !empty($_POST['pekerjaan']) ? cleanInput($_POST['pekerjaan']) : null;
    $status_hidup = cleanInput($_POST['status_hidup']);
    $status_tanggungan = isset($_POST['status_tanggungan']) ? 1 : 0;
    $no_ktp = !empty($_POST['no_ktp']) ? cleanInput($_POST['no_ktp']) : null;
    $no_kk = !empty($_POST['no_kk']) ? cleanInput($_POST['no_kk']) : null;

    // Validation
    if (empty($pegawai_id) || empty($nama) || empty($hubungan) || empty($jenis_kelamin)) {
        $error = 'Pegawai, nama, hubungan, dan jenis kelamin harus diisi!';
    } else {
        // Check if employee exists
        $stmt = $pdo->prepare("SELECT id FROM pegawai WHERE id = ?");
        $stmt->execute([$pegawai_id]);
        if (!$stmt->fetch()) {
            $error = 'Pegawai tidak ditemukan!';
        } else {
            // Insert family member
            $stmt = $pdo->prepare("
                INSERT INTO pegawai_keluarga 
                (pegawai_id, nama, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, 
                 pendidikan_terakhir, pekerjaan, status_hidup, status_tanggungan, no_ktp, no_kk)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$pegawai_id, $nama, $hubungan, $jenis_kelamin, $tempat_lahir, $tanggal_lahir,
                               $pendidikan_terakhir, $pekerjaan, $status_hidup, $status_tanggungan, $no_ktp, $no_kk])) {
                logActivity($_SESSION['user_id'], 'add_family_member', "Added family member: $nama");
                setAlert('success', 'Anggota keluarga berhasil ditambahkan!');

                // Preserve search parameters when redirecting back
                $redirect_url = 'index.php?page=pegawai_keluarga';
                if (isset($_SESSION['pegawai_keluarga_search'])) {
                    $session_search = $_SESSION['pegawai_keluarga_search'] ?? [];
                    $search = $session_search['search'] ?? '';
                    $hubunganFilter = $session_search['hubungan'] ?? '';
                    $statusHidupFilter = $session_search['status_hidup'] ?? '';

                    $params = [];
                    if ($search) $params[] = 'search=' . urlencode($search);
                    if ($hubunganFilter) $params[] = 'hubungan=' . urlencode($hubunganFilter);
                    if ($statusHidupFilter) $params[] = 'status_hidup=' . urlencode($statusHidupFilter);

                    if (!empty($params)) {
                        $redirect_url .= '&' . implode('&', $params);
                    }
                }

                redirect($redirect_url);
            } else {
                $error = 'Gagal menambahkan anggota keluarga!';
            }
        }
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <?php
        // Build back URL with preserved search parameters
        $backUrl = 'index.php?page=pegawai_keluarga';
        if (isset($_SESSION['pegawai_keluarga_search'])) {
            $session_search = $_SESSION['pegawai_keluarga_search'] ?? [];
            $search = $session_search['search'] ?? '';
            $hubunganFilter = $session_search['hubungan'] ?? '';
            $statusHidupFilter = $session_search['status_hidup'] ?? '';

            $params = [];
            if ($search) $params[] = 'search=' . urlencode($search);
            if ($hubunganFilter) $params[] = 'hubungan=' . urlencode($hubunganFilter);
            if ($statusHidupFilter) $params[] = 'status_hidup=' . urlencode($statusHidupFilter);

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
            <h1 class="text-3xl font-bold text-gray-800">Tambah Anggota Keluarga</h1>
            <p class="text-gray-500 mt-1">Tambahkan data anggota keluarga baru</p>
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
                                <?php
                                // Prioritize POST data first (in case of validation error), then preselected employee, then GET parameter
                                if (isset($_POST['pegawai_id'])) {
                                    $selectedValue = $_POST['pegawai_id'];
                                } elseif ($preselectedEmployee) {
                                    $selectedValue = $preselectedEmployee['id'];
                                } else {
                                    $selectedValue = $pegawaiId;
                                }
                                echo $selectedValue == $employee['id'] ? 'selected' : '';
                                ?>>
                            <?php echo htmlspecialchars($employee['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
                <input type="text" name="nama" required
                       value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan *</label>
                <select name="hubungan" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Hubungan</option>
                    <option value="Suami" <?php echo (isset($_POST['hubungan']) ? $_POST['hubungan'] : '') === 'Suami' ? 'selected' : ''; ?>>Suami</option>
                    <option value="Istri" <?php echo (isset($_POST['hubungan']) ? $_POST['hubungan'] : '') === 'Istri' ? 'selected' : ''; ?>>Istri</option>
                    <option value="Anak" <?php echo (isset($_POST['hubungan']) ? $_POST['hubungan'] : '') === 'Anak' ? 'selected' : ''; ?>>Anak</option>
                    <option value="Ayah" <?php echo (isset($_POST['hubungan']) ? $_POST['hubungan'] : '') === 'Ayah' ? 'selected' : ''; ?>>Ayah</option>
                    <option value="Ibu" <?php echo (isset($_POST['hubungan']) ? $_POST['hubungan'] : '') === 'Ibu' ? 'selected' : ''; ?>>Ibu</option>
                    <option value="Lainnya" <?php echo (isset($_POST['hubungan']) ? $_POST['hubungan'] : '') === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin *</label>
                <select name="jenis_kelamin" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L" <?php echo (isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : '') === 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="P" <?php echo (isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : '') === 'P' ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir</label>
                <input type="text" name="tempat_lahir"
                       value="<?php echo isset($_POST['tempat_lahir']) ? htmlspecialchars($_POST['tempat_lahir']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir"
                       value="<?php echo isset($_POST['tanggal_lahir']) ? htmlspecialchars($_POST['tanggal_lahir']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pendidikan Terakhir</label>
                <input type="text" name="pendidikan_terakhir"
                       value="<?php echo isset($_POST['pendidikan_terakhir']) ? htmlspecialchars($_POST['pendidikan_terakhir']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan</label>
                <input type="text" name="pekerjaan"
                       value="<?php echo isset($_POST['pekerjaan']) ? htmlspecialchars($_POST['pekerjaan']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Hidup</label>
                <select name="status_hidup"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="Hidup" <?php echo (isset($_POST['status_hidup']) ? $_POST['status_hidup'] : 'Hidup') === 'Hidup' ? 'selected' : ''; ?>>Hidup</option>
                    <option value="Meninggal" <?php echo (isset($_POST['status_hidup']) ? $_POST['status_hidup'] : 'Hidup') === 'Meninggal' ? 'selected' : ''; ?>>Meninggal</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Tanggungan</label>
                <div class="mt-1">
                    <input type="checkbox" id="status_tanggungan" name="status_tanggungan" value="1"
                           <?php echo (isset($_POST['status_tanggungan']) ? $_POST['status_tanggungan'] : 0) == 1 ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="status_tanggungan" class="ml-2 text-sm text-gray-700">Termasuk tanggungan</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No KTP</label>
                <input type="text" name="no_ktp"
                       value="<?php echo isset($_POST['no_ktp']) ? htmlspecialchars($_POST['no_ktp']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No KK</label>
                <input type="text" name="no_kk"
                       value="<?php echo isset($_POST['no_kk']) ? htmlspecialchars($_POST['no_kk']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Tambah Keluarga
            </button>
            <a href="<?php echo $backUrl; ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>