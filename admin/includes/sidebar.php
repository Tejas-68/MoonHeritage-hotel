<?php

$currentPage = basename($_SERVER['PHP_SELF']);


$db = getDB();
$pendingCount = $db->query("SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'pending'")->fetch()['count'];
?>

<aside class="fixed top-0 left-0 h-screen w-64 bg-gray-900 text-white shadow-lg hidden md:block z-50">
    <div class="p-6">
        
        <a href="../index.php" class="flex items-center space-x-2 mb-8 hover:opacity-80 transition">
            <i class="fas fa-moon text-2xl"></i>
            <span class="text-xl font-bold">MoonHeritage</span>
        </a>
        
        
        <nav class="space-y-2">
            <a href="dashboard.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'dashboard.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="manage-hotels.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'manage-hotels.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-hotel"></i>
                <span>Manage Hotels</span>
            </a>
            
            <a href="manage-villas.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'manage-villas.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-home"></i>
                <span>Manage Villas</span>
            </a>
            
            <a href="manage-amenities.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'manage-amenities.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-swimming-pool"></i>
                <span>Facilities</span>
            </a>
            
            <a href="manage-bookings.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'manage-bookings.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
                <?php if ($pendingCount > 0): ?>
                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-auto"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
            
            <a href="manage-users.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'manage-users.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            
            <a href="reports.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'reports.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-file-chart-line"></i>
                <span>Reports</span>
            </a>
            
            <a href="settings.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?php echo $currentPage === 'settings.php' ? 'bg-blue-600' : 'hover:bg-gray-800'; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            
            <div class="border-t border-gray-700 my-4"></div>
            
            <a href="../index.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-globe"></i>
                <span>View Website</span>
            </a>
            
            <a href="../logout.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-red-400">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    
    
    <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="bg-blue-600 w-10 h-10 rounded-full flex items-center justify-center">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <div class="font-semibold"><?php echo escape($_SESSION['first_name'] ?? 'Admin'); ?></div>
                <div class="text-xs text-gray-400">Administrator</div>
            </div>
        </div>
    </div>
</aside>