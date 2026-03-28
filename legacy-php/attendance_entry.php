<?php
require 'includes/auth_school.php';
restrictFeature('attendance');
require 'includes/db_connect.php';
require_once 'includes/sms.php';
require_once 'includes/email_helper.php';

$school_id = $_SESSION['user_id'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// ---------------------------------------------------------
// SCHEMA MIGRATION (Ensure Table Exists)
// ---------------------------------------------------------
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS student_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        student_id INT NOT NULL,
        class VARCHAR(50) NOT NULL,
        attendance_date VARCHAR(10) NOT NULL, -- Supports Nepali Date (YYYY-MM-DD)
        session ENUM('Morning', 'Evening') DEFAULT 'Morning',
        status ENUM('Present', 'Absent', 'Leave') DEFAULT 'Present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_attendance (school_id, student_id, attendance_date, session)
    )");

    // Auto-migration for existing table (if created without session)
    // Check if session column exists
    $stmt = $conn->query("SHOW COLUMNS FROM student_attendance LIKE 'session'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE student_attendance ADD COLUMN session ENUM('Morning', 'Evening') DEFAULT 'Morning' AFTER attendance_date");
        // Update unique key
        $conn->exec("ALTER TABLE student_attendance DROP INDEX unique_attendance");
        $conn->exec("ALTER TABLE student_attendance ADD UNIQUE KEY unique_attendance (school_id, student_id, attendance_date, session)");
    }

    // Update status enum to include Leave if not present
    $conn->exec("ALTER TABLE student_attendance MODIFY COLUMN status ENUM('Present', 'Absent', 'Leave', 'Late', 'Excused') DEFAULT 'Present'"); // temporary expansion to avoid data loss
    // ideally we want strictly Present, Absent, Leave, but modifying enum on live data can be tricky. 
    // Let's set it to strict if we are sure, but safer to expand. 
    // User requested "in this format the attendance has present, absent, leave".
    // I will change the UI to these 3. I will expand DB to support 'Leave'.

} catch (PDOException $e) {
    // Table likely exists or permission issue
}


// Handle Logout/Change
if (isset($_GET['action']) && $_GET['action'] == 'change') {
    unset($_SESSION['att_verified']);
    unset($_SESSION['att_date']);
    unset($_SESSION['att_class']);
    unset($_SESSION['att_session']);
    unset($_SESSION['att_teacher_id']);
    header("Location: attendance_entry.php");
    exit();
}

// Handle Gate Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gate_login'])) {
    $date = str_replace('/', '-', $_POST['attendance_date']); // Normalize to YYYY-MM-DD
    $class = $_POST['class'];
    $session = $_POST['session'];
    $teacher_id = $_POST['teacher_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ? AND school_id = ?");
    $stmt->execute([$teacher_id, $school_id]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        if ($teacher['teacher_password'] === $password) {
            $_SESSION['att_verified'] = true;
            $_SESSION['att_date'] = $date;
            $_SESSION['att_class'] = $class;
            $_SESSION['att_session'] = $session;
            $_SESSION['att_teacher_id'] = $teacher_id;
            $_SESSION['att_teacher_name'] = $teacher['full_name'];
            header("Location: attendance_entry.php");
            exit();
        } else {
            $msg = "Invalid password!";
        }
    } else {
        $msg = "Teacher not found.";
    }
}


