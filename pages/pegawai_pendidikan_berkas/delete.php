<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$fileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$fileId) {
    redirect('index.php?page=pegawai_pendidikan_berkas');
}

// Get file data
$stmt = $pdo->prepare("SELECT * FROM pendidikan_berkas WHERE id = ?");
$stmt->execute([$fileId]);
$file = $stmt->fetch();

if (!$file) {
    setAlert('error', 'Berkas pendidikan tidak ditemukan!');
    redirect('index.php?page=pegawai_pendidikan_berkas');
}

// Get education and employee name for logging
$educationStmt = $pdo->prepare("SELECT p.jenjang, p.nama_institusi, pe.nama_lengkap FROM pendidikan p
                                LEFT JOIN pegawai pe ON p.pegawai_id = pe.id
                                WHERE p.id = ?");
$educationStmt->execute([$file['pendidikan_id']]);
$education = $educationStmt->fetch();
$educationInfo = $education ? $education['jenjang'] . ' - ' . $education['nama_institusi'] . ' (' . $education['nama_lengkap'] . ')' : 'Unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete physical file if it exists
    if (!empty($file['path_file'])) {
        $fileName = basename($file['path_file']);
        deleteDocument($fileName, 'pendidikan');
    }

    // Delete file record
    $stmt = $pdo->prepare("DELETE FROM pendidikan_berkas WHERE id = ?");
    if ($stmt->execute([$fileId])) {
        logActivity($_SESSION['user_id'], 'delete_education_file', "Deleted education file: {$file['nama_file']} for education: $educationInfo");
        setAlert('success', 'Berkas pendidikan berhasil dihapus!');
    } else {
        setAlert('error', 'Gagal menghapus berkas pendidikan!');
    }
    
    // Preserve search parameters when redirecting back
    $redirect_url = 'index.php?page=pegawai_pendidikan_berkas';
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
}
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <?php
        // Build back URL with preserved search parameters
        $backUrl = 'index.php?page=pegawai_pendidikan_berkas';
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
            <h1 class="text-3xl font-bold text-gray-800">Hapus Berkas Pendidikan</h1>
            <p class="text-gray-500 mt-1">Konfirmasi penghapusan berkas pendidikan</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Hapus Berkas Pendidikan</h3>
        <p class="mt-2 text-gray-500">
            Apakah Anda yakin ingin menghapus berkas pendidikan berikut?<br>
            <strong><?php echo htmlspecialchars($file['nama_file']); ?></strong>
        </p>
        <p class="mt-1 text-gray-500">
            Jenis: <?php echo htmlspecialchars($file['jenis_berkas']); ?> |
            Ukuran: <?php echo formatFileSize($file['ukuran_file']); ?>
        </p>
        <p class="mt-1 text-gray-500">
            Pendidikan: <?php echo htmlspecialchars($educationInfo); ?>
        </p>
    </div>

    <div class="mt-8 flex justify-center space-x-4">
        <form method="POST" class="inline">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Ya, Hapus
            </button>
        </form>
        <a href="<?php echo $backUrl; ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
            Batal
        </a>
    </div>
</div>