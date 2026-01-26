<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

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
    case 'generate':
        require_once 'generate.php';
        break;
    default:
        // List saldo cuti
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $tahunFilter = isset($_GET['tahun']) ? cleanInput($_GET['tahun']) : date('Y');

        $sql = "SELECT sc.*, p.nama_lengkap
                FROM saldo_cuti sc
                LEFT JOIN pegawai p ON sc.pegawai_id = p.id
                WHERE 1=1";

        if ($search) {
            $sql .= " AND (p.nama_lengkap LIKE :search1)";
        }
        
        $sql .= " AND sc.tahun = :tahun";

        $sql .= " ORDER BY p.nama_lengkap ASC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
        }
        $stmt->bindValue(':tahun', $tahunFilter);

        $stmt->execute();
        $saldo_cuti_list = $stmt->fetchAll();
        
        // Get available years for filter
        $yearsStmt = $pdo->query("SELECT DISTINCT tahun FROM saldo_cuti ORDER BY tahun DESC");
        $availableYears = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Saldo Cuti Management</h1>
        <p class="text-gray-500 mt-1">Kelola saldo cuti pegawai</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="index.php?page=adminRole_saldo_cuti&action=generate" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span>Generate Saldo</span>
        </a>
        <?php if (hasRole(['admin'])): ?>
        <a href="index.php?page=adminRole_saldo_cuti&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Saldo Manual</span>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="adminRole_saldo_cuti">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari berdasarkan nama pegawai..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="tahun" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Tahun</option>
            <?php foreach ($availableYears as $year): ?>
                <option value="<?php echo $year; ?>" <?php echo $tahunFilter === $year ? 'selected' : ''; ?>>
                    <?php echo $year; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $tahunFilter): ?>
        <a href="index.php?page=adminRole_saldo_cuti" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Saldo Cuti List View -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pegawai</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cuti</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Cuti</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sumber</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($saldo_cuti_list) > 0): ?>
                    <?php foreach ($saldo_cuti_list as $sc): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sc['nama_lengkap']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo $sc['tahun']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo $sc['total_cuti']; ?> hari</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo $sc['sisa_cuti']; ?> hari</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php
                                        if ($sc['sumber'] === 'custom') {
                                            echo 'bg-purple-100 text-purple-800';
                                        } else {
                                            echo 'bg-blue-100 text-blue-800';
                                        }
                                    ?>">
                                    <?php echo ucfirst($sc['sumber']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="index.php?page=adminRole_saldo_cuti&action=edit&id=<?php echo $sc['id']; ?>"
                                   class="text-blue-600 hover:text-blue-900 mr-4" title="Edit">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="index.php?page=adminRole_saldo_cuti&action=delete&id=<?php echo $sc['id']; ?>"
                                   class="text-red-600 hover:text-red-900" title="Hapus">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium text-gray-500">Tidak ada data saldo cuti</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
        break;
}
?>