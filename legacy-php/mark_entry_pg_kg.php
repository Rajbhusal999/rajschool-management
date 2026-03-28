<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$exam_type = isset($_GET['exam']) ? $_GET['exam'] : 'first_terminal';

$exam_names = [
    'first_terminal' => 'First Terminal Exam',
    'second_terminal' => 'Second Terminal Exam',
    'third_terminal' => 'Third Terminal Exam',
    'final' => 'Final Exam',
    'monthly' => 'Monthly Exam'
];
$exam_name = isset($exam_names[$exam_type]) ? $exam_names[$exam_type] : 'Exam';

// Check if it's a terminal exam
$is_terminal_exam = in_array($exam_type, ['first_terminal', 'second_terminal', 'third_terminal']);

// Function to determine class group from class number
function getClassGroup($class)
{
    if (strtoupper($class) == 'PG')
        return 'PG';
    if (strtoupper($class) == 'LKG')
        return 'LKG';
    if (strtoupper($class) == 'UKG')
        return 'UKG';
    if (strtoupper($class) == 'KG')
        return 'KG';
    if (strtoupper($class) == 'NURSERY')
        return 'NURSERY';
    return 'PG'; // Default for this file
}

// Get selected filters first
$current_nepali_year = date('Y') + 56;
$selected_year = isset($_GET['year']) ? $_GET['year'] : (isset($_POST['year']) ? $_POST['year'] : $current_nepali_year);
$selected_class = isset($_GET['class']) ? $_GET['class'] : (isset($_POST['class']) ? $_POST['class'] : '');
$selected_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : (isset($_POST['student_id']) ? $_POST['student_id'] : '');

