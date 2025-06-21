<?php
$pageTitle = 'My Shipments';
$currentPage = 'shipments';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">My Shipments</h1>
                <a href="/user/create-shipment" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    Create Shipment
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm border-b">
        <div class="px-6 py-4">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <input type="text" id="searchInput" placeholder="Search by tracking number or description..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="picked_up">Picked Up</option>
                    <option value="in_transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="dateFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Time</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
                <button onclick="applyFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Shipments Grid -->
    <div class="p-6">
        <?php if (!empty($shipments)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($shipments as $shipment): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?= htmlspecialchars($shipment['tracking_number']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">ID: <?= $shipment['id'] ?></p>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    <?php
                                    echo match($shipment['status']) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'picked_up' => 'bg-purple-100 text-purple-800',
                                        'in_transit' => 'bg-indigo-100 text-indigo-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', $shipment['status'])) ?>
                                </span>
                            </div>

                            <!-- Route -->
                            <div class="mb-4">
                                <div class="flex items-center space-x-2 text-sm text-gray-600">
                                    <div class="flex-1">
                                        <div class="font-medium"><?= htmlspecialchars($shipment['sender_city']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($shipment['sender_name']) ?></div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 text-right">
                                        <div class="font-medium"><?= htmlspecialchars($shipment['receiver_city']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($shipment['receiver_name']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <div class="flex justify-between">
                                    <span>Service:</span>
                                    <span class="font-medium capitalize"><?= htmlspecialchars($shipment['service_type']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Weight:</span>
                                    <span class="font-medium"><?= $shipment['weight'] ?> kg</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Amount:</span>
                                    <span class="font-medium">$<?= number_format($shipment['amount'], 2) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Date:</span>
                                    <span class="font-medium"><?= date('M j, Y', strtotime($shipment['created_at'])) ?></span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <?php
                                $statusProgress = match($shipment['status']) {
                                    'pending' => 10,
                                    'confirmed' => 25,
                                    'picked_up' => 50,
                                    'in_transit' => 75,
                                    'delivered' => 100,
                                    'cancelled' => 0,
                                    default => 0
                                };
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: <?= $statusProgress ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1"><?= $statusProgress ?>% Complete</div>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <button onclick="trackShipment('<?= $shipment['tracking_number'] ?>')" 
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium">
                                    Track
                                </button>
                                <button onclick="viewShipment(<?= $shipment['id'] ?>)" 
                                        class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm">
                                    Details
                                </button>
                                <?php if ($shipment['status'] === 'pending'): ?>
                                    <button onclick="editShipment(<?= $shipment['id'] ?>)" 
                                            class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm">
                                        Edit
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if (!empty($pagination)): ?>
                <div class="mt-8 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <?= $pagination['start'] ?> to <?= $pagination['end'] ?> of <?= $pagination['total'] ?> results
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?page=<?= $pagination['current_page'] - 1 ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="?page=<?= $i ?>" 
                               class="px-3 py-2 border rounded-lg text-sm <?= $i === $pagination['current_page'] ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No shipments found</h3>
                <p class="text-gray-500 mb-6">You haven't created any shipments yet. Start by creating your first shipment.</p>
                <a href="/user/create-shipment" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    Create First Shipment
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Shipment Modal -->
<div id="shipmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Shipment Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6" id="shipmentDetails">
                <!-- Shipment details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const date = document.getElementById('dateFilter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (date) params.append('date', date);
    
    window.location.href = '?' + params.toString();
}

function trackShipment(trackingNumber) {
    // Open tracking modal
    window.openTrackingModal(trackingNumber);
}

function viewShipment(id) {
    fetch(`/api/shipments/${id}`)
        .then(response => response.json())
        .then(data => {
            const detailsHtml = `
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xl font-medium text-gray-900">${data.tracking_number}</h4>
                            <p class="text-sm text-gray-500">Shipment ID: ${data.id}</p>
                        </div>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                            ${getStatusColor(data.status)}">
                            ${data.status.charAt(0).toUpperCase() + data.status.slice(1).replace('_', ' ')}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-3">Sender Information</h5>
                            <div class="space-y-2 text-sm">
                                <div><strong>Name:</strong> ${data.sender_name}</div>
                                <div><strong>Phone:</strong> ${data.sender_phone}</div>
                                <div><strong>Address:</strong> ${data.sender_address}</div>
                                <div><strong>City:</strong> ${data.sender_city} ${data.sender_zip}</div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-3">Receiver Information</h5>
                            <div class="space-y-2 text-sm">
                                <div><strong>Name:</strong> ${data.receiver_name}</div>
                                <div><strong>Phone:</strong> ${data.receiver_phone}</div>
                                <div><strong>Address:</strong> ${data.receiver_address}</div>
                                <div><strong>City:</strong> ${data.receiver_city} ${data.receiver_zip}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-3">Package Details</h5>
                            <div class="space-y-2 text-sm">
                                <div><strong>Weight:</strong> ${data.weight} kg</div>
                                <div><strong>Service:</strong> ${data.service_type}</div>
                                <div><strong>Amount:</strong> $${parseFloat(data.amount).toFixed(2)}</div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-3">Dates</h5>
                            <div class="space-y-2 text-sm">
                                <div><strong>Created:</strong> ${new Date(data.created_at).toLocaleDateString()}</div>
                                <div><strong>Updated:</strong> ${new Date(data.updated_at).toLocaleDateString()}</div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-3">Actions</h5>
                            <div class="space-y-2">
                                <button onclick="trackShipment('${data.tracking_number}')" 
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm">
                                    Track Package
                                </button>
                                ${data.status === 'pending' ? 
                                    `<button onclick="editShipment(${data.id})" 
                                             class="w-full border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg text-sm">
                                        Edit Shipment
                                    </button>` : ''
                                }
                            </div>
                        </div>
                    </div>
                    
                    ${data.description ? `
                        <div>
                            <h5 class="text-sm font-medium text-gray-900 mb-2">Description</h5>
                            <p class="text-sm text-gray-600">${data.description}</p>
                        </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('shipmentDetails').innerHTML = detailsHtml;
            document.getElementById('shipmentModal').classList.remove('hidden');
        });
}

function editShipment(id) {
    window.location.href = `/user/edit-shipment/${id}`;
}

function closeModal() {
    document.getElementById('shipmentModal').classList.add('hidden');
}

function getStatusColor(status) {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'picked_up': 'bg-purple-100 text-purple-800',
        'in_transit': 'bg-indigo-100 text-indigo-800',
        'delivered': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

// Real-time updates
setInterval(() => {
    // Update shipment statuses if needed
    const shipmentCards = document.querySelectorAll('[data-shipment-id]');
    if (shipmentCards.length > 0) {
        // Batch update shipment statuses
        // This would be implemented based on WebSocket or polling
    }
}, 30000); // Update every 30 seconds
</script>
