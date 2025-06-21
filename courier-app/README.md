# Courier Web Application

A comprehensive PHP 8.2 courier management system with modern UI, real-time tracking, RBAC, and complete admin/user dashboards.

## Features

### Core Features
- **User Management**: Role-based access control (RBAC) with admin and user roles
- **Shipment Management**: Create, track, and manage shipments with real-time status updates
- **Real-time Tracking**: Track packages with detailed timeline and status updates
- **Payment Integration**: Support for multiple payment methods (Stripe, PayPal ready)
- **Notifications**: Email notifications for shipment updates and important events
- **Reports & Analytics**: Comprehensive reporting with charts and export functionality
- **Modern UI**: Responsive design with Tailwind CSS and interactive components

### User Features
- User registration and email verification
- Dashboard with shipment overview and statistics
- Create and manage shipments
- Real-time package tracking
- Payment history and invoices
- Profile management

### Admin Features
- Complete admin dashboard with analytics
- User management (create, edit, delete, role assignment)
- Shipment management with status updates
- Financial reports and revenue tracking
- System settings and configuration
- Audit logs and activity monitoring

## Technology Stack

- **Backend**: PHP 8.2+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **CSS Framework**: Tailwind CSS 3.x
- **Charts**: Chart.js
- **Server**: Apache/Nginx with mod_rewrite

## Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled
- 50MB+ disk space
- 512MB+ RAM

## Installation

### Quick Installation (Recommended)

1. **Download and Extract**
   ```bash
   # Extract the courier-app folder to your web server directory
   # For XAMPP: htdocs/courier-app
   # For other servers: /var/www/html/courier-app
   ```

2. **Set Permissions**
   ```bash
   chmod -R 755 /path/to/courier-app
   chmod -R 777 /path/to/courier-app/config
   chmod -R 777 /path/to/courier-app/logs
   chmod -R 777 /path/to/courier-app/uploads
   ```

3. **Create Database**
   ```sql
   CREATE DATABASE courier_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Run Installation Wizard**
   - Navigate to `http://your-domain/courier-app/install/`
   - Follow the step-by-step installation wizard
   - The installer will:
     - Test system requirements
     - Configure database connection
     - Create database tables
     - Insert demo data
     - Create admin account
     - Generate configuration files

5. **Complete Setup**
   - After installation, delete the `/install` directory for security
   - Login with the admin credentials you created

### Manual Installation

If you prefer manual installation:

1. **Database Setup**
   ```bash
   mysql -u root -p courier_app < install/install.sql
   ```

2. **Configuration**
   ```bash
   cp config/app.php.example config/app.php
   # Edit config/app.php with your database credentials
   ```

3. **Web Server Configuration**
   - Ensure DocumentRoot points to the `public` directory
   - Enable mod_rewrite for Apache
   - Configure virtual host if needed

## Configuration

### Database Configuration
Edit `config/app.php`:
```php
'database' => [
    'host' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'courier_app',
    'port' => 3306,
    'charset' => 'utf8mb4'
],
```

### Email Configuration
Configure SMTP settings in `config/app.php`:
```php
'email' => [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your_email@gmail.com',
    'smtp_password' => 'your_password',
    'from_email' => 'noreply@yourcompany.com',
    'from_name' => 'Your Company',
],
```

### Payment Configuration
Set up payment gateways in `config/app.php`:
```php
'payment' => [
    'stripe_public_key' => 'pk_test_...',
    'stripe_secret_key' => 'sk_test_...',
    'paypal_client_id' => 'your_paypal_client_id',
    'paypal_client_secret' => 'your_paypal_secret',
],
```

## Directory Structure

```
courier-app/
├── app/
│   ├── controllers/          # Application controllers
│   ├── core/                # Core classes (Database, Auth, Router)
│   └── views/               # View templates
│       ├── admin/           # Admin panel views
│       ├── auth/            # Authentication views
│       ├── errors/          # Error pages
│       ├── layouts/         # Layout templates
│       └── user/            # User dashboard views
├── config/                  # Configuration files
├── install/                 # Installation wizard (delete after install)
├── logs/                    # Application logs
├── public/                  # Public web assets
│   └── assets/             # CSS, JS, images
├── uploads/                 # File uploads directory
└── index.php               # Application entry point
```

