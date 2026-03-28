<?php
require 'includes/db_connect.php';

function getTableColumns($conn, $table)
{
    echo "<h3>Table: $table</h3>";
    try {
        $stmt = $conn->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
    } catch (PDOException $e) {
        echo "Error describing $table: " . $e->getMessage() . "<br>";
    }
}

try {
    getTableColumns($conn, 'students');
    getTableColumns($conn, 'teachers');
    getTableColumns($conn, 'schools');
    getTableColumns($conn, 'admins');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>