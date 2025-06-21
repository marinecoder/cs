<?php
session_start();

// Check if already installed
if(file_exists('../config.php')) {
    header('Location: ../index.php');
    exit;
}

$step = $_GET['step'] ?? 1;
$error = $_GET['error'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dash - Installation Wizard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
                    <h1 class="text-3xl font-bold">Courier Dash Installation</h1>
                    <p class="mt-2 opacity-90">Professional Courier Management System</p>
                </div>

                <div class="p-8">
                    <!-- Progress Bar -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                            <span class="<?= $step >= 1 ? 'text-blue-600 font-semibold' : '' ?>">System Check</span>
                            <span class="<?= $step >= 2 ? 'text-blue-600 font-semibold' : '' ?>">Database Setup</span>
                            <span class="<?= $step >= 3 ? 'text-blue-600 font-semibold' : '' ?>">Admin Account</span>
                            <span class="<?= $step >= 4 ? 'text-blue-600 font-semibold' : '' ?>">Complete</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: <?= ($step / 4) * 100 ?>%"></div>
                        </div>
                    </div>

                    <?php if($error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    switch($step) {
                        case 1:
                            echo '<div class="space-y-6">';
                            echo '<h2 class="text-2xl font-bold text-gray-800">System Requirements Check</h2>';
                            
                            $checks = [
                                'PHP Version (8.2+)' => version_compare(PHP_VERSION, '8.2.0', '>='),
                                'MySQLi Extension' => extension_loaded('mysqli'),
                                'Session Support' => function_exists('session_start'),
                                'File Write Permission' => is_writable('../'),
                                'Mail Function' => function_exists('mail'),
                                'JSON Extension' => extension_loaded('json')
                            ];
                            
                            $allPass = true;
                            foreach($checks as $check => $status) {
                                $allPass = $allPass && $status;
                                echo '<div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">';
                                echo '<span class="font-medium">' . $check . '</span>';
                                echo '<span class="text-2xl">' . ($status ? '✅' : '❌') . '</span>';
                                echo '</div>';
                            }
                            
                            echo '<div class="mt-8">';
                            if($allPass) {
                                echo '<a href="?step=2" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 inline-block text-center">Continue to Database Setup</a>';
                            } else {
                                echo '<div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded">';
                                echo 'Please fix the failed requirements before continuing.';
                                echo '</div>';
                            }
                            echo '</div>';
                            echo '</div>';
                            break;
                            
                        case 2:
                            echo '<div class="space-y-6">';
                            echo '<h2 class="text-2xl font-bold text-gray-800">Database Configuration</h2>';
                            echo '<form action="?step=3" method="POST" class="space-y-4">';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">MySQL Host</label>';
                            echo '<input type="text" name="db_host" value="localhost" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>';
                            echo '<input type="text" name="db_name" placeholder="courier_dash" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">Username</label>';
                            echo '<input type="text" name="db_user" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">Password</label>';
                            echo '<input type="password" name="db_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div class="flex space-x-4 mt-8">';
                            echo '<a href="?step=1" class="flex-1 bg-gray-300 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-400 transition duration-200 text-center">Back</a>';
                            echo '<button type="submit" class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">Test & Continue</button>';
                            echo '</div>';
                            echo '</form>';
                            echo '</div>';
                            break;
                            
                        case 3:
                            // Process database configuration
                            if($_POST) {
                                $db_host = $_POST['db_host'];
                                $db_name = $_POST['db_name'];
                                $db_user = $_POST['db_user'];
                                $db_password = $_POST['db_password'];
                                
                                // Test database connection
                                try {
                                    $conn = new mysqli($db_host, $db_user, $db_password);
                                    if($conn->connect_error) {
                                        throw new Exception('Connection failed: ' . $conn->connect_error);
                                    }
                                    
                                    // Create database if it doesn't exist
                                    $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name`");
                                    $conn->select_db($db_name);
                                    
                                    // Store database config in session
                                    $_SESSION['db_config'] = [
                                        'host' => $db_host,
                                        'name' => $db_name,
                                        'user' => $db_user,
                                        'password' => $db_password
                                    ];
                                    
                                } catch(Exception $e) {
                                    header('Location: ?step=2&error=' . urlencode($e->getMessage()));
                                    exit;
                                }
                            }
                            
                            echo '<div class="space-y-6">';
                            echo '<h2 class="text-2xl font-bold text-gray-800">Create Admin Account</h2>';
                            echo '<form action="database.php" method="POST" class="space-y-4">';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>';
                            echo '<input type="email" name="admin_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">Admin Password</label>';
                            echo '<input type="password" name="admin_password" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>';
                            echo '<input type="password" name="admin_password_confirm" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div>';
                            echo '<label class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>';
                            echo '<input type="text" name="company_name" value="Courier Dash" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">';
                            echo '</div>';
                            echo '<div class="flex space-x-4 mt-8">';
                            echo '<a href="?step=2" class="flex-1 bg-gray-300 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-400 transition duration-200 text-center">Back</a>';
                            echo '<button type="submit" class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-700 transition duration-200">Install System</button>';
                            echo '</div>';
                            echo '</form>';
                            echo '</div>';
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
