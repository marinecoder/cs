<?php
if (!Auth::checkPermission('backup_manage')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();
$backupDir = '/workspaces/cs/courier-app/backups/';

// Handle backup actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_backup':
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $backupDir . "backup_$timestamp.sql";
            
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            
            $command = "mysqldump -u root courier_app > $backupFile 2>&1";
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $success = "Backup created successfully!";
            } else {
                $error = "Backup failed: " . implode("\n", $output);
            }
            break;
            
        case 'restore_backup':
            $backupFile = $_POST['backup_file'] ?? '';
            if (file_exists($backupDir . $backupFile)) {
                $command = "mysql -u root courier_app < " . $backupDir . $backupFile . " 2>&1";
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0) {
                    $success = "Database restored successfully!";
                } else {
                    $error = "Restore failed: " . implode("\n", $output);
                }
            }
            break;
            
        case 'delete_backup':
            $backupFile = $_POST['backup_file'] ?? '';
            if (file_exists($backupDir . $backupFile)) {
                unlink($backupDir . $backupFile);
                $success = "Backup deleted successfully!";
            }
            break;
    }
}

// Get backup files
$backupFiles = [];
if (file_exists($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backupFiles[] = [
                'name' => $file,
                'size' => filesize($backupDir . $file),
                'date' => filemtime($backupDir . $file)
            ];
        }
    }
    // Sort by date (newest first)
    usort($backupFiles, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Get backup schedule settings
$scheduleSettings = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'backup_schedule'")->fetch_assoc()['setting_value'] ?? 'daily';
$retentionDays = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'backup_retention'")->fetch_assoc()['setting_value'] ?? '30';
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Backup Management</h1>
            <form method="POST" class="inline">
                <input type="hidden" name="action" value="create_backup">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Create Backup Now
                </button>
            </form>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= $error ?>
        </div>
        <?php endif; ?>

        <!-- Backup Settings -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Backup Settings</h2>
            <form method="POST" action="/admin/update-backup-settings">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Backup Schedule</label>
                        <select name="backup_schedule" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="daily" <?= $scheduleSettings === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $scheduleSettings === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $scheduleSettings === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Retention Period (days)</label>
                        <input type="number" name="retention_days" value="<?= htmlspecialchars($retentionDays) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                            Update Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Backup Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Backups</p>
                        <p class="text-2xl font-bold text-gray-900"><?= count($backupFiles) ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Size</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?= number_format(array_sum(array_column($backupFiles, 'size')) / 1024 / 1024, 1) ?> MB
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Last Backup</p>
                        <p class="text-sm font-bold text-gray-900">
                            <?= !empty($backupFiles) ? date('M d, Y', $backupFiles[0]['date']) : 'Never' ?>
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Schedule</p>
                        <p class="text-sm font-bold text-gray-900"><?= ucfirst($scheduleSettings) ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Files Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Backup Files</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($backupFiles)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No backup files found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($backupFiles as $backup): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono"><?= htmlspecialchars($backup['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= number_format($backup['size'] / 1024 / 1024, 2) ?> MB</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M d, Y H:i', $backup['date']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                $ageInDays = floor((time() - $backup['date']) / 86400);
                                echo $ageInDays . ' day' . ($ageInDays !== 1 ? 's' : '') . ' ago';
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="/admin/download-backup/<?= urlencode($backup['name']) ?>" 
                                   class="text-blue-600 hover:text-blue-900">Download</a>
                                <button onclick="restoreBackup('<?= htmlspecialchars($backup['name']) ?>')" 
                                        class="text-green-600 hover:text-green-900">Restore</button>
                                <button onclick="deleteBackup('<?= htmlspecialchars($backup['name']) ?>')" 
                                        class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function restoreBackup(filename) {
    if (confirm('Are you sure you want to restore this backup? This will replace all current data!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="restore_backup">
            <input type="hidden" name="backup_file" value="${filename}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteBackup(filename) {
    if (confirm('Are you sure you want to delete this backup? This action cannot be undone!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_backup">
            <input type="hidden" name="backup_file" value="${filename}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
