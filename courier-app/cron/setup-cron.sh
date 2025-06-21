#!/bin/bash

# Courier App Cron Job Setup Script
# This script sets up all necessary cron jobs for the courier application

# Get the absolute path to the application
APP_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_PATH=$(which php)

echo "Setting up cron jobs for Courier App..."
echo "Application path: $APP_PATH"
echo "PHP path: $PHP_PATH"

# Create cron job entries
CRON_JOBS="
# Courier App Cron Jobs
# Process email queue every 5 minutes
*/5 * * * * $PHP_PATH $APP_PATH/cron/process-emails.php >> $APP_PATH/logs/cron.log 2>&1

# Database backup daily at 2 AM
0 2 * * * $PHP_PATH $APP_PATH/cron/backup-database.php >> $APP_PATH/logs/cron.log 2>&1

# System health check every 15 minutes
*/15 * * * * $PHP_PATH $APP_PATH/cron/health-check.php >> $APP_PATH/logs/cron.log 2>&1

# Clean up old log files weekly (Sundays at 3 AM)
0 3 * * 0 find $APP_PATH/logs -name '*.log' -mtime +30 -delete

# Clean up old uploads monthly (1st day at 4 AM)
0 4 1 * * find $APP_PATH/uploads -type f -mtime +90 -delete

# Update shipment statuses (if needed for automatic updates)
0 */6 * * * $PHP_PATH $APP_PATH/cron/update-shipments.php >> $APP_PATH/logs/cron.log 2>&1
"

# Make cron scripts executable
chmod +x "$APP_PATH/cron/"*.php

# Create logs directory if it doesn't exist
mkdir -p "$APP_PATH/logs"
touch "$APP_PATH/logs/cron.log"

# Add cron jobs to user's crontab
echo "Adding cron jobs..."
(crontab -l 2>/dev/null; echo "$CRON_JOBS") | crontab -

echo "Cron jobs have been set up successfully!"
echo ""
echo "The following jobs have been scheduled:"
echo "- Email processing: Every 5 minutes"
echo "- Database backup: Daily at 2 AM"
echo "- Health checks: Every 15 minutes"
echo "- Log cleanup: Weekly on Sundays"
echo "- Upload cleanup: Monthly"
echo ""
echo "You can view your cron jobs with: crontab -l"
echo "Logs will be written to: $APP_PATH/logs/cron.log"
echo ""
echo "To remove these cron jobs, run: crontab -e and delete the Courier App section"