// Fetch subjects
$subjects = [];
if (!empty($selected_class)) {
    $class_group = getClassGroup($selected_class);
    // Note: ensure your subjects table has class_group set to 'PG', 'LKG' etc corresponding to what getClassGroup returns
    // Ideally map specific classes to a group if your DB uses groups like 'PG-KG' instead of individual classes
    // Assuming DB might use 'PG' or 'PG_KG'. Let's check logic.
    // In mark_entry.php logic was:
    // ... if (strtoupper($class) == 'PG') return 'PG'; ...
    // ... SELECT subject_name ... WHERE class_group = ? ...
    // So we must ensure 'PG', 'LKG' etc are valid class_groups in subjects table.
    // If not, we might need to map them all to one group 'PG_KG' if that's how subjects are stored.
    // But sticking to the existing function implies they might be separate or handled. 
    // Let's assume they are stored as 'PG', 'LKG' etc OR all mapped to 'PG_KG' if the user set it up that way.
    // For now I will use the return value.

    // ADJUSTMENT: If the user grouped them in subjects setup, we might need a map.
    // But since I don't see the subjects setup, I'll rely on the class name or a generic mapper if needed.
    // For now, let's assume specific or 'pre_primary'

    // In previous conversations or files, we saw:
    // $class_groups = [['id' => 'pg_kg', 'name' => 'PG / LKG / UKG / KG' ...]]
    // This implies a conceptual grouping, but in DB `subjects` table, `class_group` column is used.
    // Let's try to map all these to 'PG' or whatever if that's how they are stored?
    // Actually, usually subjects are specific to 'Nursery', 'LKG'.

    $sql = "SELECT subject_name, credit_hour FROM subjects WHERE school_id = ? AND class_group = ? ORDER BY subject_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class_group]);
    $subject_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no subjects found for specific class, try 'PG_KG' generic group?
    if (empty($subject_data)) {
        $stmt->execute([$school_id, 'PG_KG']); // Fallback attempt
        $subject_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Extract subject names and credits
    foreach ($subject_data as $subject) {
        $subjects[] = [
            'name' => $subject['subject_name'],
            'credit_hour' => $subject['credit_hour']
        ];
    }
}

// Handle Mark Entry Submission
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_marks'])) {
    $year = $_POST['year'];
    $class = $_POST['class'];
    $student_id = $_POST['student_id'];

    $success_count = 0;

    foreach ($subjects as $subj_data) {
        $subject = $subj_data['name'];

        $rw = isset($_POST['rw'][$student_id][$subject]) ? $_POST['rw'][$student_id][$subject] : '';
        $ls = isset($_POST['ls'][$student_id][$subject]) ? $_POST['ls'][$student_id][$subject] : '';

        // Only save if at least one mark is entered
        if ($rw !== '' || $ls !== '') {
            $participation = 0; // Not used
            $practical = ($rw !== '') ? (float) $rw : 0;
            $terminal = ($ls !== '') ? (float) $ls : 0;

            // Check if marks already exist
            $check_sql = "SELECT id FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$school_id, $student_id, $exam_type, $subject, $year]);

            if ($check_stmt->fetch()) {
                // Update
                $update_sql = "UPDATE exam_marks SET participation = ?, practical = ?, terminal = ? WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->execute([$participation, $practical, $terminal, $school_id, $student_id, $exam_type, $subject, $year]);
            } else {
                // Insert
                $insert_sql = "INSERT INTO exam_marks (school_id, student_id, exam_type, subject, participation, practical, terminal, year, class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->execute([$school_id, $student_id, $exam_type, $subject, $participation, $practical, $terminal, $year, $class]);
            }
            $success_count++;
        }
    }

    // Save Attendance
    $days_present = isset($_POST['days_present'][$student_id]) ? $_POST['days_present'][$student_id] : null;
    if ($days_present !== null && $days_present !== '') {
        $check_att = "SELECT id FROM exam_attendance WHERE school_id = ? AND student_id = ? AND exam_type = ? AND year = ?";
        $stmt_att = $conn->prepare($check_att);
        $stmt_att->execute([$school_id, $student_id, $exam_type, $year]);

        if ($stmt_att->fetch()) {
            $upd_att = "UPDATE exam_attendance SET days_present = ? WHERE school_id = ? AND student_id = ? AND exam_type = ? AND year = ?";
            $stmt_upd = $conn->prepare($upd_att);
            $stmt_upd->execute([$days_present, $school_id, $student_id, $exam_type, $year]);
        } else {
            $ins_att = "INSERT INTO exam_attendance (school_id, student_id, exam_type, days_present, year) VALUES (?, ?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($ins_att);
            $stmt_ins->execute([$school_id, $student_id, $exam_type, $days_present, $year]);
        }
    }

    header("Location: mark_entry_pg_kg.php?exam=$exam_type&year=$year&class=$class&student_id=$student_id&msg=" . urlencode("Marks saved successfully!"));
    exit();
}

