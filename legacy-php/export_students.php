<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];

// Build Query based on Filter
// Build Query based on Filter
$sql = "SELECT roll_no, symbol_no, full_name, emis_no, caste, class, dob_nepali, gender, 
          father_name, mother_name, guardian_name, parent_contact, guardian_contact,
          perm_province, perm_district, perm_local_level, perm_ward_no, perm_tole,
          temp_province, temp_district, temp_local_level, temp_ward_no, temp_tole,
          scholarship_type, disability_type, created_at 
          FROM students WHERE school_id = ?";
$params = [$school_id];

if (isset($_GET['class']) && !empty($_GET['class'])) {
    $sql .= " AND class = ?";
    $params[] = $_GET['class'];
}

$sql .= " ORDER BY class ASC, roll_no ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set Headers for CSV Download
$filename = "students_export_" . date('Ymd') . ".csv";

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
    'EMIS No',
    'Caste',
    'Class',
    'DOB (BS)',
    'Gender',
    'Father Name',
    'Mother Name',
    'Guardian Name',
    'Parent Phone',
    'Guardian Phone',
    'Perm Province',
    'Perm District',
    'Perm Local Lvl',
    'Perm Ward',
    'Perm Tole',
    'Temp Province',
    'Temp District',
    'Temp Local Lvl',
    'Temp Ward',
    'Temp Tole',
    'Scholarship',
    'Disability',
    'Registered Date'
];
fputcsv($output, $headers);

// Add Rows
foreach ($students as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>