<?php
$user = Auth::getCurrentUser();
$db = Database::getInstance();

// Get unread notifications count
$notificationCount = 0;
if($user) {
    $result = $db->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND read_at IS NULL", [$user['id']]);
    $notificationCount = $result->fetch_assoc()['count'];
}
?>

<header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <!-- Page Title / Breadcrumbs -->
        <div class="flex items-center">
            <h1 class="text-2xl font-semibold text-gray-900">
                <?= $pageTitle ?? 'Dashboard' ?>
            </h1>
            
            <?php if(isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <nav class="ml-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <?php foreach($breadcrumbs as $index => $crumb): ?>
                            <li class="flex items-center">
                                <?php if($index > 0): ?>
                                    <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                <?php endif; ?>
                                
                                <?php if(isset($crumb['url'])): ?>
                                    <a href="<?= $crumb['url'] ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                                        <?= $crumb['title'] ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-sm font-medium text-gray-900">
                                        <?= $crumb['title'] ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>
        
        <!-- Header Actions -->
        <div class="flex items-center space-x-4">
            <!-- Search -->
            <div class="relative">
                <input type="text" 
                       placeholder="Search..." 
                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Notifications -->
            <div class="relative">
                <button type="button" 
                        class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg"
                        onclick="toggleNotifications()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    
                    <?php if($notificationCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?= $notificationCount > 9 ? '9+' : $notificationCount ?>
                        </span>
                    <?php endif; ?>
                </button>
                
                <!-- Notifications Dropdown -->
                <div id="notifications-dropdown" 
                     class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <!-- Notifications will be loaded here -->
                        <div class="p-4 text-center text-gray-500">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                            <p class="mt-2">Loading notifications...</p>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-200">
                        <a href="/notifications" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
                    </div>
                </div>
            </div>
            
            <!-- User Menu -->
            <div class="relative">
                <button type="button" 
                        class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onclick="toggleUserMenu()">
                    <div class="bg-blue-600 rounded-full w-8 h-8 flex items-center justify-center text-white mr-2">
                        <span class="text-sm font-medium"><?= strtoupper(substr($user['name'], 0, 2)) ?></span>
                    </div>
                    <div class="text-left">
                        <div class="font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($user['role']) ?></div>
                    </div>
                    <svg class="ml-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <!-- User Dropdown -->
                <div id="user-menu-dropdown" 
                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </a>
                        <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            </svg>
                            Settings
                        </a>
                        <div class="border-t border-gray-200"></div>
                        <a href="/logout" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notifications-dropdown');
    dropdown.classList.toggle('hidden');
    
    if(!dropdown.classList.contains('hidden')) {
        loadNotifications();
    }
}

function toggleUserMenu() {
    const dropdown = document.getElementById('user-menu-dropdown');
    dropdown.classList.toggle('hidden');
}

function loadNotifications() {
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
            const container = document.querySelector('#notifications-dropdown .max-h-96');
            if(data.notifications && data.notifications.length > 0) {
                container.innerHTML = data.notifications.map(notification => `
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 ${notification.read_at ? '' : 'bg-blue-50'}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-blue-500 rounded-full ${notification.read_at ? 'opacity-0' : ''}"></div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                                <p class="text-sm text-gray-600">${notification.message}</p>
                                <p class="text-xs text-gray-400 mt-1">${formatDate(notification.created_at)}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if(diff < 60000) return 'Just now';
    if(diff < 3600000) return Math.floor(diff / 60000) + ' minutes ago';
    if(diff < 86400000) return Math.floor(diff / 3600000) + ' hours ago';
    return Math.floor(diff / 86400000) + ' days ago';
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');
    
    if(!event.target.closest('[onclick="toggleNotifications()"]') && !notificationsDropdown.contains(event.target)) {
        notificationsDropdown.classList.add('hidden');
    }
    
    if(!event.target.closest('[onclick="toggleUserMenu()"]') && !userMenuDropdown.contains(event.target)) {
        userMenuDropdown.classList.add('hidden');
    }
});
</script>
