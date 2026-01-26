<?php
// Check permission - allow admin, dosen, and tendik roles
if (!hasRole(['admin', 'dosen', 'tendik'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

switch ($action) {
    case 'create':
        require_once 'create.php';
        break;
    case 'edit':
        require_once 'edit.php';
        break;
    case 'detail':
        require_once 'detail.php';
        break;
    case 'delete':
        require_once 'delete.php';
        break;
    default:
        // For dosen and tendik roles, show only their own family data
        if (hasRole(['dosen', 'tendik'])) {
            // Get user info first
            $user_info_sql = "SELECT * FROM users WHERE id = :user_id";
            $user_stmt = $pdo->prepare($user_info_sql);
            $user_stmt->bindValue(':user_id', $current_user_id);
            $user_stmt->execute();
            $user_info = $user_stmt->fetch();

            // Get pegawai info based on user's email
            $pegawai_sql = "SELECT * FROM pegawai WHERE email = :email";
            $pegawai_stmt = $pdo->prepare($pegawai_sql);
            $pegawai_stmt->bindValue(':email', $user_info['email']);
            $pegawai_stmt->execute();
            $pegawai = $pegawai_stmt->fetch();

            if (!$pegawai) {
                // If no pegawai record found, show error
                require_once __DIR__ . '/../errors/404.php';
                exit;
            }

            // Get family data for this pegawai
            $family_sql = "SELECT * FROM pegawai_keluarga WHERE pegawai_id = :pegawai_id ORDER BY created_at DESC";
            $family_stmt = $pdo->prepare($family_sql);
            $family_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $family_stmt->execute();
            $keluarga = $family_stmt->fetchAll();
        } else {
            // For admin role, show all family data with filters
            $pegawaiIdParam = isset($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : null;
            $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
            $statusFilter = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
            $jenisFilter = isset($_GET['jenis']) ? cleanInput($_GET['jenis']) : '';

            // Store current search parameters in session
            $_SESSION['pegawai_keluarga_search'] = [
                'pegawai_id' => $pegawaiIdParam,
                'search' => $search,
                'status' => $statusFilter,
                'jenis' => $jenisFilter
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
                    $sqlCheck = "SELECT DISTINCT pk.pegawai_id, p.nama_lengkap FROM pegawai_keluarga pk
                                 LEFT JOIN pegawai p ON pk.pegawai_id = p.id
                                 WHERE 1=1";

                    if ($search) {
                        $sqlCheck .= " AND (p.nama_lengkap LIKE :search1 OR pk.nama_lengkap LIKE :search2 OR pk.nomor_induk LIKE :search3)";
                    }

                    $stmtCheck = $pdo->prepare($sqlCheck);

                    if ($search) {
                        $stmtCheck->bindValue(':search1', "%$search%");
                        $stmtCheck->bindValue(':search2', "%$search%");
                        $stmtCheck->bindValue(':search3', "%$search%");
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

            $sql = "SELECT pk.*, p.nama_lengkap FROM pegawai_keluarga pk
                    JOIN pegawai p ON pk.pegawai_id = p.id WHERE 1=1";

            if ($pegawaiIdFromSearch) {
                $sql .= " AND pk.pegawai_id = :pegawai_id";
            }

            if ($search) {
                $sql .= " AND (p.nama_lengkap LIKE :search1 OR pk.nama_lengkap LIKE :search2 OR pk.nomor_induk LIKE :search3)";
            }

            if ($statusFilter) {
                $sql .= " AND pk.hubungan = :status";
            }

            if ($jenisFilter) {
                $sql .= " AND pk.jenis_kelamin = :jenis";
            }

            $sql .= " ORDER BY pk.created_at DESC";

            $stmt = $pdo->prepare($sql);

            if ($pegawaiIdFromSearch) {
                $stmt->bindValue(':pegawai_id', $pegawaiIdFromSearch);
            }
            if ($search) {
                $stmt->bindValue(':search1', "%$search%");
                $stmt->bindValue(':search2', "%$search%");
                $stmt->bindValue(':search3', "%$search%");
            }
            if ($statusFilter) {
                $stmt->bindValue(':status', $statusFilter);
            }
            if ($jenisFilter) {
                $stmt->bindValue(':jenis', $jenisFilter);
            }

            $stmt->execute();
            $keluarga = $stmt->fetchAll();

            // Get distinct status and jenis for filter
            $status = $pdo->query("SELECT DISTINCT hubungan FROM pegawai_keluarga WHERE hubungan IS NOT NULL ORDER BY hubungan")->fetchAll();
            $jenis = $pdo->query("SELECT DISTINCT jenis_kelamin FROM pegawai_keluarga WHERE jenis_kelamin IS NOT NULL ORDER BY jenis_kelamin")->fetchAll();
        }
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Keluarga Pegawai</h1>
        <?php if ($pegawaiIdParam): ?>
            <?php
            $pegawaiStmt = $pdo->prepare("SELECT nama_lengkap FROM pegawai WHERE id = ?");
            $pegawaiStmt->execute([$pegawaiIdParam]);
            $pegawaiData = $pegawaiStmt->fetch();
            $pegawaiName = $pegawaiData ? $pegawaiData['nama_lengkap'] : 'Pegawai Tidak Ditemukan';
            ?>
            <p class="text-gray-500 mt-1">Data keluarga <?php echo htmlspecialchars($pegawaiName); ?></p>
        <?php else: ?>
            <p class="text-gray-500 mt-1">Data keluarga pegawai</p>
        <?php endif; ?>
    </div>
    <?php if (hasRole(['admin', 'dosen', 'tendik'])): ?>
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

<?php if (hasRole(['dosen', 'tendik'])): ?>
<!-- Family Card View for Dosen/Tendik -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($keluarga) > 0): ?>
        <?php foreach ($keluarga as $k): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Family Member Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($k['nama_lengkap'] ?? '-'); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php
                            $hubungan = $k['hubungan'] ?? '';
                            echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
                            ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Nomor Induk</p>
                            <p class="font-medium"><?php echo htmlspecialchars($k['nomor_induk'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">NIK</p>
                            <p class="font-medium"><?php echo htmlspecialchars($k['nik'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium"><?php echo htmlspecialchars(($k['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tempat Lahir</p>
                            <p class="font-medium"><?php echo htmlspecialchars($k['tempat_lahir'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars($k['tanggal_lahir'] ? date('d M Y', strtotime($k['tanggal_lahir'])) : '-'); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=pegawai_keluarga&action=detail&id=<?php echo $k['id']; ?>"
                               class="text-green-600 hover:text-green-800 transition-colors" title="Detail">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai_keluarga&action=edit&id=<?php echo $k['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data keluarga</p>
        </div>
    <?php endif; ?>
</div>
<?php else: ?>
<!-- Search & Filter for Admin -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="pegawai_keluarga">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama pegawai atau keluarga..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Status</option>
            <?php foreach ($status as $s): ?>
                <option value="<?php echo $s['hubungan']; ?>" <?php echo $statusFilter === $s['hubungan'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $s['hubungan'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="jenis" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Jenis</option>
            <?php foreach ($jenis as $j): ?>
                <option value="<?php echo $j['jenis_kelamin']; ?>" <?php echo $jenisFilter === $j['jenis_kelamin'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $j['jenis_kelamin'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $statusFilter || $jenisFilter || $pegawaiIdParam): ?>
        <a href="index.php?page=pegawai_keluarga" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Family Card View for Admin -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($keluarga) > 0): ?>
        <?php foreach ($keluarga as $k): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Family Member Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($k['nama_lengkap'] ?? '-'); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php
                            $hubungan = $k['hubungan'] ?? '';
                            echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
                            ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Nomor Induk</p>
                            <p class="font-medium"><?php echo htmlspecialchars($k['nomor_induk'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">NIK</p>
                            <p class="font-medium"><?php echo htmlspecialchars($k['nik'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium"><?php echo htmlspecialchars(($k['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tempat Lahir</p>
                            <p class="font-medium"><?php echo htmlspecialchars($k['tempat_lahir'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars($k['tanggal_lahir'] ? date('d M Y', strtotime($k['tanggal_lahir'])) : '-'); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=pegawai_keluarga&action=detail&id=<?php echo $k['id']; ?>"
                               class="text-green-600 hover:text-green-800 transition-colors" title="Detail">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai_keluarga&action=edit&id=<?php echo $k['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data keluarga</p>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
        break;
}
?>