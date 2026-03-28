<?php
require 'includes/db_connect.php';

try {
    echo "<h3>Table Structure for 'schools'</h3>";
    $stmt = $conn->query("DESCRIBE schools");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";

    echo "<h3>Existing Emails in 'schools' table</h3>";
    $stmt = $conn->query("SELECT id, school_name, email FROM schools");
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($emails) > 0) {
        echo "<table border='1'><tr><th>ID</th><th>School Name</th><th>Email</th></tr>";
        foreach ($emails as $row) {
            echo "<tr><td>" . $row['id'] . "</td><td>" . htmlspecialchars($row['school_name']) . "</td><td>" . htmlspecialchars($row['email']) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "No schools found.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>