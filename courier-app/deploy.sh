#!/bin/bash

# Courier App Deployment Script
# This script helps with common deployment tasks

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/html/courier-app"
BACKUP_DIR="/var/backups/courier-app"
DB_NAME="courier_app"
DB_USER="root"

# Functions
print_header() {
    echo -e "${GREEN}================================${NC}"
    echo -e "${GREEN}  Courier App Deployment Tool  ${NC}"
    echo -e "${GREEN}================================${NC}"
    echo ""
}

print_step() {
    echo -e "${YELLOW}[STEP]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        print_error "This script should not be run as root for security reasons"
        exit 1
    fi
}

# Check system requirements
check_requirements() {
    print_step "Checking system requirements..."
    
    # Check PHP version
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed"
        exit 1
    fi
    
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    if [[ $(echo "$PHP_VERSION" | cut -d. -f1) -lt 8 ]]; then
        print_error "PHP 8.0+ is required. Current version: $PHP_VERSION"
        exit 1
    fi
    
    # Check MySQL
    if ! command -v mysql &> /dev/null; then
        print_error "MySQL is not installed"
        exit 1
    fi
    
    # Check web server
    if ! command -v apache2 &> /dev/null && ! command -v nginx &> /dev/null; then
        print_error "Apache or Nginx web server is required"
        exit 1
    fi
    
    print_success "System requirements check passed"
}

# Create backup
create_backup() {
    print_step "Creating backup..."
    
    # Create backup directory
    mkdir -p "$BACKUP_DIR"
    
    # Create timestamp
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    
    # Backup files
    if [ -d "$APP_DIR" ]; then
        tar -czf "$BACKUP_DIR/files_$TIMESTAMP.tar.gz" -C "$(dirname "$APP_DIR")" "$(basename "$APP_DIR")"
        print_success "Files backed up to $BACKUP_DIR/files_$TIMESTAMP.tar.gz"
    fi
    
    # Backup database
    read -s -p "Enter MySQL password for user $DB_USER: " mysql_password
    echo ""
    
    if mysqldump -u "$DB_USER" -p"$mysql_password" "$DB_NAME" > "$BACKUP_DIR/database_$TIMESTAMP.sql" 2>/dev/null; then
        print_success "Database backed up to $BACKUP_DIR/database_$TIMESTAMP.sql"
    else
        print_error "Database backup failed"
        exit 1
    fi
}

# Set up permissions
setup_permissions() {
    print_step "Setting up file permissions..."
    
    if [ -d "$APP_DIR" ]; then
        # Set general permissions
        find "$APP_DIR" -type f -exec chmod 644 {} \;
        find "$APP_DIR" -type d -exec chmod 755 {} \;
        
        # Set specific permissions for writable directories
        chmod -R 777 "$APP_DIR/config" 2>/dev/null || true
        chmod -R 777 "$APP_DIR/logs" 2>/dev/null || true
        chmod -R 777 "$APP_DIR/uploads" 2>/dev/null || true
        
        print_success "Permissions set successfully"
    else
        print_error "Application directory not found: $APP_DIR"
        exit 1
    fi
}

# Install dependencies
install_dependencies() {
    print_step "Installing dependencies..."
    
    # Check if composer is available
    if command -v composer &> /dev/null; then
        cd "$APP_DIR"
        composer install --no-dev --optimize-autoloader
        print_success "Composer dependencies installed"
    else
        print_step "Composer not found, skipping PHP dependencies"
    fi
    
    # Check if npm is available
    if command -v npm &> /dev/null && [ -f "$APP_DIR/package.json" ]; then
        cd "$APP_DIR"
        npm install --production
        npm run build
        print_success "Node.js dependencies installed and built"
    else
        print_step "npm not found or package.json missing, skipping Node.js dependencies"
    fi
}

# Configure web server
configure_webserver() {
    print_step "Configuring web server..."
    
    # Apache configuration
    if command -v apache2 &> /dev/null; then
        # Enable mod_rewrite
        sudo a2enmod rewrite 2>/dev/null || true
        
        # Create virtual host if needed
        if [ ! -f "/etc/apache2/sites-available/courier-app.conf" ]; then
            cat > /tmp/courier-app.conf << EOF
<VirtualHost *:80>
    ServerName courier-app.local
    DocumentRoot $APP_DIR/public
    
    <Directory $APP_DIR/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/courier-app_error.log
    CustomLog \${APACHE_LOG_DIR}/courier-app_access.log combined
</VirtualHost>
EOF
            sudo mv /tmp/courier-app.conf /etc/apache2/sites-available/
            sudo a2ensite courier-app.conf
            print_success "Apache virtual host created"
        fi
        
        # Restart Apache
        sudo systemctl restart apache2
        print_success "Apache configured and restarted"
    fi
    
    # Nginx configuration (basic example)
    if command -v nginx &> /dev/null && [ ! -f "/etc/nginx/sites-available/courier-app" ]; then
        cat > /tmp/courier-app << EOF
server {
    listen 80;
    server_name courier-app.local;
    root $APP_DIR/public;
    index index.php index.html;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
EOF
        sudo mv /tmp/courier-app /etc/nginx/sites-available/
        sudo ln -sf /etc/nginx/sites-available/courier-app /etc/nginx/sites-enabled/
        sudo systemctl restart nginx
        print_success "Nginx configured and restarted"
    fi
}

# Run application setup
setup_application() {
    print_step "Setting up application..."
    
    cd "$APP_DIR"
    
    # Create directories if they don't exist
    mkdir -p config logs uploads
    
    # Set up configuration if it doesn't exist
    if [ ! -f "config/app.php" ] && [ -f "config/app.php.example" ]; then
        cp config/app.php.example config/app.php
        print_success "Configuration file created from template"
        print_step "Please edit config/app.php with your settings"
    fi
    
    print_success "Application setup completed"
}

# Main deployment function
deploy() {
    print_header
    
    case ${1:-"full"} in
        "check")
            check_requirements
            ;;
        "backup")
            create_backup
            ;;
        "permissions")
            setup_permissions
            ;;
        "dependencies")
            install_dependencies
            ;;
        "webserver")
            configure_webserver
            ;;
        "app")
            setup_application
            ;;
        "full")
            check_requirements
            create_backup
            setup_permissions
            install_dependencies
            configure_webserver
            setup_application
            print_success "Full deployment completed!"
            echo ""
            echo "Next steps:"
            echo "1. Edit config/app.php with your database and email settings"
            echo "2. Visit http://your-domain/courier-app/install/ to complete setup"
            echo "3. Delete the install directory after setup"
            ;;
        *)
            echo "Usage: $0 [check|backup|permissions|dependencies|webserver|app|full]"
            echo ""
            echo "Commands:"
            echo "  check        - Check system requirements"
            echo "  backup       - Create backup of files and database"
            echo "  permissions  - Set file permissions"
            echo "  dependencies - Install dependencies"
            echo "  webserver    - Configure web server"
            echo "  app          - Set up application"
            echo "  full         - Run complete deployment (default)"
            exit 1
            ;;
    esac
}

# Run deployment
deploy "$1"
