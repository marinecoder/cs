<?php
if (!Auth::checkPermission('rate_limit_manage')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle rate limit actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_limits':
            foreach ($_POST['limits'] as $endpoint => $limit) {
                $db->query("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?", 
                          ["rate_limit_$endpoint", $limit, $limit]);
            }
            $success = "Rate limits updated successfully!";
            break;
            
        case 'clear_limits':
            $db->query("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $success = "Rate limit counters cleared!";
            break;
            
        case 'whitelist_ip':
            $ip = $_POST['ip_address'] ?? '';
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $db->query("INSERT INTO ip_whitelist (ip_address, reason, created_by) VALUES (?, ?, ?)",
                          [$ip, $_POST['reason'] ?? 'Manual whitelist', $_SESSION['user_id']]);
                $success = "IP address whitelisted successfully!";
            }
            break;
    }
}

// Get rate limit statistics
$rateLimitStats = [
    'total_requests_today' => $db->query("SELECT SUM(request_count) as total FROM rate_limits WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'] ?? 0,
    'blocked_requests' => $db->query("SELECT COUNT(*) as count FROM rate_limits WHERE request_count >= 100")->fetch_assoc()['count'] ?? 0,
    'unique_ips_today' => $db->query("SELECT COUNT(DISTINCT ip_address) as count FROM rate_limits WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0,
    'api_requests_today' => $db->query("SELECT COUNT(*) as count FROM rate_limits WHERE api_key IS NOT NULL AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0,
];

// Get current rate limits
$currentLimits = [];
$result = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'rate_limit_%'");
while ($row = $result->fetch_assoc()) {
    $endpoint = str_replace('rate_limit_', '', $row['setting_key']);
    $currentLimits[$endpoint] = $row['setting_value'];
}

// Default rate limits if not set
$defaultLimits = [
    'api_general' => '100',
    'api_tracking' => '200',
    'api_shipments' => '50',
    'web_login' => '10',
    'web_register' => '5',
    'web_general' => '300'
];

foreach ($defaultLimits as $key => $value) {
    if (!isset($currentLimits[$key])) {
        $currentLimits[$key] = $value;
    }
}

// Get recent rate limit violations
$violations = $db->query("SELECT rl.*, ak.name as api_name 
                         FROM rate_limits rl 
                         LEFT JOIN api_keys ak ON rl.api_key = ak.api_key 
                         WHERE rl.request_count >= 50 
                         ORDER BY rl.created_at DESC 
                         LIMIT 50");

// Get top requesting IPs
$topIPs = $db->query("SELECT ip_address, SUM(request_count) as total_requests, COUNT(*) as entries, MAX(created_at) as last_request
                     FROM rate_limits 
                     WHERE DATE(created_at) = CURDATE()
                     GROUP BY ip_address 
                     ORDER BY total_requests DESC 
                     LIMIT 20");

// Get whitelisted IPs
$whitelistedIPs = $db->query("SELECT iw.*, u.email as created_by_email 
                             FROM ip_whitelist iw 
                             LEFT JOIN users u ON iw.created_by = u.id 
                             WHERE iw.status = 'active' 
                             ORDER BY iw.created_at DESC");
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Rate Limiting</h1>
            <div class="flex space-x-2">
                <button onclick="showWhitelistModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    Whitelist IP
                </button>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="clear_limits">
                    <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        Clear Counters
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <!-- Rate Limiting Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Requests Today</p>
                        <p class="text-2xl font-bold text-blue-600"><?= number_format($rateLimitStats['total_requests_today']) ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Rate Limited</p>
                        <p class="text-2xl font-bold text-red-600"><?= number_format($rateLimitStats['blocked_requests']) ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Unique IPs Today</p>
                        <p class="text-2xl font-bold text-green-600"><?= number_format($rateLimitStats['unique_ips_today']) ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M12 12h.008v.008H12V12z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">API Requests Today</p>
                        <p class="text-2xl font-bold text-purple-600"><?= number_format($rateLimitStats['api_requests_today']) ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limit Configuration -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Rate Limit Configuration</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_limits">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API General (per minute)</label>
                        <input type="number" name="limits[api_general]" value="<?= htmlspecialchars($currentLimits['api_general']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Tracking (per minute)</label>
                        <input type="number" name="limits[api_tracking]" value="<?= htmlspecialchars($currentLimits['api_tracking']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Shipments (per minute)</label>
                        <input type="number" name="limits[api_shipments]" value="<?= htmlspecialchars($currentLimits['api_shipments']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Web Login (per minute)</label>
                        <input type="number" name="limits[web_login]" value="<?= htmlspecialchars($currentLimits['web_login']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Web Registration (per minute)</label>
                        <input type="number" name="limits[web_register]" value="<?= htmlspecialchars($currentLimits['web_register']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Web General (per minute)</label>
                        <input type="number" name="limits[web_general]" value="<?= htmlspecialchars($currentLimits['web_general']) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Update Limits
                    </button>
                </div>
            </form>
        </div>

        <!-- Top Requesting IPs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Top Requesting IPs Today</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php while ($ip = $topIPs->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900 font-mono"><?= htmlspecialchars($ip['ip_address']) ?></p>
                                <p class="text-sm text-gray-600">Last request: <?= date('H:i', strtotime($ip['last_request'])) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900"><?= number_format($ip['total_requests']) ?> requests</p>
                                <p class="text-sm text-gray-600"><?= $ip['entries'] ?> entries</p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Whitelisted IPs -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Whitelisted IPs</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php while ($whitelist = $whitelistedIPs->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900 font-mono"><?= htmlspecialchars($whitelist['ip_address']) ?></p>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($whitelist['reason']) ?></p>
                            </div>
                            <button onclick="removeWhitelist(<?= $whitelist['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                Remove
                            </button>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limit Violations -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Rate Limit Violations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Window</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($violation = $violations->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono"><?= htmlspecialchars($violation['ip_address']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($violation['api_name'] ?: 'Web Request') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-semibold <?= $violation['request_count'] >= 100 ? 'text-red-600' : 'text-yellow-600' ?>">
                                    <?= $violation['request_count'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($violation['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="blockIP('<?= htmlspecialchars($violation['ip_address']) ?>')" 
                                        class="text-red-600 hover:text-red-900 mr-3">Block IP</button>
                                <button onclick="whitelistIP('<?= htmlspecialchars($violation['ip_address']) ?>')" 
                                        class="text-green-600 hover:text-green-900">Whitelist</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Whitelist IP Modal -->
<div id="whitelistModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Whitelist IP Address</h3>
        <form method="POST">
            <input type="hidden" name="action" value="whitelist_ip">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                    <input type="text" name="ip_address" required placeholder="192.168.1.1" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <textarea name="reason" rows="3" placeholder="Reason for whitelisting this IP address"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="hideWhitelistModal()" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Whitelist IP</button>
            </div>
        </form>
    </div>
</div>

<script>
function showWhitelistModal() {
    document.getElementById('whitelistModal').classList.remove('hidden');
}

function hideWhitelistModal() {
    document.getElementById('whitelistModal').classList.add('hidden');
}

function blockIP(ipAddress) {
    if (confirm(`Block IP address ${ipAddress}?`)) {
        window.location.href = `/admin/security?block_ip=${ipAddress}`;
    }
}

function whitelistIP(ipAddress) {
    document.querySelector('input[name="ip_address"]').value = ipAddress;
    showWhitelistModal();
}

function removeWhitelist(whitelistId) {
    if (confirm('Remove this IP from whitelist?')) {
        fetch(`/admin/remove-whitelist/${whitelistId}`, { method: 'POST' })
            .then(() => location.reload());
    }
}
</script>
