<?php
// Check permission - only admin can edit roles
if (!hasRole('admin')) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$roleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$roleId) {
    redirect('index.php?page=roles');
}

// Get role data
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$roleId]);
$role = $stmt->fetch();

if (!$role) {
    setAlert('error', 'Role tidak ditemukan!');
    redirect('index.php?page=roles');
}

// Prevent editing the admin role by non-admin users
if ($role['role_name'] === 'admin' && !hasRole('admin')) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = cleanInput($_POST['role_name']);
    $description = cleanInput($_POST['description']);

    // Validation
    if (empty($roleName)) {
        $error = 'Nama role harus diisi!';
    } elseif (strlen($roleName) < 3) {
        $error = 'Nama role minimal 3 karakter!';
    } else {
        // Check if role name already exists for other roles
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ? AND id != ?");
        $stmt->execute([$roleName, $roleId]);

        if ($stmt->fetch()) {
            $error = 'Nama role sudah digunakan!';
        } else {
            $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");

            if ($stmt->execute([$roleName, $description, $roleId])) {
                logActivity($_SESSION['user_id'], 'update_role', "Updated role: $roleName");
                setAlert('success', 'Role berhasil diperbarui!');
                redirect('index.php?page=roles');
            } else {
                $error = 'Gagal memperbarui role!';
            }
        }
    }
}

?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=roles" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Role</h1>
            <p class="text-gray-500 mt-1">Perbarui informasi role</p>
        </div>
    </div>
</div>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Role *</label>
            <input type="text" name="role_name" required
                   value="<?php echo htmlspecialchars($role['role_name']); ?>"
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                   <?php echo $role['role_name'] === 'admin' ? 'readonly' : ''; ?>>
            <?php if ($role['role_name'] === 'admin'): ?>
                <p class="text-xs text-gray-500 mt-1">Role admin tidak dapat diubah namanya</p>
            <?php else: ?>
                <p class="text-xs text-gray-500 mt-1">Gunakan huruf kecil tanpa spasi (contoh: manager, staff)</p>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo htmlspecialchars($role['description']); ?></textarea>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Update Role
            </button>
            <a href="index.php?page=roles" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>