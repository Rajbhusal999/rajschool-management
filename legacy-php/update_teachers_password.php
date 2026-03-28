<?php
require 'includes/db_connect.php';
try {
    $conn->exec("ALTER TABLE teachers ADD COLUMN teacher_password VARCHAR(255) DEFAULT NULL");
    echo "Column 'teacher_password' added successfully.";
} catch (PDOException $e) {
    echo "Column likely exists or error: " . $e->getMessage();
}
?>