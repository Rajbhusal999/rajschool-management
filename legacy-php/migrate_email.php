<?php
// Simple Migration Script
require 'includes/db_connect.php';

try {
    // Check if column exists
    $stmt = $conn->query("SHOW COLUMNS FROM students LIKE 'guardian_email'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE students ADD COLUMN guardian_email VARCHAR(100) DEFAULT NULL AFTER guardian_contact";
        $conn->exec($sql);
        echo "Micro-migration: Added 'guardian_email' column to 'students' table.\n";
    } else {
        echo "Column 'guardian_email' already exists.\n";
    }
} catch (PDOException $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
?>