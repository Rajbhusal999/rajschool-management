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

// Check if it represents a standard exam with components
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

// Get selected filters first (needed for both GET and POST)
$current_nepali_year = date('Y') + 56; // Convert to Nepali year (approximate)
$selected_year = isset($_GET['year']) ? $_GET['year'] : (isset($_POST['year']) ? $_POST['year'] : $current_nepali_year);
$selected_class = isset($_GET['class']) ? $_GET['class'] : (isset($_POST['class']) ? $_POST['class'] : '');
$selected_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : (isset($_POST['student_id']) ? $_POST['student_id'] : '');

// Automatically redirect PG, KG, Nursery classes to their specific mark entry page
if (in_array(strtoupper($selected_class), ['PG', 'KG', 'LKG', 'UKG', 'NURSERY'])) {
    $redirect_url = "mark_entry_pg_kg.php?exam=" . urlencode($exam_type) . "&year=" . urlencode($selected_year) . "&class=" . urlencode($selected_class);
    if (!empty($selected_student_id)) {
        $redirect_url .= "&student_id=" . urlencode($selected_student_id);
    }
    header("Location: $redirect_url");
    exit();
}

// Fetch subjects based on class group (needed before form processing)
$subjects = [];
if (!empty($selected_class)) {
    $class_group = getClassGroup($selected_class);
    $sql = "SELECT subject_name, credit_hour FROM subjects WHERE school_id = ? AND class_group = ? ORDER BY subject_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class_group]);
    $subject_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Extract subject names
    foreach ($subject_data as $subject) {
        $subjects[] = $subject['subject_name'];
    }
}

// Handle Mark Entry Submission
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_marks'])) {
    $year = $_POST['year'];
    $class = $_POST['class'];
    $student_id = $_POST['student_id'];

    $success_count = 0;

    foreach ($subjects as $subject) {
        $is_class_1_to_3_post = in_array($class, ['1', '2', '3']);
        if ($is_class_1_to_3_post) {
            $la_total = isset($_POST['la_total'][$student_id][$subject]) ? $_POST['la_total'][$student_id][$subject] : null;
            $la_obtained = isset($_POST['la_obtained'][$student_id][$subject]) ? $_POST['la_obtained'][$student_id][$subject] : null;
            $remarks = isset($_POST['remarks'][$student_id][$subject]) ? $_POST['remarks'][$student_id][$subject] : null;

            if (($la_total !== null && $la_total !== '') || ($la_obtained !== null && $la_obtained !== '')) {
                $check_sql = "SELECT id FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->execute([$school_id, $student_id, $exam_type, $subject, $year]);

                if ($check_stmt->fetch()) {
                    $update_sql = "UPDATE exam_marks SET la_total = ?, la_obtained = ?, remarks = ? WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->execute([$la_total, $la_obtained, $remarks, $school_id, $student_id, $exam_type, $subject, $year]);
                } else {
                    $insert_sql = "INSERT INTO exam_marks (school_id, student_id, exam_type, subject, la_total, la_obtained, remarks, year, class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->execute([$school_id, $student_id, $exam_type, $subject, $la_total, $la_obtained, $remarks, $year, $class]);
                }
                $success_count++;
            }
        } elseif ($is_terminal_exam) {
            $participation = isset($_POST['participation'][$student_id][$subject]) ? $_POST['participation'][$student_id][$subject] : null;
            $practical = isset($_POST['practical'][$student_id][$subject]) ? $_POST['practical'][$student_id][$subject] : null;
            $terminal = isset($_POST['terminal'][$student_id][$subject]) ? $_POST['terminal'][$student_id][$subject] : null;
            $external = isset($_POST['external'][$student_id][$subject]) ? $_POST['external'][$student_id][$subject] : null;

            // Only save if at least one mark is entered
            if ($participation !== null && $participation !== '' || $practical !== null && $practical !== '' || $terminal !== null && $terminal !== '' || $external !== null && $external !== '') {
                // Check if marks already exist
                $check_sql = "SELECT id FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->execute([$school_id, $student_id, $exam_type, $subject, $year]);

                if ($check_stmt->fetch()) {
                    // Update existing
                    $update_sql = "UPDATE exam_marks SET participation = ?, practical = ?, terminal = ?, external = ? WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->execute([$participation, $practical, $terminal, $external, $school_id, $student_id, $exam_type, $subject, $year]);
                } else {
                    // Insert new
                    $insert_sql = "INSERT INTO exam_marks (school_id, student_id, exam_type, subject, participation, practical, terminal, external, year, class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->execute([$school_id, $student_id, $exam_type, $subject, $participation, $practical, $terminal, $external, $year, $class]);
                }
                $success_count++;
            }
        } else {
            $total = isset($_POST['total'][$student_id][$subject]) ? $_POST['total'][$student_id][$subject] : null;

            if ($total !== null && $total !== '') {
                $check_sql = "SELECT id FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->execute([$school_id, $student_id, $exam_type, $subject, $year]);

                if ($check_stmt->fetch()) {
                    $update_sql = "UPDATE exam_marks SET total = ? WHERE school_id = ? AND student_id = ? AND exam_type = ? AND subject = ? AND year = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->execute([$total, $school_id, $student_id, $exam_type, $subject, $year]);
                } else {
                    $insert_sql = "INSERT INTO exam_marks (school_id, student_id, exam_type, subject, total, year, class) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->execute([$school_id, $student_id, $exam_type, $subject, $total, $year, $class]);
                }
                $success_count++;
            }
        }
    }

    header("Location: mark_ledger.php?exam=$exam_type&year=$year&class=$class&msg=" . urlencode("Marks for $success_count subjects saved successfully!"));
    exit();
}


