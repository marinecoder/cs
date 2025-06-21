<?php
$pageTitle = 'Create Shipment';
$currentPage = 'create-shipment';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Shipment</h1>
                    <p class="text-sm text-gray-600 mt-1">Fill in the details below to create a new shipment</p>
                </div>
                <a href="/user/shipments" class="text-gray-600 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="p-6">
        <div class="max-w-4xl mx-auto">
            <form id="shipmentForm" class="space-y-8">
                <!-- Step 1: Sender Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-medium">
                            1
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 ml-3">Sender Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="sender_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" name="sender_phone" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                            <textarea name="sender_address" rows="3" required 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                            <input type="text" name="sender_city" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ZIP Code *</label>
                            <input type="text" name="sender_zip" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Receiver Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-medium">
                            2
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 ml-3">Receiver Information</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="receiver_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" name="receiver_phone" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                            <textarea name="receiver_address" rows="3" required 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                            <input type="text" name="receiver_city" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ZIP Code *</label>
                            <input type="text" name="receiver_zip" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Package Details -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-medium">
                            3
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 ml-3">Package Details</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg) *</label>
                            <input type="number" name="weight" step="0.1" min="0.1" required 
                                   onchange="calculatePrice()"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service Type *</label>
                            <select name="service_type" required onchange="calculatePrice()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Service</option>
                                <option value="standard" data-rate="5.00">Standard (3-5 days) - $5.00/kg</option>
                                <option value="express" data-rate="8.00">Express (1-2 days) - $8.00/kg</option>
                                <option value="overnight" data-rate="15.00">Overnight - $15.00/kg</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Cost</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                <span id="estimatedCost" class="text-lg font-semibold text-green-600">$0.00</span>
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Package Description</label>
                            <textarea name="description" rows="3" placeholder="Describe the contents of your package..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Additional Options -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-medium">
                            4
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 ml-3">Additional Options</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="insurance" value="1" id="insurance" 
                                   onchange="calculatePrice()" class="rounded">
                            <label for="insurance" class="ml-2 text-sm text-gray-700">
                                Add Insurance (+$2.00) - Covers up to $100
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="signature_required" value="1" id="signature" 
                                   onchange="calculatePrice()" class="rounded">
                            <label for="signature" class="ml-2 text-sm text-gray-700">
                                Signature Required (+$1.50)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fragile" value="1" id="fragile" class="rounded">
                            <label for="fragile" class="ml-2 text-sm text-gray-700">
                                Handle as Fragile (Free)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Summary</h2>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span>Base shipping cost:</span>
                            <span id="baseCost">$0.00</span>
                        </div>
                        <div class="flex justify-between" id="insuranceRow" style="display: none;">
                            <span>Insurance:</span>
                            <span>$2.00</span>
                        </div>
                        <div class="flex justify-between" id="signatureRow" style="display: none;">
                            <span>Signature required:</span>
                            <span>$1.50</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between font-semibold text-lg">
                            <span>Total:</span>
                            <span id="totalCost" class="text-blue-600">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between">
                    <a href="/user/shipments" 
                       class="px-6 py-3 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                        Cancel
                    </a>
                    <div class="space-x-3">
                        <button type="button" onclick="saveDraft()" 
                                class="px-6 py-3 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                            Save as Draft
                        </button>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                            Create Shipment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Shipment Created!</h3>
                <p class="text-gray-600 mb-4">Your shipment has been successfully created.</p>
                <p class="text-sm text-gray-500 mb-6">
                    Tracking Number: <span id="trackingNumber" class="font-mono font-medium"></span>
                </p>
                <div class="flex space-x-3">
                    <button onclick="viewShipment()" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        View Shipment
                    </button>
                    <button onclick="createAnother()" 
                            class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg">
                        Create Another
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let createdShipmentId = null;

function calculatePrice() {
    const weight = parseFloat(document.querySelector('[name="weight"]').value) || 0;
    const serviceSelect = document.querySelector('[name="service_type"]');
    const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
    const rate = parseFloat(selectedOption.dataset.rate) || 0;
    
    const insurance = document.querySelector('[name="insurance"]').checked;
    const signature = document.querySelector('[name="signature_required"]').checked;
    
    let baseCost = weight * rate;
    let totalCost = baseCost;
    
    if (insurance) {
        totalCost += 2.00;
        document.getElementById('insuranceRow').style.display = 'flex';
    } else {
        document.getElementById('insuranceRow').style.display = 'none';
    }
    
    if (signature) {
        totalCost += 1.50;
        document.getElementById('signatureRow').style.display = 'flex';
    } else {
        document.getElementById('signatureRow').style.display = 'none';
    }
    
    document.getElementById('baseCost').textContent = '$' + baseCost.toFixed(2);
    document.getElementById('estimatedCost').textContent = '$' + totalCost.toFixed(2);
    document.getElementById('totalCost').textContent = '$' + totalCost.toFixed(2);
}

function saveDraft() {
    const formData = new FormData(document.getElementById('shipmentForm'));
    const data = Object.fromEntries(formData);
    data.status = 'draft';
    
    fetch('/api/shipments', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Draft saved successfully!');
            window.location.href = '/user/shipments';
        } else {
            alert('Failed to save draft: ' + (data.message || 'Unknown error'));
        }
    });
}

document.getElementById('shipmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Calculate final amount
    const weight = parseFloat(data.weight) || 0;
    const serviceSelect = document.querySelector('[name="service_type"]');
    const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
    const rate = parseFloat(selectedOption.dataset.rate) || 0;
    
    let amount = weight * rate;
    if (formData.has('insurance')) amount += 2.00;
    if (formData.has('signature_required')) amount += 1.50;
    
    data.amount = amount.toFixed(2);
    data.insurance = formData.has('insurance') ? 1 : 0;
    data.signature_required = formData.has('signature_required') ? 1 : 0;
    data.fragile = formData.has('fragile') ? 1 : 0;
    
    // Show loading state
    const submitBtn = this.querySelector('[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creating...';
    submitBtn.disabled = true;
    
    fetch('/api/shipments', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            createdShipmentId = data.shipment_id;
            document.getElementById('trackingNumber').textContent = data.tracking_number;
            document.getElementById('successModal').classList.remove('hidden');
        } else {
            alert('Failed to create shipment: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the shipment.');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

function viewShipment() {
    window.location.href = '/user/shipments';
}

function createAnother() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('shipmentForm').reset();
    calculatePrice();
}

// Auto-fill user's information if available
document.addEventListener('DOMContentLoaded', function() {
    // You can pre-fill sender information from user profile
    fetch('/api/user/profile')
        .then(response => response.json())
        .then(data => {
            if (data.name) document.querySelector('[name="sender_name"]').value = data.name;
            if (data.phone) document.querySelector('[name="sender_phone"]').value = data.phone;
            if (data.address) document.querySelector('[name="sender_address"]').value = data.address;
            if (data.city) document.querySelector('[name="sender_city"]').value = data.city;
            if (data.zip) document.querySelector('[name="sender_zip"]').value = data.zip;
        })
        .catch(() => {
            // Ignore errors, user can fill manually
        });
});

// Real-time validation
document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
    field.addEventListener('blur', function() {
        if (!this.value.trim()) {
            this.classList.add('border-red-300');
        } else {
            this.classList.remove('border-red-300');
        }
    });
});
</script>
