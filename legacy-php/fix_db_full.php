<?php
require 'includes/db_connect.php';

function checkAndFixColumn($conn, $table, $columnName, $columnDef, $oldColumnName = null)
{
    echo "Checking column '$columnName' in table '$table'...<br>";

    // Check if column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$columnName]);
    $exists = $stmt->fetch();

    if ($exists) {
        echo " - Column '$columnName' exists. OK.<br>";
        return;
    }

    // Check if old column exists (for renaming)
    if ($oldColumnName) {
        $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$oldColumnName]);
        $oldExists = $stmt->fetch();

        if ($oldExists) {
            echo " - Found old column '$oldColumnName'. Renaming to '$columnName'...<br>";
            try {
                $conn->exec("ALTER TABLE `$table` CHANGE `$oldColumnName` `$columnName` $columnDef");
                echo " - Renamed successfully.<br>";
                return;
            } catch (PDOException $e) {
                echo " - Error renaming: " . $e->getMessage() . "<br>";
            }
        }
    }

    // New column creation
    echo " - Column '$columnName' missing. Adding...<br>";
    try {
        $conn->exec("ALTER TABLE `$table` ADD `$columnName` $columnDef");
        echo " - Added successfully.<br>";
    } catch (PDOException $e) {
        echo " - Error adding column: " . $e->getMessage() . "<br>";
    }
}

try {
    echo "<h1>Database Integrity Check & Repair</h1>";

    // --- 1. Fix Students Table ---
    echo "<h3>Students Table</h3>";
    // Check school_id
    checkAndFixColumn($conn, 'students', 'school_id', "INT(11) UNSIGNED NOT NULL AFTER id");
    // Check full_name (rename from name if exists)
    checkAndFixColumn($conn, 'students', 'full_name', "VARCHAR(100) NOT NULL", 'name');
    checkAndFixColumn($conn, 'students', 'roll_no', "VARCHAR(20)");
    checkAndFixColumn($conn, 'students', 'class', "VARCHAR(20)");
    checkAndFixColumn($conn, 'students', 'parent_contact', "VARCHAR(20)");
    checkAndFixColumn($conn, 'students', 'address', "TEXT");
    checkAndFixColumn($conn, 'students', 'created_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

    // --- 2. Fix Teachers Table ---
    echo "<h3>Teachers Table</h3>";
    // Check school_id
    checkAndFixColumn($conn, 'teachers', 'school_id', "INT(11) UNSIGNED NOT NULL AFTER id");
    // Check full_name (rename from name if exists)
    checkAndFixColumn($conn, 'teachers', 'full_name', "VARCHAR(100) NOT NULL", 'name');
    checkAndFixColumn($conn, 'teachers', 'subject', "VARCHAR(50)");
    checkAndFixColumn($conn, 'teachers', 'contact', "VARCHAR(20)");
    checkAndFixColumn($conn, 'teachers', 'created_at', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

    // --- 3. Foreign Keys ---
    echo "<h3>Foreign Keys</h3>";

    function addForeignKey($conn, $table, $id, $column, $refTable, $refColumn)
    {
        // Simple check: try to add logic. If fails, assume exists or data mismatch
        try {
            $constraintName = "fk_{$table}_{$refTable}";
            // Check if constraint exists (MySQL specific query)
            $check = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table' AND CONSTRAINT_NAME = '$constraintName' AND TABLE_SCHEMA = DATABASE()");
            if ($check->rowCount() == 0) {
                $conn->exec("ALTER TABLE `$table` ADD CONSTRAINT `$constraintName` FOREIGN KEY (`$column`) REFERENCES `$refTable`(`$refColumn`) ON DELETE CASCADE");
                echo " - Added foreign key: $constraintName<br>";
            } else {
                echo " - Foreign key $constraintName already exists.<br>";
            }
        } catch (Exception $e) {
            echo " - Could not add foreign key to $table: " . $e->getMessage() . "<br>";
        }
    }

    addForeignKey($conn, 'students', 'id', 'school_id', 'schools', 'id');
    addForeignKey($conn, 'teachers', 'id', 'school_id', 'schools', 'id');

    echo "<br><strong>Repair Completed Successfully.</strong>";

} catch (PDOException $e) {
    echo "CRITICAL ERROR: " . $e->getMessage();
}
?>