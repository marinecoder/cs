<?php

class ApiController {
    private $db;
    private $auth;
    private $emailService;

    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
        $this->emailService = new EmailService();
    }

    /**
     * Handle API requests
     */
    public function handleRequest(string $method, string $endpoint, array $data = []) {
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Handle CORS
        if ($method === 'OPTIONS') {
            $this->handleCORS();
            return;
        }

        // Rate limiting
        if (!$this->checkRateLimit()) {
            $this->jsonResponse(['error' => 'Rate limit exceeded'], 429);
            return;
        }

        try {
            switch ($endpoint) {
                // Authentication endpoints
                case 'auth/login':
                    $this->handleLogin($data);
                    break;
                case 'auth/register':
                    $this->handleRegister($data);
                    break;
                case 'auth/logout':
                    $this->handleLogout();
                    break;

                // Shipment endpoints
                case 'shipments':
                    if ($method === 'GET') $this->getShipments();
                    elseif ($method === 'POST') $this->createShipment($data);
                    break;
                case (preg_match('/shipments\/(\d+)/', $endpoint, $matches) ? true : false):
                    $shipmentId = $matches[1];
                    if ($method === 'GET') $this->getShipment($shipmentId);
                    elseif ($method === 'PUT') $this->updateShipment($shipmentId, $data);
                    elseif ($method === 'DELETE') $this->deleteShipment($shipmentId);
                    break;
                case (preg_match('/shipments\/(\d+)\/status/', $endpoint, $matches) ? true : false):
                    $shipmentId = $matches[1];
                    $this->updateShipmentStatus($shipmentId, $data);
                    break;

                // Tracking endpoints
                case (preg_match('/tracking\/(.+)/', $endpoint, $matches) ? true : false):
                    $trackingNumber = $matches[1];
                    $this->trackShipment($trackingNumber);
                    break;

                // User endpoints
                case 'users':
                    if ($method === 'GET') $this->getUsers();
                    elseif ($method === 'POST') $this->createUser($data);
                    break;
                case (preg_match('/users\/(\d+)/', $endpoint, $matches) ? true : false):
                    $userId = $matches[1];
                    if ($method === 'GET') $this->getUser($userId);
                    elseif ($method === 'PUT') $this->updateUser($userId, $data);
                    elseif ($method === 'DELETE') $this->deleteUser($userId);
                    break;

                // Reports endpoints
                case 'reports/data':
                    $this->getReportsData($data);
                    break;
                case 'reports/export':
                    $this->exportReport($data);
                    break;

                default:
                    $this->jsonResponse(['error' => 'Endpoint not found'], 404);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Internal server error'], 500);
            error_log($e->getMessage());
        }
    }

    /**
     * Handle user login
     */
    private function handleLogin(array $data) {
        if (!isset($data['email']) || !isset($data['password'])) {
            $this->jsonResponse(['error' => 'Email and password required'], 400);
            return;
        }

        $result = $this->auth->login($data['email'], $data['password']);
        
        if ($result['success']) {
            // Generate API key for mobile access
            $apiKey = $this->generateApiKey($result['user']['id']);
            $result['api_key'] = $apiKey;
        }

        $this->jsonResponse($result, $result['success'] ? 200 : 401);
    }

    /**
     * Handle user registration
     */
    private function handleRegister(array $data) {
        $required = ['name', 'email', 'password', 'phone'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->jsonResponse(['error' => "$field is required"], 400);
                return;
            }
        }

        $result = $this->auth->register($data);
        $this->jsonResponse($result, $result['success'] ? 201 : 400);
    }

    /**
     * Get shipments for authenticated user
     */
    private function getShipments() {
        if (!$this->requireAuth()) return;

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        if ($role === 'admin') {
            $sql = "SELECT s.*, u.name as customer_name FROM shipments s 
                    JOIN users u ON s.user_id = u.id 
                    ORDER BY s.created_at DESC";
            $shipments = $this->db->query($sql);
        } else {
            $sql = "SELECT * FROM shipments WHERE user_id = ? ORDER BY created_at DESC";
            $shipments = $this->db->query($sql, [$userId]);
        }

        $result = [];
        while ($shipment = $shipments->fetch_assoc()) {
            $result[] = $shipment;
        }

        $this->jsonResponse(['shipments' => $result]);
    }

    /**
     * Create new shipment
     */
    private function createShipment(array $data) {
        if (!$this->requireAuth()) return;

        $required = ['sender_name', 'sender_phone', 'sender_address', 'sender_city',
                    'receiver_name', 'receiver_phone', 'receiver_address', 'receiver_city',
                    'weight', 'service_type', 'amount'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->jsonResponse(['error' => "$field is required"], 400);
                return;
            }
        }

        // Generate tracking number
        $trackingNumber = 'CA' . time() . rand(1000, 9999);
        
        $sql = "INSERT INTO shipments (user_id, tracking_number, sender_name, sender_phone, 
                sender_address, sender_city, receiver_name, receiver_phone, receiver_address, 
                receiver_city, weight, service_type, amount, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_SESSION['user_id'], $trackingNumber, $data['sender_name'], $data['sender_phone'],
            $data['sender_address'], $data['sender_city'], $data['receiver_name'], 
            $data['receiver_phone'], $data['receiver_address'], $data['receiver_city'],
            $data['weight'], $data['service_type'], $data['amount'], $data['description'] ?? ''
        ];

        $result = $this->db->query($sql, $params);
        
        if ($result) {
            $shipmentId = $this->db->connection->insert_id;
            
            // Send confirmation email
            $this->emailService->sendShipmentStatusUpdate($shipmentId, 'confirmed');
            
            $this->jsonResponse([
                'success' => true,
                'shipment_id' => $shipmentId,
                'tracking_number' => $trackingNumber
            ], 201);
        } else {
            $this->jsonResponse(['error' => 'Failed to create shipment'], 500);
        }
    }

    /**
     * Track shipment by tracking number
     */
    private function trackShipment(string $trackingNumber) {
        $sql = "SELECT s.*, sp.status as current_status, sp.description, sp.location, sp.timestamp
                FROM shipments s 
                LEFT JOIN shipment_progress sp ON s.id = sp.shipment_id 
                WHERE s.tracking_number = ? 
                ORDER BY sp.timestamp DESC";
        
        $result = $this->db->query($sql, [$trackingNumber]);
        
        if ($result->num_rows === 0) {
            $this->jsonResponse(['error' => 'Shipment not found'], 404);
            return;
        }

        $shipment = null;
        $progress = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!$shipment) {
                $shipment = [
                    'id' => $row['id'],
                    'tracking_number' => $row['tracking_number'],
                    'sender_name' => $row['sender_name'],
                    'receiver_name' => $row['receiver_name'],
                    'sender_city' => $row['sender_city'],
                    'receiver_city' => $row['receiver_city'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at']
                ];
            }
            
            if ($row['current_status']) {
                $progress[] = [
                    'status' => $row['current_status'],
                    'description' => $row['description'],
                    'location' => $row['location'],
                    'timestamp' => $row['timestamp']
                ];
            }
        }

        $this->jsonResponse([
            'shipment' => $shipment,
            'progress' => $progress
        ]);
    }

    /**
     * Update shipment status (Admin only)
     */
    private function updateShipmentStatus(int $shipmentId, array $data) {
        if (!$this->requireAuth() || !$this->requireRole('admin')) return;

        if (!isset($data['status'])) {
            $this->jsonResponse(['error' => 'Status is required'], 400);
            return;
        }

        $sql = "UPDATE shipments SET status = ? WHERE id = ?";
        $result = $this->db->query($sql, [$data['status'], $shipmentId]);

        if ($result) {
            // Add to progress tracking
            $sql = "INSERT INTO shipment_progress (shipment_id, status, description, location) 
                    VALUES (?, ?, ?, ?)";
            $this->db->query($sql, [
                $shipmentId, 
                $data['status'], 
                $data['description'] ?? 'Status updated',
                $data['location'] ?? ''
            ]);

            // Send email notification
            $this->emailService->sendShipmentStatusUpdate($shipmentId, $data['status']);

            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['error' => 'Failed to update status'], 500);
        }
    }

    /**
     * Generate API key for user
     */
    private function generateApiKey(int $userId): string {
        $apiKey = bin2hex(random_bytes(32));
        
        $sql = "INSERT INTO api_keys (user_id, api_key, name) VALUES (?, ?, ?)";
        $this->db->query($sql, [$userId, $apiKey, 'Mobile App']);
        
        return $apiKey;
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(): bool {
        $identifier = $_SERVER['REMOTE_ADDR'];
        $windowStart = date('Y-m-d H:i:00'); // 1-minute window
        
        // Clean old entries
        $sql = "DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        $this->db->query($sql);
        
        // Check current requests
        $sql = "SELECT request_count FROM rate_limits WHERE identifier = ? AND window_start = ?";
        $result = $this->db->query($sql, [$identifier, $windowStart]);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['request_count'] >= 100) { // Rate limit
                return false;
            }
            
            // Increment counter
            $sql = "UPDATE rate_limits SET request_count = request_count + 1 WHERE identifier = ? AND window_start = ?";
            $this->db->query($sql, [$identifier, $windowStart]);
        } else {
            // First request in this window
            $sql = "INSERT INTO rate_limits (identifier, window_start) VALUES (?, ?)";
            $this->db->query($sql, [$identifier, $windowStart]);
        }
        
        return true;
    }

    /**
     * Require authentication
     */
    private function requireAuth(): bool {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Authentication required'], 401);
            return false;
        }
        return true;
    }

    /**
     * Require specific role
     */
    private function requireRole(string $role): bool {
        if ($_SESSION['role'] !== $role) {
            $this->jsonResponse(['error' => 'Insufficient permissions'], 403);
            return false;
        }
        return true;
    }

    /**
     * Handle CORS
     */
    private function handleCORS() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        http_response_code(200);
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
