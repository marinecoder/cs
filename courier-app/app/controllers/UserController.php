<?php

class UserController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function dashboard() {
        $user = Auth::getCurrentUser();
        
        // Get user statistics
        $stats = [
            'total_shipments' => $this->db->query(
                "SELECT COUNT(*) as count FROM shipments WHERE user_id = ?",
                [$user['id']]
            )->fetch_assoc()['count'],
            
            'pending_payments' => $this->db->query(
                "SELECT COUNT(*) as count FROM payments WHERE user_id = ? AND status = 'pending'",
                [$user['id']]
            )->fetch_assoc()['count'],
            
            'in_transit' => $this->db->query(
                "SELECT COUNT(*) as count FROM shipments WHERE user_id = ? AND status = 'in_transit'",
                [$user['id']]
            )->fetch_assoc()['count'],
            
            'delivered' => $this->db->query(
                "SELECT COUNT(*) as count FROM shipments WHERE user_id = ? AND status = 'delivered'",
                [$user['id']]
            )->fetch_assoc()['count']
        ];
        
        // Get recent shipments
        $recentShipments = $this->db->query(
            "SELECT s.*, st.name as shipment_type_name
             FROM shipments s
             JOIN shipment_types st ON s.shipment_type_id = st.id
             WHERE s.user_id = ?
             ORDER BY s.created_at DESC
             LIMIT 5",
            [$user['id']]
        );
        
