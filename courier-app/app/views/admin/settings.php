<?php
if (!Auth::checkPermission('system_config')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle settings update
if ($_POST) {
    foreach ($_POST['settings'] as $key => $value) {
        $db->query("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                   ON DUPLICATE KEY UPDATE setting_value = ?", [$key, $value, $value]);
    }
    $success = "Settings updated successfully!";
}

// Get current settings
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
            <button onclick="backupSettings()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                Backup Settings
            </button>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <!-- General Settings -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                        <input type="text" name="settings[site_name]" value="<?= htmlspecialchars($settings['site_name'] ?? 'Courier Web App') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                        <input type="email" name="settings[contact_email]" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                        <select name="settings[timezone]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="UTC" <?= ($settings['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                            <option value="America/New_York" <?= ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                            <option value="America/Chicago" <?= ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                            <option value="America/Denver" <?= ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                            <option value="America/Los_Angeles" <?= ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Currency</label>
                        <select name="settings[currency]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD</option>
                            <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                            <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                        <input type="text" name="settings[smtp_host]" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                        <input type="number" name="settings[smtp_port]" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                        <input type="text" name="settings[smtp_username]" value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                        <input type="password" name="settings[smtp_password]" value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Shipping Settings -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Shipping Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Standard Rate ($)</label>
                        <input type="number" step="0.01" name="settings[standard_rate]" value="<?= htmlspecialchars($settings['standard_rate'] ?? '5.00') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Express Rate ($)</label>
                        <input type="number" step="0.01" name="settings[express_rate]" value="<?= htmlspecialchars($settings['express_rate'] ?? '8.00') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Overnight Rate ($)</label>
                        <input type="number" step="0.01" name="settings[overnight_rate]" value="<?= htmlspecialchars($settings['overnight_rate'] ?? '15.00') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Security Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
                        <input type="number" name="settings[max_login_attempts]" value="<?= htmlspecialchars($settings['max_login_attempts'] ?? '5') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lockout Duration (minutes)</label>
                        <input type="number" name="settings[lockout_duration]" value="<?= htmlspecialchars($settings['lockout_duration'] ?? '15') ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="settings[maintenance_mode]" value="1" 
                                   <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Enable Maintenance Mode</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function backupSettings() {
    if (confirm('Are you sure you want to backup all settings?')) {
        fetch('/admin/backup-settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Settings backed up successfully!');
            } else {
                alert('Error backing up settings: ' + data.message);
            }
        });
    }
}
</script>
