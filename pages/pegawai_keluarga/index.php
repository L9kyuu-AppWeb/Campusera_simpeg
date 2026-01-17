<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

// Handle search parameters persistence only when action is not set (i.e., viewing the list)
if (!$action || $action === 'list') {
    if (isset($_GET['pegawai_id']) || isset($_GET['search']) || isset($_GET['hubungan']) || isset($_GET['status_hidup'])) {
        // If search parameters are provided in GET, store them in session
        $_SESSION['pegawai_keluarga_search'] = [
            'pegawai_id' => isset($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : null,
            'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
            'hubungan' => isset($_GET['hubungan']) ? cleanInput($_GET['hubungan']) : '',
            'status_hidup' => isset($_GET['status_hidup']) ? cleanInput($_GET['status_hidup']) : ''
        ];
    } elseif (!isset($_GET['pegawai_id']) && !isset($_GET['search']) && !isset($_GET['hubungan']) && !isset($_GET['status_hidup']) && isset($_SESSION['pegawai_keluarga_search'])) {
        // If no search parameters in GET but they exist in session, use session values
        $session_search = $_SESSION['pegawai_keluarga_search'] ?? [];
        $pegawai_id = $session_search['pegawai_id'] ?? null;
        $search = $session_search['search'] ?? '';
        $hubunganFilter = $session_search['hubungan'] ?? '';
        $statusHidupFilter = $session_search['status_hidup'] ?? '';

        // Redirect to preserve search parameters in URL
        $params = [];
        if ($pegawai_id) $params[] = 'pegawai_id=' . urlencode($pegawai_id);
        if ($search) $params[] = 'search=' . urlencode($search);
        if ($hubunganFilter) $params[] = 'hubungan=' . urlencode($hubunganFilter);
        if ($statusHidupFilter) $params[] = 'status_hidup=' . urlencode($statusHidupFilter);

        if (!empty($params)) {
            $redirect_url = 'index.php?page=pegawai_keluarga&' . implode('&', $params);
            header("Location: $redirect_url");
            exit;
        }
    }
}

switch ($action) {
    case 'create':
        require_once 'create.php';
        break;
    case 'edit':
        require_once 'edit.php';
        break;
    case 'delete':
        require_once 'delete.php';
        break;
    default:
        // List families
        $pegawaiIdParam = isset($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : null;
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $hubunganFilter = isset($_GET['hubungan']) ? cleanInput($_GET['hubungan']) : '';
        $statusHidupFilter = isset($_GET['status_hidup']) ? cleanInput($_GET['status_hidup']) : '';

        // Store current search parameters in session
        $_SESSION['pegawai_keluarga_search'] = [
            'pegawai_id' => $pegawaiIdParam,
            'search' => $search,
            'hubungan' => $hubunganFilter,
            'status_hidup' => $statusHidupFilter
        ];

        // If pegawai_id parameter is provided, use it directly
        $pegawaiIdFromSearch = $pegawaiIdParam;

        // If no pegawai_id but search term exists, try to find employee by name
        if (!$pegawaiIdFromSearch && $search) {
            // First, try to find an exact match
            $pegawaiStmt = $pdo->prepare("SELECT id FROM pegawai WHERE nama_lengkap = ?");
            $pegawaiStmt->execute([$search]);
            $pegawaiResult = $pegawaiStmt->fetch();

            if ($pegawaiResult) {
                // Found exact match
                $pegawaiIdFromSearch = $pegawaiResult['id'];
            } else {
                // If no exact match, check if the search term appears in the results
                // This is to handle cases where the user clicked from employee detail page
                // and the search was auto-populated with the employee's name
                $distinctEmployees = [];
                foreach ($keluargas as $keluarga) {
                    if ($keluarga['pegawai_nama'] && stripos($keluarga['pegawai_nama'], $search) !== false) {
                        $distinctEmployees[$keluarga['pegawai_id']] = $keluarga['pegawai_nama'];
                    }
                }

                // If only one unique employee appears in the results, use that
                if (count($distinctEmployees) === 1) {
                    $pegawaiIdFromSearch = key($distinctEmployees);
                }
            }
        }

        $sql = "SELECT pk.*, p.nama_lengkap as pegawai_nama FROM pegawai_keluarga pk
                LEFT JOIN pegawai p ON pk.pegawai_id = p.id
                WHERE 1=1";

        if ($pegawaiIdParam) {
            $sql .= " AND pk.pegawai_id = :pegawai_id";
        }

        if ($search) {
            $sql .= " AND (pk.nama LIKE :search1 OR pk.hubungan LIKE :search2 OR p.nama_lengkap LIKE :search3)";
        }

        if ($hubunganFilter) {
            $sql .= " AND pk.hubungan = :hubungan";
        }

        if ($statusHidupFilter) {
            $sql .= " AND pk.status_hidup = :status_hidup";
        }

        $sql .= " ORDER BY pk.created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($pegawaiIdParam) {
            $stmt->bindValue(':pegawai_id', $pegawaiIdParam);
        }
        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
        }
        if ($hubunganFilter) {
            $stmt->bindValue(':hubungan', $hubunganFilter);
        }
        if ($statusHidupFilter) {
            $stmt->bindValue(':status_hidup', $statusHidupFilter);
        }

        $stmt->execute();
        $keluargas = $stmt->fetchAll();

        // Get distinct hubungan and status_hidup for filter
        $hubungans = $pdo->query("SELECT DISTINCT hubungan FROM pegawai_keluarga WHERE hubungan IS NOT NULL ORDER BY hubungan")->fetchAll();
        $statusHidups = $pdo->query("SELECT DISTINCT status_hidup FROM pegawai_keluarga WHERE status_hidup IS NOT NULL ORDER BY status_hidup")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Keluarga</h1>
        <?php if ($pegawaiIdParam): ?>
            <?php
            $pegawaiStmt = $pdo->prepare("SELECT nama_lengkap FROM pegawai WHERE id = ?");
            $pegawaiStmt->execute([$pegawaiIdParam]);
            $pegawaiData = $pegawaiStmt->fetch();
            $pegawaiName = $pegawaiData ? $pegawaiData['nama_lengkap'] : 'Pegawai Tidak Ditemukan';
            ?>
            <p class="text-gray-500 mt-1">Kelola data keluarga <?php echo htmlspecialchars($pegawaiName); ?></p>
        <?php else: ?>
            <p class="text-gray-500 mt-1">Kelola data keluarga semua pegawai</p>
        <?php endif; ?>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <?php
    $createUrl = "index.php?page=pegawai_keluarga&action=create";
    if ($pegawaiIdFromSearch) {
        $createUrl .= "&pegawai_id=" . $pegawaiIdFromSearch;
    }
    ?>
    <a href="<?php echo $createUrl; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Keluarga</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="pegawai_keluarga">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama keluarga, hubungan, atau nama pegawai..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="hubungan" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Hubungan</option>
            <?php foreach ($hubungans as $hubungan): ?>
                <option value="<?php echo $hubungan['hubungan']; ?>" <?php echo $hubunganFilter === $hubungan['hubungan'] ? 'selected' : ''; ?>>
                    <?php echo $hubungan['hubungan']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status_hidup" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Status Hidup</option>
            <?php foreach ($statusHidups as $status): ?>
                <option value="<?php echo $status['status_hidup']; ?>" <?php echo $statusHidupFilter === $status['status_hidup'] ? 'selected' : ''; ?>>
                    <?php echo $status['status_hidup']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $hubunganFilter || $statusHidupFilter): ?>
        <a href="index.php?page=pegawai_keluarga" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Families Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($keluargas) > 0): ?>
        <?php foreach ($keluargas as $keluarga): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Family Member Info -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($keluarga['nama']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($keluarga['pegawai_nama'] ?? 'Pegawai tidak ditemukan'); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Hubungan</p>
                            <p class="font-medium"><?php echo htmlspecialchars($keluarga['hubungan']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium"><?php echo $keluarga['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status Hidup</p>
                            <p class="font-medium"><?php echo htmlspecialchars($keluarga['status_hidup']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status Tanggungan</p>
                            <p class="font-medium"><?php echo $keluarga['status_tanggungan'] ? 'Ya' : 'Tidak'; ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars(date('d M Y', strtotime($keluarga['created_at']))); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=pegawai_keluarga&action=edit&id=<?php echo $keluarga['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai_keluarga&action=delete&id=<?php echo $keluarga['id']; ?>"
                               class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data keluarga</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>