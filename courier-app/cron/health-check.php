#!/usr/bin/env php
<?php
/**
 * System Health Check Script
 * Monitors system health and sends alerts
 * Run every 15 minutes: php /path/to/courier-app/cron/health-check.php
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/EmailService.php';

class HealthChecker {
    private $db;
    private $emailService;
    private $issues = [];

    public function __construct() {
        $this->db = new Database();
        $this->emailService = new EmailService();
    }

    public function runHealthCheck(): array {
        $this->checkDatabase();
        $this->checkDiskSpace();
        $this->checkEmailQueue();
        $this->checkSystemLoad();
        $this->checkLogFiles();
        
        return $this->issues;
    }

    private function checkDatabase(): void {
        try {
            $result = $this->db->query("SELECT 1");
            if (!$result) {
                $this->addIssue('Database connection failed', 'critical');
            }
        } catch (Exception $e) {
            $this->addIssue('Database error: ' . $e->getMessage(), 'critical');
        }
    }

    private function checkDiskSpace(): void {
        $freeBytes = disk_free_space(__DIR__ . '/../');
        $totalBytes = disk_total_space(__DIR__ . '/../');
        $freePercent = ($freeBytes / $totalBytes) * 100;

        if ($freePercent < 10) {
            $this->addIssue("Low disk space: {$freePercent}% free", 'critical');
        } elseif ($freePercent < 20) {
            $this->addIssue("Disk space warning: {$freePercent}% free", 'warning');
        }
    }

    private function checkEmailQueue(): void {
        $sql = "SELECT COUNT(*) as pending FROM email_queue WHERE status = 'pending'";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row['pending'] > 100) {
            $this->addIssue("Large email queue: {$row['pending']} pending emails", 'warning');
        }
        
        // Check for failed emails
        $sql = "SELECT COUNT(*) as failed FROM email_queue WHERE status = 'failed'";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row['failed'] > 10) {
            $this->addIssue("Many failed emails: {$row['failed']} failed", 'warning');
        }
    }

    private function checkSystemLoad(): void {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if ($load[0] > 2.0) {
                $this->addIssue("High system load: {$load[0]}", 'warning');
            }
        }
    }

    private function checkLogFiles(): void {
        $logFile = __DIR__ . '/../logs/app.log';
        if (file_exists($logFile)) {
            $size = filesize($logFile);
            if ($size > 50 * 1024 * 1024) { // 50MB
                $this->addIssue("Large log file: " . round($size / 1024 / 1024, 2) . "MB", 'warning');
            }
        }
    }

    private function addIssue(string $message, string $severity): void {
        $this->issues[] = [
            'message' => $message,
            'severity' => $severity,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public function sendAlerts(): void {
        if (empty($this->issues)) {
            return;
        }

        $criticalCount = count(array_filter($this->issues, fn($issue) => $issue['severity'] === 'critical'));
        $warningCount = count(array_filter($this->issues, fn($issue) => $issue['severity'] === 'warning'));

        if ($criticalCount > 0) {
            $subject = "CRITICAL: System Health Alert - $criticalCount critical issues";
            $this->sendHealthAlert($subject, $this->issues);
        } elseif ($warningCount > 3) { // Only send warning emails if many warnings
            $subject = "WARNING: System Health Alert - $warningCount warnings";
            $this->sendHealthAlert($subject, $this->issues);
        }
    }

    private function sendHealthAlert(string $subject, array $issues): void {
        $body = "<h2>System Health Alert</h2>\n<ul>\n";
        foreach ($issues as $issue) {
            $severityColor = $issue['severity'] === 'critical' ? 'red' : 'orange';
            $body .= "<li style='color: $severityColor;'><strong>[{$issue['severity']}]</strong> {$issue['message']} - {$issue['timestamp']}</li>\n";
        }
        $body .= "</ul>";

        // Send to admin email (you should configure this)
        $adminEmail = 'admin@example.com'; // Configure this
        $this->emailService->queueEmail($adminEmail, $subject, $body);
    }
}

try {
    $healthChecker = new HealthChecker();
    $issues = $healthChecker->runHealthCheck();
    
    $timestamp = date('Y-m-d H:i:s');
    
    if (empty($issues)) {
        echo "[$timestamp] Health check: All systems normal\n";
        file_put_contents(__DIR__ . '/../logs/cron.log', "[$timestamp] Health check: All systems normal\n", FILE_APPEND);
    } else {
        $issueCount = count($issues);
        echo "[$timestamp] Health check: Found $issueCount issues\n";
        
        foreach ($issues as $issue) {
            echo "  - [{$issue['severity']}] {$issue['message']}\n";
        }
        
        // Send alerts for critical issues
        $healthChecker->sendAlerts();
        
        file_put_contents(__DIR__ . '/../logs/cron.log', "[$timestamp] Health check: Found $issueCount issues\n", FILE_APPEND);
    }
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    $error = "[$timestamp] Health check error: " . $e->getMessage() . "\n";
    echo $error;
    file_put_contents(__DIR__ . '/../logs/cron.log', $error, FILE_APPEND);
}
