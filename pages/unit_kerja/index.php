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
        // List unit_kerja
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $tipeFilter = isset($_GET['tipe']) ? cleanInput($_GET['tipe']) : '';

        $sql = "SELECT u.*, parent.nama_unit as parent_unit, p.nama_lengkap as kepala_unit_nama FROM unit_kerja u
                LEFT JOIN unit_kerja parent ON u.parent_id = parent.id
                LEFT JOIN pegawai p ON u.kepala_unit_id = p.id
                WHERE 1=1";

        if ($search) {
            $sql .= " AND (u.nama_unit LIKE :search1 OR parent.nama_unit LIKE :search2 OR p.nama_lengkap LIKE :search3)";
        }

        if ($tipeFilter) {
            $sql .= " AND u.tipe_unit = :tipe";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
        }
        if ($tipeFilter) {
            $stmt->bindValue(':tipe', $tipeFilter);
        }

        $stmt->execute();
        $unit_kerja = $stmt->fetchAll();

        // Get distinct tipe for filter
        $tipe = $pdo->query("SELECT DISTINCT tipe_unit FROM unit_kerja WHERE tipe_unit IS NOT NULL ORDER BY tipe_unit")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Unit Kerja Management</h1>
        <p class="text-gray-500 mt-1">Kelola data unit kerja</p>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <a href="index.php?page=unit_kerja&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Unit Kerja</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="unit_kerja">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama unit, parent unit, atau kepala unit..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="tipe" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Tipe</option>
            <?php foreach ($tipe as $t): ?>
                <option value="<?php echo $t['tipe_unit']; ?>" <?php echo $tipeFilter === $t['tipe_unit'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $t['tipe_unit'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $tipeFilter): ?>
        <a href="index.php?page=unit_kerja" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Unit Kerja Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($unit_kerja) > 0): ?>
        <?php foreach ($unit_kerja as $u): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Unit Kerja Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($u['nama_unit']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Tipe: <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $u['tipe_unit']))); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Parent Unit</p>
                            <p class="font-medium"><?php echo htmlspecialchars($u['parent_unit'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Kepala Unit</p>
                            <p class="font-medium"><?php echo htmlspecialchars($u['kepala_unit_nama'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars('Dibuat: ' . date('d M Y', strtotime($u['created_at']))); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=unit_kerja&action=edit&id=<?php echo $u['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=unit_kerja&action=delete&id=<?php echo $u['id']; ?>"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data unit kerja</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>