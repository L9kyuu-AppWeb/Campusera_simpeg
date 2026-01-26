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
        // List izin pegawai
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

        $sql = "SELECT i.*, p.nama_lengkap as nama_pegawai, j.nama_izin as nama_jenis_izin
                FROM izin_pegawai i
                LEFT JOIN pegawai p ON i.pegawai_id = p.id
                LEFT JOIN jenis_izin j ON i.jenis_izin_id = j.id_jenis_izin
                WHERE 1=1";

        if ($search) {
            $sql .= " AND (p.nama_lengkap LIKE :search1 OR j.nama_izin LIKE :search2 OR i.keterangan LIKE :search3)";
        }

        $sql .= " ORDER BY i.created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
        }

        $stmt->execute();
        $izin_list = $stmt->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Izin Pegawai Management</h1>
        <p class="text-gray-500 mt-1">Kelola izin-izin pegawai</p>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <a href="index.php?page=adminRole_izin_pegawai&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Izin Pegawai</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="adminRole_izin_pegawai">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama pegawai, jenis izin atau keterangan..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Cari
        </button>
        <?php if ($search): ?>
        <a href="index.php?page=adminRole_izin_pegawai" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Izin Pegawai List View -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pegawai</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($izin_list) > 0): ?>
                    <?php foreach ($izin_list as $izin): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($izin['nama_pegawai']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($izin['nama_jenis_izin']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($izin['tanggal_mulai'])); ?> - <?php echo date('d/m/Y', strtotime($izin['tanggal_selesai'])); ?></div>
                                <div class="text-xs text-gray-400"><?php echo $izin['jumlah_hari']; ?> hari</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                        if ($izin['status'] === 'Disetujui') {
                                            echo 'bg-green-100 text-green-800';
                                        } elseif ($izin['status'] === 'Ditolak') {
                                            echo 'bg-red-100 text-red-800';
                                        } else {
                                            echo 'bg-yellow-100 text-yellow-800';
                                        }
                                    ?>">
                                    <?php echo htmlspecialchars($izin['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="index.php?page=adminRole_izin_pegawai&action=edit&id=<?php echo $izin['id_izin']; ?>"
                                   class="text-blue-600 hover:text-blue-900 mr-4" title="Edit">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="index.php?page=adminRole_izin_pegawai&action=delete&id=<?php echo $izin['id_izin']; ?>"
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
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium text-gray-500">Tidak ada data izin pegawai</p>
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