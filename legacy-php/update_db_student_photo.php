<?php
require 'includes/db_connect.php';

try {
    // Check if column exists, if not add it
    $sql = "SHOW COLUMNS FROM students LIKE 'student_photo'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $sql = "ALTER TABLE students ADD COLUMN student_photo VARCHAR(255) DEFAULT NULL";
        $conn->exec($sql);
        echo "Column 'student_photo' added successfully.";
    } else {
        echo "Column 'student_photo' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>