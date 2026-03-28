<?php
require 'includes/db_connect.php';

try {
    // Check if table exists and column type is DATE
    $stmt = $conn->query("DESCRIBE student_attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        if ($col['Field'] == 'attendance_date' && stripos($col['Type'], 'date') !== false) {
            $conn->exec("ALTER TABLE student_attendance MODIFY COLUMN attendance_date VARCHAR(10) NOT NULL");
            echo "Modified attendance_date to VARCHAR(10).";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>