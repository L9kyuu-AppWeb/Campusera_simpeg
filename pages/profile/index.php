<?php
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = cleanInput($_POST['first_name']);
    $lastName = cleanInput($_POST['last_name']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);

    if (empty($firstName) || empty($email)) {
        $error = 'Nama depan dan email harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Check if email already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);

        if ($stmt->fetch()) {
            $error = 'Email sudah digunakan oleh pengguna lain!';
        } else {
            $stmt = $pdo->prepare("
                UPDATE users
                SET first_name = ?, last_name = ?, email = ?, phone = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$firstName, $lastName, $email, $phone, $_SESSION['user_id']])) {
                logActivity($_SESSION['user_id'], 'update_profile', 'User updated profile information');
                setAlert('success', 'Profil berhasil diperbarui!');
                redirect('index.php?page=profile');
            } else {
                $error = 'Gagal memperbarui profil!';
            }
        }
    }
}


// Get fresh user data
$currentUser = getCurrentUser();
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Profil Saya</h1>
    <p class="text-gray-500 mt-1">Kelola informasi profil Anda</p>
</div>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Card -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="text-center">
            <img src="<?php echo getAvatarUrl($currentUser['avatar']); ?>" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-gray-100">
            <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')); ?></h3>
            <p class="text-sm text-gray-500 mt-1"><?php echo ucfirst($currentUser['role_name']); ?></p>
            <div class="mt-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Akun Aktif
                </span>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t space-y-4">
            <div class="flex items-start space-x-3 text-sm">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="text-gray-700 font-medium truncate"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
                </div>
            </div>

            <?php if ($currentUser['phone']): ?>
            <div class="flex items-start space-x-3 text-sm">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Telepon</p>
                    <p class="text-gray-700 font-medium"><?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex items-start space-x-3 text-sm">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Bergabung</p>
                    <p class="text-gray-700 font-medium"><?php echo formatDate($currentUser['created_at']); ?></p>
                </div>
            </div>

            <?php if ($currentUser['last_login']): ?>
            <div class="flex items-start space-x-3 text-sm">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Login Terakhir</p>
                    <p class="text-gray-700 font-medium"><?php echo formatDate($currentUser['last_login']); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Informasi Pribadi</h3>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Depan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" required
                           value="<?php echo htmlspecialchars($currentUser['first_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Belakang</label>
                    <input type="text" name="last_name"
                           value="<?php echo htmlspecialchars($currentUser['last_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" required
                       value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                <input type="tel" name="phone"
                       value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>"
                       placeholder="+62 812-3456-7890"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
            </div>

            <div class="flex space-x-3 pt-4 border-t">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
                <a href="index.php?page=dashboard" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>
</div>