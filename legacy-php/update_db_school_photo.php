<?php
require 'includes/db_connect.php';

try {
    // Check if column exists
    $stmt = $conn->query("SHOW COLUMNS FROM schools LIKE 'school_photo'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE schools ADD COLUMN school_photo VARCHAR(255) DEFAULT NULL");
        echo "Column 'school_photo' added successfully.";
    } else {
        echo "Column 'school_photo' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>