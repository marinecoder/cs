<?php
if (!Auth::checkPermission('email_manage')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle email actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_test':
            $emailService = new EmailService();
            $result = $emailService->sendEmail($_POST['test_email'], 'Test Email', 'This is a test email from the courier system.');
            if ($result) {
                $success = "Test email sent successfully!";
            } else {
                $error = "Failed to send test email.";
            }
            break;
            
        case 'retry_failed':
            $db->query("UPDATE email_queue SET status = 'pending', attempts = 0 WHERE status = 'failed'");
            $success = "Failed emails have been queued for retry.";
            break;
            
        case 'clear_sent':
            $db->query("DELETE FROM email_queue WHERE status = 'sent' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $success = "Old sent emails have been cleared.";
            break;
    }
}

// Get email statistics
$emailStats = [
    'total_sent' => $db->query("SELECT COUNT(*) as count FROM email_queue WHERE status = 'sent'")->fetch_assoc()['count'],
    'pending' => $db->query("SELECT COUNT(*) as count FROM email_queue WHERE status = 'pending'")->fetch_assoc()['count'],
    'failed' => $db->query("SELECT COUNT(*) as count FROM email_queue WHERE status = 'failed'")->fetch_assoc()['count'],
    'sent_today' => $db->query("SELECT COUNT(*) as count FROM email_queue WHERE status = 'sent' AND DATE(sent_at) = CURDATE()")->fetch_assoc()['count'],
];

// Get recent emails
$recentEmails = $db->query("SELECT * FROM email_queue ORDER BY created_at DESC LIMIT 50");

// Get email templates
$emailTemplates = [
    'shipment_created' => 'Shipment Created',
    'shipment_in_transit' => 'Shipment In Transit',
    'shipment_delivered' => 'Shipment Delivered',
    'payment_confirmed' => 'Payment Confirmed',
    'user_registered' => 'User Registration',
    'password_reset' => 'Password Reset'
];
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Email Management</h1>
            <div class="flex space-x-2">
                <button onclick="showTestEmailModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Send Test Email
                </button>
                <button onclick="showTemplateModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    Manage Templates
                </button>
            </div>
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

        <!-- Email Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Sent</p>
                        <p class="text-2xl font-bold text-green-600"><?= number_format($emailStats['total_sent']) ?></p>
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
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600"><?= number_format($emailStats['pending']) ?></p>
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
                        <p class="text-sm font-medium text-gray-600">Failed</p>
                        <p class="text-2xl font-bold text-red-600"><?= number_format($emailStats['failed']) ?></p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Sent Today</p>
                        <p class="text-2xl font-bold text-blue-600"><?= number_format($emailStats['sent_today']) ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="flex flex-wrap gap-4">
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="retry_failed">
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                        Retry Failed Emails
                    </button>
                </form>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="clear_sent">
                    <button type="submit" onclick="return confirm('Clear old sent emails?')" 
                            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        Clear Old Sent
                    </button>
                </form>
                <button onclick="viewEmailSettings()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                    Email Settings
                </button>
            </div>
        </div>

        <!-- Email Templates -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Email Templates</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($emailTemplates as $key => $name): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h3 class="font-medium text-gray-900 mb-2"><?= $name ?></h3>
                    <p class="text-sm text-gray-600 mb-3">Template: <?= $key ?></p>
                    <div class="flex space-x-2">
                        <button onclick="editTemplate('<?= $key ?>')" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                        <button onclick="previewTemplate('<?= $key ?>')" class="text-green-600 hover:text-green-800 text-sm">Preview</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Email Queue -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Email Queue</h2>
                <div class="flex space-x-2">
                    <select class="px-3 py-1 border border-gray-300 rounded text-sm">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Sent</option>
                        <option>Failed</option>
                    </select>
                    <button onclick="refreshQueue()" class="text-blue-600 hover:text-blue-800 text-sm">Refresh</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($email = $recentEmails->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($email['recipient']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($email['subject']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    <?= $email['status'] === 'sent' ? 'bg-green-100 text-green-800' : 
                                        ($email['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                         'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst($email['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $email['attempts'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($email['created_at'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewEmail(<?= $email['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                <?php if ($email['status'] === 'failed'): ?>
                                <button onclick="retryEmail(<?= $email['id'] ?>)" class="text-green-600 hover:text-green-900">Retry</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div id="testEmailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Send Test Email</h3>
        <form method="POST">
            <input type="hidden" name="action" value="send_test">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Test Email Address</label>
                    <input type="email" name="test_email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="hideTestEmailModal()" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Send Test</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTestEmailModal() {
    document.getElementById('testEmailModal').classList.remove('hidden');
}

function hideTestEmailModal() {
    document.getElementById('testEmailModal').classList.add('hidden');
}

function showTemplateModal() {
    alert('Template management modal would be shown here');
}

function editTemplate(templateKey) {
    window.location.href = `/admin/email-template/${templateKey}`;
}

function previewTemplate(templateKey) {
    window.open(`/admin/email-template-preview/${templateKey}`, '_blank');
}

function viewEmailSettings() {
    window.location.href = '/admin/settings#email';
}

function refreshQueue() {
    location.reload();
}

function viewEmail(emailId) {
    window.open(`/admin/email-view/${emailId}`, '_blank');
}

function retryEmail(emailId) {
    if (confirm('Retry sending this email?')) {
        fetch(`/admin/retry-email/${emailId}`, { method: 'POST' })
            .then(() => location.reload());
    }
}
</script>
