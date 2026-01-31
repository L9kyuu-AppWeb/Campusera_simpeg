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
                <a href="index.php?page=adminRole_roles" class="sidebar-item <?php echo $currentPage === 'adminRole_roles' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_roles' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                <!-- Fakultas (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_fakultas" class="sidebar-item <?php echo $currentPage === 'adminRole_fakultas' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_fakultas' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="font-medium text-gray-700">Fakultas</span>
                </a>
                <?php endif; ?>

                <!-- Prodi (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_prodi" class="sidebar-item <?php echo $currentPage === 'adminRole_prodi' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_prodi' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="font-medium text-gray-700">Prodi</span>
                </a>
                <?php endif; ?>

                <!-- Jenis Izin (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_jenis_izin" class="sidebar-item <?php echo $currentPage === 'adminRole_jenis_izin' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_jenis_izin' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="font-medium text-gray-700">Jenis Izin</span>
                </a>
                <?php endif; ?>

            <h3 class="text-xs font-semibold text-gray-400 uppercase px-4 pt-1 pb-1">Data Pegawai</h3>

                <!-- Data Pegawai (Admin & Dosen) -->
                <?php if (hasRole(['dosen'])): ?>
                <a href="index.php?page=dosenRole_pegawai" class="sidebar-item <?php echo $currentPage === 'dosenRole_pegawai' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'dosenRole_pegawai' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Data Pegawai</span>
                </a>
                <?php endif; ?>                

                <!-- Tim Pengajar (Dosen only) -->
                <?php if (hasRole(['dosen'])): ?>
                <a href="index.php?page=dosenRole_mengajar" class="sidebar-item <?php echo $currentPage === 'dosenRole_mengajar' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'dosenRole_mengajar' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Tim Pengajar</span>
                </a>
                <?php endif; ?>

                <!-- Keluarga Saya (Dosen only) -->
                <?php if (hasRole(['dosen'])): ?>
                <a href="index.php?page=dosenRole_keluarga" class="sidebar-item <?php echo $currentPage === 'dosenRole_keluarga' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'dosenRole_keluarga' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Keluarga Saya</span>
                </a>
                <?php endif; ?>

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

                <!-- Izin Pegawai (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_izin_pegawai" class="sidebar-item <?php echo $currentPage === 'adminRole_izin_pegawai' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_izin_pegawai' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="font-medium text-gray-700">Izin Pegawai</span>
                </a>
                <?php endif; ?>

                <!-- Pegawai (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_pegawai" class="sidebar-item <?php echo $currentPage === 'adminRole_pegawai' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_pegawai' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Pegawai</span>
                </a>
                <?php endif; ?>

                <!-- Saldo Cuti (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_saldo_cuti" class="sidebar-item <?php echo $currentPage === 'adminRole_saldo_cuti' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_saldo_cuti' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Saldo Cuti</span>
                </a>
                <?php endif; ?>

                <!-- Riwayat Kepegawaian (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_pegawai_riwayat" class="sidebar-item <?php echo $currentPage === 'adminRole_pegawai_riwayat' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_pegawai_riwayat' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="font-medium text-gray-700">Riwayat Kepegawaian</span>
                </a>
                <?php endif; ?>

                <!-- Pegawai Keluarga (Admin, Dosen, Tendik) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_pegawai_keluarga" class="sidebar-item <?php echo $currentPage === 'adminRole_pegawai_keluarga' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_pegawai_keluarga' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Keluarga Pegawai</span>
                </a>
                <?php endif; ?>

                <!-- Pegawai Pendidikan (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_pegawai_pendidikan" class="sidebar-item <?php echo $currentPage === 'adminRole_pegawai_pendidikan' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_pegawai_pendidikan' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span class="font-medium text-gray-700">Pendidikan Pegawai</span>
                </a>
                <?php endif; ?>

                <!-- Pegawai Pendidikan Berkas (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_pegawai_pendidikan_berkas" class="sidebar-item <?php echo $currentPage === 'adminRole_pegawai_pendidikan_berkas' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_pegawai_pendidikan_berkas' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Berkas Pendidikan</span>
                </a>
                <?php endif; ?>

            <h3 class="text-xs font-semibold text-gray-400 uppercase px-4 pt-1 pb-1">Tenaga Pengajar</h3>
                <!-- Dosen (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_dosen" class="sidebar-item <?php echo $currentPage === 'adminRole_dosen' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_dosen' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Dosen</span>
                </a>
                <?php endif; ?>

                <!-- Tim Mengajar (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_tim_mengajar" class="sidebar-item <?php echo $currentPage === 'adminRole_tim_mengajar' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_tim_mengajar' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Tim Mengajar</span>
                </a>
                <?php endif; ?>



            <h3 class="text-xs font-semibold text-gray-400 uppercase px-4 pt-1 pb-1">Tenega Kependidikan</h3>
                <!-- Tendik (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_tendik" class="sidebar-item <?php echo $currentPage === 'adminRole_tendik' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_tendik' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Tendik</span>
                </a>
                <?php endif; ?>

                <!-- Unit Kerja (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_unit_kerja" class="sidebar-item <?php echo $currentPage === 'adminRole_unit_kerja' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_unit_kerja' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="font-medium text-gray-700">Unit Kerja</span>
                </a>
                <?php endif; ?>

            <h3 class="text-xs font-semibold text-gray-400 uppercase px-4 pt-1 pb-1">Sistem</h3>
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

                <!-- Master Cuti (Admin only) -->
                <?php if (hasRole(['admin'])): ?>
                <a href="index.php?page=adminRole_master_cuti" class="sidebar-item <?php echo $currentPage === 'adminRole_master_cuti' ? 'active' : ''; ?> flex items-center space-x-3 px-4 py-3 rounded-xl cursor-pointer transition-all">
                    <svg class="w-5 h-5 <?php echo $currentPage === 'adminRole_master_cuti' ? 'text-blue-600' : 'text-gray-500'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium text-gray-700">Master Cuti</span>
                </a>
                <?php endif; ?>
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

