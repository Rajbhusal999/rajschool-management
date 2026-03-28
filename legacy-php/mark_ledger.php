<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$exam_type = isset($_GET['exam']) ? $_GET['exam'] : (isset($_POST['exam']) ? $_POST['exam'] : 'first_terminal');

$exam_names = [
    'first_terminal' => 'First Terminal Exam',
    'second_terminal' => 'Second Terminal Exam',
    'third_terminal' => 'Third Terminal Exam',
    'final' => 'Final Exam',
    'monthly' => 'Monthly Exam'
];
$exam_name = isset($exam_names[$exam_type]) ? $exam_names[$exam_type] : 'Exam';

// Check if it's a valid exam type for ledger display
$is_terminal_exam = in_array($exam_type, ['first_terminal', 'second_terminal', 'third_terminal', 'final']);

// Function to determine class group from class number
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

// Get selected filters
$current_nepali_year = date('Y') + 56; // Convert to Nepali year
$selected_year = isset($_GET['year']) ? $_GET['year'] : $current_nepali_year;
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';

// Redirect PG/KG category to the specialized ledger view
if (!empty($selected_class) && in_array(strtoupper($selected_class), ['PG', 'LKG', 'UKG', 'NURSERY', 'KG'])) {
    header("Location: mark_ledger_pg_kg.php?exam=" . urlencode($exam_type) . "&year=" . urlencode($selected_year) . "&class=" . urlencode($selected_class) . "&group=pg_kg");
    exit();
}

// Fetch students if class is selected
$students = [];
$is_class_1_to_3 = in_array((string) $selected_class, ['1', '2', '3']);

if (!empty($selected_class)) {
    $sql = "SELECT id, full_name, symbol_no, class, dob FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $selected_class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$is_class_4_to_8_final = in_array((string) $selected_class, ['4', '5', '6', '7', '8']) && $exam_type == 'final';

// Fetch subjects based on class group
$subjects = [];
$subject_credits = []; // Map subject name to credit hour
if (!empty($selected_class)) {
    $class_group = getClassGroup($selected_class);
    $sql = "SELECT subject_name, credit_hour FROM subjects WHERE school_id = ? AND class_group = ? ORDER BY subject_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class_group]);
    $subject_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subject_data as $subject) {
        $subjects[] = $subject['subject_name'];
        $subject_credits[$subject['subject_name']] = $subject['credit_hour'];
    }
}

// Fetch all marks for the class
$marks_data = [];
if (!empty($selected_class) && !empty($students)) {
    $student_ids = array_column($students, 'id');
    $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';

    $sql = "SELECT student_id, subject, participation, practical, terminal, external, total, la_total, la_obtained, remarks 
            FROM exam_marks 
            WHERE school_id = ? AND exam_type = ? AND year = ? AND student_id IN ($placeholders)";

    $params = array_merge([$school_id, $exam_type, $selected_year], $student_ids);
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($marks as $mark) {
        $marks_data[$mark['student_id']][$mark['subject']] = $mark;
    }

    // Fetch attendance data
    $attendance_data = [];
    $att_sql = "SELECT student_id, days_present FROM exam_attendance 
                WHERE school_id = ? AND exam_type = ? AND year = ? AND student_id IN ($placeholders)";
    $att_stmt = $conn->prepare($att_sql);
    $att_stmt->execute($params);
    $att_records = $att_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($att_records as $rec) {
        $attendance_data[$rec['student_id']] = $rec['days_present'];
    }
}

// Get unique classes
$class_sql = "SELECT DISTINCT class FROM students WHERE school_id = ? ORDER BY class";
$class_stmt = $conn->prepare($class_sql);
$class_stmt->execute([$school_id]);
$classes = $class_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get school info
$school_sql = "SELECT name FROM users WHERE id = ?";
$school_stmt = $conn->prepare($school_sql);
$school_stmt->execute([$school_id]);
$school_info = $school_stmt->fetch(PDO::FETCH_ASSOC);
// Add location manually if it doesn't exist in the table
if ($school_info && !isset($school_info['location'])) {
    $school_info['location'] = '';
}


// Function to calculate grade point based on percentage
function getGradePoint($obtained_marks, $max_marks = 50)
{
    if ($max_marks == 0)
        return '0.0';

    $percentage = $obtained_marks / $max_marks;

    if ($percentage >= 0.9)
        return '4.0';
    if ($percentage >= 0.8)
        return '3.6';
    if ($percentage >= 0.7)
        return '3.2';
    if ($percentage >= 0.6)
        return '2.8';
    if ($percentage >= 0.5)
        return '2.4';
    if ($percentage >= 0.4)
        return '2.0';
    if ($percentage >= 0.35)
        return '1.6';
    return '0.0';
}



function getGrade($gp)
{
    // Grade is determined by GP value
    if ($gp == '4.0')
        return 'A+';
    if ($gp == '3.6')
        return 'A';
    if ($gp == '3.2')
        return 'B+';
    if ($gp == '2.8')
        return 'B';
    if ($gp == '2.4')
        return 'C+';
    if ($gp == '2.0')
        return 'C';
    if ($gp == '1.6')
        return 'D';
    if ($gp == '0.0')
        return 'NG';
    return '';
}

