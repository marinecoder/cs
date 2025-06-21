<?php
// This file is included directly from the controller
// Variables available: $shipment, $progress
?>

<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <div class="flex justify-between items-center border-b p-4">
            <h3 class="text-xl font-bold">Tracking #<?= htmlspecialchars($shipment['tracking_number']) ?></h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="p-6">
            <!-- Shipment Info -->
            <div class="mb-6 bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">From:</h4>
                        <p class="text-sm text-gray-600">
                            <?= htmlspecialchars($shipment['sender_name']) ?><br>
                            <?= htmlspecialchars($shipment['sender_address']) ?><br>
                            <?= htmlspecialchars($shipment['sender_city']) ?>
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">To:</h4>
                        <p class="text-sm text-gray-600">
                            <?= htmlspecialchars($shipment['receiver_name']) ?><br>
                            <?= htmlspecialchars($shipment['receiver_address']) ?><br>
                            <?= htmlspecialchars($shipment['receiver_city']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div class="relative pl-8 border-l-2 border-blue-200">
                <?php
                $statuses = [
                    'Processing' => 'bg-blue-500',
                    'In Transit' => 'bg-yellow-500',
                    'Out for Delivery' => 'bg-orange-500',
                    'Delivered' => 'bg-green-500',
                    'Delayed' => 'bg-red-500'
                ];
                
                if($progress->num_rows > 0):
                    while($step = $progress->fetch_assoc()):
                        $statusClass = $statuses[$step['status']] ?? 'bg-gray-500';
                ?>
                <div class="mb-8 relative">
                    <div class="absolute -left-11 w-8 h-8 rounded-full <?= $statusClass ?> flex items-center justify-center text-white">
                        <?php if($step['status'] === 'Delivered'): ?>
                            ✓
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= htmlspecialchars($step['status']) ?></span>
                            <span class="text-sm text-gray-500"><?= date('M d, h:i A', strtotime($step['timestamp'])) ?></span>
                        </div>
                        <p class="mt-1"><?= htmlspecialchars($step['description']) ?></p>
                        <?php if(!empty($step['location'])): ?>
                            <div class="flex items-center mt-2 text-sm text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <?= htmlspecialchars($step['location']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="mb-8 relative">
                    <div class="absolute -left-11 w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between">
                            <span class="font-semibold">Processing</span>
                            <span class="text-sm text-gray-500"><?= date('M d, h:i A', strtotime($shipment['created_at'])) ?></span>
                        </div>
                        <p class="mt-1">Shipment created and awaiting processing</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Map Preview -->
            <div class="mt-6 bg-gray-200 border-2 border-dashed rounded-xl w-full h-48 flex items-center justify-center">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <p class="mt-2 text-gray-500">Live map integration would appear here</p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end mt-6 space-x-3">
                <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Close
                </button>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    Contact Support
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal() {
    document.getElementById('trackingModal').remove();
}
</script>
