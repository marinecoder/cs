<?php

class AdminController {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function dashboard() {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        $recentShipments = $this->getRecentShipments(10);
        $recentUsers = $this->getRecentUsers(5);
        
        Router::renderWithLayout('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'recentShipments' => $recentShipments,
            'recentUsers' => $recentUsers
        ]);
    }
    
    public function users() {
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $users = $this->db->query(
            "SELECT id, email, name, role, status, created_at 
             FROM users 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
        
        $totalUsers = $this->db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        $totalPages = ceil($totalUsers / $limit);
        
        Router::renderWithLayout('admin/users', [
            'title' => 'User Management',
            'pageTitle' => 'Users',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers
        ]);
    }
    
    public function shipments() {
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [$limit, $offset];
        
        if($status) {
            $whereClause = 'WHERE s.status = ?';
            array_unshift($params, $status);
        }
        
        $shipments = $this->db->query(
            "SELECT s.*, u.name as user_name, u.email as user_email,
                    st.name as shipment_type_name
             FROM shipments s
             JOIN users u ON s.user_id = u.id
             JOIN shipment_types st ON s.shipment_type_id = st.id
             $whereClause
             ORDER BY s.created_at DESC 
             LIMIT ? OFFSET ?",
            $params
        );
        
        $totalShipments = $this->db->query(
            "SELECT COUNT(*) as count FROM shipments s $whereClause",
            $status ? [$status] : []
        )->fetch_assoc()['count'];
        
        $totalPages = ceil($totalShipments / $limit);
        
        // Get shipment statuses for filter
        $statuses = $this->db->query("SELECT DISTINCT status FROM shipments ORDER BY status");
        
        Router::renderWithLayout('admin/shipments', [
            'title' => 'Shipment Management',
            'pageTitle' => 'Shipments',
            'shipments' => $shipments,
            'statuses' => $statuses,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalShipments' => $totalShipments,
            'selectedStatus' => $status
        ]);
    }
    
    public function couriers() {
        $couriers = $this->db->query(
            "SELECT u.*, 
                    COUNT(s.id) as total_shipments,
                    COUNT(CASE WHEN s.status = 'delivered' THEN 1 END) as delivered_shipments
             FROM users u
             LEFT JOIN shipments s ON u.id = s.assigned_courier_id
             WHERE u.role = 'COURIER'
             GROUP BY u.id
             ORDER BY u.name"
        );
        
        Router::renderWithLayout('admin/couriers', [
            'title' => 'Courier Management',
            'pageTitle' => 'Couriers',
            'couriers' => $couriers
        ]);
    }
    
    public function routes() {
        $routes = $this->db->query(
            "SELECT cr.*, u.name as courier_name,
                    COUNT(rs.shipment_id) as total_shipments
             FROM courier_routes cr
             JOIN users u ON cr.courier_id = u.id
             LEFT JOIN route_shipments rs ON cr.id = rs.route_id
             GROUP BY cr.id
             ORDER BY cr.date DESC"
        );
        
        Router::renderWithLayout('admin/routes', [
            'title' => 'Route Management',
            'pageTitle' => 'Routes',
            'routes' => $routes
        ]);
    }
    
    public function payments() {
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [$limit, $offset];
        
        if($status) {
            $whereClause = 'WHERE p.status = ?';
            array_unshift($params, $status);
        }
        
        $payments = $this->db->query(
            "SELECT p.*, s.tracking_number, u.name as user_name, u.email as user_email
             FROM payments p
             JOIN shipments s ON p.shipment_id = s.id
             JOIN users u ON p.user_id = u.id
             $whereClause
             ORDER BY p.created_at DESC 
             LIMIT ? OFFSET ?",
            $params
        );
        
        $totalPayments = $this->db->query(
            "SELECT COUNT(*) as count FROM payments p $whereClause",
            $status ? [$status] : []
        )->fetch_assoc()['count'];
        
        $totalPages = ceil($totalPayments / $limit);
        
        Router::renderWithLayout('admin/payments', [
            'title' => 'Payment Management',
            'pageTitle' => 'Payments',
            'payments' => $payments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPayments' => $totalPayments,
            'selectedStatus' => $status
        ]);
    }
    
    public function reports() {
        $reportType = $_GET['type'] ?? 'revenue';
        $period = $_GET['period'] ?? '30days';
        
        $reportData = $this->generateReport($reportType, $period);
        
        Router::renderWithLayout('admin/reports', [
            'title' => 'Reports & Analytics',
            'pageTitle' => 'Reports',
            'reportType' => $reportType,
            'period' => $period,
            'reportData' => $reportData
        ]);
    }
    
    public function analytics() {
        $analytics = [
            'shipmentsByStatus' => $this->getShipmentsByStatus(),
            'revenueByMonth' => $this->getRevenueByMonth(),
            'topCouriers' => $this->getTopCouriers(),
            'customerSatisfaction' => $this->getCustomerSatisfaction()
        ];
        
        Router::renderWithLayout('admin/analytics', [
            'title' => 'Analytics Dashboard',
            'pageTitle' => 'Analytics',
            'analytics' => $analytics
        ]);
    }
    
    public function reviews() {
        $reviews = $this->db->query(
            "SELECT r.*, s.tracking_number, u.name as user_name, 
                    c.name as courier_name
             FROM reviews r
             JOIN shipments s ON r.shipment_id = s.id
             JOIN users u ON r.user_id = u.id
             LEFT JOIN users c ON r.courier_id = c.id
             ORDER BY r.created_at DESC"
        );
        
        Router::renderWithLayout('admin/reviews', [
            'title' => 'Customer Reviews',
            'pageTitle' => 'Reviews',
            'reviews' => $reviews
        ]);
    }
    
    public function notifications() {
        $notifications = $this->db->query(
            "SELECT n.*, u.name as user_name
             FROM notifications n
             JOIN users u ON n.user_id = u.id
             ORDER BY n.created_at DESC
             LIMIT 100"
        );
        
        Router::renderWithLayout('admin/notifications', [
            'title' => 'System Notifications',
            'pageTitle' => 'Notifications',
            'notifications' => $notifications
        ]);
    }
    
    public function settings() {
        $settings = $this->db->query("SELECT * FROM settings ORDER BY setting_key");
        
        Router::renderWithLayout('admin/settings', [
            'title' => 'System Settings',
            'pageTitle' => 'Settings',
            'settings' => $settings
        ]);
    }
    
    public function apiTokens() {
        $tokens = $this->db->query(
            "SELECT at.*, u.name as user_name
             FROM api_tokens at
             JOIN users u ON at.user_id = u.id
             ORDER BY at.created_at DESC"
        );
        
        Router::renderWithLayout('admin/api-tokens', [
            'title' => 'API Token Management',
            'pageTitle' => 'API Tokens',
            'tokens' => $tokens
        ]);
    }
    
    public function auditLogs() {
        $page = $_GET['page'] ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $logs = $this->db->query(
            "SELECT al.*, u.name as user_name
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
        
        $totalLogs = $this->db->query("SELECT COUNT(*) as count FROM audit_logs")->fetch_assoc()['count'];
        $totalPages = ceil($totalLogs / $limit);
        
        Router::renderWithLayout('admin/audit-logs', [
            'title' => 'Audit Logs',
            'pageTitle' => 'Audit Logs',
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }
    
    public function backup() {
        Router::renderWithLayout('admin/backup', [
            'title' => 'System Backup',
            'pageTitle' => 'Backup & Maintenance'
        ]);
    }
    
    public function systemHealth() {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'disk_space' => $this->checkDiskSpace(),
            'queue_status' => $this->checkQueueStatus(),
            'cache_status' => $this->checkCacheStatus()
        ];
        
        Router::renderWithLayout('admin/system-health', [
            'title' => 'System Health',
            'pageTitle' => 'System Health',
            'health' => $health
        ]);
    }
    
    public function financials() {
        $monthlyRevenue = $this->getMonthlyRevenue();
        $expenseData = $this->getExpenseData();
        $profitMargins = $this->getProfitMargins();
        $paymentMethods = $this->getPaymentMethodStats();
        
        Router::renderWithLayout('admin/financials', [
            'title' => 'Financial Reports',
            'pageTitle' => 'Financials',
            'monthlyRevenue' => $monthlyRevenue,
            'expenseData' => $expenseData,
            'profitMargins' => $profitMargins,
            'paymentMethods' => $paymentMethods
        ]);
    }
    
    public function logs() {
        $page = $_GET['page'] ?? 1;
        $level = $_GET['level'] ?? '';
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [$limit, $offset];
        
        if($level) {
            $whereClause = 'WHERE level = ?';
            array_unshift($params, $level);
        }
        
        $logs = $this->db->query(
            "SELECT * FROM system_logs 
             $whereClause
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            $params
        );
        
        $totalLogs = $this->db->query(
            "SELECT COUNT(*) as count FROM system_logs $whereClause",
            $level ? [$level] : []
        )->fetch_assoc()['count'];
        
        $totalPages = ceil($totalLogs / $limit);
        
        Router::renderWithLayout('admin/logs', [
            'title' => 'System Logs',
            'pageTitle' => 'System Logs',
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'selectedLevel' => $level
        ]);
    }
    
    public function backups() {
        $backups = $this->getBackupList();
        $systemInfo = $this->getSystemInfo();
        
        Router::renderWithLayout('admin/backups', [
            'title' => 'Database Backups',
            'pageTitle' => 'Database Backups',
            'backups' => $backups,
            'systemInfo' => $systemInfo
        ]);
    }
    
    public function apiKeys() {
        $apiKeys = $this->db->query(
            "SELECT ak.*, u.name as user_name
             FROM api_keys ak
             LEFT JOIN users u ON ak.user_id = u.id
             ORDER BY ak.created_at DESC"
        );
        
        Router::renderWithLayout('admin/api-keys', [
            'title' => 'API Key Management',
            'pageTitle' => 'API Keys',
            'apiKeys' => $apiKeys
        ]);
    }
    
    public function emails() {
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $limit = 25;
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [$limit, $offset];
        
        if($status) {
            $whereClause = 'WHERE status = ?';
            array_unshift($params, $status);
        }
        
        $emails = $this->db->query(
            "SELECT * FROM email_queue 
             $whereClause
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            $params
        );
        
        $totalEmails = $this->db->query(
            "SELECT COUNT(*) as count FROM email_queue $whereClause",
            $status ? [$status] : []
        )->fetch_assoc()['count'];
        
        $totalPages = ceil($totalEmails / $limit);
        
        Router::renderWithLayout('admin/emails', [
            'title' => 'Email Management',
            'pageTitle' => 'Email Queue',
            'emails' => $emails,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'selectedStatus' => $status
        ]);
    }
    
    public function security() {
        $securityLogs = $this->getSecurityLogs();
        $loginAttempts = $this->getFailedLoginAttempts();
        $blockedIPs = $this->getBlockedIPs();
        $securitySettings = $this->getSecuritySettings();
        
        Router::renderWithLayout('admin/security', [
            'title' => 'Security Center',
            'pageTitle' => 'Security & Access Control',
            'securityLogs' => $securityLogs,
            'loginAttempts' => $loginAttempts,
            'blockedIPs' => $blockedIPs,
            'securitySettings' => $securitySettings
        ]);
    }
    
    public function rateLimits() {
        $rateLimits = $this->getRateLimitSettings();
        $currentLimits = $this->getCurrentRateLimits();
        
        Router::renderWithLayout('admin/rate-limits', [
            'title' => 'Rate Limiting',
            'pageTitle' => 'API Rate Limits',
            'rateLimits' => $rateLimits,
            'currentLimits' => $currentLimits
        ]);
    }
    
    public function maintenance() {
        $maintenanceMode = $this->getMaintenanceMode();
        $scheduledTasks = $this->getScheduledTasks();
        $systemStatus = $this->getSystemStatus();
        
        Router::renderWithLayout('admin/maintenance', [
            'title' => 'System Maintenance',
            'pageTitle' => 'Maintenance Mode',
            'maintenanceMode' => $maintenanceMode,
            'scheduledTasks' => $scheduledTasks,
            'systemStatus' => $systemStatus
        ]);
    }

    // Helper methods
    private function getDashboardStats(): array {
        $stats = [];
        
        // Total users
        $stats['total_users'] = $this->db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'];
        
        // Total shipments
        $stats['total_shipments'] = $this->db->query("SELECT COUNT(*) as count FROM shipments")->fetch_assoc()['count'];
        
        // Revenue this month
        $stats['monthly_revenue'] = $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) as revenue 
             FROM payments 
             WHERE status = 'completed' 
             AND MONTH(created_at) = MONTH(CURRENT_DATE())"
        )->fetch_assoc()['revenue'];
        
        // Pending shipments
        $stats['pending_shipments'] = $this->db->query(
            "SELECT COUNT(*) as count FROM shipments WHERE status IN ('pending', 'confirmed')"
        )->fetch_assoc()['count'];
        
        return $stats;
    }
    
    private function getRecentShipments(int $limit): mysqli_result {
        return $this->db->query(
            "SELECT s.*, u.name as user_name
             FROM shipments s
             JOIN users u ON s.user_id = u.id
             ORDER BY s.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    private function getRecentUsers(int $limit): mysqli_result {
        return $this->db->query(
            "SELECT * FROM users 
             WHERE status = 'active'
             ORDER BY created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    private function generateReport(string $type, string $period): array {
        // Implement report generation logic
        return [];
    }
    
    private function getShipmentsByStatus(): array {
        $result = $this->db->query(
            "SELECT status, COUNT(*) as count 
             FROM shipments 
             GROUP BY status"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getRevenueByMonth(): array {
        $result = $this->db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(amount) as revenue
             FROM payments
             WHERE status = 'completed'
             AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getTopCouriers(): array {
        $result = $this->db->query(
            "SELECT u.name, COUNT(s.id) as deliveries,
                    AVG(r.rating) as avg_rating
             FROM users u
             LEFT JOIN shipments s ON u.id = s.assigned_courier_id
             LEFT JOIN reviews r ON u.id = r.courier_id
             WHERE u.role = 'COURIER'
             GROUP BY u.id
             ORDER BY deliveries DESC
             LIMIT 10"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getCustomerSatisfaction(): array {
        $result = $this->db->query(
            "SELECT rating, COUNT(*) as count
             FROM reviews
             GROUP BY rating
             ORDER BY rating"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function checkDatabaseHealth(): array {
        try {
            $this->db->query("SELECT 1");
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch(Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    private function checkDiskSpace(): array {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        $used = $total - $free;
        $percentage = round(($used / $total) * 100, 2);
        
        return [
            'status' => $percentage > 90 ? 'warning' : 'healthy',
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'total' => $this->formatBytes($total),
            'percentage' => $percentage
        ];
    }
    
    private function checkQueueStatus(): array {
        $pending = $this->db->query("SELECT COUNT(*) as count FROM email_queue WHERE status = 'pending'")->fetch_assoc()['count'];
        
        return [
            'status' => $pending > 100 ? 'warning' : 'healthy',
            'pending_emails' => $pending
        ];
    }
    
    private function checkCacheStatus(): array {
        return ['status' => 'healthy', 'message' => 'Cache not implemented'];
    }
    
    private function getMonthlyRevenue(): array {
        $result = $this->db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(amount) as revenue,
                    COUNT(*) as transactions
             FROM payments
             WHERE status = 'completed'
             AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getExpenseData(): array {
        // Mock expense data - would be from expenses table in real app
        return [
            ['category' => 'Fuel', 'amount' => 12500.00],
            ['category' => 'Vehicle Maintenance', 'amount' => 8750.00],
            ['category' => 'Insurance', 'amount' => 5000.00],
            ['category' => 'Staff Salaries', 'amount' => 45000.00],
            ['category' => 'Office Rent', 'amount' => 3500.00]
        ];
    }
    
    private function getProfitMargins(): array {
        return [
            'gross_profit_margin' => 65.5,
            'net_profit_margin' => 15.2,
            'operating_margin' => 22.8
        ];
    }
    
    private function getPaymentMethodStats(): array {
        $result = $this->db->query(
            "SELECT payment_method, COUNT(*) as count, SUM(amount) as total
             FROM payments
             WHERE status = 'completed'
             GROUP BY payment_method"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getBackupList(): array {
        // Mock backup data - would scan backup directory in real app
        return [
            [
                'filename' => 'courier_db_2024_01_15_backup.sql',
                'size' => '45.2 MB',
                'created_at' => '2024-01-15 03:00:00',
                'type' => 'scheduled'
            ],
            [
                'filename' => 'courier_db_2024_01_14_backup.sql',
                'size' => '44.8 MB',
                'created_at' => '2024-01-14 03:00:00',
                'type' => 'scheduled'
            ]
        ];
    }
    
    private function getSystemInfo(): array {
        return [
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->db->query("SELECT VERSION() as version")->fetch_assoc()['version'],
            'disk_space' => $this->checkDiskSpace(),
            'memory_usage' => memory_get_usage(true),
            'uptime' => sys_getloadavg()
        ];
    }
    
    private function getSecurityLogs(): array {
        $result = $this->db->query(
            "SELECT * FROM security_logs 
             ORDER BY created_at DESC 
             LIMIT 50"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getFailedLoginAttempts(): array {
        $result = $this->db->query(
            "SELECT ip_address, COUNT(*) as attempts, MAX(created_at) as last_attempt
             FROM failed_login_attempts
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
             GROUP BY ip_address
             ORDER BY attempts DESC"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getBlockedIPs(): array {
        $result = $this->db->query(
            "SELECT * FROM blocked_ips 
             WHERE expires_at > NOW() OR expires_at IS NULL
             ORDER BY created_at DESC"
        );
        
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getSecuritySettings(): array {
        return [
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'password_min_length' => 8,
            'require_2fa' => false,
            'session_timeout' => 30
        ];
    }
    
    private function getRateLimitSettings(): array {
        return [
            'api_requests_per_minute' => 60,
            'login_attempts_per_hour' => 10,
            'registration_per_ip_per_day' => 5,
            'password_reset_per_hour' => 3
        ];
    }
    
    private function getCurrentRateLimits(): array {
        // Mock current usage data
        return [
            'active_limits' => 45,
            'blocked_requests' => 12,
            'top_consumers' => [
                ['ip' => '192.168.1.100', 'requests' => 156],
                ['ip' => '10.0.0.25', 'requests' => 89]
            ]
        ];
    }
    
    private function getMaintenanceMode(): array {
        return [
            'enabled' => false,
            'message' => 'System is under maintenance. Please try again later.',
            'scheduled_start' => null,
            'scheduled_end' => null
        ];
    }
    
    private function getScheduledTasks(): array {
        return [
            [
                'name' => 'Database Backup',
                'schedule' => 'Daily at 3:00 AM',
                'last_run' => '2024-01-15 03:00:00',
                'status' => 'completed'
            ],
            [
                'name' => 'Email Queue Processing',
                'schedule' => 'Every 5 minutes',
                'last_run' => '2024-01-15 14:35:00',
                'status' => 'running'
            ],
            [
                'name' => 'Log Cleanup',
                'schedule' => 'Weekly on Sunday',
                'last_run' => '2024-01-14 02:00:00',
                'status' => 'completed'
            ]
        ];
    }
    
    private function getSystemStatus(): array {
        return [
            'database' => $this->checkDatabaseHealth(),
            'web_server' => ['status' => 'healthy', 'response_time' => '45ms'],
            'email_service' => ['status' => 'healthy', 'queue_size' => 23],
            'storage' => $this->checkDiskSpace(),
            'memory' => [
                'status' => 'healthy',
                'usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
            ]
        ];
    }

    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;
        
        while($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }
        
        return round($bytes, 2) . ' ' . $units[$index];
    }
}
