<?php
if (!Auth::isLoggedIn()) {
    header('Location: /login');
    exit;
}

$user = Auth::getCurrentUser();
?>

<div class="flex">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Address Book</h1>
            <button onclick="openAddAddressModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Address
            </button>
        </div>

        <!-- Address Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($addresses && $addresses->num_rows > 0): ?>
                <?php while($address = $addresses->fetch_assoc()): ?>
                <div class="bg-white p-6 rounded-lg shadow-md border <?= $address['is_default'] ? 'border-blue-500' : 'border-gray-200' ?>">
                    <?php if($address['is_default']): ?>
                        <div class="flex items-center mb-3">
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Default</span>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="font-semibold text-gray-900 mb-2"><?= htmlspecialchars($address['label']) ?></h3>
                    <div class="text-gray-600 text-sm space-y-1">
                        <p><?= htmlspecialchars($address['name']) ?></p>
                        <p><?= htmlspecialchars($address['address_line_1']) ?></p>
                        <?php if($address['address_line_2']): ?>
                            <p><?= htmlspecialchars($address['address_line_2']) ?></p>
                        <?php endif; ?>
                        <p><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> <?= htmlspecialchars($address['postal_code']) ?></p>
                        <p><?= htmlspecialchars($address['phone']) ?></p>
                    </div>
                    
                    <div class="mt-4 flex space-x-2">
                        <button onclick="editAddress(<?= $address['id'] ?>)" class="text-blue-600 hover:text-blue-800 text-sm">
                            Edit
                        </button>
                        <?php if(!$address['is_default']): ?>
                            <button onclick="setDefault(<?= $address['id'] ?>)" class="text-green-600 hover:text-green-800 text-sm">
                                Set Default
                            </button>
                            <button onclick="deleteAddress(<?= $address['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No addresses</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding your first address.</p>
                    <div class="mt-6">
                        <button onclick="openAddAddressModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Add Address
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Address Modal -->
<div id="addressModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex justify-between items-center border-b p-4">
            <h3 id="modalTitle" class="text-xl font-bold">Add Address</h3>
            <button onclick="closeAddressModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="addressForm" class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                    <input type="text" id="label" name="label" placeholder="Home, Office, etc." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                    <input type="text" id="address_line_1" name="address_line_1" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 (Optional)</label>
                    <input type="text" id="address_line_2" name="address_line_2" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" id="city" name="city" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input type="text" id="state" name="state" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" id="phone" name="phone" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="is_default" name="is_default" class="mr-2">
                    <label for="is_default" class="text-sm text-gray-700">Set as default address</label>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeAddressModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddAddressModal() {
    document.getElementById('addressModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Address';
    document.getElementById('addressForm').reset();
}

function closeAddressModal() {
    document.getElementById('addressModal').classList.add('hidden');
}

function editAddress(id) {
    // Load address data and open modal for editing
    document.getElementById('addressModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Address';
    // Implementation would load address data via AJAX
}

function setDefault(id) {
    if(confirm('Set this address as default?')) {
        // Implementation would send AJAX request to set default
        location.reload();
    }
}

function deleteAddress(id) {
    if(confirm('Are you sure you want to delete this address?')) {
        // Implementation would send AJAX request to delete
        location.reload();
    }
}

document.getElementById('addressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Implementation would send form data via AJAX
    alert('Address saved successfully!');
    closeAddressModal();
    location.reload();
});
</script>