// Fetch students if class is selected
$students = [];
if (!empty($selected_class)) {
    $sql = "SELECT id, full_name, symbol_no, class FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $selected_class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Fetch existing marks if student is selected
$existing_marks = [];
if (!empty($selected_student_id)) {
    $is_class_1_to_3 = in_array($selected_class, ['1', '2', '3']);
    if ($is_class_1_to_3) {
        $marks_sql = "SELECT subject, la_total, la_obtained, remarks FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND year = ?";
    } elseif ($is_terminal_exam) {
        $marks_sql = "SELECT subject, participation, practical, terminal, external FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND year = ?";
    } else {
        $marks_sql = "SELECT subject, total FROM exam_marks WHERE school_id = ? AND student_id = ? AND exam_type = ? AND year = ?";
    }
    $marks_stmt = $conn->prepare($marks_sql);
    $marks_stmt->execute([$school_id, $selected_student_id, $exam_type, $selected_year]);
    $marks_data = $marks_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($marks_data as $mark) {
        $existing_marks[$mark['subject']] = $mark;
    }
}

// Get unique classes
$class_sql = "SELECT DISTINCT class FROM students WHERE school_id = ? ORDER BY class";
$class_stmt = $conn->prepare($class_sql);
$class_stmt->execute([$school_id]);
$classes = $class_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Entry - <?php echo $exam_name; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #f8fafc;
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
            color: #1e293b;
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
            background: linear-gradient(135deg, #6366f1, #4f46e5);
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
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
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
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .content-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .marks-container {
            flex: 1;
            overflow-y: auto;
            padding: 2.5rem;
        }

        /* Student Selection Grid */
        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .student-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .student-card:hover {
            border-color: #6366f1;
            background: #fff;
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .student-avatar {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            color: #4f46e5;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.1);
        }

        .student-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        /* Data Entry Table */
        .table-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
        }

        .marks-table th {
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

        .marks-table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid #f8fafc;
        }

        .mark-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            text-align: center;
            font-weight: 800;
            font-size: 1.1rem;
            color: #4f46e5;
            background: #fff;
            transition: all 0.2s;
        }

        .mark-input:focus {
            border-color: #6366f1;
            outline: none;
            background: #f5f3ff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .save-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 1.25rem 3rem;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .save-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
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
                padding: 1.5rem;
            }

            .main-content {
                height: auto;
                overflow: visible;
            }

            .marks-container {
                padding: 1.5rem;
            }

            .content-header {
                padding: 1rem 1.5rem;
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
                <h2 class="sidebar-title"><i class="fas fa-edit"></i> Mark Entry</h2>
                <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;"><?php echo $exam_name; ?></p>
            </div>

            <form method="GET" action="">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-clipboard-list"></i> Exam Type
                    </label>
                    <select name="exam" class="filter-select" required onchange="this.form.submit()">
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
                                Class <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Load Students
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
            <div class="content-header">
                <h1 class="content-title"><i class="fas fa-edit"></i> Mark Entry System</h1>
                <?php if (!empty($selected_class)): ?>
                    <div
                        style="font-size: 0.85rem; font-weight: 700; background: #eef2ff; color: #4f46e5; padding: 0.5rem 1rem; border-radius: 8px;">
                        Class <?php echo htmlspecialchars($selected_class); ?> •
                        <?php echo htmlspecialchars($selected_year); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="marks-container">
                <?php if ($msg): ?>
                    <div
                        style="background: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 10px; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($students)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                        <h3 class="empty-title">Ready to Start?</h3>
                        <p class="empty-text">Select exam details and class from the sidebar to load student rosters.</p>
                    </div>
                <?php elseif (empty($selected_student_id)): ?>
                    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b;">Select Student to Enter Marks</h3>
                        <span
                            style="font-size: 0.75rem; font-weight: 700; color: #64748b; background: white; padding: 4px 10px; border-radius: 20px; border: 1px solid #e2e8f0;"><?php echo count($students); ?>
                            Students found</span>
                    </div>
                    <div class="student-grid">
                        <?php foreach ($students as $student): ?>
                            <div class="student-card"
                                onclick="window.location.href='mark_entry.php?exam=<?php echo $exam_type; ?>&year=<?php echo $selected_year; ?>&class=<?php echo urlencode($selected_class); ?>&student_id=<?php echo $student['id']; ?>'">
                                <div class="student-avatar"><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></div>
                                <div class="student-info">
                                    <div class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Reg:
                                        <?php echo htmlspecialchars($student['symbol_no']); ?>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right" style="color: #cbd5e1; font-size: 0.8rem;"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php
                    $selected_student = null;
                    foreach ($students as $student)
                        if ($student['id'] == $selected_student_id) {
                            $selected_student = $student;
                            break;
                        }
                    ?>

                    <div
                        style="background: white; padding: 1rem 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div class="student-avatar" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                <?php echo strtoupper(substr($selected_student['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-size: 1.1rem; font-weight: 800; color: #111827;">
                                    <?php echo htmlspecialchars($selected_student['full_name']); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Symbol:
                                    <?php echo htmlspecialchars($selected_student['symbol_no']); ?> • Roll Index:
                                    #<?php echo $selected_student_id; ?>
                                </div>
                            </div>
                        </div>
                        <a href="mark_entry.php?exam=<?php echo $exam_type; ?>&year=<?php echo $selected_year; ?>&class=<?php echo urlencode($selected_class); ?>"
                            class="btn-filter"
                            style="width: auto; padding: 0.5rem 1rem; background: #f1f5f9; color: #475569;">
                            <i class="fas fa-users"></i> List Students
                        </a>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="save_marks" value="1">
                        <input type="hidden" name="exam" value="<?php echo htmlspecialchars($exam_type); ?>">
                        <input type="hidden" name="year" value="<?php echo htmlspecialchars($selected_year); ?>">
                        <input type="hidden" name="class" value="<?php echo htmlspecialchars($selected_class); ?>">
                        <input type="hidden" name="student_id"
                            value="<?php echo htmlspecialchars($selected_student_id); ?>">

                        <div class="table-card">
                            <table class="marks-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">S.N.</th>
                                        <th>Subject Name</th>
                                        <?php if ($is_class_1_to_3): ?>
                                            <th style="width: 15%;">Total LA (जम्मा सिकाई उपलब्धी)</th>
                                            <th style="width: 15%;">Obtained LA (प्राप्त सिकाई उपलब्धी)</th>
                                            <th style="width: 15%;">Percentage (उपलब्धी प्रतिशत)</th>
                                            <th style="width: 25%;">Remarks (कैफियत)</th>
                                        <?php elseif ($is_terminal_exam): ?>
                                            <th style="width: <?php echo ($exam_type == 'final') ? '15%' : '20%'; ?>;">
                                                Participation (4)</th>
                                            <th style="width: <?php echo ($exam_type == 'final') ? '20%' : '25%'; ?>;">
                                                Practical/Project (36)</th>
                                            <th style="width: <?php echo ($exam_type == 'final') ? '20%' : '25%'; ?>;">Terminal
                                                (10)</th>
                                            <?php if ($exam_type == 'final'): ?>
                                                <th style="width: 15%;">External (50)</th>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <th style="width: 45%;">Marks</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $index => $subject): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td>
                                                <span class="subject-name"><?php echo htmlspecialchars($subject); ?></span>
                                            </td>
                                            <?php if ($is_class_1_to_3): ?>
                                                <td>
                                                    <input type="number"
                                                        name="la_total[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="Total" min="0" step="0.01"
                                                        style="width: 100%; max-width: 120px;"
                                                        value="<?php echo isset($existing_marks[$subject]['la_total']) ? htmlspecialchars($existing_marks[$subject]['la_total']) : ''; ?>"
                                                        oninput="calculatePercentage(this, <?php echo $index; ?>)">
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="la_obtained[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="Obtained" min="0" step="0.01"
                                                        style="width: 100%; max-width: 120px;"
                                                        value="<?php echo isset($existing_marks[$subject]['la_obtained']) ? htmlspecialchars($existing_marks[$subject]['la_obtained']) : ''; ?>"
                                                        oninput="calculatePercentage(this, <?php echo $index; ?>)">
                                                </td>
                                                <td>
                                                    <input type="text" id="percentage_<?php echo $index; ?>" class="mark-input"
                                                        readonly
                                                        style="width: 100%; max-width: 100px; background-color: #f9fafb; border: none; font-weight: 700; color: #4f46e5;"
                                                        value="">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        name="remarks[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="Remarks" style="width: 100%;"
                                                        value="<?php echo isset($existing_marks[$subject]['remarks']) ? htmlspecialchars($existing_marks[$subject]['remarks']) : ''; ?>">
                                                </td>
                                            <?php elseif ($is_terminal_exam): ?>
                                                <td>
                                                    <span class="mark-label">Max: 4</span>
                                                    <input type="number"
                                                        name="participation[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="0-4" min="0" max="4" step="0.01"
                                                        value="<?php echo isset($existing_marks[$subject]) ? htmlspecialchars($existing_marks[$subject]['participation']) : ''; ?>">
                                                </td>
                                                <td>
                                                    <span class="mark-label">Max: 36</span>
                                                    <input type="number"
                                                        name="practical[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="0-36" min="0" max="36" step="0.01"
                                                        value="<?php echo isset($existing_marks[$subject]) ? htmlspecialchars($existing_marks[$subject]['practical']) : ''; ?>">
                                                </td>
                                                <td>
                                                    <span class="mark-label">Max: 10</span>
                                                    <input type="number"
                                                        name="terminal[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="0-10" min="0" max="10" step="0.01"
                                                        value="<?php echo isset($existing_marks[$subject]) ? htmlspecialchars($existing_marks[$subject]['terminal']) : ''; ?>">
                                                </td>
                                                <?php if ($exam_type == 'final'): ?>
                                                    <td>
                                                        <span class="mark-label">Max: 50</span>
                                                        <input type="number"
                                                            name="external[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                            class="mark-input" placeholder="0-50" min="0" max="50" step="0.01"
                                                            value="<?php echo isset($existing_marks[$subject]) ? htmlspecialchars($existing_marks[$subject]['external']) : ''; ?>">
                                                    </td>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <td>
                                                    <input type="number"
                                                        name="total[<?php echo $selected_student_id; ?>][<?php echo htmlspecialchars($subject); ?>]"
                                                        class="mark-input" placeholder="Total" min="0" step="0.01"
                                                        value="<?php echo isset($existing_marks[$subject]) ? htmlspecialchars($existing_marks[$subject]['total']) : ''; ?>">
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

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
        function calculatePercentage(element, index) {
            const row = element.closest('tr');
            const totalInput = row.querySelector('input[name^="la_total"]');
            const obtainedInput = row.querySelector('input[name^="la_obtained"]');
            const percentageInput = document.getElementById('percentage_' + index);

            if (totalInput && obtainedInput && percentageInput) {
                const total = parseFloat(totalInput.value);
                const obtained = parseFloat(obtainedInput.value);

                if (!isNaN(total) && !isNaN(obtained) && total > 0) {
                    const percentage = (obtained / total) * 100;
                    percentageInput.value = percentage.toFixed(2) + '%';
                } else {
                    percentageInput.value = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const obtainedInputs = document.querySelectorAll('input[name^="la_obtained"]');
            obtainedInputs.forEach((input, index) => {
                calculatePercentage(input, index);
            });
        });

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