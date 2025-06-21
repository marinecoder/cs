<?php

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        require_once __DIR__.'/../../config.php';
        
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        if($this->connection->connect_error) {
            throw new Exception('Database connection failed: ' . $this->connection->connect_error);
        }
        
        $this->connection->set_charset($charset);
        
        // Set SQL mode for better compatibility
        $this->connection->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    }
    
    public static function getInstance(): Database {
        if(self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection(): mysqli {
        return $this->connection;
    }
    
    public function query(string $sql, array $params = []): mysqli_result|bool {
        $stmt = $this->connection->prepare($sql);
        
        if(!$stmt) {
            throw new Exception('Query preparation failed: ' . $this->connection->error);
        }
        
        if(!empty($params)) {
            $types = '';
            foreach($params as $param) {
                if(is_int($param)) {
                    $types .= 'i';
                } elseif(is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            
            $stmt->bind_param($types, ...$params);
        }
        
        if(!$stmt->execute()) {
            throw new Exception('Query execution failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result;
    }
    
    public function insert(string $table, array $data): int {
        $columns = implode(',', array_keys($data));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->connection->insert_id;
    }
    
    public function update(string $table, array $data, string $where, array $whereParams = []): bool {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        $result = $this->query($sql, $params);
        
        return $this->connection->affected_rows > 0;
    }
    
    public function delete(string $table, string $where, array $whereParams = []): bool {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $result = $this->query($sql, $whereParams);
        
        return $this->connection->affected_rows > 0;
    }
    
    public function escape(string $value): string {
        return $this->connection->real_escape_string($value);
    }
    
    public function beginTransaction(): bool {
        return $this->connection->begin_transaction();
    }
    
    public function commit(): bool {
        return $this->connection->commit();
    }
    
    public function rollback(): bool {
        return $this->connection->rollback();
    }
    
    public function getLastInsertId(): int {
        return $this->connection->insert_id;
    }
    
    public function getAffectedRows(): int {
        return $this->connection->affected_rows;
    }
}
