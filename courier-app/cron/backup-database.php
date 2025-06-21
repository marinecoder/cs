#!/usr/bin/env php
<?php
/**
 * Database Backup Script
 * Creates daily backups of the database
 * Run daily at 2 AM: 0 2 * * * php /path/to/courier-app/cron/backup-database.php
 */

require_once __DIR__ . '/../config/app.php';

$config = require __DIR__ . '/../config/app.php';
$backupDir = __DIR__ . '/../backups';

// Create backup directory if it doesn't exist
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

try {
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . "/database_backup_$timestamp.sql";
    
    // Database credentials
    $host = $config['database']['host'];
    $username = $config['database']['username'];
    $password = $config['database']['password'];
    $database = $config['database']['database'];
    
    // Create mysqldump command
    $command = "mysqldump --host=$host --user=$username --password=$password $database > $backupFile";
    
    // Execute backup
    $output = shell_exec($command);
    
    if (file_exists($backupFile) && filesize($backupFile) > 0) {
        // Compress the backup
        $compressedFile = $backupFile . '.gz';
        shell_exec("gzip $backupFile");
        
        echo "[$timestamp] Database backup created: $compressedFile\n";
        
        // Clean up old backups (keep last 30 days)
        $oldBackups = glob($backupDir . '/database_backup_*.sql.gz');
        foreach ($oldBackups as $backup) {
            $fileDate = filemtime($backup);
            if (time() - $fileDate > (30 * 24 * 60 * 60)) { // 30 days
                unlink($backup);
                echo "[$timestamp] Deleted old backup: " . basename($backup) . "\n";
            }
        }
        
        // Log success
        file_put_contents(__DIR__ . '/../logs/cron.log', "[$timestamp] Database backup completed successfully\n", FILE_APPEND);
    } else {
        throw new Exception("Backup file not created or is empty");
    }
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    $error = "[$timestamp] Database backup error: " . $e->getMessage() . "\n";
    echo $error;
    file_put_contents(__DIR__ . '/../logs/cron.log', $error, FILE_APPEND);
}
