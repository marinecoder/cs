<?php
if (!Auth::checkPermission('courier_manage')) {
    header('Location: /errors/403');
    exit;
}

$db = new Database();

// Handle courier actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $courierId = $_POST['courier_id'] ?? 0;
    
    switch ($action) {
        case 'add':
            $db->query("INSERT INTO courier_routes (name, email, phone, vehicle_type, status) VALUES (?, ?, ?, ?, 'active')",
                      [$_POST['name'], $_POST['email'], $_POST['phone'], $_POST['vehicle_type']]);
            $success = "Courier added successfully!";
            break;
        case 'update_status':
            $db->query("UPDATE courier_routes SET status = ? WHERE id = ?", [$_POST['status'], $courierId]);
            $success = "Courier status updated!";
            break;
        case 'assign_route':
            $db->query("UPDATE shipments SET courier_id = ? WHERE id = ?", [$courierId, $_POST['shipment_id']]);
            $success = "Route assigned successfully!";
            break;
    }
}

// Get all couriers
$couriers = $db->query("SELECT cr.*, COUNT(s.id) as active_shipments 
                       FROM courier_routes cr 
                       LEFT JOIN shipments s ON cr.id = s.courier_id AND s.status NOT IN ('delivered', 'cancelled')
                       GROUP BY cr.id 
                       ORDER BY cr.name");

// Get unassigned shipments
$unassignedShipments = $db->query("SELECT s.*, u.email as customer_email 
                                  FROM shipments s 
                                  JOIN users u ON s.user_id = u.id 
                                  WHERE s.courier_id IS NULL AND s.status = 'pending'");
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Courier Management</h1>
            <button onclick="showAddCourierModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Add New Courier
            </button>
        </div>

        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <!-- Courier List -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <?php while ($courier = $couriers->fetch_assoc()): ?>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($courier['name']) ?></h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($courier['email']) ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($courier['phone']) ?></p>
                    </div>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                        <?= $courier['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                            ($courier['status'] === 'busy' ? 'bg-yellow-100 text-yellow-800' : 
                             'bg-red-100 text-red-800') ?>">
                        <?= ucfirst($courier['status']) ?>
                    </span>
                </div>
                
                <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                    <span>Vehicle: <?= ucfirst($courier['vehicle_type']) ?></span>
                    <span>Active Deliveries: <?= $courier['active_shipments'] ?></span>
                </div>
                
                <div class="flex space-x-2">
                    <button onclick="viewCourierDetails(<?= $courier['id'] ?>)" 
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Details</button>
                    <button onclick="showAssignModal(<?= $courier['id'] ?>)" 
                            class="text-green-600 hover:text-green-800 text-sm font-medium">Assign Route</button>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="courier_id" value="<?= $courier['id'] ?>">
                        <select name="status" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1">
                            <option value="active" <?= $courier['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="busy" <?= $courier['status'] === 'busy' ? 'selected' : '' ?>>Busy</option>
                            <option value="offline" <?= $courier['status'] === 'offline' ? 'selected' : '' ?>>Offline</option>
                        </select>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Unassigned Shipments -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Unassigned Shipments</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pickup</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($shipment = $unassignedShipments->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $shipment['tracking_number'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($shipment['customer_email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($shipment['pickup_address']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($shipment['delivery_address']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    <?= $shipment['priority'] === 'urgent' ? 'bg-red-100 text-red-800' : 
                                        ($shipment['priority'] === 'high' ? 'bg-orange-100 text-orange-800' : 
                                         'bg-gray-100 text-gray-800') ?>">
                                    <?= ucfirst($shipment['priority']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="quickAssign(<?= $shipment['id'] ?>)" 
                                        class="text-blue-600 hover:text-blue-900">Quick Assign</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Courier Modal -->
<div id="addCourierModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Add New Courier</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="tel" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
                    <select name="vehicle_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Vehicle</option>
                        <option value="bike">Bike</option>
                        <option value="car">Car</option>
                        <option value="van">Van</option>
                        <option value="truck">Truck</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="hideAddCourierModal()" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Courier</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddCourierModal() {
    document.getElementById('addCourierModal').classList.remove('hidden');
}

function hideAddCourierModal() {
    document.getElementById('addCourierModal').classList.add('hidden');
}

function viewCourierDetails(courierId) {
    // Implementation for viewing courier details
    window.location.href = `/admin/courier-details/${courierId}`;
}

function showAssignModal(courierId) {
    // Implementation for showing assign modal
    alert('Assign modal for courier ' + courierId + ' would be shown here');
}

function quickAssign(shipmentId) {
    // Implementation for quick assign
    alert('Quick assign for shipment ' + shipmentId + ' would be shown here');
}
</script>
