<?php

class Auth {
    const ROLES = [
        'USER' => [
            'shipment_create',
            'shipment_view_own',
            'tracking_view',
            'payment_process',
            'profile_edit',
            'documents_upload',
            'reviews_create'
        ],
        'COURIER' => [
            'shipment_view_assigned',
            'shipment_update_status',
            'route_view',
            'tracking_update',
            'documents_upload',
            'profile_edit'
        ],
        'ADMIN' => [
            // User management
            'user_manage',
            'user_create',
            'user_edit',
            'user_delete',
            'user_view_all',
            
            // Shipment management
            'shipment_view_all',
            'shipment_create',
            'shipment_edit',
            'shipment_delete',
            'shipment_assign',
            
            // Financial
            'financial_reports',
            'payment_view_all',
            'payment_process',
            'payment_refund',
            
            // System
            'system_config',
            'system_backup',
            'audit_logs',
            'api_tokens',
            
            // Courier management
            'courier_manage',
            'route_manage',
            'route_create',
            
            // Analytics
            'analytics_view',
            'reports_generate',
            
            // Settings
            'settings_manage',
            'company_settings',
            'email_templates'
        ]
    ];
    
    public static function startSession(): void {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function login(string $email, string $password): array {
        $db = Database::getInstance();
        
        $result = $db->query(
            "SELECT id, email, password, role, name, status FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );
        
        if($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        $user = $result->fetch_assoc();
        
        if(!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        self::startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['login_time'] = time();
        
        // Log login
        self::logActivity($user['id'], 'login', 'users', $user['id']);
        
        return ['success' => true, 'user' => $user];
    }
    
    public static function logout(): void {
        self::startSession();
        
        if(isset($_SESSION['user_id'])) {
            self::logActivity($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
        }
        
        session_destroy();
    }
    
    public static function isLoggedIn(): bool {
        self::startSession();
        return isset($_SESSION['user_id']);
    }
    
    public static function getCurrentUser(): ?array {
        self::startSession();
        
        if(!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
            'name' => $_SESSION['name']
        ];
    }
    
    public static function checkPermission(string $permission): bool {
        self::startSession();
        
        if(!self::isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['role'] ?? 'GUEST';
        
        return in_array($permission, self::ROLES[$role] ?? []);
    }
    
    public static function requirePermission(string $permission): void {
        if(!self::checkPermission($permission)) {
            http_response_code(403);
            die('Access denied. Required permission: ' . $permission);
        }
    }
    
    public static function requireLogin(): void {
        if(!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }
    
    public static function getSidebarLinks(): array {
        self::startSession();
        
        $role = $_SESSION['role'] ?? 'GUEST';
        
        return match($role) {
            'ADMIN' => [
                ['title' => 'Dashboard', 'icon' => 'chart-bar', 'url' => '/admin/dashboard'],
                ['title' => 'Users', 'icon' => 'users', 'url' => '/admin/users'],
                ['title' => 'Shipments', 'icon' => 'truck', 'url' => '/admin/shipments'],
                ['title' => 'Couriers', 'icon' => 'user-group', 'url' => '/admin/couriers'],
                ['title' => 'Analytics', 'icon' => 'chart-pie', 'url' => '/admin/analytics'],
                ['title' => 'Financials', 'icon' => 'credit-card', 'url' => '/admin/financials'],
                ['title' => 'Reports', 'icon' => 'document-report', 'url' => '/admin/reports'],
                ['title' => 'Notifications', 'icon' => 'bell', 'url' => '/admin/notifications'],
                ['title' => 'Email Management', 'icon' => 'mail', 'url' => '/admin/emails'],
                ['title' => 'API Keys', 'icon' => 'key', 'url' => '/admin/api-keys'],
                ['title' => 'Rate Limits', 'icon' => 'shield-check', 'url' => '/admin/rate-limits'],
                ['title' => 'Security', 'icon' => 'shield', 'url' => '/admin/security'],
                ['title' => 'System Logs', 'icon' => 'clipboard-list', 'url' => '/admin/logs'],
                ['title' => 'Backups', 'icon' => 'database', 'url' => '/admin/backups'],
                ['title' => 'Maintenance', 'icon' => 'cog', 'url' => '/admin/maintenance'],
                ['title' => 'Settings', 'icon' => 'adjustments', 'url' => '/admin/settings']
            ],
            'COURIER' => [
                ['title' => 'Dashboard', 'icon' => 'home', 'url' => '/courier/dashboard'],
                ['title' => 'My Routes', 'icon' => 'map', 'url' => '/courier/routes'],
                ['title' => 'Assigned Shipments', 'icon' => 'truck', 'url' => '/courier/shipments'],
                ['title' => 'Delivery History', 'icon' => 'clock', 'url' => '/courier/history'],
                ['title' => 'Profile', 'icon' => 'user', 'url' => '/courier/profile']
            ],
            'USER' => [
                ['title' => 'Dashboard', 'icon' => 'home', 'url' => '/dashboard'],
                ['title' => 'New Shipment', 'icon' => 'plus', 'url' => '/shipment/create'],
                ['title' => 'My Shipments', 'icon' => 'package', 'url' => '/shipments'],
                ['title' => 'Tracking', 'icon' => 'search', 'url' => '/tracking'],
                ['title' => 'Payment History', 'icon' => 'credit-card', 'url' => '/payments'],
                ['title' => 'Address Book', 'icon' => 'location-marker', 'url' => '/addresses'],
                ['title' => 'Profile', 'icon' => 'user', 'url' => '/profile'],
                ['title' => 'Support', 'icon' => 'support', 'url' => '/support']
            ],
            default => []
        };
    }
    
    public static function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }
    
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    public static function logActivity(int $userId, string $action, string $table = null, int $recordId = null, array $oldValues = null, array $newValues = null): void {
        $db = Database::getInstance();
        
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        try {
            $db->insert('audit_logs', $data);
        } catch(Exception $e) {
            // Log error but don't break the application
            error_log('Failed to log activity: ' . $e->getMessage());
        }
    }
    
    public static function createUser(array $userData): int {
        $db = Database::getInstance();
        
        // Hash password if provided
        if(isset($userData['password'])) {
            $userData['password'] = self::hashPassword($userData['password']);
        }
        
        $userId = $db->insert('users', $userData);
        
        self::logActivity($_SESSION['user_id'] ?? 0, 'create_user', 'users', $userId, null, $userData);
        
        return $userId;
    }
    
    public static function updateUser(int $userId, array $userData): bool {
        $db = Database::getInstance();
        
        // Get old values for audit
        $oldUser = $db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch_assoc();
        
        // Hash password if provided
        if(isset($userData['password'])) {
            $userData['password'] = self::hashPassword($userData['password']);
        }
        
        $result = $db->update('users', $userData, 'id = ?', [$userId]);
        
        if($result) {
            self::logActivity($_SESSION['user_id'] ?? 0, 'update_user', 'users', $userId, $oldUser, $userData);
        }
        
        return $result;
    }
    
    public static function deleteUser(int $userId): bool {
        $db = Database::getInstance();
        
        // Get user data for audit
        $user = $db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch_assoc();
        
        $result = $db->update('users', ['status' => 'inactive'], 'id = ?', [$userId]);
        
        if($result) {
            self::logActivity($_SESSION['user_id'] ?? 0, 'delete_user', 'users', $userId, $user);
        }
        
        return $result;
    }
}
