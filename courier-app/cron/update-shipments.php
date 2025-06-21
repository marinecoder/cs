#!/usr/bin/env php
<?php
/**
 * Automatic Shipment Status Updates
 * Updates shipment statuses based on business rules
 * Run every 6 hours: php /path/to/courier-app/cron/update-shipments.php
 */

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/EmailService.php';

class ShipmentUpdater {
    private $db;
    private $emailService;

    public function __construct() {
        $this->db = new Database();
        $this->emailService = new EmailService();
    }

    public function processUpdates(): int {
        $updated = 0;
        
        // Auto-confirm pending shipments after 1 hour
        $updated += $this->autoConfirmShipments();
        
        // Auto-pickup confirmed shipments after 24 hours
        $updated += $this->autoPickupShipments();
        
        // Mark overdue shipments
        $updated += $this->markOverdueShipments();
        
        // Send delivery reminders
        $this->sendDeliveryReminders();
        
        return $updated;
    }

    private function autoConfirmShipments(): int {
        $sql = "SELECT id, tracking_number FROM shipments 
                WHERE status = 'pending' 
                AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $shipments = $this->db->query($sql);
        $count = 0;
        
        while ($shipment = $shipments->fetch_assoc()) {
            // Update status
            $updateSql = "UPDATE shipments SET status = 'confirmed' WHERE id = ?";
            $this->db->query($updateSql, [$shipment['id']]);
            
            // Add progress entry
            $progressSql = "INSERT INTO shipment_progress (shipment_id, status, description) 
                           VALUES (?, 'confirmed', 'Shipment automatically confirmed')";
            $this->db->query($progressSql, [$shipment['id']]);
            
            // Send notification
            $this->emailService->sendShipmentStatusUpdate($shipment['id'], 'confirmed');
            
            $count++;
        }
        
        return $count;
    }

    private function autoPickupShipments(): int {
        $sql = "SELECT id, tracking_number FROM shipments 
                WHERE status = 'confirmed' 
                AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $shipments = $this->db->query($sql);
        $count = 0;
        
        while ($shipment = $shipments->fetch_assoc()) {
            // Update status
            $updateSql = "UPDATE shipments SET status = 'picked_up' WHERE id = ?";
            $this->db->query($updateSql, [$shipment['id']]);
            
            // Add progress entry
            $progressSql = "INSERT INTO shipment_progress (shipment_id, status, description) 
                           VALUES (?, 'picked_up', 'Package picked up by courier')";
            $this->db->query($progressSql, [$shipment['id']]);
            
            // Send notification
            $this->emailService->sendShipmentStatusUpdate($shipment['id'], 'picked_up');
            
            $count++;
        }
        
        return $count;
    }

    private function markOverdueShipments(): int {
        // Mark shipments as overdue if they're in transit for more than 7 days
        $sql = "SELECT id, tracking_number FROM shipments 
                WHERE status IN ('picked_up', 'in_transit') 
                AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND id NOT IN (
                    SELECT shipment_id FROM shipment_progress 
                    WHERE status = 'overdue'
                )";
        
        $shipments = $this->db->query($sql);
        $count = 0;
        
        while ($shipment = $shipments->fetch_assoc()) {
            // Add overdue status to progress (don't change main status)
            $progressSql = "INSERT INTO shipment_progress (shipment_id, status, description) 
                           VALUES (?, 'overdue', 'Shipment is overdue for delivery')";
            $this->db->query($progressSql, [$shipment['id']]);
            
            $count++;
        }
        
        return $count;
    }

    private function sendDeliveryReminders(): void {
        // Send reminders for shipments that should be delivered today
        $sql = "SELECT s.id, s.tracking_number, u.email, u.name 
                FROM shipments s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.status = 'out_for_delivery'
                AND DATE(s.expected_delivery_date) = CURDATE()
                AND s.id NOT IN (
                    SELECT shipment_id FROM shipment_progress 
                    WHERE status = 'delivery_reminder' 
                    AND DATE(timestamp) = CURDATE()
                )";
        
        $shipments = $this->db->query($sql);
        
        while ($shipment = $shipments->fetch_assoc()) {
            // Send reminder email
            $subject = "Delivery Today - " . $shipment['tracking_number'];
            $body = "Your package {$shipment['tracking_number']} is scheduled for delivery today. Please ensure someone is available to receive it.";
            
            $this->emailService->queueEmail($shipment['email'], $subject, $body);
            
            // Mark reminder sent
            $progressSql = "INSERT INTO shipment_progress (shipment_id, status, description) 
                           VALUES (?, 'delivery_reminder', 'Delivery reminder sent')";
            $this->db->query($progressSql, [$shipment['id']]);
        }
    }
}

try {
    $updater = new ShipmentUpdater();
    $updated = $updater->processUpdates();
    
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] Shipment updater: Processed $updated shipments\n";
    
    file_put_contents(__DIR__ . '/../logs/cron.log', "[$timestamp] Shipment updater: Processed $updated shipments\n", FILE_APPEND);
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    $error = "[$timestamp] Shipment updater error: " . $e->getMessage() . "\n";
    echo $error;
    file_put_contents(__DIR__ . '/../logs/cron.log', $error, FILE_APPEND);
}
