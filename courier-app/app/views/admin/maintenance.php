<?php
if (!Auth::checkPermission('system_config')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle maintenance actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'toggle_maintenance':
            $currentMode = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'")->fetch_assoc()['setting_value'] ?? '0';
            $newMode = $currentMode === '1' ? '0' : '1';
            $db->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('maintenance_mode', ?) 
                       ON DUPLICATE KEY UPDATE setting_value = ?", [$newMode, $newMode]);
            $success = $newMode === '1' ? "Maintenance mode enabled!" : "Maintenance mode disabled!";
            break;
            
        case 'update_message':
            $message = $_POST['maintenance_message'] ?? '';
            $db->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('maintenance_message', ?) 
                       ON DUPLICATE KEY UPDATE setting_value = ?", [$message, $message]);
            $success = "Maintenance message updated!";
            break;
            
        case 'clear_cache':
            // Clear cache files
            $cacheDir = '/workspaces/cs/courier-app/cache/';
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            $success = "Cache cleared successfully!";
            break;
            
        case 'optimize_db':
            // Optimize database tables
            $tables = ['users', 'shipments', 'payments', 'email_queue', 'rate_limits', 'security_logs'];
            foreach ($tables as $table) {
                $db->query("OPTIMIZE TABLE $table");
            }
            $success = "Database optimized successfully!";
            break;
    }
}

// Get current maintenance settings
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'maintenance_%'");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$maintenanceMode = $settings['maintenance_mode'] ?? '0';
$maintenanceMessage = $settings['maintenance_message'] ?? 'System is under maintenance. Please try again later.';

// Get system status
$systemStatus = [
    'php_version' => PHP_VERSION,
    'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time') . 's',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'disk_free_space' => round(disk_free_space('.') / 1024 / 1024 / 1024, 2) . ' GB',
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
];

// Get database info
$dbInfo = [];
try {
    $dbSize = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                         FROM information_schema.tables 
                         WHERE table_schema = 'courier_app'")->fetch_assoc()['size_mb'] ?? 0;
    $dbInfo['size'] = $dbSize . ' MB';
} catch (Exception $e) {
    $dbInfo['size'] = 'Unknown';
}

// Get cache info
$cacheInfo = [
    'status' => 'Enabled',
    'size' => '0 MB',
    'files' => 0
];

$cacheDir = '/workspaces/cs/courier-app/cache/';
if (is_dir($cacheDir)) {
    $cacheFiles = glob($cacheDir . '*');
    $cacheInfo['files'] = count($cacheFiles);
    $totalSize = 0;
    foreach ($cacheFiles as $file) {
        if (is_file($file)) {
            $totalSize += filesize($file);
        }
    }
    $cacheInfo['size'] = round($totalSize / 1024 / 1024, 2) . ' MB';
}

// Get recent system events
$recentEvents = [
    ['type' => 'info', 'message' => 'System started successfully', 'time' => date('Y-m-d H:i:s', time() - 3600)],
    ['type' => 'warning', 'message' => 'High memory usage detected', 'time' => date('Y-m-d H:i:s', time() - 1800)],
    ['type' => 'info', 'message' => 'Database backup completed', 'time' => date('Y-m-d H:i:s', time() - 900)],
    ['type' => 'success', 'message' => 'Cache cleared automatically', 'time' => date('Y-m-d H:i:s', time() - 300)],
];
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">System Maintenance</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Maintenance Mode:</span>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="toggle_maintenance">
                    <button type="submit" class="relative inline-flex h-6 w-12 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?= $maintenanceMode === '1' ? 'bg-red-600' : 'bg-gray-200' ?>">
                        <span class="sr-only">Toggle maintenance mode</span>
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform <?= $maintenanceMode === '1' ? 'translate-x-7' : 'translate-x-1' ?>"></span>
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <?php if ($maintenanceMode === '1'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>Warning:</strong> The system is currently in maintenance mode. Users cannot access the application.
        </div>
        <?php endif; ?>

        <!-- Maintenance Settings -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Maintenance Settings</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_message">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Message</label>
                        <textarea name="maintenance_message" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($maintenanceMessage) ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">This message will be displayed to users when maintenance mode is enabled.</p>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Update Message
                    </button>
                </div>
            </form>
        </div>

        <!-- System Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">System Information</h2>
                <div class="space-y-3">
                    <?php foreach ($systemStatus as $key => $value): ?>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 capitalize"><?= str_replace('_', ' ', $key) ?></span>
                        <span class="font-semibold text-gray-900"><?= htmlspecialchars($value) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Database & Cache</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Database Size</span>
                        <span class="font-semibold text-gray-900"><?= $dbInfo['size'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Cache Status</span>
                        <span class="font-semibold text-green-600"><?= $cacheInfo['status'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Cache Size</span>
                        <span class="font-semibold text-gray-900"><?= $cacheInfo['size'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Cache Files</span>
                        <span class="font-semibold text-gray-900"><?= $cacheInfo['files'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="clear_cache">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Clear Cache
                    </button>
                </form>

                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="optimize_db">
                    <button type="submit" onclick="return confirm('Optimize database? This may take a few minutes.')" 
                            class="w-full bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        Optimize DB
                    </button>
                </form>

                <button onclick="checkSystemHealth()" class="w-full bg-yellow-600 text-white px-4 py-3 rounded-lg hover:bg-yellow-700 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Health Check
                </button>

                <button onclick="restartServices()" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Restart Services
                </button>
            </div>
        </div>

        <!-- Recent System Events -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent System Events</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($recentEvents as $event): ?>
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <?php if ($event['type'] === 'success'): ?>
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <?php elseif ($event['type'] === 'warning'): ?>
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <?php else: ?>
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900"><?= htmlspecialchars($event['message']) ?></p>
                            <p class="text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($event['time'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkSystemHealth() {
    alert('System health check would be performed here');
    // Implementation would check various system components
}

function restartServices() {
    if (confirm('This will restart system services. Are you sure?')) {
        alert('Services restart would be triggered here');
        // Implementation would restart web server, database, etc.
    }
}
</script>
