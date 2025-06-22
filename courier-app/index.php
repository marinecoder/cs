<?php
// Check if system is installed
if(!file_exists('config.php')) {
    header('Location: /install/');
    exit;
}

require_once 'config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Auth.php';
require_once 'app/core/Router.php';
require_once 'app/controllers/AdminController.php';
require_once 'app/controllers/UserController.php';
require_once 'app/controllers/CourierController.php';
require_once 'app/controllers/ApiController.php';
require_once 'app/controllers/CourierController.php';

// Start session and initialize CSRF protection
Auth::startSession();
if(!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize router
$router = new Router();

// Handle post-installation special redirects
if (isset($_GET['admin']) && $_GET['admin'] == '1') {
    // Redirect new admin to login with pre-filled admin context
    Router::renderWithLayout('auth/login', [
        'title' => 'Admin Login - Courier Dash',
        'isAdminLogin' => true,
        'message' => 'Welcome! Please login with your admin credentials to access the dashboard.',
        'messageType' => 'info'
    ], 'auth');
    exit;
}

if (isset($_GET['user']) && $_GET['user'] == '1') {
    // Redirect to user registration/login page
    Router::renderWithLayout('auth/login', [
        'title' => 'User Login - Courier Dash',
        'isUserLogin' => true,
        'message' => 'Create an account or login to start shipping with Courier Dash.',
        'messageType' => 'info',
        'showRegisterPrompt' => true
    ], 'auth');
    exit;
}

// Public routes
$router->get('/', function() {
    if(Auth::isLoggedIn()) {
        $user = Auth::getCurrentUser();
        Router::redirect($user['role'] === 'ADMIN' ? '/admin/dashboard' : '/dashboard');
    } else {
        Router::renderWithLayout('auth/login', ['title' => 'Login - Courier Dash'], 'auth');
    }
});

$router->get('/login', function() {
    if(Auth::isLoggedIn()) {
        Router::redirect('/');
    }
    Router::renderWithLayout('auth/login', ['title' => 'Login - Courier Dash'], 'auth');
});

$router->post('/login', function() {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        Router::redirect('/login?error=Invalid request');
    }
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = Auth::login($email, $password);
    
    if($result['success']) {
        Router::redirect('/');
    } else {
        Router::redirect('/login?error=' . urlencode($result['message']));
    }
});

$router->get('/register', function() {
    if(Auth::isLoggedIn()) {
        Router::redirect('/');
    }
    Router::renderWithLayout('auth/register', ['title' => 'Register - Courier Dash'], 'auth');
});

$router->post('/register', function() {
    if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        Router::redirect('/register?error=Invalid request');
    }
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if($password !== $password_confirm) {
        Router::redirect('/register?error=Passwords do not match');
    }
    
    try {
        $userId = Auth::createUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'USER'
        ]);
        
        Router::redirect('/login?success=Account created successfully');
    } catch(Exception $e) {
        Router::redirect('/register?error=' . urlencode($e->getMessage()));
    }
});

$router->get('/logout', function() {
    Auth::logout();
    Router::redirect('/login?success=Logged out successfully');
});

// Protected routes
$router->get('/dashboard', function() {
    Auth::requireLogin();
    $controller = new UserController();
    $controller->dashboard();
});

// User routes
$router->get('/shipment/create', function() {
    Auth::requirePermission('shipment_create');
    $controller = new UserController();
    $controller->createShipment();
});

$router->post('/shipment/create', function() {
    Auth::requirePermission('shipment_create');
    $controller = new UserController();
    $controller->storeShipment();
});

$router->get('/shipments', function() {
    Auth::requirePermission('shipment_view_own');
    $controller = new UserController();
    $controller->listShipments();
});

$router->get('/tracking', function() {
    Auth::requirePermission('tracking_view');
    $controller = new UserController();
    $controller->tracking();
});

$router->get('/tracking-modal', function() {
    Auth::requirePermission('tracking_view');
    $controller = new UserController();
    $controller->trackingModal();
});

$router->get('/payments', function() {
    Auth::requirePermission('payment_process');
    $controller = new UserController();
    $controller->payments();
});

$router->get('/profile', function() {
    Auth::requireLogin();
    $controller = new UserController();
    $controller->profile();
});

$router->get('/addresses', function() {
    Auth::requireLogin();
    $controller = new UserController();
    $controller->addresses();
});

$router->get('/support', function() {
    Auth::requireLogin();
    $controller = new UserController();
    $controller->support();
});

// Courier routes
$router->get('/courier/dashboard', function() {
    Auth::requirePermission('shipment_view_assigned');
    $controller = new CourierController();
    $controller->dashboard();
});

$router->get('/courier/routes', function() {
    Auth::requirePermission('route_view');
    $controller = new CourierController();
    $controller->routes();
});

$router->get('/courier/shipments', function() {
    Auth::requirePermission('shipment_view_assigned');
    $controller = new CourierController();
    $controller->shipments();
});

$router->get('/courier/history', function() {
    Auth::requirePermission('shipment_view_assigned');
    $controller = new CourierController();
    $controller->history();
});

