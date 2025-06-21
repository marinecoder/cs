<?php
if (!Auth::checkPermission('api_manage')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle API key actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_key':
            $apiKey = 'ca_' . bin2hex(random_bytes(20));
            $db->query("INSERT INTO api_keys (api_key, name, permissions, created_by) VALUES (?, ?, ?, ?)",
                      [$apiKey, $_POST['name'], $_POST['permissions'], $_SESSION['user_id']]);
            $success = "API key created successfully!";
            break;
            
        case 'toggle_status':
            $keyId = $_POST['key_id'] ?? 0;
            $currentStatus = $db->query("SELECT status FROM api_keys WHERE id = ?", [$keyId])->fetch_assoc()['status'];
            $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
            $db->query("UPDATE api_keys SET status = ? WHERE id = ?", [$newStatus, $keyId]);
            $success = "API key status updated!";
            break;
            
        case 'delete_key':
            $keyId = $_POST['key_id'] ?? 0;
            $db->query("DELETE FROM api_keys WHERE id = ?", [$keyId]);
            $success = "API key deleted successfully!";
            break;
    }
}

// Get API keys
$apiKeys = $db->query("SELECT ak.*, u.email as created_by_email 
                      FROM api_keys ak 
                      LEFT JOIN users u ON ak.created_by = u.id 
                      ORDER BY ak.created_at DESC");

// Get API usage statistics
$apiStats = [
    'total_keys' => $db->query("SELECT COUNT(*) as count FROM api_keys")->fetch_assoc()['count'],
    'active_keys' => $db->query("SELECT COUNT(*) as count FROM api_keys WHERE status = 'active'")->fetch_assoc()['count'],
    'requests_today' => $db->query("SELECT COUNT(*) as count FROM rate_limits WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0,
    'requests_month' => $db->query("SELECT COUNT(*) as count FROM rate_limits WHERE MONTH(created_at) = MONTH(CURRENT_DATE())")->fetch_assoc()['count'] ?? 0,
];

// Get recent API requests
$recentRequests = $db->query("SELECT rl.*, ak.name as api_name 
                             FROM rate_limits rl 
                             LEFT JOIN api_keys ak ON rl.api_key = ak.api_key 
                             ORDER BY rl.created_at DESC 
                             LIMIT 20");
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">API Management</h1>
            <button onclick="showCreateKeyModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Create API Key
            </button>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <!-- API Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total API Keys</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $apiStats['total_keys'] ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2v6m0 0v6a2 2 0 01-2 2h-6M9 7a2 2 0 00-2 2v6a2 2 0 002 2h6m0 0a2 2 0 002-2V9a2 2 0 00-2-2M9 7a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Keys</p>
                        <p class="text-2xl font-bold text-green-600"><?= $apiStats['active_keys'] ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Requests Today</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($apiStats['requests_today']) ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Requests This Month</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($apiStats['requests_month']) ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Keys Table -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">API Keys</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($key = $apiKeys->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($key['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                <span class="blur-sm hover:blur-none transition-all duration-200 cursor-pointer" 
                                      onclick="copyToClipboard('<?= $key['api_key'] ?>')"><?= $key['api_key'] ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php 
                                $permissions = json_decode($key['permissions'], true) ?: [];
                                echo implode(', ', array_slice($permissions, 0, 2));
                                if (count($permissions) > 2) echo ', +' . (count($permissions) - 2) . ' more';
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    <?= $key['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= ucfirst($key['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($key['created_by_email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y', strtotime($key['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                    <button type="submit" class="text-blue-600 hover:text-blue-900">
                                        <?= $key['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                                <button onclick="viewKeyDetails(<?= $key['id'] ?>)" class="text-green-600 hover:text-green-900">View</button>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="delete_key">
                                    <input type="hidden" name="key_id" value="<?= $key['id'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent API Requests -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent API Requests</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Request</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($request = $recentRequests->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($request['api_name'] ?: 'Unknown') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono"><?= htmlspecialchars($request['ip_address']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $request['request_count'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($request['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create API Key Modal -->
<div id="createKeyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Create API Key</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create_key">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Key Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Permissions</label>
                    <select name="permissions" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value='["read"]'>Read Only</option>
                        <option value='["read", "write"]'>Read & Write</option>
                        <option value='["read", "write", "admin"]'>Full Access</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="hideCreateKeyModal()" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create Key</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateKeyModal() {
    document.getElementById('createKeyModal').classList.remove('hidden');
}

function hideCreateKeyModal() {
    document.getElementById('createKeyModal').classList.add('hidden');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('API key copied to clipboard!');
    });
}

function viewKeyDetails(keyId) {
    // Implementation for viewing key details
    window.location.href = `/admin/api-key-details/${keyId}`;
}
</script>
