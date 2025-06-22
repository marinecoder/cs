<?php
Auth::requireLogin();

$db = new Database();
$userId = $_SESSION['user_id'];

// Get user's payment history
$payments = $db->query("
    SELECT p.*, s.tracking_number, s.description as shipment_description,
           s.receiver_name, s.receiver_city
    FROM payments p
    JOIN shipments s ON p.shipment_id = s.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
", [$userId]);

// Get payment statistics
$totalPaid = $db->query("
    SELECT SUM(amount) as total
    FROM payments 
    WHERE user_id = ? AND status = 'completed'
", [$userId])->fetch_assoc()['total'] ?? 0;

$pendingPayments = $db->query("
    SELECT COUNT(*) as count
    FROM payments 
    WHERE user_id = ? AND status = 'pending'
", [$userId])->fetch_assoc()['count'] ?? 0;
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Payment History</h1>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Download Statement
            </button>
        </div>

        <!-- Payment Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Paid</p>
                        <p class="text-2xl font-bold text-green-600">$<?= number_format($totalPaid, 2) ?></p>
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
                        <p class="text-sm font-medium text-gray-600">Pending Payments</p>
                        <p class="text-2xl font-bold text-orange-600"><?= $pendingPayments ?></p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">This Month</p>
                        <p class="text-2xl font-bold text-blue-600">
                            $<?php 
                            $monthlyTotal = $db->query("
                                SELECT SUM(amount) as total
                                FROM payments 
                                WHERE user_id = ? AND status = 'completed' 
                                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                            ", [$userId])->fetch_assoc()['total'] ?? 0;
                            echo number_format($monthlyTotal, 2);
                            ?>
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Payment Transactions</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($payment = $payments->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('M d, Y', strtotime($payment['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-blue-600"><?= htmlspecialchars($payment['tracking_number']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate">
                                    <?= htmlspecialchars($payment['shipment_description']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    To: <?= htmlspecialchars($payment['receiver_name']) ?>, <?= htmlspecialchars($payment['receiver_city']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                $<?= number_format($payment['amount'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClasses = [
                                    'completed' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    'refunded' => 'bg-blue-100 text-blue-800'
                                ];
                                $statusClass = $statusClasses[$payment['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                    <?= ucfirst($payment['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <button onclick="viewReceipt(<?= $payment['id'] ?>)" class="text-blue-600 hover:text-blue-900">
                                        Receipt
                                    </button>
                                    <?php if($payment['status'] === 'completed'): ?>
                                    <button onclick="requestRefund(<?= $payment['id'] ?>)" class="text-red-600 hover:text-red-900">
                                        Refund
                                    </button>
                                    <?php endif; ?>
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
function viewReceipt(paymentId) {
    // Open receipt in new window
    window.open(`/receipt/${paymentId}`, '_blank', 'width=800,height=600');
}

function requestRefund(paymentId) {
    if(confirm('Are you sure you want to request a refund for this payment?')) {
        // Submit refund request
        fetch(`/api/refund-request`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
            },
            body: JSON.stringify({payment_id: paymentId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Refund request submitted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
