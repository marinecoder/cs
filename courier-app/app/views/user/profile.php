<?php
Auth::requireLogin();

$db = new Database();
$userId = $_SESSION['user_id'];

// Handle profile update
if($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request token";
    } else {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        
        $updateResult = $db->query(
            "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?",
            [$name, $phone, $address, $userId]
        );
        
        if($updateResult) {
            $_SESSION['name'] = $name; // Update session
            $success = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile";
        }
    }
}

// Handle password change
if($_POST && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request token";
    } else {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if($newPassword !== $confirmPassword) {
            $error = "New passwords do not match";
        } else {
            // Verify current password
            $user = $db->query("SELECT password FROM users WHERE id = ?", [$userId])->fetch_assoc();
            
            if(!password_verify($currentPassword, $user['password'])) {
                $error = "Current password is incorrect";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateResult = $db->query(
                    "UPDATE users SET password = ? WHERE id = ?",
                    [$hashedPassword, $userId]
                );
                
                if($updateResult) {
                    $success = "Password updated successfully!";
                } else {
                    $error = "Failed to update password";
                }
            }
        }
    }
}

// Get user information
$user = $db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch_assoc();

// Get user statistics
$stats = [
    'total_shipments' => $db->query("SELECT COUNT(*) as count FROM shipments WHERE user_id = ?", [$userId])->fetch_assoc()['count'],
    'delivered_shipments' => $db->query("SELECT COUNT(*) as count FROM shipments WHERE user_id = ? AND status = 'delivered'", [$userId])->fetch_assoc()['count'],
    'total_spent' => $db->query("SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status = 'completed'", [$userId])->fetch_assoc()['total'] ?? 0
];
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">My Profile</h1>

            <?php if(isset($success)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Profile Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V8a2 2 0 00-2-2H9a2 2 0 00-2 2v3"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Shipments</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_shipments'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Delivered</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $stats['delivered_shipments'] ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Spent</p>
                            <p class="text-2xl font-bold text-gray-900">$<?= number_format($stats['total_spent'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Profile Information -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                                <p class="text-xs text-gray-500 mt-1">Email cannot be changed. Contact support if needed.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Change Password</h2>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password" minlength="8"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information -->
            <div class="mt-8 bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Account Information</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Account Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Member Since</p>
                            <p class="text-sm font-medium text-gray-900"><?= date('F d, Y', strtotime($user['created_at'])) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Account Type</p>
                            <p class="text-sm font-medium text-gray-900"><?= ucfirst($user['role']) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900"><?= date('F d, Y g:i A', strtotime($user['updated_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