function getRemarks($gpa)
{
    $gpa = floatval($gpa);
    if ($gpa > 3.6)
        return "OUTSTANDING";
    if ($gpa > 3.2)
        return "EXCELLENT";
    if ($gpa > 2.8)
        return "VERY GOOD";
    if ($gpa > 2.4)
        return "GOOD";
    if ($gpa > 2.0)
        return "SATISFACTORY";
    if ($gpa > 1.6)
        return "ACCEPTABLE";
    if ($gpa == 1.6)
        return "BASIC";
    return "NOT GRADED";
}

// Calculate Ranks based on GPA
$student_ranks = [];
if (!empty($students) && !empty($subjects) && $is_terminal_exam) {
    foreach ($students as $student) {
        $st_total_wgp = 0;
        $st_total_credits = 0;
        $has_failed_subject = false;
        foreach ($subjects as $subject) {
            $marks = isset($marks_data[$student['id']][$subject]) ? $marks_data[$student['id']][$subject] : null;
            $credit_hour = isset($subject_credits[$subject]) ? floatval($subject_credits[$subject]) : 1;

            if ($is_class_1_to_3 || $is_class_4_to_8_final) {
                $st_total_credits += $credit_hour;
            }
            if ($marks) {
                if ($is_class_1_to_3) {
                    $la_obtained = isset($marks['la_obtained']) && $marks['la_obtained'] !== '' ? floatval($marks['la_obtained']) : null;
                    $la_total = floatval($marks['la_total'] ?? 0);
                    if ($la_total > 0 && $la_obtained !== null) {
                        $percentage = ($la_obtained / $la_total) * 100;
                        if ($percentage >= 90)
                            $gp = '4.0';
                        elseif ($percentage >= 80)
                            $gp = '3.6';
                        elseif ($percentage >= 70)
                            $gp = '3.2';
                        elseif ($percentage >= 60)
                            $gp = '2.8';
                        elseif ($percentage >= 50)
                            $gp = '2.4';
                        elseif ($percentage >= 40)
                            $gp = '2.0';
                        elseif ($percentage >= 35)
                            $gp = '1.6';
                        else {
                            $gp = '0.0';
                            $has_failed_subject = true;
                        }
                    } else {
                        $gp = '0.0';
                        $has_failed_subject = true;
                    }
                } elseif ($is_class_4_to_8_final) {
                    $th_marks = floatval($marks['external'] ?? 0);
                    $pr_marks = floatval($marks['participation'] ?? 0) + floatval($marks['practical'] ?? 0) + floatval($marks['terminal'] ?? 0);
                    $th_gp = floatval(getGradePoint($th_marks, 50));
                    $pr_gp = floatval(getGradePoint($pr_marks, 50));
                    $th_c = $credit_hour / 2;
                    $pr_c = $credit_hour / 2;
                    $st_total_wgp += ($th_gp * $th_c) + ($pr_gp * $pr_c);

                    // Same failure logic check as 1-3. If any element zeroes the GPA out, subject fails completely!
                    if ($th_gp == 0.0 || $pr_gp == 0.0) {
                        $has_failed_subject = true;
                    }
                } else {
                    $subj_total = ($marks['participation'] ?? 0) + ($marks['practical'] ?? 0) + ($marks['terminal'] ?? 0);
                    if ($exam_type == 'final') {
                        $subj_total += ($marks['external'] ?? 0);
                        $gp = getGradePoint($subj_total, 100);
                    } else {
                        $gp = getGradePoint($subj_total, 50);
                    }
                }
                if (!$is_class_1_to_3 && !$is_class_4_to_8_final) { // Only add WGP and credits if not handled by 1-3 or 4-8 final
                    $st_total_wgp += floatval($gp) * $credit_hour;
                    $st_total_credits += $credit_hour;
                }
            } else {
                if ($is_class_1_to_3 || $is_class_4_to_8_final) {
                    $has_failed_subject = true;
                }
            }
        }
        $gpa = $st_total_credits > 0 ? ($st_total_wgp / $st_total_credits) : 0;
        if (($is_class_1_to_3 || $is_class_4_to_8_final) && $has_failed_subject) {
            $gpa = 0;
        }
        // Only rank students who have some marks
        if ($st_total_credits > 0) {
            $gpa_list[$student['id']] = round($gpa, 2);
        }
    }

    // Sort GPAs descending
    arsort($gpa_list);

    $current_rank = 0;
    $prev_gpa = -1;
    $count = 0;
    foreach ($gpa_list as $sid => $gpa) {
        $count++;
        if ($gpa != $prev_gpa) {
            $current_rank = $count;
        }
        $student_ranks[$sid] = $current_rank;
        $prev_gpa = $gpa;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Ledger -
        <?php echo $exam_name; ?>
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
            --bg: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: #1e293b;
            margin: 0;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            height: calc(100vh - 70px);
            overflow: hidden;
        }

        /* Premium Filter Sidebar */
        .filter-sidebar {
            width: 320px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 2.5rem 1.5rem;
            overflow-y: auto;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 2rem;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        .sidebar-header {
            margin-bottom: 0.5rem;
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .filter-group {
            margin-bottom: 0.5rem;
        }

        .filter-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-select,
        .filter-input {
            width: 100%;
            padding: 12px 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.95rem;
            background: #fcfdfe;
            transition: all 0.2s;
            font-weight: 500;
        }

        .filter-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: white;
        }

        .btn-filter {
            width: 100%;
            padding: 14px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
        }

        /* Main Workspace */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #f8fafc;
        }

        .content-header {
            padding: 1.5rem 2.5rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .content-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .ledger-container {
            flex: 1;
            overflow: auto;
            padding: 2rem;
        }

        .table-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            white-space: nowrap;
        }

        .ledger-table th {
            background: #1e293b;
            color: white;
            padding: 12px 6px;
            font-weight: 700;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .ledger-table td {
            padding: 8px 10px;
            border: 1px solid #f1f5f9;
            text-align: center;
            font-weight: 500;
            color: #334155;
        }

        .student-info-cell {
            text-align: left !important;
            font-weight: 800;
            color: #0f172a !important;
            background: white !important;
        }

        .symbol-cell {
            color: #4f46e5 !important;
            font-weight: 700;
        }

        .total-cell {
            background: #f8fafc !important;
            font-weight: 800;
            color: #0f172a !important;
        }

        /* Print Enhancements */
        .print-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        @media (max-width: 1024px) {
            .filter-sidebar {
                width: 280px;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
                overflow: visible;
            }

            .filter-sidebar {
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }

            .main-content {
                height: auto;
                overflow: visible;
            }

            .ledger-container {
                padding: 1rem;
            }

            .content-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
                padding: 2rem 1.5rem;
            }
        }

        @media print {

            .filter-sidebar,
            .breadcrumb,
            .print-btn,
            .export-btn,
            .navbar-container {
                display: none !important;
            }

            .container {
                display: block;
                height: auto;
            }

            .main-content {
                margin: 0;
                padding: 0;
            }

            .ledger-table th {
                background: #f1f5f9 !important;
                color: black !important;
                border: 1px solid #cbd5e1 !important;
            }

            .ledger-table td {
                border: 1px solid #cbd5e1 !important;
            }

            @page {
                size: landscape;
                margin: 1cm;
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
                    <?php echo $exam_name; ?> Ledger
                </p>
            </div>

            <form method="GET" action="">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-calendar"></i> Academic Year (B.S.)
                    </label>
                    <input type="number" name="year" class="filter-input"
                        value="<?php echo htmlspecialchars($selected_year); ?>" placeholder="e.g., 2081" required>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-clipboard-list"></i> Exam Type
                    </label>
                    <select name="exam" class="filter-select" required>
                        <option value="">Select Exam Type</option>
                        <option value="first_terminal" <?php echo ($exam_type == 'first_terminal') ? 'selected' : ''; ?>>
                            First Terminal Exam</option>
                        <option value="second_terminal" <?php echo ($exam_type == 'second_terminal') ? 'selected' : ''; ?>>Second Terminal Exam</option>
                        <option value="third_terminal" <?php echo ($exam_type == 'third_terminal') ? 'selected' : ''; ?>>
                            Third Terminal Exam</option>
                        <option value="final" <?php echo ($exam_type == 'final') ? 'selected' : ''; ?>>Final Exam</option>
                        <option value="monthly" <?php echo ($exam_type == 'monthly') ? 'selected' : ''; ?>>Monthly Exam
                        </option>
                    </select>
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
                    <i class="fas fa-search"></i> Generate Ledger
                </button>
            </form>

            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
                <a href="exam_marks.php?exam=<?php echo $exam_type; ?>"
                    style="color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-arrow-left"></i> Back to Options
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="breadcrumb">
                <a href="exams.php">Exams</a>
                <span>/</span>
                <span>Mark Ledger</span>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div id="successMessage"
                    style="background: linear-gradient(135deg, #10b981, #34d399); color: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                    <div style="flex: 1;">
                        <strong style="display: block; margin-bottom: 0.25rem;">Success!</strong>
                        <span>
                            <?php echo htmlspecialchars($_GET['msg']); ?>
                        </span>
                    </div>
                    <button onclick="this.parentElement.remove()"
                        style="background: none; border: none; color: white; cursor: pointer; font-size: 1.25rem;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <script>
                    setTimeout(function () {
                        var msg = document.getElementById('successMessage');
                        if (msg) {
                            msg.style.transition = 'opacity 0.5s';
                            msg.style.opacity = '0';
                            setTimeout(function () { msg.remove(); }, 500);
                        }
                    }, 5000);
                </script>
            <?php endif; ?>

            <div class="content-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 class="content-title">Mark Ledger</h1>
                        <p class="content-subtitle">
                            <?php if (!empty($selected_class)): ?>
                                Class
                                <?php echo htmlspecialchars($selected_class); ?> - Academic Year
                                <?php echo htmlspecialchars($selected_year); ?>
                            <?php else: ?>
                                Select a class to view the mark ledger
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if (!empty($students) && !empty($subjects)): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <button onclick="window.print()" class="print-btn">
                                <i class="fas fa-print"></i> Print (A3)
                            </button>
                            <button onclick="exportToExcel()" class="print-btn export-btn"
                                style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ledger-container">
                <?php if (empty($selected_class)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3 class="empty-title">No Class Selected</h3>
                        <p class="empty-text">Please select a year and class from the filter sidebar</p>
                    </div>
                <?php elseif (empty($students)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <h3 class="empty-title">No Students Found</h3>
                        <p class="empty-text">No students found in Class
                            <?php echo htmlspecialchars($selected_class); ?>
                        </p>
                    </div>
                <?php elseif (empty($subjects)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="empty-title">No Subjects Found</h3>
                        <p class="empty-text">Please add subjects for this class group in Subject Management</p>
                    </div>
                <?php else: ?>
                    <!-- School Header -->
                    <div class="report-header"
                        style="background: white; padding: 2rem; text-align: center; margin-bottom: 1rem; border-radius: 12px; position: relative;">
                        <?php if (!empty($_SESSION['school_logo'])): ?>
                            <img src="<?php echo htmlspecialchars((string) $_SESSION['school_logo']); ?>" alt="Logo"
                                style="position: absolute; left: 2rem; top: 50%; transform: translateY(-50%); height: 80px; width: auto;">
                        <?php endif; ?>

                        <h1
                            style="font-size: 1.75rem; font-weight: 800; color: #7c3aed; margin-bottom: 0.5rem; text-transform: uppercase;">
                            <?php echo htmlspecialchars((string) ($_SESSION['school_name'] ?? 'School Name')); ?>
                        </h1>
                        <p style="color: #6b7280; font-size: 1rem; margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars((string) ($_SESSION['school_address'] ?? 'Address Not Found')); ?>
                        </p>
                        <?php if (!empty($_SESSION['estd_date'])): ?>
                            <p style="color: #6b7280; font-size: 0.9rem; font-weight: 600;">
                                ESTD: <?php echo htmlspecialchars((string) $_SESSION['estd_date']); ?>
                            </p>
                        <?php endif; ?>

                        <h2
                            style="font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-top: 1rem; text-transform: uppercase;">
                            <?php echo strtoupper($exam_name); ?>     <?php echo $selected_year; ?>
                        </h2>
                        <p style="color: #6b7280; font-size: 0.95rem; margin-top: 0.5rem;">
                            Class: <?php echo htmlspecialchars($selected_class); ?>
                        </p>
                    </div>

                    <div class="ledger-wrapper">
                        <table class="ledger-table" id="ledgerTable">
                            <thead>
                                <tr>
                                    <?php if ($is_class_1_to_3): ?>
                                        <th rowspan="3" style="width: 40px; background: #374151; color: white;">क्र.स</th>
                                        <th rowspan="3" style="min-width: 150px; background: #374151; color: white;">
                                            विद्यार्थीको नाम थर
                                        </th>
                                        <th rowspan="3" style="min-width: 80px; background: #374151; color: white;">रोल</th>
                                    <?php elseif ($is_class_4_to_8_final): ?>
                                        <th rowspan="3" style="width: 40px; background: #eab308; color: black;">R.N</th>
                                        <th rowspan="3" style="min-width: 180px; background: #eab308; color: black;">Name Of
                                            Student</th>
                                        <th rowspan="3" style="min-width: 80px; background: #eab308; color: black;">DOB</th>
                                        <th rowspan="3" style="min-width: 80px; background: #eab308; color: black;">Grade</th>
                                    <?php else: ?>
                                        <th rowspan="3" style="width: 40px; background: #374151; color: white;">S.N</th>
                                        <th rowspan="3" style="background: #374151; color: white;">Symbol No</th>
                                        <th rowspan="3" style="min-width: 200px; background: #374151; color: white;">Student
                                            Details
                                        </th>
                                    <?php endif; ?>
                                    <?php if ($is_class_1_to_3): ?>
                                        <th rowspan="3" style="width: 40px; background: #6b21a8; color: white;"
                                            class="vert-cell">Attendance</th>
                                    <?php endif; ?>

                                    <?php
                                    $bg_colors = ['#f59e0b', '#eab308', '#3b82f6', '#84cc16', '#ef4444', '#8b5cf6', '#14b8a6'];
                                    foreach ($subjects as $idx => $subject):
                                        $bg_color = $bg_colors[$idx % count($bg_colors)];
                                        ?>
                                        <?php if ($is_class_1_to_3): ?>
                                            <th colspan="7" class="subject-header"
                                                style="background: <?php echo $bg_color; ?>; color: white;">
                                                <?php echo strtoupper(htmlspecialchars($subject)); ?>
                                            </th>
                                        <?php elseif ($is_class_4_to_8_final): ?>
                                            <th colspan="12" class="subject-header"
                                                style="background: <?php echo $bg_color; ?>; color: white;">
                                                <?php echo htmlspecialchars($subject); ?>
                                            </th>
                                        <?php elseif ($is_terminal_exam): ?>
                                            <th colspan="<?php echo ($exam_type == 'final') ? '8' : '7'; ?>" class="subject-header"
                                                style="background: #dc2626; color: white;">
                                                <?php echo strtoupper(htmlspecialchars($subject)); ?>
                                            </th>
                                        <?php else: ?>
                                            <th rowspan="3" class="subject-header" style="color: white;">
                                                <?php echo htmlspecialchars($subject); ?>
                                            </th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <?php if ($is_class_1_to_3): ?>
                                        <th rowspan="3" style="min-width: 60px; background: #3b82f6; color: white;">GPA</th>
                                        <th rowspan="3" style="min-width: 60px; background: #dc2626; color: white;">RANK</th>
                                        <th rowspan="3" style="min-width: 120px; background: #1f2937; color: white;">REMARK</th>
                                    <?php elseif ($is_class_4_to_8_final): ?>
                                        <th rowspan="3" style="min-width: 60px; background: #eab308; color: black;">GPA</th>
                                        <th rowspan="3" style="min-width: 60px; background: #eab308; color: black;">Total</th>
                                        <th rowspan="3" style="min-width: 80px; background: #eab308; color: black;">Attendance
                                        </th>
                                        <th rowspan="3" style="min-width: 100px; background: #eab308; color: black;">Remarks
                                        </th>
                                        <th rowspan="3" style="min-width: 60px; background: #eab308; color: black;">Ranks</th>
                                    <?php elseif ($is_terminal_exam): ?>
                                        <th rowspan="3" style="min-width: 60px; background: #1e40af; color: white;">GPA</th>
                                        <th rowspan="3" style="min-width: 80px; background: #fbbf24; color: #78350f;">Total</th>
                                        <th rowspan="3"
                                            style="min-width: 100px; background: #10b981; color: white; font-weight: 700;">
                                            ATTENDANCE</th>
                                        <th rowspan="3" style="min-width: 60px; background: #8b5cf6; color: white;">Rank</th>
                                        <th rowspan="3" style="min-width: 120px; background: #8b5cf6; color: white;">Remarks
                                        </th>
                                    <?php endif; ?>
                                </tr>
                                <?php if ($is_class_1_to_3): ?>
                                    <tr>
                                        <?php foreach ($subjects as $idx => $subject):
                                            $bg_color = '#6b21a8';
                                            ?>
                                            <th rowspan="2"
                                                style="font-size: 0.70rem; color: white; background: <?php echo $bg_color; ?>;"
                                                class="vert-cell">जम्मा सिकाइ<br>उपलब्धि</th>
                                            <th rowspan="2"
                                                style="font-size: 0.70rem; color: white; background: <?php echo $bg_color; ?>;"
                                                class="vert-cell">मूल्याङ्कन<br>गरिएका<br>सि.उ.</th>
                                            <th rowspan="2"
                                                style="font-size: 0.70rem; color: white; background: <?php echo $bg_color; ?>;"
                                                class="vert-cell">जम्मा<br>अंक</th>
                                            <th rowspan="2"
                                                style="font-size: 0.70rem; color: white; background: <?php echo $bg_color; ?>;"
                                                class="vert-cell">प्रतिशत</th>
                                            <th rowspan="2"
                                                style="font-size: 0.70rem; color: white; background: <?php echo $bg_color; ?>;"
                                                class="vert-cell">GRADE</th>
                                            <th rowspan="2"
                                                style="font-size: 0.70rem; color: white; background: <?php echo $bg_color; ?>;"
                                                class="vert-cell">Grade<br>Point</th>
                                            <th rowspan="2"
                                                style="font-size: 0.70rem; color: white; background: <?php echo $bg_color; ?>;"
                                                class="vert-cell">WGP</th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr></tr>
                                <?php elseif ($is_class_4_to_8_final): ?>
                                    <tr>
                                        <?php foreach ($subjects as $idx => $subject):
                                            // Alternate colors for secondary header row based on subject index
                                            $hdr_bg = ($idx % 2 == 0) ? '#4b5563' : '#6b7280';
                                            ?>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">TH</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">G.P.<br>(TH)
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">Grade</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">WGP<br>(TH)
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">PR</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">G.P.<br>(PR)
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">Grade</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">WGP<br>(PR)
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">Total</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">Total<br>WGP
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">TOT.<br>GP
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">FINAL<br>GRADE
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <?php foreach ($subjects as $idx => $subject):
                                            $hdr_bg = ($idx % 2 == 0) ? '#4b5563' : '#6b7280';
                                            $cred = isset($subject_credits[$subject]) ? floatval($subject_credits[$subject]) : 1;
                                            $c_half = number_format($cred / 2, 1);
                                            ?>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">50</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"><?= $c_half ?>
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"></th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"></th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">50</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"><?= $c_half ?>
                                            </th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"></th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"></th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;">100</th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"></th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"></th>
                                            <th style="font-size: 0.70rem; background: <?= $hdr_bg ?>; color: white;"></th>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php elseif ($is_terminal_exam): ?>
                                    <?php
                                    $ter_headers = [
                                        'first_terminal' => '1st Ter',
                                        'second_terminal' => '2nd Ter',
                                        'third_terminal' => '3rd Ter',
                                        'final' => 'Final',
                                        'monthly' => 'Month'
                                    ];
                                    $ter_header = isset($ter_headers[$exam_type]) ? $ter_headers[$exam_type] : 'Ter';
                                    ?>
                                    <tr>
                                        <?php foreach ($subjects as $subject): ?>
                                            <th style="font-size: 0.75rem; color: white; background: #1e40af;">PAR</th>
                                            <th style="font-size: 0.75rem; color: white; background: #1e40af;">PW</th>
                                            <th style="font-size: 0.75rem; color: white; background: #1e40af;">
                                                <?php echo $ter_header; ?>
                                            </th>
                                            <?php if ($exam_type == 'final'): ?>
                                                <th style="font-size: 0.75rem; color: white; background: #1e40af;">EXT</th>
                                            <?php endif; ?>
                                            <th style="font-size: 0.75rem; color: white; background: #1e40af;">Total</th>
                                            <th style="font-size: 0.75rem; color: white; background: #1e40af;">G.P</th>
                                            <th style="font-size: 0.75rem; color: white; background: #1e40af;">Grade</th>
                                            <th style="font-size: 0.75rem; color: white; background: #1e40af;">WGP</th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <?php foreach ($subjects as $subject): ?>
                                            <th style="font-size: 0.7rem; color: white; background: #1e40af;">4</th>
                                            <th style="font-size: 0.7rem; color: white; background: #1e40af;">36</th>
                                            <th style="font-size: 0.7rem; color: white; background: #1e40af;">10</th>
                                            <?php if ($exam_type == 'final'): ?>
                                                <th style="font-size: 0.7rem; color: white; background: #1e40af;">50</th>
                                            <?php endif; ?>
                                            <th style="font-size: 0.7rem; color: white; background: #1e40af;">
                                                <?php echo ($exam_type == 'final') ? '100' : '50'; ?>
                                            </th>
                                            <th style="font-size: 0.7rem; color: white; background: #1e40af;">-</th>
                                            <th style="font-size: 0.7rem; color: white; background: #1e40af;">-</th>
                                            <th style="font-size: 0.7rem; color: white; background: #1e40af;">-</th>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <?php if ($is_class_1_to_3): ?>
                                            <td class="student-info-cell"><?php echo htmlspecialchars($student['full_name']); ?>
                                            </td>
                                            <td class="symbol-cell"><?php echo htmlspecialchars($student['symbol_no']); ?></td>
                                        <?php elseif ($is_class_4_to_8_final): ?>
                                            <td class="student-info-cell" style="font-weight: bold;">
                                                <?php echo htmlspecialchars($student['full_name']); ?>
                                            </td>
                                            <td class="symbol-cell"><?php echo htmlspecialchars($student['dob'] ?? ''); ?></td>
                                            <td class="symbol-cell" style="font-weight: bold;">
                                                <?php echo htmlspecialchars($student['class']); ?>
                                            </td>
                                        <?php else: ?>
                                            <td class="symbol-cell"><?php echo htmlspecialchars($student['symbol_no']); ?></td>
                                            <td class="student-info-cell"><?php echo htmlspecialchars($student['full_name']); ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if ($is_class_1_to_3): ?>
                                            <td class="mark-cell" style="font-size: 0.9rem; font-weight: 700;">
                                                <?php echo isset($attendance_data[$student['id']]) ? $attendance_data[$student['id']] : ''; ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php
                                        $student_total = 0;
                                        $total_gp = 0;
                                        $total_wgp = 0;
                                        $total_credit_hours = 0;
                                        $subject_count = 0;
                                        $has_failed_subject = false;

                                        foreach ($subjects as $subject):
                                            $marks = isset($marks_data[$student['id']][$subject]) ? $marks_data[$student['id']][$subject] : null;
                                            $credit_hour = isset($subject_credits[$subject]) ? floatval($subject_credits[$subject]) : 1;

                                            if ($is_class_1_to_3 || $is_class_4_to_8_final) {
                                                $total_credit_hours += $credit_hour;
                                                $subject_count++;
                                            }

                                            if ($is_class_1_to_3):

                                                $la_total = $marks ? floatval($marks['la_total']) : 0;
                                                $la_obtained_raw = $marks['la_obtained'] ?? null;
                                                $la_obtained = ($la_obtained_raw !== null && $la_obtained_raw !== '') ? floatval($la_obtained_raw) : null;

                                                if ($la_total > 0 && $la_obtained !== null) {
                                                    $percentage = ($la_obtained / $la_total) * 100;

                                                    if ($percentage >= 90) {
                                                        $grade = 'A+';
                                                        $gp = '4.0';
                                                    } elseif ($percentage >= 80) {
                                                        $grade = 'A';
                                                        $gp = '3.6';
                                                    } elseif ($percentage >= 70) {
                                                        $grade = 'B+';
                                                        $gp = '3.2';
                                                    } elseif ($percentage >= 60) {
                                                        $grade = 'B';
                                                        $gp = '2.8';
                                                    } elseif ($percentage >= 50) {
                                                        $grade = 'C+';
                                                        $gp = '2.4';
                                                    } elseif ($percentage >= 40) {
                                                        $grade = 'C';
                                                        $gp = '2.0';
                                                    } elseif ($percentage >= 35) {
                                                        $grade = 'D';
                                                        $gp = '1.6';
                                                    } elseif ($percentage > 0) {
                                                        $grade = 'NG';
                                                        $gp = '-';
                                                        $has_failed_subject = true;
                                                    } else {
                                                        $grade = 'ABS';
                                                        $gp = 'ABS';
                                                        $has_failed_subject = true;
                                                    }
                                                } else {
                                                    $percentage = '';
                                                    $grade = $marks ? 'ABS' : '';
                                                    $gp = $marks ? 'ABS' : '';
                                                    if ($marks)
                                                        $has_failed_subject = true;
                                                }

                                                $display_gp = ($grade === 'ABS') ? 'ABS' : (($grade === 'NG') ? '-' : ($gp !== '' ? number_format((float) $gp, 2) : ''));
                                                $wgp = ($grade === 'ABS') ? 'ABS' : (($grade === 'NG') ? '-' : (($gp !== '') ? number_format(floatval($gp) * $credit_hour, 2) : ''));

                                                if ($gp !== '' && $gp !== '-' && $gp !== 'ABS' && $grade !== 'ABS' && $grade !== 'NG') {
                                                    $total_gp += floatval($gp);
                                                    $total_wgp += floatval($gp) * $credit_hour;
                                                }
                                                ?>
                                                <td class="mark-cell" style="font-size: 0.85rem;"></td>
                                                <td class="mark-cell" style="font-size: 0.85rem;">
                                                    <?php echo $la_total > 0 ? $la_total : ''; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo $la_obtained !== null ? $la_obtained : ''; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem;">
                                                    <?php echo $percentage !== '' ? number_format($percentage, 2) : ''; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo $grade; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo $display_gp; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo $wgp; ?>
                                                </td>
                                                <?php
                                            elseif ($is_class_4_to_8_final):
                                                $th_marks = $marks ? floatval($marks['external'] ?? 0) : 0;
                                                $pr_marks = $marks ? floatval($marks['participation'] ?? 0) + floatval($marks['practical'] ?? 0) + floatval($marks['terminal'] ?? 0) : 0;

                                                if ($marks) {
                                                    $th_gp = floatval(getGradePoint($th_marks, 50));
                                                    $pr_gp = floatval(getGradePoint($pr_marks, 50));

                                                    $th_c = $credit_hour / 2;
                                                    $pr_c = $credit_hour / 2;

                                                    $th_grade = getGrade(number_format($th_gp, 1));
                                                    $pr_grade = getGrade(number_format($pr_gp, 1));

                                                    $th_wgp = $th_gp * $th_c;
                                                    $pr_wgp = $pr_gp * $pr_c;

                                                    $subj_total = $th_marks + $pr_marks;
                                                    $student_total += $subj_total;
                                                    $avg_gp = ($th_wgp + $pr_wgp) / $credit_hour;

                                                    if ($th_gp == 0.0 || $pr_gp == 0.0) {
                                                        $tot_gp = 0.0;
                                                        $has_failed_subject = true;
                                                    } else {
                                                        $tot_gp = floatval(getGradePoint($subj_total, 100));
                                                    }

                                                    $final_grade = getGrade(number_format($tot_gp, 1));

                                                    $total_wgp += ($th_wgp + $pr_wgp);
                                                } else {
                                                    $has_failed_subject = true;
                                                    $th_gp = $pr_gp = $tot_gp = 0;
                                                    $th_grade = $pr_grade = $final_grade = 'NG';
                                                    $th_wgp = $pr_wgp = $avg_gp = 0;
                                                    $subj_total = 0;
                                                }
                                                ?>
                                                <td class="mark-cell"><?= $marks ? $th_marks : 0 ?></td>
                                                <td class="mark-cell"><?= number_format($th_gp, 1) ?></td>
                                                <td class="mark-cell"><?= $th_grade ?></td>
                                                <td class="mark-cell"><?= number_format($th_wgp, 1) ?></td>
                                                <td class="mark-cell"><?= $marks ? $pr_marks : 0 ?></td>
                                                <td class="mark-cell"><?= number_format($pr_gp, 1) ?></td>
                                                <td class="mark-cell"><?= $pr_grade ?></td>
                                                <td class="mark-cell"><?= number_format($pr_wgp, 1) ?></td>
                                                <td class="mark-cell" style="font-weight: bold;"><?= $subj_total ?></td>
                                                <td class="mark-cell" style="font-weight: bold;"><?= number_format($avg_gp, 1) ?></td>
                                                <td class="mark-cell" style="font-weight: bold;"><?= number_format($tot_gp, 1) ?></td>
                                                <td class="mark-cell" style="font-weight: bold;"><?= $final_grade ?></td>
                                                <?php
                                            elseif ($is_terminal_exam):
                                                $pa = $marks ? $marks['participation'] : '';
                                                $pr = $marks ? $marks['practical'] : '';
                                                $te = $marks ? $marks['terminal'] : '';
                                                $ext = $marks ? ($marks['external'] ?? '') : '';
                                                $subject_total = 0;
                                                $gp = '';
                                                $grade = '';
                                                $wgp = '';

                                                if ($marks) {
                                                    $subject_total = ($marks['participation'] ?? 0) + ($marks['practical'] ?? 0) + ($marks['terminal'] ?? 0);
                                                    if ($exam_type == 'final') {
                                                        $subject_total += ($marks['external'] ?? 0);
                                                        $gp = getGradePoint($subject_total, 100);
                                                    } else {
                                                        $gp = getGradePoint($subject_total, 50);
                                                    }
                                                    $student_total += $subject_total;
                                                    $grade = getGrade($gp);
                                                    $wgp = number_format(floatval($gp) * $credit_hour, 2); // WGP = GP × Credit Hour
                                                    $total_gp += floatval($gp);
                                                    $total_wgp += floatval($wgp);
                                                    $total_credit_hours += $credit_hour;
                                                    $subject_count++;
                                                }
                                                ?>
                                                <td class="mark-cell" style="font-size: 0.85rem;"><?php echo $pa !== '' ? $pa : ''; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem;"><?php echo $pr !== '' ? $pr : ''; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem;"><?php echo $te !== '' ? $te : ''; ?>
                                                </td>
                                                <?php if ($exam_type == 'final'): ?>
                                                    <td class="mark-cell" style="font-size: 0.85rem;"><?php echo $ext !== '' ? $ext : ''; ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="total-cell" style="font-size: 0.85rem; background: #f3f4f6;">
                                                    <?php echo $marks ? number_format($subject_total, 0) : ''; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo $gp; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo $grade; ?>
                                                </td>
                                                <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                    <?php echo $wgp; ?>
                                                </td>
                                                <?php
                                            else:
                                                $mark_val = $marks ? floatval($marks['total']) : 0;
                                                if ($marks) {
                                                    $student_total += $mark_val;
                                                }
                                                echo '<td class="mark-cell">' . ($marks ? $marks['total'] : '') . '</td>';
                                            endif;
                                        endforeach;

                                        // Calculate percentage, grade, and GPA
                                        $max_marks = count($subjects) * (($exam_type == 'final') ? 100 : 50);
                                        $percentage = $student_total > 0 ? ($student_total / $max_marks) * 100 : 0;
                                        $final_gpa = $total_credit_hours > 0 ? number_format($total_wgp / $total_credit_hours, 2) : '0.00';
                                        if ($is_class_1_to_3 && $has_failed_subject) {
                                            $final_gpa = '0.00';
                                        }
                                        $final_grade = $final_gpa ? getGrade($final_gpa) : '';
                                        ?>

                                        <?php if ($is_class_1_to_3): ?>
                                            <td class="mark-cell"
                                                style="font-weight: 700; font-size: 0.85rem; background: #dbeafe; color: #1e40af;">
                                                <?php echo $final_gpa; ?>
                                            </td>
                                            <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                <?php echo isset($student_ranks[$student['id']]) ? $student_ranks[$student['id']] : ''; ?>
                                            </td>
                                            <td class="mark-cell" style="font-weight: 600; font-size: 0.85rem;">
                                                <?php echo ($final_gpa !== '' && $final_gpa !== '0.00') ? getRemarks($final_gpa) : 'NOT GRADED'; ?>
                                            </td>
                                        <?php elseif ($is_class_4_to_8_final): ?>
                                            <!-- GPA -->
                                            <td class="mark-cell" style="font-weight: bold; font-size: 0.85rem;">
                                                <?php echo $final_gpa; ?>
                                            </td>
                                            <!-- Total -->
                                            <td class="total-cell" style="font-weight: bold;">
                                                <?php echo $student_total > 0 ? number_format($student_total, 0) : '0'; ?>
                                            </td>
                                            <!-- Attendance -->
                                            <td class="mark-cell">
                                                <?php echo isset($attendance_data[$student['id']]) ? $attendance_data[$student['id']] : ''; ?>
                                            </td>
                                            <td class="mark-cell" style="font-size: 0.85rem;">
                                                <?php
                                                // Convert GPA safely to number, fallback if blank
                                                $remark_val = is_numeric($final_gpa) && floatval($final_gpa) > 0 ? getRemarks($final_gpa) : 'NOT GRADED';
                                                echo $remark_val;
                                                ?>
                                            </td>
                                            <td class="mark-cell" style="font-weight: bold;">
                                                <?php echo isset($student_ranks[$student['id']]) ? $student_ranks[$student['id']] : ''; ?>
                                            </td>
                                        <?php elseif ($is_terminal_exam): ?>
                                            <!-- GPA -->
                                            <td class="mark-cell"
                                                style="font-weight: 700; font-size: 0.85rem; background: #dbeafe; color: #1e40af;">
                                                <?php echo $final_gpa; ?>
                                            </td>
                                            <!-- Total -->
                                            <td class="total-cell"
                                                style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.9rem;">
                                                <?php echo $student_total > 0 ? number_format($student_total, 0) : ''; ?>
                                            </td>
                                            <!-- Attendance -->
                                            <td class="mark-cell"
                                                style="font-size: 0.9rem; font-weight: 700; background: #d1fae5; color: #065f46; text-align: center;">
                                                <?php echo isset($attendance_data[$student['id']]) ? $attendance_data[$student['id']] : '-'; ?>
                                            </td>
                                            <!-- Rank -->
                                            <td class="mark-cell" style="font-size: 0.85rem; font-weight: 600;">
                                                <?php echo isset($student_ranks[$student['id']]) ? $student_ranks[$student['id']] : ''; ?>
                                            </td>
                                            <!-- Remarks -->
                                            <td class="mark-cell" style="font-weight: 600; color: #7c3aed; font-size: 0.85rem;">
                                                <?php echo $final_gpa !== '' ? getRemarks($final_gpa) : ''; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function exportToExcel() {
            var table = document.getElementById("ledgerTable");

            // Create a new workbook
            var wb = XLSX.utils.book_new();

            // Clone the table to avoid modifying the DOM
            var tableClone = table.cloneNode(true);

            // Convert table to worksheet
            var ws = XLSX.utils.table_to_sheet(tableClone, { raw: true });

            // Set col widths if needed (optional)
            // ws['!cols'] = [{wch: 5}, {wch: 10}, {wch: 20}]; 

            // Append worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, "Mark Ledger");

            // Generate filename
            var filename = "Mark_Ledger_<?php echo $selected_class . '_' . $exam_type; ?>.xlsx";

            // Write file
            XLSX.writeFile(wb, filename);
        }
    </script>
</body>

</html>