<?php
require 'includes/db_connect.php';
try {
    $stmt = $conn->query("DESCRIBE teachers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>