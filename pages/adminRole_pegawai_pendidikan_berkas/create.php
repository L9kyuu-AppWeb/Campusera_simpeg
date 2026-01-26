<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$pendidikanId = isset($_GET['pendidikan_id']) ? (int)$_GET['pendidikan_id'] : 0;

// Get all educations for the dropdown
$stmt = $pdo->query("SELECT p.id, p.jenjang, p.nama_institusi, pe.nama_lengkap FROM pendidikan p
                     LEFT JOIN pegawai pe ON p.pegawai_id = pe.id
                     ORDER BY pe.nama_lengkap ASC, p.jenjang DESC");
$educations = $stmt->fetchAll();

// If accessed from education detail page, pre-select that education
$preselectedEducation = null;
if ($pendidikanId > 0) {
    $stmt = $pdo->prepare("SELECT p.id, p.jenjang, p.nama_institusi, pe.nama_lengkap FROM pendidikan p
                           LEFT JOIN pegawai pe ON p.pegawai_id = pe.id
                           WHERE p.id = ?");
    $stmt->execute([$pendidikanId]);
    $preselectedEducation = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendidikan_id = cleanInput($_POST['pendidikan_id']);
    $jenis_berkas = cleanInput($_POST['jenis_berkas']);
    $nama_file = cleanInput($_POST['nama_file']);

    // Validation first
    if (empty($pendidikan_id) || empty($jenis_berkas) || empty($nama_file)) {
        $error = 'Pendidikan, jenis berkas, dan nama file harus diisi!';
    } else {
        // Check if education exists
        $stmt = $pdo->prepare("SELECT id FROM pendidikan WHERE id = ?");
        $stmt->execute([$pendidikan_id]);
        if (!$stmt->fetch()) {
            $error = 'Pendidikan tidak ditemukan!';
        } else {
            // File upload handling using the uploadDocument function
            if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK && $_FILES['file_upload']['size'] > 0) {
                // Upload document using the utility function
                $documentFileName = uploadDocument($_FILES['file_upload'], 0, 'pendidikan');

                if (!$documentFileName) {
                    $error = 'Gagal mengupload dokumen! Pastikan file adalah dokumen valid (PDF, DOC, DOCX, JPEG, JPG, PNG) dengan ukuran maksimal 5MB.';
                } else {
                    // Get file info for database storage
                    $fileSize = $_FILES['file_upload']['size'];
                    $fileExtension = strtolower(pathinfo($documentFileName, PATHINFO_EXTENSION));

                    // Insert file record
                    $stmt = $pdo->prepare("
                        INSERT INTO pendidikan_berkas
                        (pendidikan_id, jenis_berkas, nama_file, path_file, ukuran_file, tipe_file)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");

                    $relativePath = 'assets/uploads/pendidikan/' . $documentFileName;

                    if ($stmt->execute([$pendidikan_id, $jenis_berkas, $nama_file, $relativePath, $fileSize, $fileExtension])) {
                        logActivity($_SESSION['user_id'], 'add_education_file', "Added education file: $nama_file");
                        setAlert('success', 'Berkas pendidikan berhasil ditambahkan!');

                        // Preserve search parameters when redirecting back
                        $redirect_url = 'index.php?page=adminRole_pegawai_pendidikan_berkas';
                        if (isset($_SESSION['pegawai_pendidikan_berkas_search'])) {
                            $session_search = $_SESSION['pegawai_pendidikan_berkas_search'] ?? [];
                            $pendidikan_id_param = $session_search['pendidikan_id'] ?? null;
                            $search = $session_search['search'] ?? '';
                            $jenisBerkasFilter = $session_search['jenis_berkas'] ?? '';

                            $params = [];
                            if ($pendidikan_id_param) $params[] = 'pendidikan_id=' . urlencode($pendidikan_id_param);
                            if ($search) $params[] = 'search=' . urlencode($search);
                            if ($jenisBerkasFilter) $params[] = 'jenis_berkas=' . urlencode($jenisBerkasFilter);

                            if (!empty($params)) {
                                $redirect_url .= '&' . implode('&', $params);
                            }
                        }

                        redirect($redirect_url);
                    } else {
                        // If database insert fails, delete the uploaded file
                        deleteDocument($documentFileName, 'pendidikan');
                        $error = 'Gagal menambahkan berkas pendidikan!';
                    }
                }
            } else {
                $error = 'File harus diunggah!';
            }
        }
    }
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <?php
        // Build back URL with preserved search parameters
        $backUrl = 'index.php?page=adminRole_pegawai_pendidikan_berkas';
        if (isset($_SESSION['pegawai_pendidikan_berkas_search'])) {
            $session_search = $_SESSION['pegawai_pendidikan_berkas_search'] ?? [];
            $pendidikan_id_param = $session_search['pendidikan_id'] ?? null;
            $search = $session_search['search'] ?? '';
            $jenisBerkasFilter = $session_search['jenis_berkas'] ?? '';

            $params = [];
            if ($pendidikan_id_param) $params[] = 'pendidikan_id=' . urlencode($pendidikan_id_param);
            if ($search) $params[] = 'search=' . urlencode($search);
            if ($jenisBerkasFilter) $params[] = 'jenis_berkas=' . urlencode($jenisBerkasFilter);

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
            <h1 class="text-3xl font-bold text-gray-800">Tambah Berkas Pendidikan</h1>
            <p class="text-gray-500 mt-1">Unggah berkas pendidikan baru</p>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pendidikan *</label>
                <select name="pendidikan_id" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Pendidikan</option>
                    <?php foreach ($educations as $education): ?>
                        <option value="<?php echo $education['id']; ?>"
                                <?php 
                                $selectedValue = isset($_POST['pendidikan_id']) ? $_POST['pendidikan_id'] : 
                                               ($preselectedEducation ? $preselectedEducation['id'] : $pendidikanId);
                                echo $selectedValue == $education['id'] ? 'selected' : ''; 
                                ?>>
                            <?php echo htmlspecialchars($education['jenjang'] . ' - ' . $education['nama_institusi'] . ' (' . $education['nama_lengkap'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Berkas *</label>
                <select name="jenis_berkas" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jenis Berkas</option>
                    <option value="Ijazah" <?php echo (isset($_POST['jenis_berkas']) ? $_POST['jenis_berkas'] : '') === 'Ijazah' ? 'selected' : ''; ?>>Ijazah</option>
                    <option value="Transkrip" <?php echo (isset($_POST['jenis_berkas']) ? $_POST['jenis_berkas'] : '') === 'Transkrip' ? 'selected' : ''; ?>>Transkrip</option>
                    <option value="SK Penyetaraan" <?php echo (isset($_POST['jenis_berkas']) ? $_POST['jenis_berkas'] : '') === 'SK Penyetaraan' ? 'selected' : ''; ?>>SK Penyetaraan</option>
                    <option value="Lainnya" <?php echo (isset($_POST['jenis_berkas']) ? $_POST['jenis_berkas'] : '') === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama File *</label>
                <input type="text" name="nama_file" required
                       value="<?php echo isset($_POST['nama_file']) ? htmlspecialchars($_POST['nama_file']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File *</label>
                <input type="file" name="file_upload" accept=".pdf,.doc,.docx,.jpeg,.jpg,.png" required
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="mt-1 text-sm text-gray-500">Format yang diperbolehkan: PDF, DOC, DOCX, JPEG, JPG, PNG</p>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Tambah Berkas
            </button>
            <a href="<?php echo $backUrl; ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>