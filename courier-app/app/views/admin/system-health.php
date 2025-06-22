<?php
/**
 * Admin System Health Monitor View
 */
$pageTitle = $pageTitle ?? 'System Health';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="refreshHealth()">
                        <i class="fas fa-sync fa-sm text-white-50"></i> Refresh
                    </button>
                    <button class="btn btn-warning" onclick="runDiagnostics()">
                        <i class="fas fa-stethoscope fa-sm text-white-50"></i> Run Diagnostics
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-right">
                                <div class="mb-2">
                                    <i class="fas fa-server fa-3x <?= isset($systemHealth['overall']) && $systemHealth['overall'] === 'healthy' ? 'text-success' : 'text-danger' ?>"></i>
                                </div>
                                <h5 class="font-weight-bold">Overall Status</h5>
                                <p class="text-muted mb-0">
                                    <?= isset($systemHealth['overall']) ? ucfirst($systemHealth['overall']) : 'Unknown' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-right">
                                <div class="mb-2">
                                    <i class="fas fa-database fa-3x <?= isset($systemHealth['database']) && $systemHealth['database'] === 'connected' ? 'text-success' : 'text-danger' ?>"></i>
                                </div>
                                <h5 class="font-weight-bold">Database</h5>
                                <p class="text-muted mb-0">
                                    <?= isset($systemHealth['database']) ? ucfirst($systemHealth['database']) : 'Unknown' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-right">
                                <div class="mb-2">
                                    <i class="fas fa-memory fa-3x <?= isset($systemHealth['memory_usage']) && $systemHealth['memory_usage'] < 80 ? 'text-success' : 'text-warning' ?>"></i>
                                </div>
                                <h5 class="font-weight-bold">Memory</h5>
                                <p class="text-muted mb-0">
                                    <?= isset($systemHealth['memory_usage']) ? $systemHealth['memory_usage'] . '%' : 'N/A' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <i class="fas fa-hdd fa-3x <?= isset($systemHealth['disk_usage']) && $systemHealth['disk_usage'] < 80 ? 'text-success' : 'text-warning' ?>"></i>
                            </div>
                            <h5 class="font-weight-bold">Storage</h5>
                            <p class="text-muted mb-0">
                                <?= isset($systemHealth['disk_usage']) ? $systemHealth['disk_usage'] . '%' : 'N/A' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Health Metrics -->
    <div class="row">
        <!-- Server Resources -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Server Resources</h6>
                </div>
                <div class="card-body">
                    <!-- CPU Usage -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>CPU Usage</span>
                            <span><?= isset($metrics['cpu_usage']) ? $metrics['cpu_usage'] . '%' : 'N/A' ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= isset($metrics['cpu_usage']) && $metrics['cpu_usage'] > 80 ? 'bg-danger' : 'bg-success' ?>" 
                                 style="width: <?= $metrics['cpu_usage'] ?? 0 ?>%"></div>
                        </div>
                    </div>

                    <!-- Memory Usage -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Memory Usage</span>
                            <span><?= isset($metrics['memory_used']) ? $metrics['memory_used'] . ' / ' . $metrics['memory_total'] : 'N/A' ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= isset($metrics['memory_percent']) && $metrics['memory_percent'] > 80 ? 'bg-warning' : 'bg-info' ?>" 
                                 style="width: <?= $metrics['memory_percent'] ?? 0 ?>%"></div>
                        </div>
                    </div>

                    <!-- Disk Usage -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Disk Usage</span>
                            <span><?= isset($metrics['disk_used']) ? $metrics['disk_used'] . ' / ' . $metrics['disk_total'] : 'N/A' ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= isset($metrics['disk_percent']) && $metrics['disk_percent'] > 90 ? 'bg-danger' : 'bg-success' ?>" 
                                 style="width: <?= $metrics['disk_percent'] ?? 0 ?>%"></div>
                        </div>
                    </div>

                    <!-- Load Average -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Load Average</span>
                            <span><?= isset($metrics['load_average']) ? $metrics['load_average'] : 'N/A' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Health -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Application Health</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td>Database Connection</td>
                                    <td class="text-right">
                                        <span class="badge badge-<?= isset($appHealth['database']) && $appHealth['database'] ? 'success' : 'danger' ?>">
                                            <?= isset($appHealth['database']) && $appHealth['database'] ? 'Connected' : 'Failed' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Email Service</td>
                                    <td class="text-right">
                                        <span class="badge badge-<?= isset($appHealth['email']) && $appHealth['email'] ? 'success' : 'warning' ?>">
                                            <?= isset($appHealth['email']) && $appHealth['email'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>File System</td>
                                    <td class="text-right">
                                        <span class="badge badge-<?= isset($appHealth['filesystem']) && $appHealth['filesystem'] ? 'success' : 'danger' ?>">
                                            <?= isset($appHealth['filesystem']) && $appHealth['filesystem'] ? 'Writable' : 'Error' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Session Storage</td>
                                    <td class="text-right">
                                        <span class="badge badge-<?= isset($appHealth['sessions']) && $appHealth['sessions'] ? 'success' : 'warning' ?>">
                                            <?= isset($appHealth['sessions']) && $appHealth['sessions'] ? 'Working' : 'Issues' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Cron Jobs</td>
                                    <td class="text-right">
                                        <span class="badge badge-<?= isset($appHealth['cron']) && $appHealth['cron'] ? 'success' : 'warning' ?>">
                                            <?= isset($appHealth['cron']) && $appHealth['cron'] ? 'Running' : 'Stopped' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>API Endpoints</td>
                                    <td class="text-right">
                                        <span class="badge badge-<?= isset($appHealth['api']) && $appHealth['api'] ? 'success' : 'danger' ?>">
                                            <?= isset($appHealth['api']) && $appHealth['api'] ? 'Responsive' : 'Error' ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics (Last 24 Hours)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="small text-muted">Average Response Time</div>
                            <div class="h4 font-weight-bold text-primary">
                                <?= isset($stats['avg_response_time']) ? $stats['avg_response_time'] . 'ms' : 'N/A' ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="small text-muted">Active Users</div>
                            <div class="h4 font-weight-bold text-success">
                                <?= isset($stats['active_users']) ? number_format($stats['active_users']) : '0' ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="small text-muted">Requests/Hour</div>
                            <div class="h4 font-weight-bold text-info">
                                <?= isset($stats['requests_per_hour']) ? number_format($stats['requests_per_hour']) : '0' ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="small text-muted">Error Rate</div>
                            <div class="h4 font-weight-bold <?= isset($stats['error_rate']) && $stats['error_rate'] > 5 ? 'text-danger' : 'text-success' ?>">
                                <?= isset($stats['error_rate']) ? $stats['error_rate'] . '%' : '0%' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Alerts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Alerts</h6>
                </div>
                <div class="card-body">
                    <?php if(isset($alerts) && !empty($alerts)): ?>
                        <?php foreach($alerts as $alert): ?>
                            <div class="alert alert-<?= $alert['level'] ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-<?= $alert['icon'] ?> mr-2"></i>
                                <strong><?= htmlspecialchars($alert['title']) ?></strong><br>
                                <?= htmlspecialchars($alert['message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">No active alerts</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td><strong>Server OS:</strong></td>
                                        <td><?= isset($sysinfo['os']) ? $sysinfo['os'] : 'Unknown' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>PHP Version:</strong></td>
                                        <td><?= phpversion() ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Web Server:</strong></td>
                                        <td><?= isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Database:</strong></td>
                                        <td><?= isset($sysinfo['db_version']) ? $sysinfo['db_version'] : 'Unknown' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td><strong>Application Version:</strong></td>
                                        <td><?= isset($sysinfo['app_version']) ? $sysinfo['app_version'] : '1.0.0' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uptime:</strong></td>
                                        <td><?= isset($sysinfo['uptime']) ? $sysinfo['uptime'] : 'Unknown' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Backup:</strong></td>
                                        <td><?= isset($sysinfo['last_backup']) ? date('M j, Y g:i A', strtotime($sysinfo['last_backup'])) : 'Never' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Timezone:</strong></td>
                                        <td><?= date_default_timezone_get() ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Diagnostics Modal -->
<div class="modal fade" id="diagnosticsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">System Diagnostics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="diagnosticsBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Running diagnostics...</span>
                    </div>
                    <p class="mt-3">Running system diagnostics...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function refreshHealth() {
    location.reload();
}

function runDiagnostics() {
    $('#diagnosticsModal').modal('show');
    
    $.post('/api/admin/system-health/diagnostics', {
        _token: '<?= $_SESSION['csrf_token'] ?>'
    }, function(response) {
        $('#diagnosticsBody').html(response.html);
    }).fail(function() {
        $('#diagnosticsBody').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Failed to run diagnostics. Please try again.
            </div>
        `);
    });
}

// Auto-refresh every 30 seconds
setInterval(function() {
    $.get('/api/admin/system-health/status', function(data) {
        // Update status indicators without full page reload
        updateHealthIndicators(data);
    });
}, 30000);

function updateHealthIndicators(data) {
    // Update overall status
    const overallIcon = $('.fa-server');
    overallIcon.removeClass('text-success text-danger text-warning');
    overallIcon.addClass(data.overall === 'healthy' ? 'text-success' : 'text-danger');
    
    // Update other indicators
    if(data.metrics) {
        // Update progress bars
        $('.progress-bar').each(function() {
            const metric = $(this).data('metric');
            if(data.metrics[metric]) {
                $(this).css('width', data.metrics[metric] + '%');
            }
        });
    }
}

// Performance Chart
$(document).ready(function() {
    const ctx = document.getElementById('performanceChart');
    if(ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels ?? []) ?>,
                datasets: [{
                    label: 'Response Time (ms)',
                    data: <?= json_encode($chartData ?? []) ?>,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
