<?php
/**
 * Admin Routes Management View
 */
$pageTitle = $pageTitle ?? 'Routes Management';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRouteModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Create Route
                </button>
            </div>
        </div>
    </div>

    <!-- Routes Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Routes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $routes ? $routes->num_rows : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-route fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Routes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">42</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Distance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">15.2 mi</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-road fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Duration</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2.3 hrs</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Routes Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Route Management</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="#" onclick="exportRoutes()">Export Routes</a>
                    <a class="dropdown-item" href="#" onclick="importRoutes()">Import Routes</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="optimizeRoutes()">Optimize All Routes</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="routesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Route ID</th>
                            <th>Courier</th>
                            <th>Start Location</th>
                            <th>End Location</th>
                            <th>Distance</th>
                            <th>Duration</th>
                            <th>Shipments</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($routes && $routes->num_rows > 0): ?>
                            <?php while($route = $routes->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($route['id']) ?></td>
                                    <td><?= htmlspecialchars($route['courier_name'] ?? 'Unassigned') ?></td>
                                    <td><?= htmlspecialchars($route['start_location']) ?></td>
                                    <td><?= htmlspecialchars($route['end_location']) ?></td>
                                    <td><?= number_format($route['distance'], 1) ?> mi</td>
                                    <td><?= htmlspecialchars($route['estimated_duration']) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= $route['shipment_count'] ?> items</span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($route['status']) {
                                            'active' => 'success',
                                            'completed' => 'primary',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($route['status']) ?></span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($route['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewRoute(<?= $route['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="editRoute(<?= $route['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="optimizeRoute(<?= $route['id'] ?>)">
                                                <i class="fas fa-route"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRoute(<?= $route['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-route fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">No routes found. Create your first route to get started.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Route Modal -->
<div class="modal fade" id="createRouteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRouteForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Courier</label>
                                <select class="form-select" name="courier_id" required>
                                    <option value="">Select Courier</option>
                                    <!-- Populate with available couriers -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Route Date</label>
                                <input type="date" class="form-control" name="date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Location</label>
                                <input type="text" class="form-control" name="start_location" placeholder="Enter start address" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Location</label>
                                <input type="text" class="form-control" name="end_location" placeholder="Enter end address" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Route description or notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Route</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#routesTable').DataTable({
        "pageLength": 25,
        "order": [[ 8, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 9 }
        ]
    });
});

function viewRoute(routeId) {
    // Implementation for viewing route details
    console.log('Viewing route:', routeId);
}

function editRoute(routeId) {
    // Implementation for editing route
    console.log('Editing route:', routeId);
}

function optimizeRoute(routeId) {
    // Implementation for route optimization
    console.log('Optimizing route:', routeId);
}

function deleteRoute(routeId) {
    if(confirm('Are you sure you want to delete this route?')) {
        // Implementation for deleting route
        console.log('Deleting route:', routeId);
    }
}

function exportRoutes() {
    // Implementation for exporting routes
    console.log('Exporting routes');
}

function importRoutes() {
    // Implementation for importing routes
    console.log('Importing routes');
}

function optimizeRoutes() {
    // Implementation for optimizing all routes
    console.log('Optimizing all routes');
}
</script>
