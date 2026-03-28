<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];

// Build Query based on Filter
$sql = "SELECT roll_no, symbol_no, full_name, class, caste, gender, parent_contact, guardian_contact, address 
        FROM students WHERE school_id = ? AND caste IS NOT NULL AND caste != ''";
$params = [$school_id];

if (isset($_GET['class']) && !empty($_GET['class'])) {
    $sql .= " AND class = ?";
    $params[] = $_GET['class'];
}

if (isset($_GET['caste_filter']) && !empty($_GET['caste_filter'])) {
    $sql .= " AND caste LIKE ?";
    $params[] = "%" . $_GET['caste_filter'] . "%";
}

$sql .= " ORDER BY class ASC, caste ASC, full_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set Headers for CSV Download
$filename = "caste_based_report_" . date('Ymd') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Add Column Headers
$headers = [
    'Roll No',
    'Symbol No',
    'Full Name',
    'Class',
    'Caste (Extracted)',
    'Gender',
    'Parent Phone',
    'Guardian Phone',
    'Address'
];
fputcsv($output, $headers);

// Add Rows
foreach ($students as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>