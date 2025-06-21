<?php
if (!Auth::checkPermission('analytics_view')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Get analytics data
$totalShipments = $db->query("SELECT COUNT(*) as count FROM shipments")->fetch_assoc()['count'];
$totalRevenue = $db->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
$activeUsers = $db->query("SELECT COUNT(*) as count FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
$avgDeliveryTime = $db->query("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, delivered_at)) as avg_time FROM shipments WHERE status = 'delivered'")->fetch_assoc()['avg_time'] ?? 0;

// Monthly shipment data for chart
$monthlyData = $db->query("SELECT MONTH(created_at) as month, COUNT(*) as count 
                          FROM shipments 
                          WHERE YEAR(created_at) = YEAR(CURRENT_DATE()) 
                          GROUP BY MONTH(created_at) 
                          ORDER BY month");

$chartData = array_fill(1, 12, 0);
while ($row = $monthlyData->fetch_assoc()) {
    $chartData[$row['month']] = $row['count'];
}

// Top performing couriers
$topCouriers = $db->query("SELECT cr.name, COUNT(s.id) as deliveries, AVG(sr.rating) as avg_rating
                          FROM courier_routes cr
                          LEFT JOIN shipments s ON cr.id = s.courier_id AND s.status = 'delivered'
                          LEFT JOIN shipment_ratings sr ON s.id = sr.shipment_id
                          GROUP BY cr.id
                          ORDER BY deliveries DESC
                          LIMIT 5");

// Recent activity
$recentActivity = $db->query("SELECT 'shipment' as type, s.tracking_number as reference, u.email as user, s.created_at as timestamp
                             FROM shipments s 
                             JOIN users u ON s.user_id = u.id
                             UNION ALL
                             SELECT 'payment' as type, CONCAT('Payment #', p.id) as reference, u.email as user, p.created_at as timestamp
                             FROM payments p 
                             JOIN shipments s ON p.shipment_id = s.id
                             JOIN users u ON s.user_id = u.id
                             ORDER BY timestamp DESC
                             LIMIT 10");
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
            <div class="flex space-x-2">
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Export Data</button>
                <select class="px-3 py-2 border border-gray-300 rounded-md">
                    <option>Last 30 Days</option>
                    <option>Last 90 Days</option>
                    <option>This Year</option>
                </select>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Shipments</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($totalShipments) ?></p>
                        <p class="text-xs text-green-600">+12% from last month</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">$<?= number_format($totalRevenue, 2) ?></p>
                        <p class="text-xs text-green-600">+8% from last month</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($activeUsers) ?></p>
                        <p class="text-xs text-red-600">-3% from last month</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Delivery Time</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($avgDeliveryTime, 1) ?>h</p>
                        <p class="text-xs text-green-600">-5% from last month</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Monthly Shipments Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Shipments</h2>
                <div class="h-64 flex items-end justify-between space-x-2">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                    <div class="flex flex-col items-center">
                        <div class="bg-blue-500 rounded-t" style="height: <?= ($chartData[$i] / max($chartData)) * 200 ?>px; width: 20px;"></div>
                        <span class="text-xs text-gray-600 mt-2"><?= date('M', mktime(0, 0, 0, $i, 1)) ?></span>
                        <span class="text-xs text-gray-800 font-medium"><?= $chartData[$i] ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Top Couriers -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Performing Couriers</h2>
                <div class="space-y-4">
                    <?php while ($courier = $topCouriers->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($courier['name']) ?></p>
                            <p class="text-sm text-gray-600"><?= $courier['deliveries'] ?> deliveries</p>
                        </div>
                        <div class="flex items-center">
                            <div class="flex text-yellow-400">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-4 h-4 <?= $i <= round($courier['avg_rating'] ?? 0) ? 'fill-current' : '' ?>" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <?php endfor; ?>
                            </div>
                            <span class="ml-2 text-sm text-gray-600"><?= number_format($courier['avg_rating'] ?? 0, 1) ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php while ($activity = $recentActivity->fetch_assoc()): ?>
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <?php if ($activity['type'] === 'shipment'): ?>
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <?php else: ?>
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                <span class="font-medium"><?= htmlspecialchars($activity['user']) ?></span>
                                <?= $activity['type'] === 'shipment' ? 'created a new shipment' : 'made a payment' ?>
                                <span class="font-medium"><?= htmlspecialchars($activity['reference']) ?></span>
                            </p>
                            <p class="text-sm text-gray-500"><?= date('M d, Y h:i A', strtotime($activity['timestamp'])) ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>
