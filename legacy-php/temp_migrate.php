<?php
require 'includes/db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS exam_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT,
    class VARCHAR(50),
    exam_type VARCHAR(50),
    year VARCHAR(10),
    shift VARCHAR(50),
    time VARCHAR(50),
    subject_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $conn->exec($sql);
    echo "TABLE_CREATED_SUCCESSFULLY";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>