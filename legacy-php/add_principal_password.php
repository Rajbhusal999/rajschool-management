<?php
require 'includes/db_connect.php';

try {
    // Add principal_password column to schools table
    $conn->exec("ALTER TABLE schools ADD COLUMN principal_password VARCHAR(255) DEFAULT NULL");
    echo "Column 'principal_password' added to 'schools' table.";
} catch (PDOException $e) {
    echo "Column likely exists or error: " . $e->getMessage();
}
?>