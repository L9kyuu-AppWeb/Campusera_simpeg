<aside id="sidebar" class="fixed left-0 top-16 h-full bg-white shadow-lg w-64 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-40 overflow-hidden">
    <div class="h-full flex flex-col">
        <div class="flex-1 overflow-y-auto p-6">
            <div class="space-y-2">
                <!-- Dashboard -->
                <a href="index.php?page=dashboard" class="sidebar-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'dashboard' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="font-medium text-gray-700">Dashboard</span>
                </a>
            <h3 class="text-xs font-semibold text-gray-400 uppercase px-4 pt-1 pb-1">Master</h3>  
                <!-- Roles (Admin only) -->
                <?php if (hasRole('admin')): ?>
                <a href="index.php?page=roles" class="sidebar-item <?php echo $currentPage === 'roles' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'roles' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Roles</span>
                </a>
                <?php endif; ?>

                <!-- Users (Admin & Manager only) -->
                <?php if (hasRole(['admin', 'manager'])): ?>
                <a href="index.php?page=users" class="sidebar-item <?php echo $currentPage === 'users' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'users' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Users</span>
                </a>
                <?php endif; ?>

                <h3 class="text-xs font-semibold text-gray-400 uppercase px-4 pt-1 pb-1">Data</h3>

                <!-- Games (Admin & Manager only) -->
                <?php if (hasRole(['admin', 'manager'])): ?>
                <a href="index.php?page=games" class="sidebar-item <?php echo $currentPage === 'games' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'games' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Games</span>
                </a>
                <?php endif; ?>

                <!-- Profile -->
                <a href="index.php?page=profile" class="sidebar-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'profile' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Profile</span>
                </a>

                <!-- Settings -->
                <a href="index.php?page=settings" class="sidebar-item <?php echo $currentPage === 'settings' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'settings' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Settings</span>
                </a>
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t bg-gray-50">
            <div class="flex items-center space-x-3 px-2">
                <img src="<?php echo getAvatarUrl($currentUser['avatar']); ?>" class="w-10 h-10 rounded-full">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($currentUser['first_name']); ?></p>
                    <p class="text-xs text-gray-500 truncate"><?php echo ucfirst($currentUser['role_name']); ?></p>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Overlay for mobile -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

