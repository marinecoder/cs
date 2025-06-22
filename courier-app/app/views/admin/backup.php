<?php
/**
 * Admin Backup Management View
 */
$pageTitle = $pageTitle ?? 'System Backup';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                <button class="btn btn-primary" onclick="createBackup()">
                    <i class="fas fa-save fa-sm text-white-50"></i> Create Backup Now
                </button>
            </div>
        </div>
    </div>

    <!-- Backup Status Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Last Backup</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($lastBackup) ? date('M j, Y', strtotime($lastBackup['created_at'])) : 'Never' ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= isset($lastBackup) ? date('g:i A', strtotime($lastBackup['created_at'])) : '' ?>
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Backup Status</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($lastBackup) && $lastBackup['status'] === 'completed' ? 'Healthy' : 'Needs Attention' ?>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Storage Used</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($storageStats) ? $storageStats['used_gb'] : '0' ?> GB
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Auto Backup</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($autoBackupEnabled) && $autoBackupEnabled ? 'Enabled' : 'Disabled' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-robot fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Backup Configuration -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Backup Settings</h6>
                </div>
                <div class="card-body">
                    <form id="backupSettings">
                        <div class="mb-3">
                            <label class="form-label">Auto Backup</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoBackup" 
                                       <?= isset($autoBackupEnabled) && $autoBackupEnabled ? 'checked' : '' ?>>
                                <label class="form-check-label" for="autoBackup">
                                    Enable automatic backups
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Backup Frequency</label>
                            <select class="form-select" name="frequency">
                                <option value="daily">Daily</option>
                                <option value="weekly" selected>Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Retention Period</label>
                            <select class="form-select" name="retention">
                                <option value="7">7 days</option>
                                <option value="30" selected>30 days</option>
                                <option value="90">90 days</option>
                                <option value="365">1 year</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Backup Components</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="database" checked>
                                <label class="form-check-label" for="database">Database</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="files" checked>
                                <label class="form-check-label" for="files">Application Files</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="uploads">
                                <label class="form-check-label" for="uploads">User Uploads</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="config" checked>
                                <label class="form-check-label" for="config">Configuration</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="createBackup()">
                            <i class="fas fa-save mr-2"></i>Create Full Backup
                        </button>
                        <button class="btn btn-info" onclick="createDatabaseBackup()">
                            <i class="fas fa-database mr-2"></i>Database Only
                        </button>
                        <button class="btn btn-warning" onclick="testBackup()">
                            <i class="fas fa-vial mr-2"></i>Test Backup
                        </button>
                        <button class="btn btn-secondary" onclick="validateBackups()">
                            <i class="fas fa-check-circle mr-2"></i>Validate All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup History -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Backup History</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="#" onclick="cleanupOldBackups()">Cleanup Old Backups</a>
                            <a class="dropdown-item" href="#" onclick="exportBackupLog()">Export Log</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" onclick="uploadBackup()">Upload External Backup</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="backupTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset($backups) && $backups->num_rows > 0): ?>
                                    <?php while($backup = $backups->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?= date('M j, Y', strtotime($backup['created_at'])) ?></strong><br>
                                                    <small class="text-muted"><?= date('g:i:s A', strtotime($backup['created_at'])) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= ucfirst($backup['type']) ?></span>
                                                <?php if($backup['is_auto']): ?>
                                                    <br><small class="text-muted">Automatic</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($backup['file_size']): ?>
                                                    <?= number_format($backup['file_size'] / 1024 / 1024, 1) ?> MB
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($backup['duration']): ?>
                                                    <?= gmdate("H:i:s", $backup['duration']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = match($backup['status']) {
                                                    'completed' => 'success',
                                                    'running' => 'warning',
                                                    'failed' => 'danger',
                                                    'cancelled' => 'secondary',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($backup['status']) ?></span>
                                                <?php if($backup['status'] === 'running'): ?>
                                                    <div class="progress mt-1" style="height: 4px;">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                             style="width: <?= $backup['progress'] ?? 0 ?>%"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if($backup['status'] === 'completed'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="downloadBackup(<?= $backup['id'] ?>)">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="restoreBackup(<?= $backup['id'] ?>)">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="validateBackup(<?= $backup['id'] ?>)">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteBackup(<?= $backup['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-save fa-3x text-gray-300 mb-3"></i>
                                            <p class="text-gray-500">No backups found. Create your first backup to get started.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Backup Restore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Warning:</strong> This will restore the system to the selected backup point. 
                    All current data will be replaced. This action cannot be undone.
                </div>
                <p>Are you sure you want to restore from backup <strong id="restoreBackupId"></strong>?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmRestore">
                    <label class="form-check-label" for="confirmRestore">
                        I understand this will overwrite current data
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRestoreBtn" disabled>Restore Backup</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Backup Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload External Backup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadBackupForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Backup File</label>
                        <input type="file" class="form-control" name="backup_file" accept=".sql,.zip,.tar.gz" required>
                        <small class="form-text text-muted">Accepted formats: .sql, .zip, .tar.gz</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="External backup description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentRestoreId = null;

$(document).ready(function() {
    $('#backupTable').DataTable({
        "pageLength": 10,
        "order": [[ 0, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 5 }
        ]
    });
    
    // Confirm restore checkbox
    $('#confirmRestore').change(function() {
        $('#confirmRestoreBtn').prop('disabled', !$(this).prop('checked'));
    });
});

function createBackup() {
    if(confirm('Create a full system backup now? This may take several minutes.')) {
        $.post('/api/admin/backup/create', {
            type: 'full',
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                alert('Backup started successfully');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function createDatabaseBackup() {
    if(confirm('Create a database-only backup?')) {
        $.post('/api/admin/backup/create', {
            type: 'database',
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                alert('Database backup started');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function downloadBackup(backupId) {
    window.location.href = '/api/admin/backup/' + backupId + '/download';
}

function restoreBackup(backupId) {
    currentRestoreId = backupId;
    $('#restoreBackupId').text('#' + backupId);
    $('#confirmRestore').prop('checked', false);
    $('#confirmRestoreBtn').prop('disabled', true);
    $('#restoreModal').modal('show');
}

function validateBackup(backupId) {
    $.post('/api/admin/backup/' + backupId + '/validate', {
        _token: '<?= $_SESSION['csrf_token'] ?>'
    }, function(response) {
        if(response.success) {
            alert('Backup validation: ' + (response.valid ? 'PASSED' : 'FAILED'));
        } else {
            alert('Error: ' + response.message);
        }
    });
}

function deleteBackup(backupId) {
    if(confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
        $.ajax({
            url: '/api/admin/backup/' + backupId,
            type: 'DELETE',
            data: {
                _token: '<?= $_SESSION['csrf_token'] ?>'
            },
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}

function testBackup() {
    if(confirm('Run backup system test? This will verify backup capabilities without creating an actual backup.')) {
        $.post('/api/admin/backup/test', {
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            alert('Backup test: ' + (response.success ? 'PASSED' : 'FAILED') + '\n' + response.message);
        });
    }
}

function validateBackups() {
    if(confirm('Validate all existing backups? This may take some time.')) {
        $.post('/api/admin/backup/validate-all', {
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                alert(`Validation complete: ${response.valid_count} valid, ${response.invalid_count} invalid`);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}

function cleanupOldBackups() {
    const days = prompt('Delete backups older than how many days?', '90');
    if(days && !isNaN(days) && days > 0) {
        if(confirm(`Delete all backups older than ${days} days?`)) {
            $.post('/api/admin/backup/cleanup', {
                days: days,
                _token: '<?= $_SESSION['csrf_token'] ?>'
            }, function(response) {
                if(response.success) {
                    alert(`${response.deleted_count} old backups deleted`);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            });
        }
    }
}

function uploadBackup() {
    $('#uploadModal').modal('show');
}

function exportBackupLog() {
    window.location.href = '/admin/backup/export-log';
}

// Handle confirm restore
$('#confirmRestoreBtn').click(function() {
    if(currentRestoreId && $('#confirmRestore').prop('checked')) {
        $.post('/api/admin/backup/' + currentRestoreId + '/restore', {
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                alert('Restore initiated. The system will restart shortly.');
                $('#restoreModal').modal('hide');
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
});

// Handle backup settings form
$('#backupSettings').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '<?= $_SESSION['csrf_token'] ?>');
    
    $.post('/api/admin/backup/settings', formData, function(response) {
        if(response.success) {
            alert('Backup settings saved successfully');
        } else {
            alert('Error: ' + response.message);
        }
    });
});

// Handle upload backup form
$('#uploadBackupForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '<?= $_SESSION['csrf_token'] ?>');
    
    $.post('/api/admin/backup/upload', formData, function(response) {
        if(response.success) {
            alert('Backup uploaded successfully');
            $('#uploadModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + response.message);
        }
    });
});
</script>
