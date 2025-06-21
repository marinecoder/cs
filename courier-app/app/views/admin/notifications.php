<?php
if (!Auth::checkPermission('notification_manage')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle notification actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_bulk':
            $recipients = $_POST['recipients'] ?? 'all_users';
            $message = $_POST['message'] ?? '';
            $title = $_POST['title'] ?? '';
            
            // Get recipients based on selection
            if ($recipients === 'all_users') {
                $users = $db->query("SELECT id, email FROM users WHERE status = 'active'");
            } else {
                $users = $db->query("SELECT id, email FROM users WHERE role = ? AND status = 'active'", [$recipients]);
            }
            
            $emailService = new EmailService();
            $sentCount = 0;
            
            while ($user = $users->fetch_assoc()) {
                if ($emailService->sendEmail($user['email'], $title, $message)) {
                    $sentCount++;
                }
            }
            
            $success = "Bulk notification sent to $sentCount users!";
            break;
            
        case 'update_settings':
            foreach ($_POST['settings'] as $key => $value) {
                $db->query("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?", [$key, $value, $value]);
            }
            $success = "Notification settings updated successfully!";
            break;
    }
}

// Get notification statistics
$notificationStats = [
    'total_sent' => $db->query("SELECT COUNT(*) as count FROM email_queue WHERE subject LIKE '%Notification%'")->fetch_assoc()['count'],
    'active_users' => $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'],
    'email_enabled' => $db->query("SELECT COUNT(*) as count FROM users WHERE email_notifications = 1")->fetch_assoc()['count'],
    'sms_enabled' => $db->query("SELECT COUNT(*) as count FROM users WHERE sms_notifications = 1")->fetch_assoc()['count'] ?? 0,
];

// Get recent notifications
$recentNotifications = $db->query("SELECT * FROM email_queue WHERE subject LIKE '%Notification%' OR subject LIKE '%Alert%' ORDER BY created_at DESC LIMIT 20");

// Get notification settings
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'notification_%'");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Notification types
$notificationTypes = [
    'shipment_updates' => 'Shipment Status Updates',
    'payment_confirmations' => 'Payment Confirmations',
    'delivery_reminders' => 'Delivery Reminders',
    'promotional' => 'Promotional Messages',
    'system_alerts' => 'System Alerts',
    'security_alerts' => 'Security Alerts'
];
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Notification Center</h1>
            <button onclick="showBulkNotificationModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Send Bulk Notification
            </button>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <!-- Notification Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Sent</p>
                        <p class="text-2xl font-bold text-blue-600"><?= number_format($notificationStats['total_sent']) ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 1 1 0 15z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-2xl font-bold text-green-600"><?= number_format($notificationStats['active_users']) ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Email Enabled</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= number_format($notificationStats['email_enabled']) ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">SMS Enabled</p>
                        <p class="text-2xl font-bold text-purple-600"><?= number_format($notificationStats['sms_enabled']) ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Settings</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($notificationTypes as $key => $name): ?>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900"><?= $name ?></h3>
                            <p class="text-sm text-gray-600">Enable/disable <?= strtolower($name) ?></p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" name="settings[notification_<?= $key ?>]" value="1" 
                                   <?= ($settings["notification_$key"] ?? '1') === '1' ? 'checked' : '' ?>
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Enabled</span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Notification Templates -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Templates</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">System Maintenance</h3>
                    <p class="text-sm text-gray-600 mb-3">Notify users about scheduled maintenance</p>
                    <button onclick="useTemplate('maintenance')" class="text-blue-600 hover:text-blue-800 text-sm">Use Template</button>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Service Update</h3>
                    <p class="text-sm text-gray-600 mb-3">Announce new features or improvements</p>
                    <button onclick="useTemplate('update')" class="text-blue-600 hover:text-blue-800 text-sm">Use Template</button>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Holiday Notice</h3>
                    <p class="text-sm text-gray-600 mb-3">Inform about holiday schedules</p>
                    <button onclick="useTemplate('holiday')" class="text-blue-600 hover:text-blue-800 text-sm">Use Template</button>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Security Alert</h3>
                    <p class="text-sm text-gray-600 mb-3">Send security-related notifications</p>
                    <button onclick="useTemplate('security')" class="text-blue-600 hover:text-blue-800 text-sm">Use Template</button>
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Notifications</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($notification = $recentNotifications->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($notification['subject']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($notification['recipient']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    <?= $notification['status'] === 'sent' ? 'bg-green-100 text-green-800' : 
                                        ($notification['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                         'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst($notification['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Email</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $notification['sent_at'] ? date('M d, Y H:i', strtotime($notification['sent_at'])) : 'Not sent' ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Notification Modal -->
<div id="bulkNotificationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
        <h3 class="text-lg font-semibold mb-4">Send Bulk Notification</h3>
        <form method="POST">
            <input type="hidden" name="action" value="send_bulk">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipients</label>
                    <select name="recipients" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all_users">All Users</option>
                        <option value="admin">Administrators</option>
                        <option value="user">Regular Users</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea name="message" rows="6" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="hideBulkNotificationModal()" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="submit" onclick="return confirm('Send notification to selected users?')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Send Notification</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBulkNotificationModal() {
    document.getElementById('bulkNotificationModal').classList.remove('hidden');
}

function hideBulkNotificationModal() {
    document.getElementById('bulkNotificationModal').classList.add('hidden');
}

function useTemplate(templateType) {
    const templates = {
        'maintenance': {
            title: 'Scheduled System Maintenance',
            message: 'We will be performing scheduled maintenance on our systems. During this time, our services may be temporarily unavailable. We apologize for any inconvenience.'
        },
        'update': {
            title: 'Service Update Available',
            message: 'We\'ve released new features and improvements to enhance your experience. Please log in to explore the latest updates.'
        },
        'holiday': {
            title: 'Holiday Schedule Notice',
            message: 'Please note our modified schedule during the upcoming holidays. Deliveries and customer service may be affected during this period.'
        },
        'security': {
            title: 'Security Alert',
            message: 'We detected unusual activity on your account. Please review your recent activities and update your password if necessary.'
        }
    };
    
    const template = templates[templateType];
    if (template) {
        showBulkNotificationModal();
        document.querySelector('input[name="title"]').value = template.title;
        document.querySelector('textarea[name="message"]').value = template.message;
    }
}
</script>