## Default Login Credentials

After installation, you can login with:
- **Admin**: admin@example.com / admin123
- **User**: user@example.com / user123

**Important**: Change these credentials immediately after first login!

## Features Guide

### For Users
1. **Create Account**: Register with email verification
2. **Create Shipment**: Fill sender/receiver details, package info
3. **Track Package**: Use tracking number for real-time updates
4. **Manage Profile**: Update personal information and preferences
5. **View History**: Access past shipments and payments

### For Admins
1. **Dashboard**: Overview of system metrics and recent activity
2. **Manage Users**: Create, edit, disable user accounts
3. **Manage Shipments**: Update statuses, edit details, handle issues
4. **Reports**: Generate financial and operational reports
5. **Settings**: Configure system settings and preferences

## API Endpoints

The application includes RESTful API endpoints:

- `GET /api/shipments` - List shipments
- `POST /api/shipments` - Create shipment
- `GET /api/shipments/{id}` - Get shipment details
- `PUT /api/shipments/{id}` - Update shipment
- `DELETE /api/shipments/{id}` - Delete shipment
- `GET /api/tracking/{number}` - Track shipment
- `GET /api/users` - List users (admin only)
- `POST /api/users` - Create user (admin only)

## Security Features

- **Password Hashing**: Secure password storage with PHP's password_hash()
- **CSRF Protection**: Cross-site request forgery protection
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output encoding
- **Rate Limiting**: Protection against brute force attacks
- **Session Security**: Secure session configuration
- **File Upload Security**: Restricted file types and validation

## Customization

### Adding New Features
1. Create controller in `app/controllers/`
2. Add routes in `index.php`
3. Create views in `app/views/`
4. Update database schema if needed

### Theming
- Modify `public/assets/css/tailwind.css` for styling
- Update layout files in `app/views/layouts/`
- Customize colors and branding in CSS variables

### Email Templates
- Email templates are in controller methods
- Customize HTML structure and styling as needed

## Maintenance

### Regular Tasks
- Monitor log files in `/logs/`
- Regular database backups
- Update dependencies and security patches
- Clean up old uploaded files
- Monitor disk space and performance

### Backup
```bash
# Database backup
mysqldump -u username -p courier_app > backup_$(date +%Y%m%d).sql

# Full application backup
tar -czf courier_app_backup_$(date +%Y%m%d).tar.gz /path/to/courier-app/
```

### Updates
1. Backup current installation
2. Replace files with new version
3. Run any database migrations
4. Clear application cache
5. Test functionality

## Troubleshooting

### Common Issues

1. **Installation Issues**
   - Check PHP version (8.2+ required)
   - Verify MySQL connection
   - Ensure proper file permissions
   - Check Apache mod_rewrite

2. **Database Connection Errors**
   - Verify database credentials in config
   - Check MySQL service status
   - Confirm database exists

3. **Email Not Sending**
   - Check SMTP configuration
   - Verify firewall settings
   - Test with different email provider

4. **File Upload Issues**
   - Check upload directory permissions
   - Verify PHP upload settings
   - Check file size limits

### Log Files
- Application logs: `/logs/app.log`
- Error logs: Check web server error logs
- Database logs: Check MySQL error logs

## Support

For support and questions:
- Check the troubleshooting section
- Review log files for errors
- Ensure system requirements are met
- Verify configuration settings

## License

This courier web application is provided as-is for educational and commercial use. Modify and distribute according to your needs.

## Changelog

### Version 1.0.0
- Initial release
- Complete courier management system
- User and admin dashboards
- Real-time tracking
- Payment integration ready
- Modern responsive UI
- RBAC implementation
- Comprehensive reporting

---

**Note**: This is a production-ready application. Always follow security best practices, keep backups, and test changes in a development environment first.
