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
        // List dosen
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
        $ikatanFilter = isset($_GET['ikatan']) ? cleanInput($_GET['ikatan']) : '';

        $sql = "SELECT d.*, p.nama_lengkap, p.email FROM dosen d JOIN pegawai p ON d.pegawai_id = p.id WHERE 1=1";

        if ($search) {
            $sql .= " AND (p.nama_lengkap LIKE :search1 OR p.email LIKE :search2 OR d.nidn LIKE :search3 OR d.nidk LIKE :search4)";
        }

        if ($statusFilter) {
            $sql .= " AND d.status_dosen = :status";
        }

        if ($ikatanFilter) {
            $sql .= " AND d.status_ikatan = :ikatan";
        }

        $sql .= " ORDER BY d.created_at DESC";

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
        if ($ikatanFilter) {
            $stmt->bindValue(':ikatan', $ikatanFilter);
        }

        $stmt->execute();
        $dosen = $stmt->fetchAll();

        // Get distinct status and ikatan for filter
        $status = $pdo->query("SELECT DISTINCT status_dosen FROM dosen WHERE status_dosen IS NOT NULL ORDER BY status_dosen")->fetchAll();
        $ikatan = $pdo->query("SELECT DISTINCT status_ikatan FROM dosen WHERE status_ikatan IS NOT NULL ORDER BY status_ikatan")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Dosen Management</h1>
        <p class="text-gray-500 mt-1">Kelola data dosen</p>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <a href="index.php?page=dosen&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Dosen</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="dosen">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama, email, NIDN, atau NIDK..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Status</option>
            <?php foreach ($status as $s): ?>
                <option value="<?php echo $s['status_dosen']; ?>" <?php echo $statusFilter === $s['status_dosen'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $s['status_dosen'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="ikatan" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Ikatan</option>
            <?php foreach ($ikatan as $i): ?>
                <option value="<?php echo $i['status_ikatan']; ?>" <?php echo $ikatanFilter === $i['status_ikatan'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $i['status_ikatan'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $statusFilter || $ikatanFilter): ?>
        <a href="index.php?page=dosen" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Dosen Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($dosen) > 0): ?>
        <?php foreach ($dosen as $d): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Dosen Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($d['nama_lengkap']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($d['email']); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">NIDN</p>
                            <p class="font-medium"><?php echo htmlspecialchars($d['nidn'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">NIDK</p>
                            <p class="font-medium"><?php echo htmlspecialchars($d['nidk'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status Dosen</p>
                            <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $d['status_dosen']))); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Ikatan Kerja</p>
                            <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $d['status_ikatan']))); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jenjang Pendidikan</p>
                            <p class="font-medium"><?php echo htmlspecialchars($d['jenjang_pendidikan']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jabatan Fungsional</p>
                            <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $d['jabatan_fungsional']))); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars('Sertifikat: ' . ($d['sertifikat_pendidik'] ? 'Ya' : 'Tidak')); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=dosen&action=edit&id=<?php echo $d['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=dosen&action=delete&id=<?php echo $d['id']; ?>"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data dosen</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>