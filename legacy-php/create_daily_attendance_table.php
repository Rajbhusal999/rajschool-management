<?php
require 'includes/db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS student_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        student_id INT NOT NULL,
        class VARCHAR(50) NOT NULL,
        attendance_date DATE NOT NULL,
        status ENUM('Present', 'Absent', 'Late', 'Excused') DEFAULT 'Present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_attendance (school_id, student_id, attendance_date)
    )";
    $conn->exec($sql);
    echo "Table 'student_attendance' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>