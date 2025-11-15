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
        // List prodi
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $fakultasFilter = isset($_GET['fakultas']) ? (int)$_GET['fakultas'] : 0;
        $jenjangFilter = isset($_GET['jenjang']) ? cleanInput($_GET['jenjang']) : '';

        $sql = "SELECT p.*, f.nama_fakultas, pe.nama_lengkap as nama_kaprodi FROM prodi p
                LEFT JOIN fakultas f ON p.fakultas_id = f.id
                LEFT JOIN dosen d ON p.kaprodi_id = d.id
                LEFT JOIN pegawai pe ON d.pegawai_id = pe.id
                WHERE 1=1";

        if ($search) {
            $sql .= " AND (p.nama_prodi LIKE :search1 OR p.kode_prodi LIKE :search2 OR f.nama_fakultas LIKE :search3)";
        }

        if ($fakultasFilter) {
            $sql .= " AND p.fakultas_id = :fakultas";
        }

        if ($jenjangFilter) {
            $sql .= " AND p.jenjang = :jenjang";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
        }
        if ($fakultasFilter) {
            $stmt->bindValue(':fakultas', $fakultasFilter);
        }
        if ($jenjangFilter) {
            $stmt->bindValue(':jenjang', $jenjangFilter);
        }

        $stmt->execute();
        $prodi = $stmt->fetchAll();

        // Get all fakultas for filter
        $fakultasList = $pdo->query("SELECT id, nama_fakultas FROM fakultas ORDER BY nama_fakultas ASC")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Prodi Management</h1>
        <p class="text-gray-500 mt-1">Kelola data program studi</p>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <a href="index.php?page=prodi&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Prodi</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="prodi">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama prodi, kode prodi, atau nama fakultas..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="fakultas" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Fakultas</option>
            <?php foreach ($fakultasList as $fakultas): ?>
                <option value="<?php echo $fakultas['id']; ?>" <?php echo $fakultasFilter === $fakultas['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($fakultas['nama_fakultas']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="jenjang" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Jenjang</option>
            <option value="D3" <?php echo $jenjangFilter === 'D3' ? 'selected' : ''; ?>>D3</option>
            <option value="D4" <?php echo $jenjangFilter === 'D4' ? 'selected' : ''; ?>>D4</option>
            <option value="S1" <?php echo $jenjangFilter === 'S1' ? 'selected' : ''; ?>>S1</option>
            <option value="S2" <?php echo $jenjangFilter === 'S2' ? 'selected' : ''; ?>>S2</option>
            <option value="S3" <?php echo $jenjangFilter === 'S3' ? 'selected' : ''; ?>>S3</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $fakultasFilter || $jenjangFilter): ?>
        <a href="index.php?page=prodi" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Prodi Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($prodi) > 0): ?>
        <?php foreach ($prodi as $p): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Prodi Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($p['nama_prodi']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Kode: <?php echo htmlspecialchars($p['kode_prodi']); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Fakultas</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['nama_fakultas']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jenjang</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['jenjang']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Kaprodi</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['nama_kaprodi'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Kuota</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['kuota_mahasiswa']); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full <?php echo $p['status_aktif'] ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                                <?php echo $p['status_aktif'] ? 'Aktif' : 'Tidak Aktif'; ?>
                            </span>
                            <span class="ml-2 text-sm text-gray-500"><?php echo htmlspecialchars($p['akreditasi'] ?? 'Belum ada'); ?></span>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=prodi&action=edit&id=<?php echo $p['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=prodi&action=delete&id=<?php echo $p['id']; ?>"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data program studi</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>