$router->get('/courier/profile', function() {
    Auth::requireLogin();
    $controller = new CourierController();
    $controller->profile();
});

$router->post('/courier/update-status', function() {
    Auth::requirePermission('shipment_update_status');
    $controller = new CourierController();
    $controller->updateShipmentStatus();
});

// Admin routes
$router->get('/admin/dashboard', function() {
    Auth::requirePermission('analytics_view');
    $controller = new AdminController();
    $controller->dashboard();
});

$router->get('/admin/users', function() {
    Auth::requirePermission('user_manage');
    $controller = new AdminController();
    $controller->users();
});

$router->get('/admin/shipments', function() {
    Auth::requirePermission('shipment_view_all');
    $controller = new AdminController();
    $controller->shipments();
});

$router->get('/admin/couriers', function() {
    Auth::requirePermission('courier_manage');
    $controller = new AdminController();
    $controller->couriers();
});

$router->get('/admin/routes', function() {
    Auth::requirePermission('route_manage');
    $controller = new AdminController();
    $controller->routes();
});

$router->get('/admin/payments', function() {
    Auth::requirePermission('payment_view_all');
    $controller = new AdminController();
    $controller->payments();
});

$router->get('/admin/reports', function() {
    Auth::requirePermission('reports_generate');
    $controller = new AdminController();
    $controller->reports();
});

$router->get('/admin/analytics', function() {
    Auth::requirePermission('analytics_view');
    $controller = new AdminController();
    $controller->analytics();
});

$router->get('/admin/reviews', function() {
    Auth::requirePermission('user_manage');
    $controller = new AdminController();
    $controller->reviews();
});

$router->get('/admin/notifications', function() {
    Auth::requirePermission('system_config');
    $controller = new AdminController();
    $controller->notifications();
});

$router->get('/admin/settings', function() {
    Auth::requirePermission('settings_manage');
    $controller = new AdminController();
    $controller->settings();
});

$router->get('/admin/api-tokens', function() {
    Auth::requirePermission('api_tokens');
    $controller = new AdminController();
    $controller->apiTokens();
});

$router->get('/admin/audit-logs', function() {
    Auth::requirePermission('audit_logs');
    $controller = new AdminController();
    $controller->auditLogs();
});

$router->get('/admin/backup', function() {
    Auth::requirePermission('system_backup');
    $controller = new AdminController();
    $controller->backup();
});

$router->get('/admin/system-health', function() {
    Auth::requirePermission('system_config');
    $controller = new AdminController();
    $controller->systemHealth();
});

$router->get('/admin/financials', function() {
    Auth::requirePermission('analytics_view');
    $controller = new AdminController();
    $controller->financials();
});

$router->get('/admin/logs', function() {
    Auth::requirePermission('audit_logs');
    $controller = new AdminController();
    $controller->logs();
});

$router->get('/admin/backups', function() {
    Auth::requirePermission('system_backup');
    $controller = new AdminController();
    $controller->backups();
});

$router->get('/admin/api-keys', function() {
    Auth::requirePermission('api_tokens');
    $controller = new AdminController();
    $controller->apiKeys();
});

$router->get('/admin/emails', function() {
    Auth::requirePermission('system_config');
    $controller = new AdminController();
    $controller->emails();
});

$router->get('/admin/security', function() {
    Auth::requirePermission('system_config');
    $controller = new AdminController();
    $controller->security();
});

$router->get('/admin/rate-limits', function() {
    Auth::requirePermission('system_config');
    $controller = new AdminController();
    $controller->rateLimits();
});

$router->get('/admin/maintenance', function() {
    Auth::requirePermission('system_config');
    $controller = new AdminController();
    $controller->maintenance();
});

// API routes
$router->get('/api/notifications', function() {
    Auth::requireLogin();
    $controller = new UserController();
    $controller->apiNotifications();
});

// Error handling routes
$router->get('/403', function() {
    http_response_code(403);
    Router::renderWithLayout('errors/403', ['title' => 'Access Denied']);
});

$router->get('/404', function() {
    http_response_code(404);
    Router::renderWithLayout('errors/404', ['title' => 'Page Not Found']);
});

// Dispatch the request
try {
    $router->dispatch();
} catch(Exception $e) {
    error_log('Application error: ' . $e->getMessage());
    
    if(defined('APP_DEBUG') && APP_DEBUG) {
        echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
    } else {
        http_response_code(500);
        Router::renderWithLayout('errors/500', ['title' => 'Server Error']);
    }
}

// API Routes
if (str_starts_with($url, 'api/')) {
    $method = $_SERVER['REQUEST_METHOD'];
    $endpoint = substr($url, 4); // Remove 'api/' prefix
    $data = [];
    
    // Get request data
    if ($method === 'POST' || $method === 'PUT') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: [];
        $data = array_merge($data, $_POST); // Include form data
    } elseif ($method === 'GET') {
        $data = $_GET;
    }
    
    $apiController = new ApiController();
    $apiController->handleRequest($method, $endpoint, $data);
    exit;
}
