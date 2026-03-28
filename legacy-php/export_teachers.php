<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];

// Build Query based on Filter
$sql = "SELECT full_name, subject, contact, teacher_type, tah, attendance_date_nepali, address, pan_no, blood_group, citizenship_no, created_at 
        FROM teachers WHERE school_id = ?";
$params = [$school_id];

if (isset($_GET['type_filter']) && !empty($_GET['type_filter'])) {
    $sql .= " AND teacher_type = ?";
    $params[] = $_GET['type_filter'];
}

$sql .= " ORDER BY full_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set Headers for CSV Download
$filename = "teachers_export_" . date('Ymd') . ".csv";

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
    'Full Name',
    'Subject',
    'Contact Phone',
    'Teacher Type',
    'Level (Tah)',
    'Attendance Date (BS)',
    'Address',
    'PAN No',
    'Blood Group',
    'Citizenship No',
    'Registered Date'
];
fputcsv($output, $headers);

// Add Rows
foreach ($teachers as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>