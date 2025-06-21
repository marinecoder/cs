<?php

class EmailService {
    private $db;
    private $config;

    public function __construct() {
        $this->db = new Database();
        $this->config = require_once __DIR__ . '/../../config/app.php';
    }

    /**
     * Queue an email for sending
     */
    public function queueEmail(string $to, string $subject, string $body): bool {
        $sql = "INSERT INTO email_queue (to_email, subject, body) VALUES (?, ?, ?)";
        $result = $this->db->query($sql, [$to, $subject, $body]);
        return $result !== false;
    }

    /**
     * Send queued emails
     */
    public function processEmailQueue(int $limit = 10): int {
        $sql = "SELECT * FROM email_queue WHERE status = 'pending' AND attempts < 3 ORDER BY created_at ASC LIMIT ?";
        $emails = $this->db->query($sql, [$limit]);
        
        $sent = 0;
        while ($email = $emails->fetch_assoc()) {
            if ($this->sendEmail($email['to_email'], $email['subject'], $email['body'])) {
                $this->markEmailSent($email['id']);
                $sent++;
            } else {
                $this->incrementEmailAttempts($email['id']);
            }
        }
        
        return $sent;
    }

    /**
     * Send email using SMTP
     */
    private function sendEmail(string $to, string $subject, string $body): bool {
        if (!$this->config['email']['smtp_enabled'] ?? false) {
            return false;
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->config['email']['from_email'],
            'Reply-To: ' . $this->config['email']['from_email'],
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Mark email as sent
     */
    private function markEmailSent(int $emailId): void {
        $sql = "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?";
        $this->db->query($sql, [$emailId]);
    }

    /**
     * Increment email attempts
     */
    private function incrementEmailAttempts(int $emailId): void {
        $sql = "UPDATE email_queue SET attempts = attempts + 1 WHERE id = ?";
        $this->db->query($sql, [$emailId]);
        
        // Mark as failed if max attempts reached
        $sql = "UPDATE email_queue SET status = 'failed' WHERE id = ? AND attempts >= 3";
        $this->db->query($sql, [$emailId]);
    }

    /**
     * Send shipment status update email
     */
    public function sendShipmentStatusUpdate(int $shipmentId, string $status): bool {
        $sql = "SELECT s.*, u.email, u.name FROM shipments s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.id = ?";
        $result = $this->db->query($sql, [$shipmentId]);
        $shipment = $result->fetch_assoc();

        if (!$shipment) {
            return false;
        }

        $subject = "Shipment Update - " . $shipment['tracking_number'];
        $body = $this->getShipmentUpdateEmailTemplate($shipment, $status);
        
        return $this->queueEmail($shipment['email'], $subject, $body);
    }

    /**
     * Send welcome email to new users
     */
    public function sendWelcomeEmail(string $email, string $name): bool {
        $subject = "Welcome to " . ($this->config['app']['name'] ?? 'Courier App');
        $body = $this->getWelcomeEmailTemplate($name);
        
        return $this->queueEmail($email, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $email, string $resetToken): bool {
        $subject = "Password Reset Request";
        $body = $this->getPasswordResetEmailTemplate($resetToken);
        
        return $this->queueEmail($email, $subject, $body);
    }

    /**
     * Get shipment update email template
     */
    private function getShipmentUpdateEmailTemplate(array $shipment, string $status): string {
        $statusMessages = [
            'confirmed' => 'Your shipment has been confirmed and is being prepared.',
            'picked_up' => 'Your package has been picked up by our courier.',
            'in_transit' => 'Your package is on its way to the destination.',
            'out_for_delivery' => 'Your package is out for delivery today.',
            'delivered' => 'Your package has been successfully delivered.',
            'cancelled' => 'Your shipment has been cancelled.',
        ];

        $message = $statusMessages[$status] ?? 'Your shipment status has been updated.';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Shipment Update</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .tracking-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .status-badge { 
                    display: inline-block; 
                    padding: 5px 15px; 
                    background: #10b981; 
                    color: white; 
                    border-radius: 20px; 
                    font-size: 12px; 
                    text-transform: uppercase; 
                }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Shipment Update</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$shipment['sender_name']},</h2>
                    <p>{$message}</p>
                    
                    <div class='tracking-info'>
                        <h3>Shipment Details</h3>
                        <p><strong>Tracking Number:</strong> {$shipment['tracking_number']}</p>
                        <p><strong>Status:</strong> <span class='status-badge'>{$status}</span></p>
                        <p><strong>From:</strong> {$shipment['sender_city']}</p>
                        <p><strong>To:</strong> {$shipment['receiver_city']}</p>
                    </div>
                    
                    <p>You can track your shipment anytime by visiting our website and entering your tracking number.</p>
                </div>
                <div class='footer'>
                    <p>Thank you for choosing our courier service!</p>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get welcome email template
     */
    private function getWelcomeEmailTemplate(string $name): string {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3b82f6; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .features { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Our Courier Service!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$name},</h2>
                    <p>Thank you for joining our courier service! We're excited to help you with all your shipping needs.</p>
                    
                    <div class='features'>
                        <h3>What you can do:</h3>
                        <ul>
                            <li>Create and manage shipments</li>
                            <li>Track packages in real-time</li>
                            <li>View delivery history</li>
                            <li>Manage your profile and preferences</li>
                        </ul>
                    </div>
                    
                    <p>Get started by logging into your account and creating your first shipment!</p>
                </div>
                <div class='footer'>
                    <p>If you have any questions, feel free to contact our support team.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get password reset email template
     */
    private function getPasswordResetEmailTemplate(string $resetToken): string {
        $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $resetToken;
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ef4444; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .reset-button { 
                    display: inline-block; 
                    padding: 12px 25px; 
                    background: #3b82f6; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 15px 0; 
                }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset Request</h1>
                </div>
                <div class='content'>
                    <p>You have requested to reset your password. Click the button below to reset it:</p>
                    
                    <p><a href='{$resetUrl}' class='reset-button'>Reset Password</a></p>
                    
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p>{$resetUrl}</p>
                    
                    <p><strong>Note:</strong> This link will expire in 1 hour for security reasons.</p>
                    
                    <p>If you didn't request this password reset, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
