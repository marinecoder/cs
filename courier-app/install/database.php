<?php
session_start();

if(!isset($_SESSION['db_config'])) {
    header('Location: index.php?step=2');
    exit;
}

if($_POST) {
    $admin_email = $_POST['admin_email'];
    $admin_password = $_POST['admin_password'];
    $admin_password_confirm = $_POST['admin_password_confirm'];
    $company_name = $_POST['company_name'];
    
    if($admin_password !== $admin_password_confirm) {
        header('Location: index.php?step=3&error=Passwords do not match');
        exit;
    }
    
    $db_config = $_SESSION['db_config'];
    
    try {
        // Connect to database
        $conn = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['name']);
        
        if($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
        
        // Set charset to avoid encoding issues
        $conn->set_charset("utf8mb4");
        
        // Create database tables
        $sql = file_get_contents('install.sql');
        
        if($sql === false) {
            throw new Exception('Could not read install.sql file');
        }
        
        // Execute multiple queries
        $queries = explode(';', $sql);
        $executed = 0;
        
        foreach($queries as $query) {
            $query = trim($query);
            if(!empty($query) && !preg_match('/^--/', $query)) {
                if(!$conn->query($query)) {
                    throw new Exception('Database setup failed at query ' . ($executed + 1) . ': ' . $conn->error . "\nQuery: " . substr($query, 0, 100) . '...');
                }
                $executed++;
            }
        }
        
        // Create admin user
        $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (email, password, role, name, status, created_at) VALUES (?, ?, 'ADMIN', ?, 'active', NOW())");
        $stmt->bind_param('sss', $admin_email, $password_hash, $company_name);
        $stmt->execute();
        
        // Create config.php
        $config_content = "<?php\n";
        $config_content .= "// Auto-generated configuration file\n";
        $config_content .= "define('DB_HOST', '" . addslashes($db_config['host']) . "');\n";
        $config_content .= "define('DB_NAME', '" . addslashes($db_config['name']) . "');\n";
        $config_content .= "define('DB_USER', '" . addslashes($db_config['user']) . "');\n";
        $config_content .= "define('DB_PASSWORD', '" . addslashes($db_config['password']) . "');\n";
        $config_content .= "define('DB_CHARSET', 'utf8mb4');\n";
        $config_content .= "define('APP_NAME', '" . addslashes($company_name) . "');\n";
        $config_content .= "define('APP_URL', 'http://' . \$_SERVER['HTTP_HOST']);\n";
        $config_content .= "define('ADMIN_EMAIL', '" . addslashes($admin_email) . "');\n";
        $config_content .= "define('ENCRYPTION_KEY', '" . bin2hex(random_bytes(32)) . "');\n";
        $config_content .= "define('APP_DEBUG', false);\n";
        $config_content .= "date_default_timezone_set('UTC');\n";
        $config_content .= "ini_set('session.cookie_httponly', 1);\n";
        $config_content .= "ini_set('session.use_strict_mode', 1);\n";
        
        file_put_contents('../config.php', $config_content);
        
        // Clear session
        session_destroy();
        
        header('Location: finalize.php');
        exit;
        
    } catch(Exception $e) {
        header('Location: index.php?step=3&error=' . urlencode($e->getMessage()));
        exit;
    }
}
?>
