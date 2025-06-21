<?php
// Check if config.php exists
if(!file_exists('../config.php')) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete - Courier Dash</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6">
                    <div class="text-center">
                        <div class="text-6xl mb-4">🎉</div>
                        <h1 class="text-3xl font-bold">Installation Complete!</h1>
                        <p class="mt-2 opacity-90">Your Courier Dash system is ready to use</p>
                    </div>
                </div>

                <div class="p-8">
                    <div class="space-y-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <div class="flex items-center">
                                <div class="text-green-600 text-2xl mr-4">✓</div>
                                <div>
                                    <h3 class="font-semibold text-green-800">System Successfully Installed</h3>
                                    <p class="text-green-700">Database created and configured</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h3 class="font-semibold text-blue-800 mb-3">Next Steps:</h3>
                            <ul class="text-blue-700 space-y-2">
                                <li class="flex items-center">
                                    <span class="text-blue-600 mr-2">•</span>
                                    Access your admin panel with the credentials you created
                                </li>
                                <li class="flex items-center">
                                    <span class="text-blue-600 mr-2">•</span>
                                    Configure your system settings
                                </li>
                                <li class="flex items-center">
                                    <span class="text-blue-600 mr-2">•</span>
                                    Set up payment methods and shipping rates
                                </li>
                                <li class="flex items-center">
                                    <span class="text-blue-600 mr-2">•</span>
                                    Add courier users and configure routes
                                </li>
                            </ul>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <h3 class="font-semibold text-yellow-800 mb-3">Security Recommendations:</h3>
                            <ul class="text-yellow-700 space-y-2">
                                <li class="flex items-center">
                                    <span class="text-yellow-600 mr-2">⚠</span>
                                    Delete the /install directory for security
                                </li>
                                <li class="flex items-center">
                                    <span class="text-yellow-600 mr-2">⚠</span>
                                    Set up SSL certificate for production use
                                </li>
                                <li class="flex items-center">
                                    <span class="text-yellow-600 mr-2">⚠</span>
                                    Configure regular database backups
                                </li>
                                <li class="flex items-center">
                                    <span class="text-yellow-600 mr-2">⚠</span>
                                    Review and update default passwords
                                </li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                            <h3 class="font-semibold text-gray-800 mb-3">Demo Accounts Created:</h3>
                            <div class="space-y-2 text-gray-700">
                                <div>
                                    <strong>User:</strong> demo@user.com / password
                                </div>
                                <div>
                                    <strong>Courier:</strong> courier@demo.com / password
                                </div>
                            </div>
                        </div>

                        <div class="text-center space-y-4">
                            <a href="../index.php" class="inline-block bg-blue-600 text-white py-3 px-8 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                                Launch Courier Dash
                            </a>
                            
                            <div class="text-sm text-gray-600">
                                <p>Need help? Check our documentation or contact support.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect after 10 seconds
        setTimeout(function() {
            window.location.href = '../index.php';
        }, 10000);
    </script>
</body>
</html>
