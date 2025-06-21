#!/usr/bin/env php
<?php
/**
 * Email Queue Processor
 * Processes queued emails and sends them
 * Run every 5 minutes: */5 * * * * php /path/to/courier-app/cron/process-emails.php
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/EmailService.php';

try {
    $emailService = new EmailService();
    
    // Process up to 50 emails per run
    $sent = $emailService->processEmailQueue(50);
    
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] Processed $sent emails\n";
    
    // Log to file
    file_put_contents(__DIR__ . '/../logs/cron.log', "[$timestamp] Email processor: Sent $sent emails\n", FILE_APPEND);
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    $error = "[$timestamp] Email processor error: " . $e->getMessage() . "\n";
    echo $error;
    file_put_contents(__DIR__ . '/../logs/cron.log', $error, FILE_APPEND);
}
