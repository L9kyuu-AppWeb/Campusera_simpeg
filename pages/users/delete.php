<?php
// Check permission - only admin can delete users
if (!hasRole('admin')) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$userId) {
    redirect('index.php?page=users');
}

// Cannot delete yourself
if ($userId == $_SESSION['user_id']) {
    setAlert('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
    redirect('index.php?page=users');
}

// Get user data
$stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setAlert('error', 'User tidak ditemukan!');
    redirect('index.php?page=users');
}

// Process delete confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$userId])) {
        logActivity($_SESSION['user_id'], 'delete_user', "Deleted user: " . $user['username']);
        setAlert('success', 'User berhasil dihapus!');
    } else {
        setAlert('error', 'Gagal menghapus user!');
    }
    redirect('index.php?page=users');
}
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
        <!-- User Card Header -->
        <div class="bg-red-50 border-b border-red-100 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-gray-800">Konfirmasi Penghapusan</h2>
                    <p class="text-gray-600">Anda akan menghapus user secara permanen</p>
                </div>
            </div>
        </div>
        
        <!-- User Info Section -->
        <div class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail User</h3>
                
                <div class="flex items-center space-x-4 mb-6">
                    <img src="<?php echo getAvatarUrl($user['avatar']); ?>" class="w-16 h-16 rounded-full border">
                    <div>
                        <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                        <p class="text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Role</p>
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                            <?php
                                echo $user['role_name'] === 'admin' ? 'bg-purple-100 text-purple-700' :
                                    ($user['role_name'] === 'manager' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700');
                            ?>">
                            <?php echo ucfirst($user['role_name']); ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="font-medium">
                            <?php if ($user['is_active']): ?>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Aktif
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    Nonaktif
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Terdaftar</p>
                        <p class="font-medium"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700 text-sm">
                            <strong>Peringatan:</strong> Penghapusan user ini akan menghapus semua data terkait secara permanen dan tidak dapat dikembalikan.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="index.php?page=users" 
                   class="px-6 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Batal
                </a>
                <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user <?php echo addslashes(htmlspecialchars($user['username'])); ?>? Tindakan ini tidak dapat dibatalkan.')">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit"
                            class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors">
                        Hapus User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>