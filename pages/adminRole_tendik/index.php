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
    default:
        // List tendik
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? cleanInput($_GET['status']) : '';

        $sql = "SELECT t.*, p.nama_lengkap, p.email FROM tendik t JOIN pegawai p ON t.pegawai_id = p.id WHERE 1=1";

        if ($search) {
            $sql .= " AND (p.nama_lengkap LIKE :search1 OR p.email LIKE :search2 OR t.nip LIKE :search3 OR t.jabatan LIKE :search4)";
        }

        if ($statusFilter) {
            $sql .= " AND t.status_kepegawaian = :status";
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
            $stmt->bindValue(':search4', "%$search%");
        }
        if ($statusFilter) {
            $stmt->bindValue(':status', $statusFilter);
        }

        $stmt->execute();
        $tendik = $stmt->fetchAll();

        // Get distinct status for filter
        $status = $pdo->query("SELECT DISTINCT status_kepegawaian FROM tendik WHERE status_kepegawaian IS NOT NULL ORDER BY status_kepegawaian")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Tendik Management</h1>
        <p class="text-gray-500 mt-1">Kelola data tenaga kependidikan</p>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <a href="index.php?page=adminRole_tendik&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Tendik</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="adminRole_tendik">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama, email, NIP, atau jabatan..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Status</option>
            <?php foreach ($status as $s): ?>
                <option value="<?php echo $s['status_kepegawaian']; ?>" <?php echo $statusFilter === $s['status_kepegawaian'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $s['status_kepegawaian'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $statusFilter): ?>
        <a href="index.php?page=adminRole_tendik" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tendik Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($tendik) > 0): ?>
        <?php foreach ($tendik as $t): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Tendik Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($t['nama_lengkap']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($t['email']); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">NIP</p>
                            <p class="font-medium"><?php echo htmlspecialchars($t['nip'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jabatan</p>
                            <p class="font-medium"><?php echo htmlspecialchars($t['jabatan']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status Kepegawaian</p>
                            <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $t['status_kepegawaian']))); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Golongan</p>
                            <p class="font-medium"><?php echo htmlspecialchars($t['golongan'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars('Mulai Kerja: ' . date('d M Y', strtotime($t['tanggal_mulai_kerja']))); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=adminRole_tendik&action=edit&id=<?php echo $t['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=adminRole_tendik&action=delete&id=<?php echo $t['id']; ?>"
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
            <p class="text-lg font-medium text-gray-500">Tidak ada data tenaga kependidikan</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>