// ---------------------------------------------------------
// GATE VIEW (If not verified)
// ---------------------------------------------------------
if (!isset($_SESSION['att_verified'])) {
    // Fetch Teachers
    $teachers = $conn->query("SELECT id, full_name FROM teachers WHERE school_id = $school_id ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
    // Fetch Classes
    $classes = $conn->query("SELECT DISTINCT class FROM students WHERE school_id = $school_id")->fetchAll(PDO::FETCH_COLUMN);

    // Sort classes naturally (1, 2, 10...)
    natsort($classes);

    require_once 'includes/nepali_date_helper.php';
    $current_nepali_date = NepaliDateHelper::convertToNepali(date('Y-m-d'));
    $current_nepali_date_slashed = str_replace('-', '/', $current_nepali_date);

    // Custom sort to put Nursery, LKG, UKG at the beginning if present
    $pre_primary = ['Nursery', 'LKG', 'UKG', 'KG'];
    $sorted_classes = [];
    foreach ($pre_primary as $p) {
        if (($key = array_search($p, $classes)) !== false) {
            $sorted_classes[] = $p;
            unset($classes[$key]);
        }
    }
    $classes = array_merge($sorted_classes, $classes);

    $exam_names = [
        'first_terminal' => 'First Terminal Exam',
        'second_terminal' => 'Second Terminal Exam',
        'third_terminal' => 'Third Terminal Exam',
        'final' => 'Final Exam',
        'monthly' => 'Monthly Exam'
    ];
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Attendance Login - Smart विद्यालय</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Outfit', sans-serif;
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }

            .login-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                padding: 3rem 2.5rem;
                border-radius: 30px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 450px;
                border: 1px solid rgba(255, 255, 255, 0.5);
            }

            .brand-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #6366f1, #4f46e5);
                border-radius: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
                color: white;
                font-size: 2rem;
                box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            }

            .form-group {
                margin-bottom: 1.25rem;
            }

            .form-label {
                display: block;
                margin-bottom: 8px;
                color: #475569;
                font-weight: 700;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .form-control {
                width: 100%;
                padding: 14px 1rem;
                border: 2px solid #e2e8f0;
                border-radius: 16px;
                box-sizing: border-box;
                font-size: 1rem;
                font-weight: 500;
                transition: all 0.3s ease;
                background: #fcfdfe;
                font-family: 'Outfit', sans-serif;
            }

            .form-control:focus {
                outline: none;
                border-color: #6366f1;
                background: white;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            .btn-primary {
                width: 100%;
                background: linear-gradient(135deg, #6366f1, #4f46e5);
                color: white;
                padding: 16px;
                border: none;
                border-radius: 18px;
                font-weight: 800;
                font-size: 1.1rem;
                cursor: pointer;
                box-shadow: 0 10px 15px rgba(79, 70, 229, 0.2);
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                margin-top: 1rem;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 25px rgba(79, 70, 229, 0.3);
            }

            .back-link {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                margin-top: 2rem;
                color: #64748b;
                text-decoration: none;
                font-size: 0.95rem;
                font-weight: 600;
                transition: color 0.2s;
            }

            .back-link:hover {
                color: #4f46e5;
            }

            .error-alert {
                background: #fef2f2;
                color: #dc2626;
                padding: 1rem;
                border-radius: 16px;
                margin-bottom: 1.5rem;
                text-align: center;
                font-weight: 600;
                border: 1px solid #fee2e2;
                font-size: 0.9rem;
            }
        </style>
    </head>

    <body>
        <div class="login-card">
            <div class="brand-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h2 style="text-align: center; color: #0f172a; margin-bottom: 0.5rem; font-weight: 800; font-size: 1.75rem;">
                Attendance Portal</h2>
            <p style="text-align: center; color: #64748b; margin-bottom: 2rem; font-weight: 500;">Secure gateway for daily
                verification.</p>

            <?php if ($msg): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="gate_login" value="1">

                <div class="form-group">
                    <label class="form-label">Verification Date (BS)</label>
                    <input type="text" name="attendance_date" class="form-control nepali-date" required
                        placeholder="YYYY/MM/DD" pattern="[0-9]{4}/[0-9]{2}/[0-9]{2}"
                        value="<?php echo $current_nepali_date_slashed; ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Select Class</label>
                        <select name="class" class="form-control" required>
                            <option value="">Class</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Daily Session</label>
                        <select name="session" class="form-control" required>
                            <option value="Morning">Morning</option>
                            <option value="Evening">Evening</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Authorized Staff</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Select Official</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Staff Credentials</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-primary">Initialize Tracking</button>
            </form>
            <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Systems Dashboard</a>
        </div>

        <script>
            document.querySelectorAll('.nepali-date').forEach(input => {
                input.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 4) value = value.slice(0, 4) + '/' + value.slice(4);
                    if (value.length > 7) value = value.slice(0, 7) + '/' + value.slice(7);
                    e.target.value = value.slice(0, 10);
                });
            });
        </script>
    </body>

    </html>

    </html>
    <?php
    exit();
}

