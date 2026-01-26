<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

// Handle search parameters persistence only when action is not set (i.e., viewing the list)
if (!$action || $action === 'list') {
    if (isset($_GET['pegawai_id']) || isset($_GET['search']) || isset($_GET['jenjang']) || isset($_GET['tahun_lulus'])) {
        // If search parameters are provided in GET, store them in session
        $_SESSION['pegawai_pendidikan_search'] = [
            'pegawai_id' => isset($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : null,
            'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
            'jenjang' => isset($_GET['jenjang']) ? cleanInput($_GET['jenjang']) : '',
            'tahun_lulus' => isset($_GET['tahun_lulus']) ? cleanInput($_GET['tahun_lulus']) : ''
        ];
    } elseif (!isset($_GET['pegawai_id']) && !isset($_GET['search']) && !isset($_GET['jenjang']) && !isset($_GET['tahun_lulus']) && isset($_SESSION['pegawai_pendidikan_search'])) {
        // If no search parameters in GET but they exist in session, use session values
        $session_search = $_SESSION['pegawai_pendidikan_search'] ?? [];
        $pegawai_id = $session_search['pegawai_id'] ?? null;
        $search = $session_search['search'] ?? '';
        $jenjangFilter = $session_search['jenjang'] ?? '';
        $tahunLulusFilter = $session_search['tahun_lulus'] ?? '';

        // Redirect to preserve search parameters in URL
        $params = [];
        if ($pegawai_id) $params[] = 'pegawai_id=' . urlencode($pegawai_id);
        if ($search) $params[] = 'search=' . urlencode($search);
        if ($jenjangFilter) $params[] = 'jenjang=' . urlencode($jenjangFilter);
        if ($tahunLulusFilter) $params[] = 'tahun_lulus=' . urlencode($tahunLulusFilter);

        if (!empty($params)) {
            $redirect_url = 'index.php?page=adminRole_pegawai_pendidikan&' . implode('&', $params);
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
        // List educations
        $pegawaiIdParam = isset($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : null;
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $jenjangFilter = isset($_GET['jenjang']) ? cleanInput($_GET['jenjang']) : '';
        $tahunLulusFilter = isset($_GET['tahun_lulus']) ? cleanInput($_GET['tahun_lulus']) : '';

        // Store current search parameters in session
        $_SESSION['pegawai_pendidikan_search'] = [
            'pegawai_id' => $pegawaiIdParam,
            'search' => $search,
            'jenjang' => $jenjangFilter,
            'tahun_lulus' => $tahunLulusFilter
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
                $sqlCheck = "SELECT DISTINCT p.pegawai_id, pe.nama_lengkap FROM pendidikan p
                             LEFT JOIN pegawai pe ON p.pegawai_id = pe.id
                             WHERE 1=1";

                if ($search) {
                    $sqlCheck .= " AND (p.nama_institusi LIKE :search1 OR p.jurusan LIKE :search2 OR pe.nama_lengkap LIKE :search3 OR p.jenjang LIKE :search4)";
                }

                $stmtCheck = $pdo->prepare($sqlCheck);

                if ($search) {
                    $stmtCheck->bindValue(':search1', "%$search%");
                    $stmtCheck->bindValue(':search2', "%$search%");
                    $stmtCheck->bindValue(':search3', "%$search%");
                    $stmtCheck->bindValue(':search4', "%$search%");
                }

                $stmtCheck->execute();
                $results = $stmtCheck->fetchAll();

                foreach ($results as $result) {
                    if ($result['nama_lengkap'] && stripos($result['nama_lengkap'], $search) !== false) {
                        $distinctEmployees[$result['pegawai_id']] = $result['nama_lengkap'];
                    }
                }

                // If only one unique employee appears in the results, use that
                if (count($distinctEmployees) === 1) {
                    $pegawaiIdFromSearch = key($distinctEmployees);
                }
            }
        }

        $sql = "SELECT p.*, pe.nama_lengkap as pegawai_nama FROM pendidikan p
                LEFT JOIN pegawai pe ON p.pegawai_id = pe.id
                WHERE 1=1";

        if ($pegawaiIdParam) {
            $sql .= " AND p.pegawai_id = :pegawai_id";
        }

        if ($search) {
            $sql .= " AND (p.nama_institusi LIKE :search1 OR p.jurusan LIKE :search2 OR pe.nama_lengkap LIKE :search3 OR p.jenjang LIKE :search4)";
        }

        if ($jenjangFilter) {
            $sql .= " AND p.jenjang = :jenjang";
        }

        if ($tahunLulusFilter) {
            $sql .= " AND p.tahun_lulus = :tahun_lulus";
        }

        $sql .= " ORDER BY p.tahun_lulus DESC, p.jenjang DESC";

        $stmt = $pdo->prepare($sql);

        if ($pegawaiIdParam) {
            $stmt->bindValue(':pegawai_id', $pegawaiIdParam);
        }
        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
            $stmt->bindValue(':search4', "%$search%");
        }
        if ($jenjangFilter) {
            $stmt->bindValue(':jenjang', $jenjangFilter);
        }
        if ($tahunLulusFilter) {
            $stmt->bindValue(':tahun_lulus', $tahunLulusFilter);
        }

        $stmt->execute();
        $pendidikans = $stmt->fetchAll();

        // Get distinct jenjang and tahun_lulus for filter
        $jenjangs = $pdo->query("SELECT DISTINCT jenjang FROM pendidikan WHERE jenjang IS NOT NULL ORDER BY jenjang DESC")->fetchAll();
        $tahunLulus = $pdo->query("SELECT DISTINCT tahun_lulus FROM pendidikan WHERE tahun_lulus IS NOT NULL ORDER BY tahun_lulus DESC")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Pendidikan</h1>
        <?php if ($pegawaiIdParam): ?>
            <?php
            $pegawaiStmt = $pdo->prepare("SELECT nama_lengkap FROM pegawai WHERE id = ?");
            $pegawaiStmt->execute([$pegawaiIdParam]);
            $pegawaiData = $pegawaiStmt->fetch();
            $pegawaiName = $pegawaiData ? $pegawaiData['nama_lengkap'] : 'Pegawai Tidak Ditemukan';
            ?>
            <p class="text-gray-500 mt-1">Kelola data pendidikan <?php echo htmlspecialchars($pegawaiName); ?></p>
        <?php else: ?>
            <p class="text-gray-500 mt-1">Kelola data pendidikan semua pegawai</p>
        <?php endif; ?>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <?php
    $createUrl = "index.php?page=adminRole_pegawai_pendidikan&action=create";
    if ($pegawaiIdFromSearch) {
        $createUrl .= "&pegawai_id=" . $pegawaiIdFromSearch;
    }
    ?>
    <a href="<?php echo $createUrl; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Pendidikan</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="adminRole_pegawai_pendidikan">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari institusi, jurusan, pegawai, atau jenjang..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="jenjang" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Jenjang</option>
            <?php foreach ($jenjangs as $jenjang): ?>
                <option value="<?php echo $jenjang['jenjang']; ?>" <?php echo $jenjangFilter === $jenjang['jenjang'] ? 'selected' : ''; ?>>
                    <?php echo $jenjang['jenjang']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="tahun_lulus" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Tahun Lulus</option>
            <?php foreach ($tahunLulus as $tahun): ?>
                <option value="<?php echo $tahun['tahun_lulus']; ?>" <?php echo $tahunLulusFilter === $tahun['tahun_lulus'] ? 'selected' : ''; ?>>
                    <?php echo $tahun['tahun_lulus']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $jenjangFilter || $tahunLulusFilter): ?>
        <a href="index.php?page=adminRole_pegawai_pendidikan" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Educations Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($pendidikans) > 0): ?>
        <?php foreach ($pendidikans as $pendidikan): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Education Info -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($pendidikan['jenjang']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($pendidikan['pegawai_nama'] ?? 'Pegawai tidak ditemukan'); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Institusi</p>
                            <p class="font-medium"><?php echo htmlspecialchars($pendidikan['nama_institusi']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jurusan</p>
                            <p class="font-medium"><?php echo htmlspecialchars($pendidikan['jurusan'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tahun Masuk</p>
                            <p class="font-medium"><?php echo htmlspecialchars($pendidikan['tahun_masuk'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tahun Lulus</p>
                            <p class="font-medium"><?php echo htmlspecialchars($pendidikan['tahun_lulus'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars(date('d M Y', strtotime($pendidikan['created_at']))); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=adminRole_pegawai_pendidikan_berkas&pendidikan_id=<?php echo $pendidikan['id']; ?>"
                               class="text-green-600 hover:text-green-800 transition-colors" title="Kelola Berkas">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai_pendidikan&action=edit&id=<?php echo $pendidikan['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai_pendidikan&action=delete&id=<?php echo $pendidikan['id']; ?>"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data pendidikan</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>