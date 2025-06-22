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
                            <h3 class="font-semibold text-gray-800 mb-3">Default Admin Account:</h3>
                            <div class="bg-white border border-gray-300 rounded p-3 mb-4">
                                <div class="text-sm text-gray-700">
                                    <div class="mb-2">
                                        <strong class="text-gray-900">Email:</strong> 
                                        <span class="font-mono bg-gray-100 px-2 py-1 rounded">The email you provided during setup</span>
                                    </div>
                                    <div>
                                        <strong class="text-gray-900">Password:</strong> 
                                        <span class="font-mono bg-gray-100 px-2 py-1 rounded">The password you created</span>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="font-medium text-gray-800 mb-2">Demo Accounts (Optional - for testing):</h4>
                            <div class="space-y-2 text-sm text-gray-700">
                                <div class="bg-blue-50 border border-blue-200 rounded p-2">
                                    <strong>Demo User:</strong> user@demo.com / demo123
                                    <br><span class="text-xs text-blue-600">Create shipments and track packages</span>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded p-2">
                                    <strong>Demo Courier:</strong> courier@demo.com / demo123
                                    <br><span class="text-xs text-green-600">Manage deliveries and routes</span>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-xs text-gray-500">
                                <p>💡 Demo accounts are created automatically for testing purposes.</p>
                                <p>You can delete them later from the admin panel.</p>
                            </div>
                        </div>

                        <div class="text-center space-y-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">What would you like to do next?</h3>
                            
                            <div class="grid md:grid-cols-3 gap-4">
                                <!-- Admin Login -->
                                <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg p-6 hover:shadow-md transition duration-200">
                                    <div class="text-center">
                                        <div class="text-red-600 text-3xl mb-3">👨‍💼</div>
                                        <h4 class="font-semibold text-red-800 mb-2">Login as Admin</h4>
                                        <p class="text-red-700 text-sm mb-4">Access admin dashboard to configure your system</p>
                                        <a href="../index.php?admin=1" class="inline-block bg-red-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-red-700 transition duration-200">
                                            Admin Login
                                        </a>
                                    </div>
                                </div>

                                <!-- User Login -->
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-6 hover:shadow-md transition duration-200">
                                    <div class="text-center">
                                        <div class="text-blue-600 text-3xl mb-3">👤</div>
                                        <h4 class="font-semibold text-blue-800 mb-2">Login as User</h4>
                                        <p class="text-blue-700 text-sm mb-4">Create shipments and track packages</p>
                                        <a href="../index.php?user=1" class="inline-block bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                                            User Login
                                        </a>
                                    </div>
                                </div>

                                <!-- Visit Homepage -->
                                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-6 hover:shadow-md transition duration-200">
                                    <div class="text-center">
                                        <div class="text-green-600 text-3xl mb-3">🏠</div>
                                        <h4 class="font-semibold text-green-800 mb-2">Visit Homepage</h4>
                                        <p class="text-green-700 text-sm mb-4">Explore the public website and features</p>
                                        <a href="../public/frontend/index.html" class="inline-block bg-green-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-green-700 transition duration-200">
                                            Homepage
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-3">Quick Access Links</h4>
                                <div class="flex flex-wrap justify-center gap-3">
                                    <a href="../index.php" class="bg-gray-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-gray-700 transition duration-200">
                                        Main Application
                                    </a>
                                    <a href="../public/frontend/tracking.html" class="bg-purple-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-purple-700 transition duration-200">
                                        Package Tracking
                                    </a>
                                    <a href="../public/frontend/services.html" class="bg-indigo-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-indigo-700 transition duration-200">
                                        Services
                                    </a>
                                    <a href="../public/frontend/contact.html" class="bg-orange-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-orange-700 transition duration-200">
                                        Contact
                                    </a>
                                </div>
                            </div>
                            
                            <div class="text-sm text-gray-600 mt-6">
                                <p>Need help? Check our documentation or contact support.</p>
                                <p class="mt-2 text-xs">Auto-redirect to main application in <span id="countdown">30</span> seconds</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect after 30 seconds with countdown
        let countdown = 30;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(function() {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '../index.php';
            }
        }, 1000);
        
        // Add click tracking for analytics
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' && !e.target.href.includes('#')) {
                // Clear the auto-redirect timer when user clicks a link
                clearInterval(timer);
            }
        });
    </script>
</body>
</html>