// ---------------------------------------------------------
// ATTENDANCE ENTRY VIEW (Verified)
// ---------------------------------------------------------
$selected_date = $_SESSION['att_date'];
$selected_class = $_SESSION['att_class'];
$selected_session = $_SESSION['att_session'];
$teacher_name = $_SESSION['att_teacher_name'];
$teacher_id = $_SESSION['att_teacher_id'];

// Handle Save
// Handle Save or Send SMS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['save_attendance']) || isset($_POST['send_sms_guardian']) || isset($_POST['send_email_guardian']))) {
    $attendance_data = $_POST['attendance'];
    $count = 0;

    // 1. Save Attendance First
    foreach ($attendance_data as $student_id => $status) {
        if (!empty($status)) {
            // Check if record exists
            $check_sql = "SELECT id FROM student_attendance WHERE school_id = ? AND student_id = ? AND attendance_date = ? AND session = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$school_id, $student_id, $selected_date, $selected_session]);

            if ($row = $check_stmt->fetch()) {
                $update_sql = "UPDATE student_attendance SET status = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->execute([$status, $row['id']]);
            } else {
                $insert_sql = "INSERT INTO student_attendance (school_id, student_id, attendance_date, session, status, class) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->execute([$school_id, $student_id, $selected_date, $selected_session, $status, $selected_class]);
            }
            $count++;
        }
    }
    $msg = "Attendance for $count students updated successfully!";

    // 2. Fetch Students Info (Name, Contact, Email)
    $student_info_map = [];
    $student_ids = array_keys($attendance_data);
    if (!empty($student_ids)) {
        $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
        $sql_s = "SELECT id, full_name, guardian_contact, guardian_email FROM students WHERE id IN ($placeholders)";
        $stmt_s = $conn->prepare($sql_s);
        $stmt_s->execute($student_ids);
        $fetched_students = $stmt_s->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fetched_students as $si) {
            $student_info_map[$si['id']] = $si;
        }
    }

    // 3. Send SMS if prompted
    if (isset($_POST['send_sms_guardian'])) {
        $sms_count = 0;
        // Logic already fetched students above, re-use $student_info_map
        if (!empty($student_ids)) {
            // (Removed redundant query)

            foreach ($attendance_data as $student_id => $status) {
                if (isset($student_info_map[$student_id]) && !empty($student_info_map[$student_id]['guardian_contact'])) {
                    $phone = $student_info_map[$student_id]['guardian_contact'];
                    $name = $student_info_map[$student_id]['full_name'];

                    // Determine Message
                    $message = "";
                    if ($selected_session == 'Morning') {
                        if ($status == 'Present') {
                            $message = "$name is at school";
                        } elseif ($status == 'Absent') {
                            $message = "$name is absent in school";
                        } elseif ($status == 'Leave') {
                            $message = "$name is leave on today";
                        }
                    } elseif ($selected_session == 'Evening') {
                        if ($status == 'Present') { // Attend
                            $message = "$name has left school";
                        } elseif ($status == 'Extra Class') {
                            $message = "$name is in extra class";
                        } elseif ($status == 'Leave') { // Just in case
                            $message = "$name is leave on today";
                        }
                    }

                    if (!empty($message)) {
                        send_sms_message($phone, $message);
                        $sms_count++;
                    }
                }
            }
        }
        $msg .= " SMS sent to $sms_count guardians.";
    }

    // 4. Send Email if prompted
    if (isset($_POST['send_email_guardian'])) {
        $email_count = 0;
        foreach ($attendance_data as $student_id => $status) {
            if (isset($student_info_map[$student_id]) && !empty($student_info_map[$student_id]['guardian_email'])) {
                $email = $student_info_map[$student_id]['guardian_email'];
                $name = $student_info_map[$student_id]['full_name'];

                // Determine Message (Reuse logic or keep separate)
                $message_text = "";
                $subject = "Attendance Notification - $selected_date";

                if ($selected_session == 'Morning') {
                    if ($status == 'Present')
                        $message_text = "$name is at school.";
                    elseif ($status == 'Absent')
                        $message_text = "$name is absent in school today.";
                    elseif ($status == 'Leave')
                        $message_text = "$name is on leave today.";
                } elseif ($selected_session == 'Evening') {
                    if ($status == 'Present')
                        $message_text = "$name has left school.";
                    elseif ($status == 'Extra Class')
                        $message_text = "$name is in extra class.";
                    elseif ($status == 'Leave')
                        $message_text = "$name is on leave.";
                }

                if (!empty($message_text)) {
                    $full_email_body = "
                        <h3>Attendance Update</h3>
                        <p>Dear Guardian,</p>
                        <p>$message_text</p>
                        <br>
                        <p>Date: $selected_date</p>
                        <p>Session: $selected_session</p>
                        <br>
                        <p>Regards,<br>Smart विद्यालय</p>
                    ";

                    if (sendCustomEmail($email, $name, $subject, $full_email_body)) {
                        $email_count++;
                    }
                }
            }
        }
        $msg .= " Email sent to $email_count guardians.";
    }
}

