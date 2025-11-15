<?php
// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalActive = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.role_name = 'admin'")->fetchColumn();

// Get recent activities
$stmt = $pdo->prepare("
    SELECT al.*, u.username, u.first_name, u.last_name 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$activities = $stmt->fetchAll();

// Get user's recent activities
$stmtUserActivities = $pdo->prepare("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmtUserActivities->execute([$_SESSION['user_id']]);
$userActivities = $stmtUserActivities->fetchAll();
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-500 mt-1">Selamat datang kembali, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Users</p>
                <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totalUsers); ?></p>
                <p class="text-xs text-gray-400 mt-2">Semua pengguna terdaftar</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Active Users</p>
                <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totalActive); ?></p>
                <p class="text-xs text-green-600 mt-2">
                    <?php echo $totalUsers > 0 ? round(($totalActive/$totalUsers)*100) : 0; ?>% dari total
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Administrators</p>
                <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totalAdmins); ?></p>
                <p class="text-xs text-gray-400 mt-2">Total admin aktif</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Your Role</p>
                <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo ucfirst($currentUser['role_name']); ?></p>
                <p class="text-xs text-gray-400 mt-2">Level akses Anda</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity & Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent Activity (All Users - Admin & Manager only) -->
    <?php if (hasRole(['admin', 'manager'])): ?>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Recent Activity (All Users)</h3>
            <span class="text-xs text-gray-500"><?php echo count($activities); ?> aktivitas</span>
        </div>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php if (count($activities) > 0): ?>
                <?php foreach ($activities as $activity): ?>
                <div class="flex items-start space-x-3 pb-3 border-b last:border-b-0">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700">
                            <strong class="font-semibold"><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></strong>
                            <span class="text-gray-600"><?php echo htmlspecialchars($activity['description']); ?></span>
                        </p>
                        <div class="flex items-center space-x-2 mt-1">
                            <p class="text-xs text-gray-400"><?php echo formatDate($activity['created_at']); ?></p>
                            <?php if ($activity['ip_address']): ?>
                            <span class="text-gray-300">•</span>
                            <p class="text-xs text-gray-400">IP: <?php echo htmlspecialchars($activity['ip_address']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Belum ada aktivitas</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- My Recent Activity (For Staff & User) -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">My Recent Activity</h3>
            <span class="text-xs text-gray-500"><?php echo count($userActivities); ?> aktivitas</span>
        </div>
        <div class="space-y-3">
            <?php if (count($userActivities) > 0): ?>
                <?php foreach ($userActivities as $activity): ?>
                <div class="flex items-start space-x-3 pb-3 border-b last:border-b-0">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($activity['description']); ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?php echo formatDate($activity['created_at']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Belum ada aktivitas</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <?php if (hasRole(['admin', 'manager'])): ?>
            <a href="index.php?page=users&action=create" class="flex flex-col items-center justify-center space-y-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl py-4 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span class="font-medium text-sm">Add User</span>
            </a>
            
            <a href="index.php?page=users" class="flex flex-col items-center justify-center space-y-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-xl py-4 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="font-medium text-sm">Manage Users</span>
            </a>
            <?php endif; ?>
            
            <a href="index.php?page=profile" class="flex flex-col items-center justify-center space-y-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-xl py-4 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="font-medium text-sm">View Profile</span>
            </a>
            
            <a href="index.php?page=settings" class="flex flex-col items-center justify-center space-y-2 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-xl py-4 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="font-medium text-sm">Settings</span>
            </a>
        </div>
    </div>
</div>

<!-- Account Info Card -->
<div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold mb-2">Informasi Akun</h3>
            <div class="space-y-2 text-sm">
                <p><span class="opacity-80">Username:</span> <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong></p>
                <p><span class="opacity-80">Email:</span> <strong><?php echo htmlspecialchars($currentUser['email']); ?></strong></p>
                <p><span class="opacity-80">Last Login:</span> <strong><?php echo $currentUser['last_login'] ? formatDate($currentUser['last_login']) : 'Baru pertama kali'; ?></strong></p>
            </div>
        </div>
        <div class="hidden md:block">
            <img src="<?php echo getAvatarUrl($currentUser['avatar']); ?>" class="w-24 h-24 rounded-full border-4 border-white/30">
        </div>
    </div>
</div>