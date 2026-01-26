<?php
// Check permission - only admin can create roles
if (!hasRole('admin')) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = cleanInput($_POST['role_name']);
    $description = cleanInput($_POST['description']);

    // Validation
    if (empty($roleName)) {
        $error = 'Nama role harus diisi!';
    } elseif (strlen($roleName) < 3) {
        $error = 'Nama role minimal 3 karakter!';
    } else {
        // Check if role name already exists
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
        $stmt->execute([$roleName]);

        if ($stmt->fetch()) {
            $error = 'Nama role sudah digunakan!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");

            if ($stmt->execute([$roleName, $description])) {
                logActivity($_SESSION['user_id'], 'create_role', "Created new role: $roleName");
                setAlert('success', 'Role berhasil ditambahkan!');
                redirect('index.php?page=adminRole_roles');
            } else {
                $error = 'Gagal menambahkan role!';
            }
        }
    }
}

?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_roles" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Role Baru</h1>
            <p class="text-gray-500 mt-1">Buat role akses baru</p>
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
                   value="<?php echo isset($_POST['role_name']) ? htmlspecialchars($_POST['role_name']) : ''; ?>"
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <p class="text-xs text-gray-500 mt-1">Gunakan huruf kecil tanpa spasi (contoh: admin, manager, staff)</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Simpan Role
            </button>
            <a href="index.php?page=adminRole_roles" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>