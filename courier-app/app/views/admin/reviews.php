<?php
/**
 * Admin Reviews Management View
 */
$pageTitle = $pageTitle ?? 'Reviews Management';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="exportReviews()">
                        <i class="fas fa-download fa-sm text-white-50"></i> Export Reviews
                    </button>
                    <button class="btn btn-warning" onclick="moderateReviews()">
                        <i class="fas fa-shield-alt fa-sm text-white-50"></i> Moderate All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['total_reviews']) ? number_format($stats['total_reviews']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Average Rating</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['average_rating']) ? number_format($stats['average_rating'], 1) : '0.0' ?>
                                <span class="text-warning">
                                    <?php 
                                    $rating = $stats['average_rating'] ?? 0;
                                    for($i = 1; $i <= 5; $i++): 
                                        if($i <= $rating): 
                                    ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; endfor; ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Moderation</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['pending_reviews']) ? number_format($stats['pending_reviews']) : '0' ?>
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
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Flagged Reviews</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['flagged_reviews']) ? number_format($stats['flagged_reviews']) : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Distribution Chart -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rating Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="ratingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Review Trends</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between small mb-3">
                        <div class="text-xs font-weight-bold text-success">5 Stars</div>
                        <div class="small">60%</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%"></div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between small mb-3">
                        <div class="text-xs font-weight-bold text-info">4 Stars</div>
                        <div class="small">25%</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 25%"></div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between small mb-3">
                        <div class="text-xs font-weight-bold text-warning">3 Stars</div>
                        <div class="small">10%</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 10%"></div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between small mb-3">
                        <div class="text-xs font-weight-bold text-danger">2 Stars</div>
                        <div class="small">3%</div>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 3%"></div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between small mb-3">
                        <div class="text-xs font-weight-bold text-dark">1 Star</div>
                        <div class="small">2%</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-dark" role="progressbar" style="width: 2%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Customer Reviews</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <a class="dropdown-item" href="#" onclick="exportReviews()">Export Reviews</a>
                    <a class="dropdown-item" href="#" onclick="generateReviewReport()">Generate Report</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="bulkApprove()">Bulk Approve</a>
                    <a class="dropdown-item" href="#" onclick="bulkReject()">Bulk Reject</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-3" id="reviewTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                        All Reviews <span class="badge bg-secondary ms-1"><?= $stats['total_reviews'] ?? 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                        Pending <span class="badge bg-warning ms-1"><?= $stats['pending_reviews'] ?? 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="flagged-tab" data-bs-toggle="tab" data-bs-target="#flagged" type="button">
                        Flagged <span class="badge bg-danger ms-1"><?= $stats['flagged_reviews'] ?? 0 ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button">
                        Approved <span class="badge bg-success ms-1"><?= $stats['approved_reviews'] ?? 0 ?></span>
                    </button>
                </li>
            </ul>

            <div class="table-responsive">
                <table class="table table-bordered" id="reviewsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Review ID</th>
                            <th>Customer</th>
                            <th>Shipment</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($reviews) && $reviews->num_rows > 0): ?>
                            <?php while($review = $reviews->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" name="review_ids[]" value="<?= $review['id'] ?>"></td>
                                    <td><?= htmlspecialchars($review['id']) ?></td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($review['customer_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($review['customer_email']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="/admin/shipments?id=<?= $review['shipment_id'] ?>" class="text-decoration-none">
                                            #<?= htmlspecialchars($review['shipment_id']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="text-warning">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted"><?= $review['rating'] ?>/5</small>
                                    </td>
                                    <td>
                                        <div class="review-text" style="max-width: 200px;">
                                            <?= strlen($review['comment']) > 100 ? 
                                                htmlspecialchars(substr($review['comment'], 0, 100)) . '...' : 
                                                htmlspecialchars($review['comment']) ?>
                                        </div>
                                        <?php if(strlen($review['comment']) > 100): ?>
                                            <small><a href="#" onclick="showFullReview(<?= $review['id'] ?>)">Read more</a></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($review['status']) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            'flagged' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($review['status']) ?></span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($review['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewReview(<?= $review['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($review['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="approveReview(<?= $review['id'] ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejectReview(<?= $review['id'] ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="flagReview(<?= $review['id'] ?>)">
                                                <i class="fas fa-flag"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-star fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">No reviews found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Review Details Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <!-- Review details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="approveCurrentReview()">Approve</button>
                <button type="button" class="btn btn-danger" onclick="rejectCurrentReview()">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentReviewId = null;

$(document).ready(function() {
    $('#reviewsTable').DataTable({
        "pageLength": 25,
        "order": [[ 7, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 8] }
        ]
    });
    
    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('input[name="review_ids[]"]').prop('checked', $(this).prop('checked'));
    });
});

function viewReview(reviewId) {
    currentReviewId = reviewId;
    // Load review details via AJAX
    $.get('/api/admin/reviews/' + reviewId, function(data) {
        $('#reviewModalBody').html(data);
        $('#reviewModal').modal('show');
    });
}

function showFullReview(reviewId) {
    viewReview(reviewId);
}

function approveReview(reviewId) {
    $.post('/api/admin/reviews/' + reviewId + '/approve', {
        _token: '<?= $_SESSION['csrf_token'] ?>'
    }, function(response) {
        if(response.success) {
            location.reload();
        } else {
            alert('Error: ' + response.message);
        }
    });
}

function rejectReview(reviewId) {
    if(confirm('Are you sure you want to reject this review?')) {
        $.post('/api/admin/reviews/' + reviewId + '/reject', {
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

function flagReview(reviewId) {
    const reason = prompt('Please enter the reason for flagging this review:');
    if(reason) {
        $.post('/api/admin/reviews/' + reviewId + '/flag', {
            reason: reason,
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

function approveCurrentReview() {
    if(currentReviewId) {
        approveReview(currentReviewId);
        $('#reviewModal').modal('hide');
    }
}

function rejectCurrentReview() {
    if(currentReviewId && confirm('Are you sure you want to reject this review?')) {
        rejectReview(currentReviewId);
        $('#reviewModal').modal('hide');
    }
}

function bulkApprove() {
    const selectedReviews = $('input[name="review_ids[]"]:checked').map(function() {
        return $(this).val();
    }).get();
    
    if(selectedReviews.length === 0) {
        alert('Please select reviews to approve');
        return;
    }
    
    if(confirm('Are you sure you want to approve ' + selectedReviews.length + ' reviews?')) {
        $.post('/api/admin/reviews/bulk-approve', {
            review_ids: selectedReviews,
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

function bulkReject() {
    const selectedReviews = $('input[name="review_ids[]"]:checked').map(function() {
        return $(this).val();
    }).get();
    
    if(selectedReviews.length === 0) {
        alert('Please select reviews to reject');
        return;
    }
    
    if(confirm('Are you sure you want to reject ' + selectedReviews.length + ' reviews?')) {
        $.post('/api/admin/reviews/bulk-reject', {
            review_ids: selectedReviews,
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

function exportReviews() {
    window.location.href = '/admin/reviews/export';
}

function generateReviewReport() {
    window.open('/admin/reviews/report', '_blank');
}

function moderateReviews() {
    if(confirm('This will auto-moderate all pending reviews based on content analysis. Continue?')) {
        $.post('/api/admin/reviews/auto-moderate', {
            _token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(response) {
            if(response.success) {
                alert('Auto-moderation completed: ' + response.processed + ' reviews processed');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        });
    }
}
</script>
