<?php
require 'includes/db_connect.php';

try {
    $stmt = $conn->query("DESCRIBE teachers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>