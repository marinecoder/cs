<?php
if (!Auth::checkPermission('system_config')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Get system logs
$logFile = '/workspaces/cs/courier-app/logs/app.log';
$systemLogs = [];

if (file_exists($logFile)) {
    $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $systemLogs = array_slice(array_reverse($logs), 0, 100); // Last 100 log entries
}

// Get database statistics
$dbStats = [
    'users' => $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'shipments' => $db->query("SELECT COUNT(*) as count FROM shipments")->fetch_assoc()['count'],
    'payments' => $db->query("SELECT COUNT(*) as count FROM payments")->fetch_assoc()['count'],
    'api_requests' => $db->query("SELECT COUNT(*) as count FROM api_keys")->fetch_assoc()['count'] ?? 0,
];

// System health checks
$healthChecks = [
    'php_version' => [
        'status' => version_compare(PHP_VERSION, '8.2.0', '>=') ? 'good' : 'warning',
        'value' => PHP_VERSION,
        'description' => 'PHP Version'
    ],
    'memory_usage' => [
        'status' => memory_get_usage(true) < 128 * 1024 * 1024 ? 'good' : 'warning',
        'value' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'description' => 'Memory Usage'
    ],
    'disk_space' => [
        'status' => disk_free_space('.') > 1024 * 1024 * 1024 ? 'good' : 'critical',
        'value' => round(disk_free_space('.') / 1024 / 1024 / 1024, 2) . ' GB',
        'description' => 'Free Disk Space'
    ],
    'database' => [
        'status' => $db->query("SELECT 1")->num_rows > 0 ? 'good' : 'critical',
        'value' => 'Connected',
        'description' => 'Database Connection'
    ]
];

// Handle log actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_logs':
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
                $success = "Logs cleared successfully!";
            }
            break;
        case 'backup_db':
            // Trigger database backup
            $backupFile = '/workspaces/cs/courier-app/backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
            if (!file_exists(dirname($backupFile))) {
                mkdir(dirname($backupFile), 0777, true);
            }
            exec("mysqldump -u root courier_app > $backupFile");
            $success = "Database backup created successfully!";
            break;
    }
}
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">System Logs</h1>
            <div class="flex space-x-2">
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="backup_db">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Backup Database
                    </button>
                </form>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" onclick="return confirm('Are you sure you want to clear all logs?')" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        Clear Logs
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <!-- System Health -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <?php foreach ($healthChecks as $check): ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600"><?= $check['description'] ?></p>
                        <p class="text-lg font-bold text-gray-900"><?= $check['value'] ?></p>
                    </div>
                    <div class="w-3 h-3 rounded-full 
                        <?= $check['status'] === 'good' ? 'bg-green-500' : 
                            ($check['status'] === 'warning' ? 'bg-yellow-500' : 'bg-red-500') ?>">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Database Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Database Statistics</h2>
                <div class="space-y-3">
                    <?php foreach ($dbStats as $table => $count): ?>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 capitalize"><?= str_replace('_', ' ', $table) ?></span>
                        <span class="font-semibold text-gray-900"><?= number_format($count) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">System Information</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Server OS</span>
                        <span class="font-semibold text-gray-900"><?= php_uname('s') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Server Load</span>
                        <span class="font-semibold text-gray-900"><?= sys_getloadavg()[0] ?? 'N/A' ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Uptime</span>
                        <span class="font-semibold text-gray-900"><?= shell_exec('uptime -p') ?: 'N/A' ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">PHP Extensions</span>
                        <span class="font-semibold text-gray-900"><?= count(get_loaded_extensions()) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Viewer -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Recent System Logs</h2>
                <div class="flex space-x-2">
                    <button onclick="autoRefresh()" class="text-blue-600 hover:text-blue-800 text-sm">Auto Refresh</button>
                    <button onclick="downloadLogs()" class="text-green-600 hover:text-green-800 text-sm">Download</button>
                </div>
            </div>
            <div class="p-6">
                <div id="logContainer" class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm max-h-96 overflow-y-auto">
                    <?php if (empty($systemLogs)): ?>
                    <p class="text-gray-500">No logs available</p>
                    <?php else: ?>
                    <?php foreach ($systemLogs as $log): ?>
                    <div class="mb-1"><?= htmlspecialchars($log) ?></div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Error Logs -->
        <div class="mt-6 bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Error Summary</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">
                            <?= count(array_filter($systemLogs, function($log) { return strpos($log, 'ERROR') !== false; })) ?>
                        </div>
                        <div class="text-sm text-gray-600">Errors Today</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">
                            <?= count(array_filter($systemLogs, function($log) { return strpos($log, 'WARNING') !== false; })) ?>
                        </div>
                        <div class="text-sm text-gray-600">Warnings Today</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">
                            <?= count(array_filter($systemLogs, function($log) { return strpos($log, 'INFO') !== false; })) ?>
                        </div>
                        <div class="text-sm text-gray-600">Info Messages</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval;

function autoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        event.target.textContent = 'Auto Refresh';
    } else {
        autoRefreshInterval = setInterval(() => {
            location.reload();
        }, 30000);
        event.target.textContent = 'Stop Refresh';
    }
}

function downloadLogs() {
    window.open('/admin/download-logs', '_blank');
}

// Auto-scroll to bottom of logs
document.getElementById('logContainer').scrollTop = document.getElementById('logContainer').scrollHeight;
</script>
