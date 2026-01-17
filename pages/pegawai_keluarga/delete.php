<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$familyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$familyId) {
    redirect('index.php?page=pegawai_keluarga');
}

// Get family data
$stmt = $pdo->prepare("SELECT * FROM pegawai_keluarga WHERE id = ?");
$stmt->execute([$familyId]);
$family = $stmt->fetch();

if (!$family) {
    setAlert('error', 'Anggota keluarga tidak ditemukan!');
    redirect('index.php?page=pegawai_keluarga');
}

// Get employee name for logging
$pegawaiStmt = $pdo->prepare("SELECT nama_lengkap FROM pegawai WHERE id = ?");
$pegawaiStmt->execute([$family['pegawai_id']]);
$pegawai = $pegawaiStmt->fetch();
$pegawaiName = $pegawai ? $pegawai['nama_lengkap'] : 'Unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete family member
    $stmt = $pdo->prepare("DELETE FROM pegawai_keluarga WHERE id = ?");
    if ($stmt->execute([$familyId])) {
        logActivity($_SESSION['user_id'], 'delete_family_member', "Deleted family member: {$family['nama']} from employee: $pegawaiName");
        setAlert('success', 'Anggota keluarga berhasil dihapus!');
    } else {
        setAlert('error', 'Gagal menghapus anggota keluarga!');
    }

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
            <h1 class="text-3xl font-bold text-gray-800">Hapus Anggota Keluarga</h1>
            <p class="text-gray-500 mt-1">Konfirmasi penghapusan data anggota keluarga</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Hapus Anggota Keluarga</h3>
        <p class="mt-2 text-gray-500">
            Apakah Anda yakin ingin menghapus anggota keluarga berikut?<br>
            <strong><?php echo htmlspecialchars($family['nama']); ?></strong>
        </p>
        <p class="mt-1 text-gray-500">
            Hubungan: <?php echo htmlspecialchars($family['hubungan']); ?> | 
            Jenis Kelamin: <?php echo $family['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?>
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