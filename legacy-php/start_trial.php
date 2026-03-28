<?php
session_start();
require 'includes/db_connect.php';

// Define Demo School Credentials
$demo_email = 'demo@smartvidhyalaya.com';
$demo_password = 'demo'; // We will hash this
$demo_school_name = 'Smart Demo School';

// Check if Demo School Exists and Delete it for a fresh start
$stmt = $conn->prepare("SELECT id FROM schools WHERE email = ?");
$stmt->execute([$demo_email]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $existing_id = $existing['id'];
    // Delete associated data
    // Handle specific table deletions inside try-catch to avoid stopping on error if tables don't exist
    try {
        $conn->prepare("DELETE FROM students WHERE school_id = ?")->execute([$existing_id]);
        $conn->prepare("DELETE FROM teachers WHERE school_id = ?")->execute([$existing_id]);
        // Add other tables as needed if you want complete cleanup
    } catch (Exception $e) {
        // Ignore key constraint errors for now or log them
    }
    // Delete the school
    $conn->prepare("DELETE FROM schools WHERE id = ?")->execute([$existing_id]);
}

// Create Fresh Demo School
$hashed_password = password_hash($demo_password, PASSWORD_DEFAULT);
$emis_code = 'DEMO' . rand(100, 999); // Randomize EMIS to avoid conflict if any
$address = 'Kathmandu, Nepal';
$phone = '9800000000';
$expiry_date = date('Y-m-d', strtotime('+1 day'));

// Insert into DB
$sql = "INSERT INTO schools (school_name, emis_code, email, phone, address, password_hash, subscription_status, subscription_plan, subscription_expiry) 
        VALUES (?, ?, ?, ?, ?, ?, 'active', 'Trial (1 Day)', ?)";
$stmt = $conn->prepare($sql);

if ($stmt->execute([$demo_school_name, $emis_code, $demo_email, $phone, $address, $hashed_password, $expiry_date])) {
    $school_id = $conn->lastInsertId();

    // Add some dummy data
    // 1. Add a dummy teacher
    // 1. Add a dummy teacher
    // Based on teachers.php schema: full_name, subject, contact, teacher_type, attendance_date_nepali, address...
    $conn->query("INSERT INTO teachers (school_id, full_name, subject, contact, teacher_type, attendance_date_nepali, address) 
                  VALUES ($school_id, 'Demo Teacher', 'Science', '9800000001', 'Permanent', '2080-01-01', 'Kathmandu')");

    // 2. Add a dummy student
    $conn->query("INSERT INTO students (school_id, full_name, roll_no, class, father_name, parent_contact, perm_province, gender) VALUES ($school_id, 'Demo Student', '1', '10', 'Demo Parent', '9800000002', 'Bagmati', 'male')");

} else {
    $err = $stmt->errorInfo();
    die("Error creating demo account: " . $err[2]);
}

// Log user in as Demo User
$_SESSION['user_id'] = $school_id;
$_SESSION['school_name'] = $demo_school_name;
$_SESSION['subscription_status'] = 'active'; // Force active for demo
$_SESSION['is_demo'] = true; // Set Demo Flag

// Redirect to Dashboard
header("Location: dashboard.php");
exit();
?>