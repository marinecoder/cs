# Installation Troubleshooting Guide

## Common Installation Issues and Solutions

### 1. "Table 'courier-app.settings' doesn't exist"

**Problem**: The database setup fails because the settings table is missing.

**Solution**: 
- This has been fixed in the latest version
- The SQL file now includes both `settings` and `system_settings` tables
- Re-run the installation process

### 2. Database Connection Issues

**Problem**: Cannot connect to MySQL database.

**Solutions**:
- Check MySQL service is running: `sudo service mysql start`
- Verify database credentials are correct
- Ensure database user has proper permissions:
  ```sql
  GRANT ALL PRIVILEGES ON *.* TO 'your_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

### 3. Permission Denied Errors

**Problem**: Cannot write config.php file.

**Solutions**:
- Set proper file permissions:
  ```bash
  chmod 755 /path/to/courier-app/
  chmod 666 /path/to/courier-app/config.php (if exists)
  ```
- Ensure web server user can write to the directory

### 4. PHP Version Issues

**Problem**: Features not working due to old PHP version.

**Solutions**:
- Ensure PHP 8.2 or higher is installed
- Check PHP version: `php -v`
- Update PHP if necessary

### 5. MySQL Syntax Errors

**Problem**: SQL queries failing during installation.

**Solutions**:
- Ensure MySQL 5.7+ or MariaDB 10.2+ is installed
- Check MySQL mode settings
- Verify database charset is set to utf8mb4

### 6. Session Issues

**Problem**: Login not working or session errors.

**Solutions**:
- Check PHP session configuration
- Ensure session directory is writable
- Verify session cookies are enabled

### 7. Email Configuration

**Problem**: Email notifications not working.

**Solutions**:
- Check SMTP settings in config/app.php
- Verify firewall allows SMTP connections
- Test email configuration separately

## Installation Checklist

Before installation, ensure:

- [ ] PHP 8.2+ installed
- [ ] MySQL 5.7+ or MariaDB 10.2+ installed
- [ ] Apache/Nginx web server configured
- [ ] PHP extensions enabled: mysqli, json, session, openssl
- [ ] Directory permissions set correctly
- [ ] Database user has proper privileges

## Post-Installation Steps

After successful installation:

1. **Set up cron jobs**:
   ```bash
   ./cron/setup-cron.sh
   ```

2. **Configure email settings**:
   - Edit `config/app.php`
   - Add SMTP credentials

3. **Set up SSL certificate** (recommended):
   - Configure HTTPS in web server
   - Update APP_URL in config

4. **Test the system**:
   - Create test shipment
   - Verify email notifications
   - Check admin dashboard

## Getting Help

If you encounter issues not covered here:

1. Check the error logs:
   - PHP error log
   - MySQL error log
   - Web server error log

2. Enable debug mode:
   - Set `APP_DEBUG = true` in config.php
   - Check for detailed error messages

3. Verify system requirements:
   - Run the system check in step 1 of installation

## Performance Optimization

For production deployment:

1. **Database optimization**:
   - Add indexes for frequently queried columns
   - Enable query caching
   - Optimize MySQL configuration

2. **PHP optimization**:
   - Enable OPcache
   - Set appropriate memory limits
   - Configure session handling

3. **Web server optimization**:
   - Enable gzip compression
   - Set appropriate cache headers
   - Configure static file serving

## Security Considerations

Post-installation security steps:

1. **Remove installation files** (after successful setup):
   ```bash
   rm -rf install/
   ```

2. **Secure file permissions**:
   ```bash
   chmod 644 config.php
   chmod -R 755 app/
   chmod -R 644 app/**/*.php
   ```

3. **Configure firewall**:
   - Allow only necessary ports (80, 443, 22)
   - Block direct access to sensitive directories

4. **Regular backups**:
   - Set up automated database backups
   - Backup configuration files
   - Test restore procedures