// Fetch students
$students = [];
if (!empty($selected_class)) {
    $sql = "SELECT id, full_name, symbol_no, class FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $selected_class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch existing marks
$existing_marks = [];
if (!empty($selected_student_id) && $is_terminal_exam) {
    $marks_sql = "SELECT subject, participation, practical, terminal FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND year = ?";
    $marks_stmt = $conn->prepare($marks_sql);
    $marks_stmt->execute([$school_id, $selected_student_id, $exam_type, $selected_year]);
    $marks_data = $marks_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($marks_data as $mark) {
        $existing_marks[$mark['subject']] = $mark;
    }
}

// Fetch existing attendance
$existing_attendance = '';
if (!empty($selected_student_id)) {
    $att_sql = "SELECT days_present FROM exam_attendance WHERE school_id = ? AND student_id = ? AND exam_type = ? AND year = ?";
    $att_stmt = $conn->prepare($att_sql);
    $att_stmt->execute([$school_id, $selected_student_id, $exam_type, $selected_year]);
    $att_res = $att_stmt->fetch(PDO::FETCH_ASSOC);
    if ($att_res) {
        $existing_attendance = $att_res['days_present'];
    }
}

// Get classes for PG/KG/LKG/UKG/Nursery
$class_sql = "SELECT DISTINCT class FROM students WHERE school_id = ? AND UPPER(class) IN ('PG', 'LKG', 'UKG', 'NURSERY', 'KG') ORDER BY class";
$class_stmt = $conn->prepare($class_sql);
$class_stmt->execute([$school_id]);
$classes = $class_stmt->fetchAll(PDO::FETCH_COLUMN);

// If no classes found in DB, provide default options for dropdown so user knows what this page is for
if (empty($classes)) {
    // This might happen if no students are registered yet
    //$classes = ['PG', 'NURSERY', 'LKG', 'UKG', 'KG']; // Optional: Dont force if not in DB
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Entry (PG-KG) -
        <?php echo $exam_name; ?>
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f9fafb;
        }

        .container {
            display: flex;
            height: calc(100vh - 65px);
            overflow: hidden;
        }

        .filter-sidebar {
            width: 320px;
            background: white;
            border-right: 1px solid #e5e7eb;
            padding: 2rem;
            overflow-y: auto;
        }

        .sidebar-header {
            margin-bottom: 2rem;
        }

        .sidebar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .sidebar-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .filter-group {
            margin-bottom: 1.5rem;
        }

        .filter-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .btn-filter {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #ec4899, #f472b6);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(236, 72, 153, 0.3);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .breadcrumb {
            padding: 1rem 2rem;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: #ec4899;
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: #db2777;
        }

        .content-header {
            padding: 2rem;
            background: white;
            border-bottom: 1px solid #e5e7eb;
        }

        .content-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .content-subtitle {
            color: #6b7280;
        }

        .marks-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .success-message {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: #6b7280;
        }

        .student-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .student-item {
            padding: 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .student-item:hover {
            background: #fdf2f8;
        }

        .student-item.selected {
            background: #fce7f3;
            border-left: 4px solid #ec4899;
        }

        .student-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ec4899, #f472b6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .student-symbol {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .marks-table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
        }

        .marks-table thead {
            background: linear-gradient(135deg, #ec4899, #f472b6);
            color: white;
        }

        .marks-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .marks-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .marks-table tbody tr:hover {
            background: #f9fafb;
        }

        .subject-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 1rem;
        }

        .mark-input {
            width: 70px;
            padding: 0.6rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            transition: all 0.2s;
        }

        .mark-input:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .mark-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            display: block;
        }

        .save-btn {
            position: sticky;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 1rem 3rem;
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .save-btn:hover {
            transform: translateX(-50%) translateY(-3px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .filter-sidebar {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <!-- Filter Sidebar -->
        <div class="filter-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">
                    <i class="fas fa-filter"></i> Filter Options
                </h2>
                <p class="sidebar-subtitle">
                    <?php echo $exam_name; ?> (PG/KG)
                </p>
            </div>

            <form method="GET" action="">
                <input type="hidden" name="exam" value="<?php echo htmlspecialchars($exam_type); ?>">

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-calendar"></i> Academic Year (B.S.)
                    </label>
                    <input type="number" name="year" class="filter-input"
                        value="<?php echo htmlspecialchars($selected_year); ?>" placeholder="e.g., 2081" required>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-users"></i> Class
                    </label>
                    <select name="class" class="filter-select" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" <?php echo ($class == $selected_class) ? 'selected' : ''; ?>>
                                Class
                                <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Load Students
                </button>
            </form>

            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
                <a href="exam_class_selector.php?exam=<?php echo $exam_type; ?>"
                    style="color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-arrow-left"></i> Back to Classes
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="breadcrumb">
                <a href="exams.php">Exams</a>
                <span>/</span>
                <a href="exam_marks.php?exam=<?php echo $exam_type; ?>&group=pg_kg">PG/KG</a>
                <span>/</span>
                <span>Mark Entry</span>
            </div>

            <div class="content-header">
                <h1 class="content-title">Mark Entry (Pre-Primary)</h1>
                <p class="content-subtitle">
                    <?php if (!empty($selected_class)): ?>
                        Class <?php echo htmlspecialchars($selected_class); ?> - Academic Year
                        <?php echo htmlspecialchars($selected_year); ?>
                    <div style="margin-top: 5px; font-size: 0.9rem;">
                        <span
                            style="color: #4b5563; background: #f3f4f6; padding: 2px 8px; border-radius: 4px; margin-right: 10px;">
                            <i class="fas fa-info-circle"></i> Reading/Writing (RW) = 50 Marks
                        </span>
                        <span style="color: #4b5563; background: #f3f4f6; padding: 2px 8px; border-radius: 4px;">
                            <i class="fas fa-info-circle"></i> Listening/Speaking (LS) = 50 Marks
                        </span>
                    </div>
                <?php else: ?>
                    Select a class to start entering marks
                <?php endif; ?>
                </p>
            </div>

            <div class="marks-container">
                <?php if ($msg): ?>
                    <div class="success-message" id="successMessage">
                        <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                        <span>
                            <?php echo htmlspecialchars($msg); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (empty($students)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-child"></i>
                        </div>
                        <h3 class="empty-title">No Students Found</h3>
                        <p class="empty-text">
                            Please select a year and class (PG, LKG, UKG, Nursery) from the sidebar
                        </p>
                    </div>
                <?php elseif (empty($selected_student_id)): ?>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
                        Select a Student
                    </h3>
                    <div class="student-list">
                        <?php foreach ($students as $student): ?>
                            <div class="student-item"
                                onclick="window.location.href='mark_entry_pg_kg.php?exam=<?php echo $exam_type; ?>&year=<?php echo $selected_year; ?>&class=<?php echo urlencode($selected_class); ?>&student_id=<?php echo $student['id']; ?>'">
                                <div class="student-avatar">
                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                </div>
                                <div class="student-info">
                                    <div class="student-name">
                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                    </div>
                                    <div class="student-symbol">Symbol:
                                        <?php echo htmlspecialchars($student['symbol_no']); ?> •
                                        Class
                                        <?php echo htmlspecialchars($student['class']); ?>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #9ca3af;"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php
                    // Get selected student details
                    $selected_student = null;
                    foreach ($students as $student) {
                        if ($student['id'] == $selected_student_id) {
                            $selected_student = $student;
                            break;
                        }
                    }
                    ?>

                    <div
                        style="background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="student-avatar">
                                <?php echo strtoupper(substr($selected_student['full_name'], 0, 1)); ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 1.25rem; font-weight: 700; color: #1f2937;">
                                    <?php echo htmlspecialchars($selected_student['full_name']); ?>
                                </div>
                                <div style="color: #6b7280;">
                                    Symbol:
                                    <?php echo htmlspecialchars($selected_student['symbol_no']); ?> • Class
                                    <?php echo htmlspecialchars($selected_student['class']); ?>
                                </div>
                            </div>
                            <a href="mark_entry_pg_kg.php?exam=<?php echo $exam_type; ?>&year=<?php echo $selected_year; ?>&class=<?php echo urlencode($selected_class); ?>"
                                style="color: #ec4899; text-decoration: none; font-weight: 600;">
                                <i class="fas fa-arrow-left"></i> Change Student
                            </a>
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="save_marks" value="1">
                        <input type="hidden" name="year" value="<?php echo htmlspecialchars($selected_year); ?>">
                        <input type="hidden" name="class" value="<?php echo htmlspecialchars($selected_class); ?>">
                        <input type="hidden" name="student_id"
                            value="<?php echo htmlspecialchars($selected_student_id); ?>">


                        <div class="marks-table-wrapper" style="margin-bottom: 20px;">
                            <div
                                style="padding: 15px; background: #fff3e0; border-bottom: 1px solid #ffd8a8; font-weight: bold; color: #e65100; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-calendar-check"></i> Attendance Details
                                <input type="number" name="days_present[<?php echo $selected_student_id; ?>]"
                                    placeholder="Days Present" value="<?php echo htmlspecialchars($existing_attendance); ?>"
                                    style="padding: 5px 10px; border: 1px solid #ccc; border-radius: 4px; width: 120px;"
                                    min="0" max="365">
                            </div>
                        </div>

                        <div class="marks-table-wrapper">
                            <table class="marks-table">
                                <thead>
                                    <tr>
                                        <th
                                            style="width: 5%; border: 1px solid #e5e7eb; padding: 10px; text-align: center;">
                                            क्र.सं. (S.N.)</th>
                                        <th
                                            style="width: 25%; border: 1px solid #e5e7eb; padding: 10px; text-align: center;">
                                            विषय (Subject)</th>
                                        <th style="border: 1px solid #e5e7eb; padding: 10px; text-align: center;">
                                            RW (50)</th>
                                        <th style="border: 1px solid #e5e7eb; padding: 10px; text-align: center;">
                                            LS (50)</th>
                                        <th
                                            style="width: 10%; border: 1px solid #e5e7eb; padding: 10px; text-align: center;">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $index => $subj_data):
                                        $subject = $subj_data['name'];

                                        $credit = floatval($subj_data['credit_hour']);
                                        if ($credit == 100) {
                                            $is_100 = true;
                                        } else if ($credit == 50) {
                                            $is_100 = false;
                                        } else {
                                            $is_100 = ($credit >= 3) ? true : false;
                                        }

                                        $existing_rw = '';
                                        $existing_ls = '';

                                        if (isset($existing_marks[$subject])) {
                                            $mark = $existing_marks[$subject];
                                            $total_prac = floatval($mark['practical']);
                                            $total_term = floatval($mark['terminal']);

                                            $existing_rw = $total_prac > 0 ? $total_prac : '';
                                            $existing_ls = $total_term > 0 ? $total_term : '';
                                        }
                                        ?>
                                        <tr>
                                            <td style="border: 1px solid #e5e7eb; text-align: center;"><?php echo $index + 1; ?>
                                            </td>
                                            <td style="border: 1px solid #e5e7eb;">
                                                <span class="subject-name"><?php echo htmlspecialchars($subject); ?></span>
                                            </td>
                                            <td style="border: 1px solid #e5e7eb; text-align: center;">
                                                <input type="number"
                                                    name="rw[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                    class="mark-input" placeholder="" min="0" max="50" step="0.5"
                                                    style="width: 70px;" value="<?php echo $existing_rw; ?>">
                                            </td>
                                            <td style="border: 1px solid #e5e7eb; text-align: center;">
                                                <?php if ($is_100): ?>
                                                    <input type="number"
                                                        name="ls[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="" min="0" max="50" step="0.5"
                                                        style="width: 70px;" value="<?php echo $existing_ls; ?>">
                                                <?php else: ?>
                                                    <input type="text" class="mark-input" placeholder="-" disabled
                                                        style="width: 70px; background: #f3f4f6; cursor: not-allowed;">
                                                <?php endif; ?>
                                            </td>
                                            <td style="border: 1px solid #e5e7eb; text-align: center; background: rgba(0,0,0,0.02); font-weight: bold;"
                                                class="subject-total" data-is-100="<?php echo $is_100 ? 'true' : 'false'; ?>">
                                                <?php echo isset($existing_marks[$subject]) ? (floatval($existing_marks[$subject]['practical']) + ($is_100 ? floatval($existing_marks[$subject]['terminal']) : 0)) : ''; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <script>
                            document.querySelectorAll('.marks-table tbody tr').forEach(row => {
                                const rwInput = row.querySelector('input[name^="rw"]');
                                const lsInput = row.querySelector('input[name^="ls"]');
                                const subTotal = row.querySelector('.subject-total');
                                const is100 = subTotal ? subTotal.getAttribute('data-is-100') === 'true' : true;

                                const updateSubTotal = () => {
                                    if (rwInput) {
                                        const val1 = parseFloat(rwInput.value) || 0;
                                        const val2 = is100 && lsInput ? parseFloat(lsInput.value) || 0 : 0;

                                        if (rwInput.value || (is100 && lsInput && lsInput.value)) {
                                            subTotal.textContent = val1 + val2;
                                        } else {
                                            subTotal.textContent = '';
                                        }
                                    }
                                };

                                if (rwInput) rwInput.addEventListener('input', updateSubTotal);
                                if (lsInput && is100) lsInput.addEventListener('input', updateSubTotal);
                            });
                        </script>

                        <div style="text-align: center; margin-top: 2rem;">
                            <button type="submit" class="save-btn">
                                <i class="fas fa-save"></i>
                                Save All Marks
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss success message
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.transition = 'opacity 0.5s ease';
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 500);
            }, 3000);
        }
    </script>
</body>

</html>