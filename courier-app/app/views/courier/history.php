<?php
if (!Auth::checkPermission('shipment_view_assigned')) {
    header('Location: /errors/403');
    exit;
}
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Delivery History</h1>

        <!-- Delivery History Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($shipment = $deliveredShipments->fetch_assoc()): ?>
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
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($shipment['receiver_city']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M d, Y g:i A', strtotime($shipment['actual_delivery_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                $<?= number_format($shipment['total_amount'], 2) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
