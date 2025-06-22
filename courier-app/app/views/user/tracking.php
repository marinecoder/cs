<?php
if (!Auth::isLoggedIn()) {
    header('Location: /login');
    exit;
}

$user = Auth::getCurrentUser();
$db = new Database();

// Handle tracking search
$trackingResults = null;
$trackingNumber = $_GET['tracking'] ?? '';

if ($trackingNumber) {
    // Get shipment by tracking number (user can only see their own shipments)
    $trackingResults = $db->query(
        "SELECT s.*, st.name as shipment_type_name
         FROM shipments s
         JOIN shipment_types st ON s.shipment_type_id = st.id
         WHERE s.tracking_number = ? AND s.user_id = ?",
        [$trackingNumber, $user['id']]
    )->fetch_assoc();
}
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Track Your Shipment</h1>

            <!-- Tracking Search -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form method="GET" class="flex space-x-4">
                    <div class="flex-1">
                        <label for="tracking" class="block text-sm font-medium text-gray-700 mb-2">
                            Enter Tracking Number
                        </label>
                        <input type="text" 
                               id="tracking" 
                               name="tracking" 
                               value="<?= htmlspecialchars($trackingNumber) ?>"
                               placeholder="CD123456"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Track Package
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($trackingNumber && !$trackingResults): ?>
                <!-- No Results -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
                    <div class="flex">
                        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-red-800">Tracking number not found</h3>
                            <p class="text-red-700 mt-1">
                                No shipment found with tracking number "<?= htmlspecialchars($trackingNumber) ?>". 
                                Please check the tracking number and try again.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($trackingResults): ?>
                <!-- Tracking Results -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Shipment Header -->
                    <div class="bg-blue-600 text-white p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold"><?= htmlspecialchars($trackingResults['tracking_number']) ?></h2>
                                <p class="text-blue-100 mt-1"><?= htmlspecialchars($trackingResults['shipment_type_name']) ?> Shipment</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    <?php
                                    switch($trackingResults['status']) {
                                        case 'delivered':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'in_transit':
                                        case 'out_for_delivery':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'pending':
                                        case 'confirmed':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', $trackingResults['status'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Shipment Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-3">From:</h3>
                                <div class="text-gray-600">
                                    <p class="font-medium"><?= htmlspecialchars($trackingResults['sender_name']) ?></p>
                                    <p><?= htmlspecialchars($trackingResults['sender_address']) ?></p>
                                    <p><?= htmlspecialchars($trackingResults['sender_city']) ?></p>
                                    <p><?= htmlspecialchars($trackingResults['sender_phone']) ?></p>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-3">To:</h3>
                                <div class="text-gray-600">
                                    <p class="font-medium"><?= htmlspecialchars($trackingResults['receiver_name']) ?></p>
                                    <p><?= htmlspecialchars($trackingResults['receiver_address']) ?></p>
                                    <p><?= htmlspecialchars($trackingResults['receiver_city']) ?></p>
                                    <p><?= htmlspecialchars($trackingResults['receiver_phone']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Package Details -->
                        <?php if ($trackingResults['description'] || $trackingResults['weight']): ?>
                        <div class="bg-gray-50 rounded-lg p-4 mb-8">
                            <h3 class="font-semibold text-gray-900 mb-3">Package Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <?php if ($trackingResults['description']): ?>
                                <div>
                                    <span class="text-gray-500">Description:</span>
                                    <p class="font-medium"><?= htmlspecialchars($trackingResults['description']) ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($trackingResults['weight']): ?>
                                <div>
                                    <span class="text-gray-500">Weight:</span>
                                    <p class="font-medium"><?= $trackingResults['weight'] ?> kg</p>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <p class="font-medium"><?= date('M d, Y', strtotime($trackingResults['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="flex space-x-4">
                            <button onclick="showTrackingModal(<?= $trackingResults['id'] ?>)" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                View Detailed Timeline
                            </button>
                            
                            <?php if ($trackingResults['status'] !== 'delivered' && $trackingResults['status'] !== 'cancelled'): ?>
                            <button onclick="contactSupport('<?= $trackingResults['tracking_number'] ?>')" 
                                    class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.444l-3.846 1.154L6.4 16.863A8.001 8.001 0 0121 12z"></path>
                                </svg>
                                Contact Support
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Shipments -->
            <div class="mt-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Your Recent Shipments</h2>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <?php
                    $recentShipments = $db->query(
                        "SELECT s.*, st.name as shipment_type_name
                         FROM shipments s
                         JOIN shipment_types st ON s.shipment_type_id = st.id
                         WHERE s.user_id = ?
                         ORDER BY s.created_at DESC
                         LIMIT 5",
                        [$user['id']]
                    );
                    ?>
                    
                    <?php if ($recentShipments && $recentShipments->num_rows > 0): ?>
                        <div class="divide-y divide-gray-200">
                            <?php while($shipment = $recentShipments->fetch_assoc()): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($shipment['tracking_number']) ?></p>
                                        <p class="text-sm text-gray-600">
                                            To: <?= htmlspecialchars($shipment['receiver_city']) ?> • 
                                            <?= date('M d, Y', strtotime($shipment['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?php
                                            switch($shipment['status']) {
                                                case 'delivered':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'in_transit':
                                                case 'out_for_delivery':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'pending':
                                                case 'confirmed':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= ucfirst(str_replace('_', ' ', $shipment['status'])) ?>
                                        </span>
                                        <button onclick="window.location.href='/tracking?tracking=<?= $shipment['tracking_number'] ?>'" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            Track
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m-3 0h1m0 0l-1-1v1z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No shipments</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first shipment.</p>
                            <div class="mt-6">
                                <a href="/shipment/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    Create Shipment
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTrackingModal(shipmentId) {
    // Fetch tracking modal
    fetch(`/tracking-modal?id=${shipmentId}`)
        .then(response => response.text())
        .then(html => {
            document.body.insertAdjacentHTML('beforeend', html);
        });
}

function contactSupport(trackingNumber) {
    window.location.href = `/support?tracking=${trackingNumber}`;
}
</script>
