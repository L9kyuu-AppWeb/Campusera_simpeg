<?php
if (!isset($currentUser)) {
    $currentUser = getCurrentUser();
}
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo ucfirst(str_replace('_', ' ', $currentPage)); ?></title>
    <link rel="icon" type="image/svg" href="company-icon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8f9fa; }
        .sidebar-item:hover { background: rgba(59, 130, 246, 0.1); }
        .sidebar-item.active { background: rgba(59, 130, 246, 0.15); border-left: 3px solid #3b82f6; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .transition-all { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-800"><?php echo SITE_NAME; ?></span>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button onclick="toggleProfileMenu()" class="flex items-center space-x-3 pl-4 border-l hover:bg-gray-50 rounded-lg p-2 transition-colors">
                        <img src="<?php echo getAvatarUrl($currentUser['avatar']); ?>" class="w-9 h-9 rounded-full">
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($currentUser['first_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo ucfirst($currentUser['role_name']); ?></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 z-50">
                        <div class="p-4 border-b">
                            <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                        </div>
                        <div class="py-2">
                            <a href="index.php?page=profile" class="w-full flex items-center space-x-3 px-4 py-2 hover:bg-gray-50 transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-sm text-gray-700">Profil Saya</span>
                            </a>
                            <a href="index.php?page=settings" class="w-full flex items-center space-x-3 px-4 py-2 hover:bg-gray-50 transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm text-gray-700">Pengaturan</span>
                            </a>
                        </div>
                        <div class="border-t py-2">
                            <a href="index.php?page=logout" onclick="return confirm('Apakah Anda yakin ingin logout?')" class="w-full flex items-center space-x-3 px-4 py-2 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="text-sm text-red-600 font-medium">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($alert): ?>
    <div id="alert" class="fixed top-20 right-6 z-50 max-w-md animate-slide-in">
        <div class="bg-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'error' ? 'red' : 'blue'); ?>-100 border border-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'error' ? 'red' : 'blue'); ?>-400 text-<?php echo $alert['type'] === 'success' ? 'green' : ($alert['type'] === 'error' ? 'red' : 'blue'); ?>-700 px-4 py-3 rounded-xl shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm"><?php echo htmlspecialchars($alert['message']); ?></span>
                <button onclick="closeAlert()" class="ml-4">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <script>
        setTimeout(() => closeAlert(), 5000);
        function closeAlert() {
            const alert = document.getElementById('alert');
            if (alert) alert.remove();
        }
        
        // Function to confirm delete actions
        function confirmDelete(message) {
            return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
        }
    </script>
    <?php endif; ?>