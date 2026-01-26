<?php
// Check permission
if (!hasRole(['admin', 'manager'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

switch ($action) {
    case 'create':
        require_once __DIR__ . '/create.php';
        break;
    case 'edit':
        require_once __DIR__ . '/edit.php';
        break;
    case 'delete':
        require_once __DIR__ . '/delete.php';
        break;
    default:
        // List roles
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

        $sql = "SELECT * FROM roles WHERE 1=1";

        if ($search) {
            $sql .= " AND (role_name LIKE :search1 OR description LIKE :search2)";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
        }

        $stmt->execute();
        $roles = $stmt->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Role Management</h1>
        <p class="text-gray-500 mt-1">Kelola hak akses sistem</p>
    </div>
    <?php if (hasRole('admin')): ?>
    <a href="index.php?page=adminRole_roles&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Role</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="adminRole_roles">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama role atau deskripsi..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Cari
        </button>
        <?php if ($search): ?>
        <a href="index.php?page=adminRole_roles" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Roles Table -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Deskripsi</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Dibuat</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (count($roles) > 0): ?>
                    <?php foreach ($roles as $role): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-800"><?php echo htmlspecialchars(ucfirst($role['role_name'])); ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($role['description']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d M Y', strtotime($role['created_at'])); ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="index.php?page=adminRole_roles&action=edit&id=<?php echo $role['id']; ?>"
                                   class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php if (hasRole('admin') && $role['role_name'] !== 'admin'): ?>
                                <a href="index.php?page=adminRole_roles&action=delete&id=<?php echo $role['id']; ?>"
                                   onclick="return confirmDelete('Apakah Anda yakin ingin menghapus role <?php echo htmlspecialchars($role['role_name']); ?>?')"
                                   class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-lg font-medium">Tidak ada data role</p>
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