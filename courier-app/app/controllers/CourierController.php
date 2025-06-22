<?php

class CourierController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function dashboard() {
        $courierId = $_SESSION['user_id'];
        
        // Get courier statistics
        $stats = [
            'assigned_shipments' => $this->db->query(
                "SELECT COUNT(*) as count FROM shipments WHERE assigned_courier_id = ? AND status NOT IN ('delivered', 'cancelled')",
                [$courierId]
            )->fetch_assoc()['count'],
            
            'delivered_today' => $this->db->query(
                "SELECT COUNT(*) as count FROM shipments WHERE assigned_courier_id = ? AND status = 'delivered' AND DATE(actual_delivery_date) = CURDATE()",
                [$courierId]
            )->fetch_assoc()['count'],
            
            'total_delivered' => $this->db->query(
                "SELECT COUNT(*) as count FROM shipments WHERE assigned_courier_id = ? AND status = 'delivered'",
                [$courierId]
            )->fetch_assoc()['count'],
            
            'earnings_today' => $this->db->query(
                "SELECT SUM(s.total_amount * 0.1) as earnings FROM shipments s WHERE s.assigned_courier_id = ? AND s.status = 'delivered' AND DATE(s.actual_delivery_date) = CURDATE()",
                [$courierId]
            )->fetch_assoc()['earnings'] ?? 0
        ];
        
        // Get today's shipments
        $todayShipments = $this->db->query(
            "SELECT s.*, st.name as shipment_type_name 
             FROM shipments s 
             JOIN shipment_types st ON s.shipment_type_id = st.id 
             WHERE s.assigned_courier_id = ? 
             AND s.status NOT IN ('delivered', 'cancelled')
             ORDER BY s.created_at DESC 
             LIMIT 10",
            [$courierId]
        );
        
        Router::renderWithLayout('courier/dashboard', [
            'title' => 'Courier Dashboard',
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'todayShipments' => $todayShipments
        ]);
    }
    
    public function routes() {
        $courierId = $_SESSION['user_id'];
        
        $routes = $this->db->query(
            "SELECT cr.*, COUNT(rs.shipment_id) as shipment_count
             FROM courier_routes cr
             LEFT JOIN route_shipments rs ON cr.id = rs.route_id
             WHERE cr.courier_id = ?
             GROUP BY cr.id
             ORDER BY cr.date DESC",
            [$courierId]
        );
        
        Router::renderWithLayout('courier/routes', [
            'title' => 'My Routes',
            'pageTitle' => 'My Routes',
            'routes' => $routes
        ]);
    }
    
    public function shipments() {
        $courierId = $_SESSION['user_id'];
        $status = $_GET['status'] ?? '';
        
        $whereClause = "WHERE s.assigned_courier_id = ?";
        $params = [$courierId];
        
        if($status) {
            $whereClause .= " AND s.status = ?";
            $params[] = $status;
        }
        
        $shipments = $this->db->query(
            "SELECT s.*, u.name as user_name, u.email as user_email, st.name as shipment_type_name
             FROM shipments s
             JOIN users u ON s.user_id = u.id
             JOIN shipment_types st ON s.shipment_type_id = st.id
             $whereClause
             ORDER BY s.created_at DESC",
            $params
        );
        
        Router::renderWithLayout('courier/shipments', [
            'title' => 'Assigned Shipments',
            'pageTitle' => 'Assigned Shipments',
            'shipments' => $shipments,
            'selectedStatus' => $status
        ]);
    }
    
    public function history() {
        $courierId = $_SESSION['user_id'];
        
        $deliveredShipments = $this->db->query(
            "SELECT s.*, u.name as user_name, u.email as user_email, st.name as shipment_type_name
             FROM shipments s
             JOIN users u ON s.user_id = u.id
             JOIN shipment_types st ON s.shipment_type_id = st.id
             WHERE s.assigned_courier_id = ? AND s.status = 'delivered'
             ORDER BY s.actual_delivery_date DESC",
            [$courierId]
        );
        
        Router::renderWithLayout('courier/history', [
            'title' => 'Delivery History',
            'pageTitle' => 'Delivery History',
            'deliveredShipments' => $deliveredShipments
        ]);
    }
    
    public function profile() {
        $courierId = $_SESSION['user_id'];
        
        // Handle profile update
        if($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
            if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = "Invalid request token";
            } else {
                $name = $_POST['name'];
                $phone = $_POST['phone'];
                $address = $_POST['address'];
                
                $updateResult = $this->db->query(
                    "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?",
                    [$name, $phone, $address, $courierId]
                );
                
                if($updateResult) {
                    $_SESSION['name'] = $name;
                    $success = "Profile updated successfully!";
                } else {
                    $error = "Failed to update profile";
                }
            }
        }
        
        $courier = $this->db->query("SELECT * FROM users WHERE id = ?", [$courierId])->fetch_assoc();
        
        Router::renderWithLayout('courier/profile', [
            'title' => 'Courier Profile',
            'pageTitle' => 'My Profile',
            'courier' => $courier,
            'success' => $success ?? null,
            'error' => $error ?? null
        ]);
    }
    
    public function updateShipmentStatus() {
        $shipmentId = $_POST['shipment_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $courierId = $_SESSION['user_id'];
        
        // Verify courier is assigned to this shipment
        $shipment = $this->db->query(
            "SELECT * FROM shipments WHERE id = ? AND assigned_courier_id = ?",
            [$shipmentId, $courierId]
        )->fetch_assoc();
        
        if(!$shipment) {
            Router::jsonResponse(['success' => false, 'message' => 'Shipment not found'], 404);
            return;
        }
        
        // Update shipment status
        $updateResult = $this->db->query(
            "UPDATE shipments SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $shipmentId]
        );
        
        if($updateResult) {
            // Add progress tracking
            $this->db->query(
                "INSERT INTO shipment_progress (shipment_id, status, description, updated_by, timestamp) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$shipmentId, $status, $notes ?: "Status updated by courier", $courierId]
            );
            
            // If delivered, set delivery date
            if($status === 'delivered') {
                $this->db->query(
                    "UPDATE shipments SET actual_delivery_date = NOW() WHERE id = ?",
                    [$shipmentId]
                );
            }
            
            Router::jsonResponse(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            Router::jsonResponse(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }
}