// Fetch Students
$students = [];
$sql = "SELECT s.id, s.full_name, s.symbol_no, a.status 
        FROM students s 
        LEFT JOIN student_attendance a ON s.id = a.student_id 
            AND a.attendance_date = ? AND a.session = ?
        WHERE s.school_id = ? AND s.class = ? 
        ORDER BY s.full_name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([$selected_date, $selected_session, $school_id, $selected_class]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance - <?php echo $selected_date; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
            --bg: #f8fafc;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 0;
            color: #1e293b;
        }

        .main-content {
            padding: 2.5rem;
            min-height: calc(100vh - 70px);
        }

        .attendance-card {
            background: white;
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            max-width: 1000px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
        }

        .info-bar {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #3730a3;
            padding: 1.5rem 2rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(79, 70, 229, 0.1);
        }

        .info-bar strong {
            font-size: 1.25rem;
            font-weight: 800;
            display: block;
            margin-bottom: 4px;
        }

        .info-bar small {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.8;
            letter-spacing: 0.5px;
        }

        .responsive-table-container {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-top: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #f8fafc;
            padding: 1.25rem 1rem;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1px;
            border-bottom: 2px solid #f1f5f9;
        }

        .table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #f8fafc;
        }

        .status-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .status-option {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: 700;
            font-size: 0.9rem;
            background: #fcfdfe;
        }

        .status-option:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
        }

        input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        /* Status colors */
        label:has(input[value='Present']:checked),
        label:has(input[value='Attend']:checked) {
            background: #ecfdf5;
            color: #065f46;
            border-color: #10b981;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.1);
        }

        label:has(input[value='Absent']:checked) {
            background: #fef2f2;
            color: #991b1b;
            border-color: #ef4444;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.1);
        }

        label:has(input[value='Leave']:checked) {
            background: #fffbeb;
            color: #92400e;
            border-color: #f59e0b;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.1);
        }

        label:has(input[value='Extra Class']:checked) {
            background: #eef2ff;
            color: #3730a3;
            border-color: #6366f1;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.1);
        }

        .action-btns {
            margin-top: 3rem;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .btn-save {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }

        .btn-sms {
            background: #ea580c;
            color: white;
            box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2);
        }

        .btn-email {
            background: #0284c7;
            color: white;
            box-shadow: 0 10px 20px rgba(2, 132, 199, 0.2);
        }

        .btn:hover {
            transform: translateY(-3px) scale(1.02);
            filter: brightness(1.1);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .attendance-card {
                padding: 1.5rem;
                border-radius: 0;
            }

            .info-bar {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 1.5rem;
            }

            .status-options {
                flex-direction: column;
                gap: 8px;
            }

            .status-option {
                width: 100%;
                justify-content: flex-start;
            }

            .action-btns {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="main-content">
        <div class="attendance-card">

            <div class="info-bar">
                <div>
                    <strong><?php echo htmlspecialchars($teacher_name); ?></strong>
                    <small>Class: <?php echo htmlspecialchars($selected_class); ?> | Date:
                        <?php echo htmlspecialchars($selected_date); ?> | Session:
                        <?php echo htmlspecialchars($selected_session); ?></small>
                </div>
                <a href="attendance_entry.php?action=change" class="btn"
                    style="background: rgba(255,255,255,0.5); font-size: 0.85rem; padding: 10px 15px; color: #4f46e5;">
                    <i class="fas fa-sync-alt"></i> Change Session
                </a>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="margin: 0; color: #0f172a; font-size: 2rem; font-weight: 800; letter-spacing: -1px;">Daily
                    Attendance</h1>
                <a href="dashboard.php"
                    style="color: #64748b; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-times-circle" style="font-size: 1.2rem;"></i> Close
                </a>
            </div>

            <?php if ($msg): ?>
                <div
                    style="background: #ecfdf5; color: #065f46; padding: 1.25rem; border-radius: 18px; margin-bottom: 2rem; border: 1px solid #10b981; display: flex; align-items: center; gap: 12px; font-weight: 600;">
                    <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="responsive-table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 150px;">Symbol No</th>
                                <th>Student Name</th>
                                <th>Status Recording</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="3"
                                        style="text-align:center; padding: 4rem; color:#94a3b8; font-weight: 600;">
                                        <i class="fas fa-user-slash"
                                            style="display: block; font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                        No students found in Class <?php echo htmlspecialchars($selected_class); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($students as $s):
                                $status = $s['status'] ? $s['status'] : 'Present';
                                ?>
                                <tr>
                                    <td style="font-weight: 700; color: #64748b;">
                                        <?php echo htmlspecialchars($s['symbol_no']); ?>
                                    </td>
                                    <td style="font-weight: 800; color: #1e293b; font-size: 1.05rem;">
                                        <?php echo htmlspecialchars($s['full_name']); ?>
                                    </td>
                                    <td>
                                        <div class="status-options">
                                            <?php if ($selected_session == 'Evening'): ?>
                                                <label class="status-option">
                                                    <input type="radio" name="attendance[<?php echo $s['id']; ?>]"
                                                        value="Present" <?php echo ($status == 'Present' || empty($s['status'])) ? 'checked' : ''; ?>>
                                                    <span>Left School</span>
                                                </label>
                                                <label class="status-option">
                                                    <input type="radio" name="attendance[<?php echo $s['id']; ?>]"
                                                        value="Extra Class" <?php echo ($status == 'Extra Class') ? 'checked' : ''; ?>>
                                                    <span>Extra Class</span>
                                                </label>
                                            <?php else: ?>
                                                <label class="status-option">
                                                    <input type="radio" name="attendance[<?php echo $s['id']; ?>]"
                                                        value="Present" <?php echo ($status == 'Present') ? 'checked' : ''; ?>>
                                                    <span>Present</span>
                                                </label>
                                                <label class="status-option">
                                                    <input type="radio" name="attendance[<?php echo $s['id']; ?>]"
                                                        value="Absent" <?php echo ($status == 'Absent') ? 'checked' : ''; ?>>
                                                    <span>Absent</span>
                                                </label>
                                                <label class="status-option">
                                                    <input type="radio" name="attendance[<?php echo $s['id']; ?>]" value="Leave"
                                                        <?php echo ($status == 'Leave') ? 'checked' : ''; ?>>
                                                    <span>Leave</span>
                                                </label>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($students)): ?>
                    <div class="action-btns">
                        <button type="submit" name="save_attendance" class="btn btn-save">
                            <i class="fas fa-save"></i> Save Records
                        </button>
                        <button type="submit" name="send_sms_guardian" class="btn btn-sms">
                            <i class="fas fa-sms"></i> Alert Guardians (SMS)
                        </button>
                        <button type="submit" name="send_email_guardian" class="btn btn-email">
                            <i class="fas fa-envelope"></i> Notify via Email
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        // Auto-dismiss success message
        setTimeout(() => {
            const msg = document.querySelector('[style*="background: #ecfdf5"]');
            if (msg) {
                msg.style.transition = 'all 0.5s ease';
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-20px)';
                setTimeout(() => msg.remove(), 500);
            }
        }, 4000);
    </script>
</body>

</html>