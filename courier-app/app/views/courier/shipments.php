<?php
if (!Auth::checkPermission('shipment_view_assigned')) {
    header('Location: /errors/403');
    exit;
}
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Assigned Shipments</h1>
            <div class="flex space-x-2">
                <select onchange="filterByStatus(this.value)" class="px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $selectedStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="picked_up" <?= $selectedStatus === 'picked_up' ? 'selected' : '' ?>>Picked Up</option>
                    <option value="in_transit" <?= $selectedStatus === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                    <option value="out_for_delivery" <?= $selectedStatus === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                </select>
            </div>
        </div>

        <!-- Shipments Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($shipment = $shipments->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-blue-600"><?= htmlspecialchars($shipment['tracking_number']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div><?= htmlspecialchars($shipment['user_name']) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($shipment['user_email']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div><?= htmlspecialchars($shipment['receiver_name']) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($shipment['receiver_address']) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($shipment['receiver_city']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($shipment['shipment_type_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'picked_up' => 'bg-purple-100 text-purple-800',
                                    'in_transit' => 'bg-indigo-100 text-indigo-800',
                                    'out_for_delivery' => 'bg-orange-100 text-orange-800',
                                    'delivered' => 'bg-green-100 text-green-800'
                                ];
                                $statusClass = $statusClasses[$shipment['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $shipment['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <button onclick="updateStatus(<?= $shipment['id'] ?>)" class="text-blue-600 hover:text-blue-900">
                                        Update
                                    </button>
                                    <button onclick="viewDetails(<?= $shipment['id'] ?>)" class="text-green-600 hover:text-green-900">
                                        Details
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    window.location.href = `/courier/shipments${status ? '?status=' + status : ''}`;
}

function updateStatus(shipmentId) {
    // Reuse the modal from dashboard
    console.log('Update status for shipment:', shipmentId);
}

function viewDetails(shipmentId) {
    console.log('View details for shipment:', shipmentId);
}
</script>
