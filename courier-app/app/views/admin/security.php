<?php
if (!Auth::checkPermission('security_manage')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle security actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_settings':
            foreach ($_POST['settings'] as $key => $value) {
                $db->query("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?", [$key, $value, $value]);
            }
            $success = "Security settings updated successfully!";
            break;
            
        case 'clear_logs':
            $db->query("DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $success = "Old security logs cleared successfully!";
            break;
            
        case 'block_ip':
            $ip = $_POST['ip_address'] ?? '';
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $db->query("INSERT INTO blocked_ips (ip_address, reason, blocked_by) VALUES (?, ?, ?)",
                          [$ip, $_POST['reason'] ?? 'Manual block', $_SESSION['user_id']]);
                $success = "IP address blocked successfully!";
            }
            break;
    }
}

// Get security statistics
$securityStats = [
    'failed_logins_today' => $db->query("SELECT COUNT(*) as count FROM security_logs WHERE event_type = 'failed_login' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0,
    'blocked_ips' => $db->query("SELECT COUNT(*) as count FROM blocked_ips WHERE status = 'active'")->fetch_assoc()['count'] ?? 0,
    'suspicious_activities' => $db->query("SELECT COUNT(*) as count FROM security_logs WHERE severity = 'high' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0,
    'active_sessions' => $db->query("SELECT COUNT(DISTINCT user_id) as count FROM sessions WHERE expires_at > NOW()")->fetch_assoc()['count'] ?? 0,
];

// Get recent security logs
$securityLogs = $db->query("SELECT sl.*, u.email as user_email 
                           FROM security_logs sl 
                           LEFT JOIN users u ON sl.user_id = u.id 
                           ORDER BY sl.created_at DESC 
                           LIMIT 50");

// Get blocked IPs
$blockedIPs = $db->query("SELECT bi.*, u.email as blocked_by_email 
                         FROM blocked_ips bi 
                         LEFT JOIN users u ON bi.blocked_by = u.id 
                         WHERE bi.status = 'active' 
                         ORDER BY bi.created_at DESC");

// Get security settings
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'security_%'");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Security Center</h1>
            <div class="flex space-x-2">
                <button onclick="showBlockIPModal()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Block IP Address
                </button>
                <button onclick="showSecurityScanModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Run Security Scan
                </button>
            </div>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <!-- Security Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Failed Logins Today</p>
                        <p class="text-2xl font-bold text-red-600"><?= $securityStats['failed_logins_today'] ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Blocked IPs</p>
                        <p class="text-2xl font-bold text-orange-600"><?= $securityStats['blocked_ips'] ?></p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Suspicious Activities</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= $securityStats['suspicious_activities'] ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Sessions</p>
                        <p class="text-2xl font-bold text-blue-600"><?= $securityStats['active_sessions'] ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Security Settings</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
                        <input type="number" name="settings[security_max_login_attempts]" 
                               value="<?= htmlspecialchars($settings['security_max_login_attempts'] ?? '5') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lockout Duration (minutes)</label>
                        <input type="number" name="settings[security_lockout_duration]" 
                               value="<?= htmlspecialchars($settings['security_lockout_duration'] ?? '15') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                        <input type="number" name="settings[security_session_timeout]" 
                               value="<?= htmlspecialchars($settings['security_session_timeout'] ?? '120') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Minimum Length</label>
                        <input type="number" name="settings[security_password_min_length]" 
                               value="<?= htmlspecialchars($settings['security_password_min_length'] ?? '8') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="settings[security_two_factor_required]" value="1" 
                               <?= ($settings['security_two_factor_required'] ?? '0') === '1' ? 'checked' : '' ?>
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Require Two-Factor Authentication</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="settings[security_ip_blocking_enabled]" value="1" 
                               <?= ($settings['security_ip_blocking_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Enable IP Blocking</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="settings[security_brute_force_protection]" value="1" 
                               <?= ($settings['security_brute_force_protection'] ?? '1') === '1' ? 'checked' : '' ?>
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Brute Force Protection</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="settings[security_audit_logging]" value="1" 
                               <?= ($settings['security_audit_logging'] ?? '1') === '1' ? 'checked' : '' ?>
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Enable Audit Logging</span>
                    </label>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Blocked IPs -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Blocked IP Addresses</h2>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" onclick="return confirm('Clear old security logs?')" 
                            class="text-gray-600 hover:text-gray-800 text-sm">Clear Old Logs</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blocked By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($ip = $blockedIPs->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono"><?= htmlspecialchars($ip['ip_address']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($ip['reason']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($ip['blocked_by_email'] ?? 'System') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($ip['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="unblockIP(<?= $ip['id'] ?>)" class="text-green-600 hover:text-green-900">Unblock</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Security Logs -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Security Activity Log</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($log = $securityLogs->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($log['event_type']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($log['user_email'] ?? 'Unknown') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono"><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    <?= $log['severity'] === 'high' ? 'bg-red-100 text-red-800' : 
                                        ($log['severity'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                                         'bg-gray-100 text-gray-800') ?>">
                                    <?= ucfirst($log['severity'] ?? 'low') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div id="blockIPModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Block IP Address</h3>
        <form method="POST">
            <input type="hidden" name="action" value="block_ip">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                    <input type="text" name="ip_address" required placeholder="192.168.1.1" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <textarea name="reason" rows="3" placeholder="Reason for blocking this IP address"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="hideBlockIPModal()" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Block IP</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBlockIPModal() {
    document.getElementById('blockIPModal').classList.remove('hidden');
}

function hideBlockIPModal() {
    document.getElementById('blockIPModal').classList.add('hidden');
}

function showSecurityScanModal() {
    alert('Security scan feature would be implemented here');
}

function unblockIP(ipId) {
    if (confirm('Are you sure you want to unblock this IP address?')) {
        fetch(`/admin/unblock-ip/${ipId}`, { method: 'POST' })
            .then(() => location.reload());
    }
}
</script>
