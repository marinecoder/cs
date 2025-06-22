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
            <h1 class="text-3xl font-bold text-gray-900">Support Center</h1>
            <button onclick="openTicketModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Ticket
            </button>
        </div>

        <!-- Quick Help Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-blue-900 mb-4">Quick Help</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-2">Track Shipment</h3>
                    <p class="text-sm text-gray-600 mb-3">Find your package location and delivery status</p>
                    <a href="/tracking" class="text-blue-600 hover:text-blue-800 text-sm">Go to Tracking →</a>
                </div>
                <div class="bg-white p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-2">Create Shipment</h3>
                    <p class="text-sm text-gray-600 mb-3">Send a new package to anywhere</p>
                    <a href="/shipment/create" class="text-blue-600 hover:text-blue-800 text-sm">Create Shipment →</a>
                </div>
                <div class="bg-white p-4 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-2">Payment Issues</h3>
                    <p class="text-sm text-gray-600 mb-3">View payment history and resolve issues</p>
                    <a href="/payments" class="text-blue-600 hover:text-blue-800 text-sm">View Payments →</a>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-4">
                    <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(1)">
                        <span class="font-medium text-gray-900">How long does delivery take?</span>
                        <svg id="faq-icon-1" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-1" class="mt-3 text-gray-600 text-sm hidden">
                        Standard delivery takes 2-3 business days, Express delivery is 1-2 business days, and Overnight delivery is guaranteed next business day.
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(2)">
                        <span class="font-medium text-gray-900">How can I track my shipment?</span>
                        <svg id="faq-icon-2" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-2" class="mt-3 text-gray-600 text-sm hidden">
                        You can track your shipment using the tracking number provided in your confirmation email. Visit the tracking page and enter your tracking number.
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <button class="flex justify-between items-center w-full text-left" onclick="toggleFAQ(3)">
                        <span class="font-medium text-gray-900">What payment methods do you accept?</span>
                        <svg id="faq-icon-3" class="w-5 h-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="faq-content-3" class="mt-3 text-gray-600 text-sm hidden">
                        We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers.
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Tickets -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Your Support Tickets</h2>
            </div>
            
            <div class="p-6">
                <?php if ($tickets && $tickets->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while($ticket = $tickets->fetch_assoc()): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">
                                        <?= htmlspecialchars($ticket['subject']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Ticket #<?= $ticket['id'] ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Created: <?= date('M d, Y h:i A', strtotime($ticket['created_at'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php
                                        switch($ticket['status']) {
                                            case 'open':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'in_progress':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'closed':
                                                echo 'bg-gray-100 text-gray-800';
                                                break;
                                            default:
                                                echo 'bg-blue-100 text-blue-800';
                                        }
                                        ?>">
                                        <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                                    </span>
                                    <div class="mt-2">
                                        <button onclick="viewTicket(<?= $ticket['id'] ?>)" class="text-blue-600 hover:text-blue-800 text-sm">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.444l-3.846 1.154L6.4 16.863A8.001 8.001 0 0121 12z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No support tickets</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't created any support tickets yet.</p>
                        <div class="mt-6">
                            <button onclick="openTicketModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                Create First Ticket
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- New Ticket Modal -->
<div id="ticketModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
        <div class="flex justify-between items-center border-b p-4">
            <h3 class="text-xl font-bold">Create Support Ticket</h3>
            <button onclick="closeTicketModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="ticketForm" class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" id="subject" name="subject" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category" name="category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Category</option>
                        <option value="delivery">Delivery Issue</option>
                        <option value="payment">Payment Problem</option>
                        <option value="tracking">Tracking Issue</option>
                        <option value="damage">Damaged Package</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select id="priority" name="priority" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number (if applicable)</label>
                    <input type="text" id="tracking_number" name="tracking_number"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Please describe your issue in detail..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeTicketModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Create Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTicketModal() {
    document.getElementById('ticketModal').classList.remove('hidden');
    document.getElementById('ticketForm').reset();
}

function closeTicketModal() {
    document.getElementById('ticketModal').classList.add('hidden');
}

function toggleFAQ(id) {
    const content = document.getElementById(`faq-content-${id}`);
    const icon = document.getElementById(`faq-icon-${id}`);
    
    content.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

function viewTicket(id) {
    // Implementation would show ticket details in a modal or navigate to ticket page
    alert('Ticket details for ticket #' + id);
}

document.getElementById('ticketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Implementation would send form data via AJAX
    alert('Support ticket created successfully!');
    closeTicketModal();
    location.reload();
});
</script>
