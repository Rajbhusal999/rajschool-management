<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['subscription_status'] != 'active') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$class = isset($_GET['class']) ? $_GET['class'] : '';

if (empty($class)) {
    echo json_encode(['success' => false, 'message' => 'Class not specified']);
    exit();
}

try {
    $sql = "SELECT id, full_name, symbol_no FROM students 
            WHERE school_id = ? AND class = ? 
            ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>