        Router::renderWithLayout('user/dashboard', [
            'title' => 'Dashboard',
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'recentShipments' => $recentShipments
        ]);
    }
    
    public function createShipment() {
        // Get shipment types
        $shipmentTypes = $this->db->query("SELECT * FROM shipment_types WHERE status = 'active' ORDER BY name");
        
        Router::renderWithLayout('user/create-shipment', [
            'title' => 'Create Shipment',
            'pageTitle' => 'New Shipment',
            'shipmentTypes' => $shipmentTypes,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'New Shipment']
            ]
        ]);
    }
    
    public function storeShipment() {
        if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            Router::redirect('/shipment/create?error=Invalid request');
        }
        
        $user = Auth::getCurrentUser();
        
        try {
            $this->db->beginTransaction();
            
            // Generate tracking number
            $trackingNumber = 'CD' . strtoupper(uniqid());
            
            // Calculate total amount (simplified)
            $shipmentType = $this->db->query(
                "SELECT * FROM shipment_types WHERE id = ?",
                [$_POST['shipment_type_id']]
            )->fetch_assoc();
            
            $totalAmount = $shipmentType['base_price'];
            
            // Create shipment
            $shipmentData = [
                'tracking_number' => $trackingNumber,
                'user_id' => $user['id'],
                'shipment_type_id' => $_POST['shipment_type_id'],
                'sender_name' => $_POST['sender_name'],
                'sender_phone' => $_POST['sender_phone'],
                'sender_email' => $_POST['sender_email'] ?? '',
                'sender_address' => $_POST['sender_address'],
                'sender_city' => $_POST['sender_city'],
                'sender_postal_code' => $_POST['sender_postal_code'] ?? '',
                'receiver_name' => $_POST['receiver_name'],
                'receiver_phone' => $_POST['receiver_phone'],
                'receiver_email' => $_POST['receiver_email'] ?? '',
                'receiver_address' => $_POST['receiver_address'],
                'receiver_city' => $_POST['receiver_city'],
                'receiver_postal_code' => $_POST['receiver_postal_code'] ?? '',
                'description' => $_POST['description'] ?? '',
                'weight' => $_POST['weight'] ?? 0,
                'dimensions' => $_POST['dimensions'] ?? '',
                'value' => $_POST['value'] ?? 0,
                'insurance_required' => isset($_POST['insurance_required']) ? 1 : 0,
                'fragile' => isset($_POST['fragile']) ? 1 : 0,
                'urgent' => isset($_POST['urgent']) ? 1 : 0,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ];
            
            $shipmentId = $this->db->insert('shipments', $shipmentData);
            
            // Create initial progress entry
            $this->db->insert('shipment_progress', [
                'shipment_id' => $shipmentId,
                'status' => 'Processing',
                'description' => 'Shipment created and awaiting confirmation',
                'location' => 'Origin',
                'updated_by' => $user['id']
            ]);
            
            // Create payment record
            $this->db->insert('payments', [
                'shipment_id' => $shipmentId,
                'user_id' => $user['id'],
                'amount' => $totalAmount,
                'payment_method' => 'pending',
                'status' => 'pending'
            ]);
            
            $this->db->commit();
            
            // Send confirmation email (simplified)
            $this->sendShipmentConfirmationEmail($user['email'], $trackingNumber);
            
            Router::redirect('/shipments?success=Shipment created successfully. Tracking: ' . $trackingNumber);
            
        } catch(Exception $e) {
            $this->db->rollback();
            Router::redirect('/shipment/create?error=' . urlencode($e->getMessage()));
        }
    }
    
    public function listShipments() {
        $user = Auth::getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $whereClause = 'WHERE s.user_id = ?';
        $params = [$user['id']];
        
        if($status) {
            $whereClause .= ' AND s.status = ?';
            $params[] = $status;
        }
        
        $params[] = $limit;
        $params[] = $offset;
        
        $shipments = $this->db->query(
            "SELECT s.*, st.name as shipment_type_name
             FROM shipments s
             JOIN shipment_types st ON s.shipment_type_id = st.id
             $whereClause
             ORDER BY s.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        );
        
        // Get total count
        $countParams = array_slice($params, 0, -2);
        $totalShipments = $this->db->query(
            "SELECT COUNT(*) as count FROM shipments s $whereClause",
            $countParams
        )->fetch_assoc()['count'];
        
        $totalPages = ceil($totalShipments / $limit);
        
        Router::renderWithLayout('user/shipments', [
            'title' => 'My Shipments',
            'pageTitle' => 'My Shipments',
            'shipments' => $shipments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'selectedStatus' => $status,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'My Shipments']
            ]
        ]);
    }
    
    public function tracking() {
        $trackingNumber = $_GET['tracking'] ?? '';
        $shipment = null;
        $progress = null;
        
        if($trackingNumber) {
            // Get shipment details
            $shipmentResult = $this->db->query(
                "SELECT s.*, st.name as shipment_type_name, u.name as user_name
                 FROM shipments s
                 JOIN shipment_types st ON s.shipment_type_id = st.id
                 JOIN users u ON s.user_id = u.id
                 WHERE s.tracking_number = ?",
                [$trackingNumber]
            );
            
            if($shipmentResult->num_rows > 0) {
                $shipment = $shipmentResult->fetch_assoc();
                
                // Get progress
                $progress = $this->db->query(
                    "SELECT * FROM shipment_progress 
                     WHERE shipment_id = ? 
                     ORDER BY timestamp DESC",
                    [$shipment['id']]
                );
            }
        }
        
        Router::renderWithLayout('user/tracking', [
            'title' => 'Track Shipment',
            'pageTitle' => 'Tracking',
            'trackingNumber' => $trackingNumber,
            'shipment' => $shipment,
            'progress' => $progress,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Tracking']
            ]
        ]);
    }
    
    public function trackingModal() {
        $user = Auth::getCurrentUser();
        $shipmentId = $_GET['id'] ?? 0;
        
        // Verify shipment belongs to user (or is admin)
        $whereClause = "s.id = ?";
        $params = [$shipmentId];
        
        if($user['role'] !== 'ADMIN') {
            $whereClause .= " AND s.user_id = ?";
            $params[] = $user['id'];
        }
        
        $shipment = $this->db->query(
            "SELECT s.*, st.name as shipment_type_name
             FROM shipments s
             JOIN shipment_types st ON s.shipment_type_id = st.id
             WHERE $whereClause",
            $params
        )->fetch_assoc();
        
        if(!$shipment) {
            http_response_code(404);
            die('Shipment not found');
        }
        
        // Get progress
        $progress = $this->db->query(
            "SELECT * FROM shipment_progress 
             WHERE shipment_id = ? 
             ORDER BY timestamp DESC",
            [$shipmentId]
        );
        
        // Render modal template
        include __DIR__ . '/../views/user/tracking-modal.php';
    }
    
    public function payments() {
        $user = Auth::getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $payments = $this->db->query(
            "SELECT p.*, s.tracking_number, s.receiver_name
             FROM payments p
             JOIN shipments s ON p.shipment_id = s.id
             WHERE p.user_id = ?
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$user['id'], $limit, $offset]
        );
        
        $totalPayments = $this->db->query(
            "SELECT COUNT(*) as count FROM payments WHERE user_id = ?",
            [$user['id']]
        )->fetch_assoc()['count'];
        
        $totalPages = ceil($totalPayments / $limit);
        
        Router::renderWithLayout('user/payments', [
            'title' => 'Payment History',
            'pageTitle' => 'Payments',
            'payments' => $payments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Payments']
            ]
        ]);
    }
    
    public function profile() {
        $user = Auth::getCurrentUser();
        
        // Get full user details
        $userDetails = $this->db->query(
            "SELECT * FROM users WHERE id = ?",
            [$user['id']]
        )->fetch_assoc();
        
        Router::renderWithLayout('user/profile', [
            'title' => 'Profile',
            'pageTitle' => 'My Profile',
            'user' => $userDetails,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Profile']
            ]
        ]);
    }
    
    public function apiNotifications() {
        if(!Router::isAjax()) {
            http_response_code(400);
            Router::jsonResponse(['error' => 'AJAX request required']);
        }
        
        $user = Auth::getCurrentUser();
        
        $notifications = $this->db->query(
            "SELECT * FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 10",
            [$user['id']]
        );
        
        $data = [];
        while($notification = $notifications->fetch_assoc()) {
            $data[] = $notification;
        }
        
        Router::jsonResponse(['notifications' => $data]);
    }
    
    // Helper methods
    private function sendShipmentConfirmationEmail(string $email, string $trackingNumber): void {
        $subject = "Shipment Confirmation - " . $trackingNumber;
        $body = "
        <h1>Shipment Confirmed</h1>
        <p>Your shipment has been created successfully.</p>
        <p><strong>Tracking Number:</strong> {$trackingNumber}</p>
        <p>You can track your shipment at: " . APP_URL . "/tracking?tracking={$trackingNumber}</p>
        <p>Thank you for choosing our service!</p>
        ";
        
        // Add to email queue
        $this->db->insert('email_queue', [
            'to_email' => $email,
            'subject' => $subject,
            'body' => $body,
            'headers' => 'From: ' . ADMIN_EMAIL . "\r\nContent-Type: text/html; charset=UTF-8"
        ]);
    }
}
