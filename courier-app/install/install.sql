-- Courier Dash Database Schema
-- PHP 8.2 Courier Web App - Complete Database Structure

-- Users table with RBAC
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'USER', 'COURIER') DEFAULT 'USER',
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- Companies/Organizations
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    logo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shipment types and pricing
CREATE TABLE shipment_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    price_per_km DECIMAL(10,2) DEFAULT 0,
    max_weight DECIMAL(8,2),
    max_dimensions VARCHAR(100),
    delivery_time_hours INT DEFAULT 24,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Main shipments table
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    shipment_type_id INT NOT NULL,
    
    -- Sender information
    sender_name VARCHAR(255) NOT NULL,
    sender_phone VARCHAR(50) NOT NULL,
    sender_email VARCHAR(255),
    sender_address TEXT NOT NULL,
    sender_city VARCHAR(100) NOT NULL,
    sender_postal_code VARCHAR(20),
    
    -- Receiver information
    receiver_name VARCHAR(255) NOT NULL,
    receiver_phone VARCHAR(50) NOT NULL,
    receiver_email VARCHAR(255),
    receiver_address TEXT NOT NULL,
    receiver_city VARCHAR(100) NOT NULL,
    receiver_postal_code VARCHAR(20),
    
    -- Shipment details
    description TEXT,
    weight DECIMAL(8,2),
    dimensions VARCHAR(100),
    value DECIMAL(10,2) DEFAULT 0,
    insurance_required BOOLEAN DEFAULT FALSE,
    fragile BOOLEAN DEFAULT FALSE,
    urgent BOOLEAN DEFAULT FALSE,
    
    -- Pricing and payment
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    
    -- Status and tracking
    status ENUM('pending', 'confirmed', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'cancelled') DEFAULT 'pending',
    assigned_courier_id INT,
    pickup_date DATE,
    expected_delivery_date DATE,
    actual_delivery_date DATETIME,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (shipment_type_id) REFERENCES shipment_types(id),
    FOREIGN KEY (assigned_courier_id) REFERENCES users(id),
    
    INDEX idx_tracking (tracking_number),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_courier (assigned_courier_id),
    INDEX idx_created (created_at)
);

-- Shipment progress tracking
CREATE TABLE shipment_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    location VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    updated_by INT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_shipment (shipment_id),
    INDEX idx_timestamp (timestamp)
);

-- Payment transactions
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(255),
    gateway_response TEXT,
    status ENUM('pending', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (shipment_id) REFERENCES shipments(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_shipment (shipment_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);

-- Courier assignments and routes
CREATE TABLE courier_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    courier_id INT NOT NULL,
    route_name VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    status ENUM('planned', 'active', 'completed', 'cancelled') DEFAULT 'planned',
    total_shipments INT DEFAULT 0,
    completed_shipments INT DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (courier_id) REFERENCES users(id),
    INDEX idx_courier (courier_id),
    INDEX idx_date (date),
    INDEX idx_status (status)
);

-- Route shipments mapping
CREATE TABLE route_shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    shipment_id INT NOT NULL,
    sequence_order INT NOT NULL,
    estimated_arrival DATETIME,
    actual_arrival DATETIME,
    status ENUM('pending', 'delivered', 'failed') DEFAULT 'pending',
    notes TEXT,
    
    FOREIGN KEY (route_id) REFERENCES courier_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id),
    UNIQUE KEY unique_route_shipment (route_id, shipment_id),
    INDEX idx_route (route_id),
    INDEX idx_shipment (shipment_id)
);

-- Document attachments
CREATE TABLE shipment_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    document_type ENUM('invoice', 'receipt', 'photo', 'signature', 'other') DEFAULT 'other',
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_shipment (shipment_id),
    INDEX idx_type (document_type)
);

-- Customer feedback and ratings
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    user_id INT NOT NULL,
    courier_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (shipment_id) REFERENCES shipments(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (courier_id) REFERENCES users(id),
    UNIQUE KEY unique_shipment_review (shipment_id, user_id),
    INDEX idx_rating (rating),
    INDEX idx_courier (courier_id)
);

-- System notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    related_id INT,
    related_type VARCHAR(50),
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (read_at),
    INDEX idx_created (created_at)
);

-- Email queue table for reliable email delivery
CREATE TABLE email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- API keys table for mobile app access
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_used TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_api_key (api_key),
    INDEX idx_user_id (user_id)
);

-- Rate limiting table
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    request_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier),
    INDEX idx_window (window_start)
);

-- System settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- Audit log for security and tracking
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_created (created_at)
);

-- API tokens for external integrations
CREATE TABLE api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_name VARCHAR(100) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    permissions JSON,
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    status ENUM('active', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_hash (token_hash),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);

-- Financial reports and analytics
CREATE TABLE financial_summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_revenue DECIMAL(12,2) DEFAULT 0,
    total_shipments INT DEFAULT 0,
    total_users INT DEFAULT 0,
    avg_shipment_value DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_date (date),
    INDEX idx_date (date)
);

-- Insert default shipment types
INSERT INTO shipment_types (name, description, base_price, price_per_km, max_weight, delivery_time_hours) VALUES
('Standard', 'Regular delivery within 2-3 business days', 15.00, 0.50, 10.00, 72),
('Express', 'Same day or next day delivery', 25.00, 0.75, 5.00, 24),
('Overnight', 'Guaranteed overnight delivery', 35.00, 1.00, 2.00, 12),
('International', 'International shipping', 50.00, 2.00, 20.00, 168),
('Fragile', 'Special handling for fragile items', 30.00, 1.25, 5.00, 48);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('company_name', 'Courier Dash', 'string', 'Company name displayed in the application'),
('company_email', 'info@courierdash.com', 'string', 'Main company email address'),
('company_phone', '+1-555-0123', 'string', 'Main company phone number'),
('currency', 'USD', 'string', 'Default currency for transactions'),
('timezone', 'America/New_York', 'string', 'Default timezone'),
('email_notifications', '1', 'boolean', 'Enable email notifications'),
('sms_notifications', '0', 'boolean', 'Enable SMS notifications'),
('auto_assign_couriers', '1', 'boolean', 'Automatically assign couriers to shipments'),
('max_file_upload_size', '10485760', 'integer', 'Maximum file upload size in bytes (10MB)'),
('tracking_update_interval', '30', 'integer', 'Tracking update interval in minutes');

-- Sample data for demonstration
INSERT INTO users (email, password, role, name, phone, status) VALUES
('demo@user.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER', 'Demo User', '+1-555-0100', 'active'),
('courier@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'COURIER', 'Demo Courier', '+1-555-0101', 'active');

-- Sample shipment for demo
INSERT INTO shipments (tracking_number, user_id, shipment_type_id, sender_name, sender_phone, sender_address, sender_city, receiver_name, receiver_phone, receiver_address, receiver_city, description, weight, total_amount, status) VALUES
('CD' + LPAD(FLOOR(RAND() * 999999), 6, '0'), 2, 1, 'John Sender', '+1-555-0200', '123 Main St', 'New York', 'Jane Receiver', '+1-555-0201', '456 Oak Ave', 'Boston', 'Sample package for demo', 2.5, 25.50, 'in_transit');

-- Insert sample tracking data
INSERT INTO shipment_progress (shipment_id, status, description, location) VALUES
(1, 'Processing', 'Order received and being processed', 'NYC Warehouse'),
(1, 'In Transit', 'Package departed from facility', 'Distribution Center'),
(1, 'Out for Delivery', 'Package is on delivery truck', 'Boston Hub');
