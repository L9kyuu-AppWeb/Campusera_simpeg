<?php
$error = '';
$success = false;

// Handle change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Semua field password harus diisi!';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Password baru dan konfirmasi password tidak cocok!';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch();
        
        if (password_verify($currentPassword, $userData['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                logActivity($_SESSION['user_id'], 'change_password', 'User changed password');
                setAlert('success', 'Password berhasil diubah!');
                redirect('index.php?page=settings');
            } else {
                $error = 'Gagal mengubah password!';
            }
        } else {
            $error = 'Password saat ini salah!';
        }
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Settings</h1>
    <p class="text-gray-500 mt-1">Kelola pengaturan akun Anda</p>
</div>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Settings Navigation -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Menu Settings</h3>
        <div class="space-y-2">
            <button onclick="showSection('security')" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors setting-nav active" data-section="security">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Keamanan</span>
                </div>
            </button>
            
            <button onclick="showSection('account')" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors setting-nav" data-section="account">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Informasi Akun</span>
                </div>
            </button>
            
            <button onclick="showSection('activity')" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 transition-colors setting-nav" data-section="activity">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="font-medium text-gray-700">Aktivitas</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="lg:col-span-2">
        <!-- Security Section -->
        <div id="security-section" class="setting-section bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Keamanan Akun</h3>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="change_password" value="1">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini *</label>
                    <input type="password" name="current_password" required 
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru *</label>
                    <input type="password" name="new_password" required 
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru *</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Account Info Section -->
        <div id="account-section" class="setting-section hidden bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Informasi Akun</h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Username</p>
                        <p class="text-sm text-gray-500 mt-1"><?php echo $currentUser['username']; ?></p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between py-3 border-b">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Email</p>
                        <p class="text-sm text-gray-500 mt-1"><?php echo $currentUser['email']; ?></p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between py-3 border-b">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Role</p>
                        <p class="text-sm text-gray-500 mt-1"><?php echo ucfirst($currentUser['role_name']); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between py-3 border-b">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Terdaftar Sejak</p>
                        <p class="text-sm text-gray-500 mt-1"><?php echo formatDate($currentUser['created_at']); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Login Terakhir</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo $currentUser['last_login'] ? formatDate($currentUser['last_login']) : 'Belum pernah login'; ?>
                        </p>
                    </div>
                </div>
                
                <div class="pt-4">
                    <a href="index.php?page=profile" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                        Edit Profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Activity Section -->
        <div id="activity-section" class="setting-section hidden bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Aktivitas Terbaru</h3>
            
            <?php
            $activities = $pdo->prepare("
                SELECT * FROM activity_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 15
            ");
            $activities->execute([$_SESSION['user_id']]);
            $userActivities = $activities->fetchAll();
            ?>
            
            <div class="space-y-3">
                <?php if (count($userActivities) > 0): ?>
                    <?php foreach ($userActivities as $activity): ?>
                    <div class="flex items-start space-x-3 py-3 border-b last:border-b-0">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($activity['description']); ?></p>
                            <div class="flex items-center space-x-2 mt-1">
                                <p class="text-xs text-gray-400"><?php echo formatDate($activity['created_at']); ?></p>
                                <span class="text-gray-300">•</span>
                                <p class="text-xs text-gray-400">IP: <?php echo htmlspecialchars($activity['ip_address']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-sm text-gray-500 text-center py-8">Belum ada aktivitas</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(section) {
    // Hide all sections
    document.querySelectorAll('.setting-section').forEach(el => {
        el.classList.add('hidden');
    });
    
    // Remove active class from all nav items
    document.querySelectorAll('.setting-nav').forEach(el => {
        el.classList.remove('active', 'bg-blue-50');
    });
    
    // Show selected section
    document.getElementById(section + '-section').classList.remove('hidden');
    
    // Add active class to selected nav
    document.querySelector(`[data-section="${section}"]`).classList.add('active', 'bg-blue-50');
}
</script>
