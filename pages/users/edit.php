<?php
// Check permission
if (!hasRole(['admin', 'manager'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    redirect('index.php?page=users');
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setAlert('error', 'User tidak ditemukan!');
    redirect('index.php?page=users');
}

// Manager cannot edit admin users
if (hasRole('manager') && $user['role_id'] == 1) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $firstName = cleanInput($_POST['first_name']);
    $lastName = cleanInput($_POST['last_name']);
    $phone = cleanInput($_POST['phone']);
    $roleId = cleanInput($_POST['role_id']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($firstName) || empty($roleId)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } elseif (!empty($password) && $password !== $confirmPassword) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Check if username or email already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $userId]);

        if ($stmt->fetch()) {
            $error = 'Username atau email sudah digunakan!';
        } else {
            // Update user
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, password = ?, first_name = ?, last_name = ?, 
                        phone = ?, role_id = ?, is_active = ? 
                    WHERE id = ?
                ");
                $result = $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $phone, $roleId, $isActive, $userId]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, first_name = ?, last_name = ?, 
                        phone = ?, role_id = ?, is_active = ? 
                    WHERE id = ?
                ");
                $result = $stmt->execute([$username, $email, $firstName, $lastName, $phone, $roleId, $isActive, $userId]);
            }

            if ($result) {
                logActivity($_SESSION['user_id'], 'update_user', "Updated user: $username");
                setAlert('success', 'User berhasil diperbarui!');
                redirect('index.php?page=users');
            } else {
                $error = 'Gagal memperbarui user!';
            }
        }
    }
}

// Get all roles
$roles = $pdo->query("SELECT * FROM roles ORDER BY role_name")->fetchAll();
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=users" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit User</h1>
            <p class="text-gray-500 mt-1">Perbarui informasi pengguna</p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                <select name="role_id" required
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"
                            <?php echo ($user['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center space-x-3 mt-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        <?php echo $user['is_active'] ? 'checked' : ''; ?>
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm text-gray-700">Akun Aktif</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>"
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>"
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl">
            </div>
        </div>
        
        <hr class="my-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                <input type="password" name="password"
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl"
                    placeholder="Kosongkan jika tidak diganti">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                <input type="password" name="confirm_password"
                    class="w-full px-4 py-2 border border-gray-200 rounded-xl"
                    placeholder="Ulangi password baru">
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Update User
            </button>
            <a href="index.php?page=users" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>