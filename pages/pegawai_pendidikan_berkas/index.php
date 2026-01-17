<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

// Handle search parameters persistence only when action is not set (i.e., viewing the list)
if (!$action || $action === 'list') {
    if (isset($_GET['pendidikan_id']) || isset($_GET['search']) || isset($_GET['jenis_berkas'])) {
        // If search parameters are provided in GET, store them in session
        $_SESSION['pegawai_pendidikan_berkas_search'] = [
            'pendidikan_id' => isset($_GET['pendidikan_id']) ? (int)$_GET['pendidikan_id'] : null,
            'search' => isset($_GET['search']) ? cleanInput($_GET['search']) : '',
            'jenis_berkas' => isset($_GET['jenis_berkas']) ? cleanInput($_GET['jenis_berkas']) : ''
        ];
    } elseif (!isset($_GET['pendidikan_id']) && !isset($_GET['search']) && !isset($_GET['jenis_berkas']) && isset($_SESSION['pegawai_pendidikan_berkas_search'])) {
        // If no search parameters in GET but they exist in session, use session values
        $session_search = $_SESSION['pegawai_pendidikan_berkas_search'] ?? [];
        $pendidikan_id = $session_search['pendidikan_id'] ?? null;
        $search = $session_search['search'] ?? '';
        $jenisBerkasFilter = $session_search['jenis_berkas'] ?? '';

        // Redirect to preserve search parameters in URL
        $params = [];
        if ($pendidikan_id) $params[] = 'pendidikan_id=' . urlencode($pendidikan_id);
        if ($search) $params[] = 'search=' . urlencode($search);
        if ($jenisBerkasFilter) $params[] = 'jenis_berkas=' . urlencode($jenisBerkasFilter);

        if (!empty($params)) {
            $redirect_url = 'index.php?page=pegawai_pendidikan_berkas&' . implode('&', $params);
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
        // List education files
        $pendidikanIdParam = isset($_GET['pendidikan_id']) ? (int)$_GET['pendidikan_id'] : null;
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $jenisBerkasFilter = isset($_GET['jenis_berkas']) ? cleanInput($_GET['jenis_berkas']) : '';

        // Store current search parameters in session
        $_SESSION['pegawai_pendidikan_berkas_search'] = [
            'pendidikan_id' => $pendidikanIdParam,
            'search' => $search,
            'jenis_berkas' => $jenisBerkasFilter
        ];

        // If pendidikan_id parameter is provided, use it directly
        $pendidikanIdFromSearch = $pendidikanIdParam;

        $sql = "SELECT pb.*, p.jenjang, p.nama_institusi, pe.nama_lengkap as pegawai_nama FROM pendidikan_berkas pb
                LEFT JOIN pendidikan p ON pb.pendidikan_id = p.id
                LEFT JOIN pegawai pe ON p.pegawai_id = pe.id
                WHERE 1=1";

        if ($pendidikanIdParam) {
            $sql .= " AND pb.pendidikan_id = :pendidikan_id";
        }

        if ($search) {
            $sql .= " AND (pb.nama_file LIKE :search1 OR pb.jenis_berkas LIKE :search2 OR p.jenjang LIKE :search3 OR p.nama_institusi LIKE :search4 OR pe.nama_lengkap LIKE :search5)";
        }

        if ($jenisBerkasFilter) {
            $sql .= " AND pb.jenis_berkas = :jenis_berkas";
        }

        $sql .= " ORDER BY pb.uploaded_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($pendidikanIdParam) {
            $stmt->bindValue(':pendidikan_id', $pendidikanIdParam);
        }
        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
            $stmt->bindValue(':search4', "%$search%");
            $stmt->bindValue(':search5', "%$search%");
        }
        if ($jenisBerkasFilter) {
            $stmt->bindValue(':jenis_berkas', $jenisBerkasFilter);
        }

        $stmt->execute();
        $berkas = $stmt->fetchAll();

        // Get distinct jenis_berkas for filter
        $jenisBerkas = $pdo->query("SELECT DISTINCT jenis_berkas FROM pendidikan_berkas WHERE jenis_berkas IS NOT NULL ORDER BY jenis_berkas")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Berkas Pendidikan</h1>
        <?php if ($pendidikanIdParam): ?>
            <?php
            $pendidikanStmt = $pdo->prepare("SELECT p.jenjang, p.nama_institusi, pe.nama_lengkap FROM pendidikan p
                                             LEFT JOIN pegawai pe ON p.pegawai_id = pe.id
                                             WHERE p.id = ?");
            $pendidikanStmt->execute([$pendidikanIdParam]);
            $pendidikanData = $pendidikanStmt->fetch();
            $pendidikanInfo = $pendidikanData ? $pendidikanData['jenjang'] . ' - ' . $pendidikanData['nama_institusi'] . ' (' . $pendidikanData['nama_lengkap'] . ')' : 'Pendidikan Tidak Ditemukan';
            ?>
            <p class="text-gray-500 mt-1">Kelola berkas pendidikan <?php echo htmlspecialchars($pendidikanInfo); ?></p>
        <?php else: ?>
            <p class="text-gray-500 mt-1">Kelola berkas pendidikan semua pegawai</p>
        <?php endif; ?>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <?php
    $createUrl = "index.php?page=pegawai_pendidikan_berkas&action=create";
    if ($pendidikanIdFromSearch) {
        $createUrl .= "&pendidikan_id=" . $pendidikanIdFromSearch;
    }
    ?>
    <a href="<?php echo $createUrl; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Berkas</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="pegawai_pendidikan_berkas">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama file, jenis berkas, atau pendidikan..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="jenis_berkas" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Jenis Berkas</option>
            <?php foreach ($jenisBerkas as $jenis): ?>
                <option value="<?php echo $jenis['jenis_berkas']; ?>" <?php echo $jenisBerkasFilter === $jenis['jenis_berkas'] ? 'selected' : ''; ?>>
                    <?php echo $jenis['jenis_berkas']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $jenisBerkasFilter): ?>
        <a href="index.php?page=pegawai_pendidikan_berkas" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Files Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($berkas) > 0): ?>
        <?php foreach ($berkas as $file): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- File Info -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800 truncate" title="<?php echo htmlspecialchars($file['nama_file']); ?>"><?php echo htmlspecialchars($file['nama_file']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($file['pegawai_nama'] ?? 'Pegawai tidak ditemukan'); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Jenis Berkas</p>
                            <p class="font-medium"><?php echo htmlspecialchars($file['jenis_berkas']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Pendidikan</p>
                            <p class="font-medium"><?php echo htmlspecialchars($file['jenjang'] . ' - ' . $file['nama_institusi']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Ukuran File</p>
                            <p class="font-medium"><?php echo $file['ukuran_file'] ? formatFileSize($file['ukuran_file']) : '-'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tipe File</p>
                            <p class="font-medium"><?php echo htmlspecialchars($file['tipe_file'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars(date('d M Y', strtotime($file['uploaded_at']))); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="<?php echo getEducationFileUrl(htmlspecialchars($file['path_file'])); ?>" target="_blank"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Lihat File">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai_pendidikan_berkas&action=edit&id=<?php echo $file['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai_pendidikan_berkas&action=delete&id=<?php echo $file['id']; ?>"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada berkas pendidikan</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>