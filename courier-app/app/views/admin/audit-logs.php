<?php
/**
 * Admin Audit Logs View
 */
$pageTitle = $pageTitle ?? 'Audit Logs';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="exportLogs()">
                        <i class="fas fa-download fa-sm text-white-50"></i> Export Logs
                    </button>
                    <button class="btn btn-warning" onclick="archiveLogs()">
                        <i class="fas fa-archive fa-sm text-white-50"></i> Archive Old Logs
                    </button>
                    <button class="btn btn-danger" onclick="clearLogs()">
                        <i class="fas fa-trash fa-sm text-white-50"></i> Clear Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Events</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['total_events']) ? number_format($stats['total_events']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Events</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['today_events']) ? number_format($stats['today_events']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Security Events</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['security_events']) ? number_format($stats['security_events']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed Attempts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['failed_attempts']) ? number_format($stats['failed_attempts']) : '0' ?>
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

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Audit Log Filters</h6>
        </div>
        <div class="card-body">
            <form id="auditFilters" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Event Type</label>
                    <select class="form-select" name="event_type">
                        <option value="">All Events</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="security">Security</option>
                        <option value="payment">Payment</option>
                        <option value="admin">Admin Action</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">User Role</label>
                    <select class="form-select" name="user_role">
                        <option value="">All Roles</option>
                        <option value="ADMIN">Admin</option>
                        <option value="USER">User</option>
                        <option value="COURIER">Courier</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Severity</label>
                    <select class="form-select" name="severity">
                        <option value="">All Levels</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
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

    <!-- Audit Logs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Audit Trail</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="#" onclick="exportLogs()">Export to CSV</a>
                    <a class="dropdown-item" href="#" onclick="generateAuditReport()">Generate Report</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="archiveLogs()">Archive Old Logs</a>
                    <a class="dropdown-item text-danger" href="#" onclick="clearLogs()">Clear All Logs</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="auditTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Event Type</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Resource</th>
                            <th>Action</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($auditLogs) && $auditLogs->num_rows > 0): ?>
                            <?php while($log = $auditLogs->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="text-nowrap">
                                            <?= date('M j, Y', strtotime($log['timestamp'])) ?><br>
                                            <small class="text-muted"><?= date('g:i:s A', strtotime($log['timestamp'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $typeIcon = match($log['event_type']) {
                                            'login' => 'fas fa-sign-in-alt text-success',
                                            'logout' => 'fas fa-sign-out-alt text-info',
                                            'create' => 'fas fa-plus text-success',
                                            'update' => 'fas fa-edit text-warning',
                                            'delete' => 'fas fa-trash text-danger',
                                            'security' => 'fas fa-shield-alt text-danger',
                                            'payment' => 'fas fa-credit-card text-primary',
                                            'admin' => 'fas fa-cog text-info',
                                            default => 'fas fa-info-circle text-secondary'
                                        };
                                        ?>
                                        <i class="<?= $typeIcon ?> mr-2"></i>
                                        <?= ucfirst($log['event_type']) ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($log['user_name'] ?? 'System') ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($log['user_email'] ?? 'N/A') ?></small>
                                            <?php if($log['user_role']): ?>
                                                <br><span class="badge badge-secondary badge-sm"><?= $log['user_role'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace"><?= htmlspecialchars($log['ip_address']) ?></span>
                                        <?php if($log['user_agent']): ?>
                                            <br><small class="text-muted" title="<?= htmlspecialchars($log['user_agent']) ?>">
                                                <?= substr($log['user_agent'], 0, 20) ?>...
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code class="small"><?= htmlspecialchars($log['resource']) ?></code>
                                        <?php if($log['resource_id']): ?>
                                            <br><small class="text-muted">ID: <?= $log['resource_id'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-light"><?= htmlspecialchars($log['action']) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $severityClass = match($log['severity']) {
                                            'low' => 'success',
                                            'medium' => 'warning',
                                            'high' => 'danger',
                                            'critical' => 'dark',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $severityClass ?>"><?= ucfirst($log['severity']) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($log['status']) {
                                            'success' => 'success',
                                            'failed' => 'danger',
                                            'error' => 'danger',
                                            'warning' => 'warning',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($log['status']) ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(<?= $log['id'] ?>)">
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">No audit logs found matching the current filters.</p>
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
        <nav aria-label="Audit log pagination">
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

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailsBody">
                <!-- Log details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#auditTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 8 }
        ]
    });
});

function viewLogDetails(logId) {
    // Load log details via AJAX
    $.get('/api/admin/audit-logs/' + logId, function(data) {
        $('#logDetailsBody').html(data);
        $('#logDetailsModal').modal('show');
    }).fail(function() {
        alert('Error loading log details');
    });
}

function exportLogs() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/audit-logs/export';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?>';
    form.appendChild(csrfInput);
    
    // Add current filters
    const formData = new FormData(document.getElementById('auditFilters'));
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

function generateAuditReport() {
    window.open('/admin/audit-logs/report', '_blank');
}

function archiveLogs() {
    const days = prompt('Archive logs older than how many days?', '90');
    if(days && !isNaN(days) && days > 0) {
        if(confirm(`This will archive audit logs older than ${days} days. Continue?`)) {
            $.post('/api/admin/audit-logs/archive', {
                days: days,
                _token: '<?= $_SESSION['csrf_token'] ?>'
            }, function(response) {
                if(response.success) {
                    alert(`${response.archived_count} logs archived successfully`);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            });
        }
    }
}

function clearLogs() {
    const confirmation = prompt('This will permanently delete ALL audit logs. Type "DELETE" to confirm:', '');
    if(confirmation === 'DELETE') {
        $.post('/api/admin/audit-logs/clear', {
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                alert('All audit logs have been cleared');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

// Apply filters on form submit
$('#auditFilters').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams();
    
    for(let [key, value] of formData.entries()) {
        if(value) {
            params.append(key, value);
        }
    }
    
    window.location.href = '/admin/audit-logs?' + params.toString();
});
</script>
