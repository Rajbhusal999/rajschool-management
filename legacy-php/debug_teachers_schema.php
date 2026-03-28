<?php
require 'includes/db_connect.php';
try {
    // Get table structure
    $stmt = $conn->query("DESCRIBE teachers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Teachers Table Structure:\n";
    echo str_repeat("=", 80) . "\n";
    foreach ($columns as $col) {
        printf(
            "%-25s %-15s %-10s %-10s %-10s %s\n",
            $col['Field'],
            $col['Type'],
            $col['Null'],
            $col['Key'],
            $col['Default'] ?? 'NULL',
            $col['Extra']
        );
    }

    echo "\n\nChecking for existing empty employee_id values:\n";
    echo str_repeat("=", 80) . "\n";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM teachers WHERE employee_id = '' OR employee_id IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Rows with empty/NULL employee_id: " . $result['count'] . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>