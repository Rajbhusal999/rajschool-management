<?php
require 'includes/db_connect.php';

try {
    // 1. Schools Table (Users of the system)
    $sql = "CREATE TABLE IF NOT EXISTS schools (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        school_name VARCHAR(100) NOT NULL,
        emis_code VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        subscription_status ENUM('inactive', 'pending', 'active') DEFAULT 'inactive',
        subscription_plan VARCHAR(20) DEFAULT NULL,
        subscription_expiry DATE DEFAULT NULL,
        payment_verification_code VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'schools' created successfully.<br>";

    // 2. Admin Table (Super Admin)
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'admins' created successfully.<br>";

    // Create default admin if not exists (User: admin, Pass: admin123)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO admins (username, password_hash) VALUES ('admin', '$pass')";
        $conn->exec($sql);
        echo "Default admin user created (User: admin, Pass: admin123).<br>";
    }

    // 3. Students Table
    $sql = "CREATE TABLE IF NOT EXISTS students (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        school_id INT(11) UNSIGNED NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        roll_no VARCHAR(20),
        class VARCHAR(20),
        parent_contact VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Table 'students' created successfully.<br>";

    // 4. Teachers Table
    $sql = "CREATE TABLE IF NOT EXISTS teachers (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        school_id INT(11) UNSIGNED NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        subject VARCHAR(50),
        contact VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Table 'teachers' created successfully.<br>";

    // 5. Student Attendance Table
    $sql = "CREATE TABLE IF NOT EXISTS student_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        student_id INT NOT NULL, 
        class VARCHAR(50) NOT NULL,
        attendance_date DATE NOT NULL,
        status ENUM('Present', 'Absent', 'Late', 'Excused') DEFAULT 'Present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_attendance (school_id, student_id, attendance_date)
    )";
    $conn->exec($sql);
    echo "Table 'student_attendance' created successfully.<br>";

    // 6. Exam Schedules Table
    $sql = "CREATE TABLE IF NOT EXISTS exam_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT,
        class VARCHAR(50),
        exam_type VARCHAR(50),
        year VARCHAR(10),
        shift VARCHAR(50),
        time VARCHAR(50),
        subject_data TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'exam_schedules' created successfully.<br>";

    // 7. Student Receipts Table
    $sql = "CREATE TABLE IF NOT EXISTS student_receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        receipt_no INT NOT NULL,
        student_id INT,
        student_name VARCHAR(255) DEFAULT NULL,
        total_amount DECIMAL(10,2) DEFAULT NULL,
        topics TEXT DEFAULT NULL,
        receipt_date VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'student_receipts' created successfully.<br>";

    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>