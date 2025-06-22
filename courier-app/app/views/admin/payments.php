<?php
/**
 * Admin Payments Management View
 */
$pageTitle = $pageTitle ?? 'Payments Management';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="exportPayments()">
                        <i class="fas fa-download fa-sm text-white-50"></i> Export
                    </button>
                    <button class="btn btn-success" onclick="processRefunds()">
                        <i class="fas fa-undo fa-sm text-white-50"></i> Process Refunds
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['total_payments']) ? number_format($stats['total_payments']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Revenue (This Month)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= isset($stats['monthly_revenue']) ? number_format($stats['monthly_revenue'], 2) : '0.00' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['pending_payments']) ? number_format($stats['pending_payments']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Failed Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['failed_payments']) ? number_format($stats['failed_payments']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Filters</h6>
        </div>
        <div class="card-body">
            <form id="paymentFilters" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" name="method">
                        <option value="">All Methods</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Payment Transactions</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="#" onclick="exportPayments()">Export to CSV</a>
                    <a class="dropdown-item" href="#" onclick="generateReport()">Generate Report</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="bulkRefund()">Bulk Refund</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="paymentsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Payment ID</th>
                            <th>Shipment</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($payments) && $payments->num_rows > 0): ?>
                            <?php while($payment = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" name="payment_ids[]" value="<?= $payment['id'] ?>"></td>
                                    <td><?= htmlspecialchars($payment['id']) ?></td>
                                    <td>
                                        <a href="/admin/shipments?id=<?= $payment['shipment_id'] ?>" class="text-decoration-none">
                                            #<?= htmlspecialchars($payment['shipment_id']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($payment['customer_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($payment['customer_email']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($payment['amount'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($payment['status']) {
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'refunded' => 'info',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($payment['status']) ?></span>
                                    </td>
                                    <td>
                                        <small class="font-monospace"><?= htmlspecialchars($payment['transaction_id']) ?></small>
                                    </td>
                                    <td><?= date('M j, Y g:i A', strtotime($payment['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewPayment(<?= $payment['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($payment['status'] === 'completed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="refundPayment(<?= $payment['id'] ?>)">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if($payment['status'] === 'failed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="retryPayment(<?= $payment['id'] ?>)">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-credit-card fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">No payments found matching the current filters.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if(isset($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Payment pagination">
            <ul class="pagination justify-content-center">
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentModalBody">
                <!-- Payment details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        "pageLength": 25,
        "order": [[ 8, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 9] }
        ]
    });
    
    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('input[name="payment_ids[]"]').prop('checked', $(this).prop('checked'));
    });
});

function viewPayment(paymentId) {
    // Load payment details via AJAX
    $.get('/api/admin/payments/' + paymentId, function(data) {
        $('#paymentModalBody').html(data);
        $('#paymentModal').modal('show');
    });
}

function refundPayment(paymentId) {
    if(confirm('Are you sure you want to refund this payment?')) {
        $.post('/api/admin/payments/' + paymentId + '/refund', {
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function retryPayment(paymentId) {
    if(confirm('Retry this failed payment?')) {
        $.post('/api/admin/payments/' + paymentId + '/retry', {
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function exportPayments() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/payments/export';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?>';
    form.appendChild(csrfInput);
    
    // Add current filters
    const formData = new FormData(document.getElementById('paymentFilters'));
    for(let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function generateReport() {
    // Implementation for generating payment reports
    console.log('Generating payment report');
}

function bulkRefund() {
    const selectedPayments = $('input[name="payment_ids[]"]:checked').map(function() {
        return $(this).val();
    }).get();
    
    if(selectedPayments.length === 0) {
        alert('Please select payments to refund');
        return;
    }
    
    if(confirm('Are you sure you want to refund ' + selectedPayments.length + ' payments?')) {
        $.post('/api/admin/payments/bulk-refund', {
            payment_ids: selectedPayments,
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}
</script>
