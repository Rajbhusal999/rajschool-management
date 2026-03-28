<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$school_id = $_SESSION['user_id'];
$class = isset($_GET['class']) ? $_GET['class'] : '';

if (empty($class)) {
    echo json_encode([]);
    exit();
}

function getClassGroup($class)
{
    if (strtoupper($class) == 'PG')
        return 'PG';
    if (strtoupper($class) == 'LKG')
        return 'LKG';
    if (strtoupper($class) == 'NURSERY')
        return 'NURSERY';

    $class_num = intval($class);
    if ($class_num >= 1 && $class_num <= 3)
        return '1-3';
    if ($class_num >= 4 && $class_num <= 5)
        return '4-5';
    if ($class_num >= 6 && $class_num <= 8)
        return '6-8';
    if ($class_num >= 9 && $class_num <= 10)
        return '9-10';

    return '1-3'; // Default
}

$class_group = getClassGroup($class);

$sql = "SELECT subject_name FROM subjects WHERE school_id = ? AND class_group = ? ORDER BY subject_name";
$stmt = $conn->prepare($sql);
$stmt->execute([$school_id, $class_group]);
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($subjects);
