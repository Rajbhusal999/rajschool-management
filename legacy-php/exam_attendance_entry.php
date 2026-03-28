<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$msg = '';
$msg_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $year = $_POST['year'];
    $exam_type = $_POST['exam_type'];
    $class = $_POST['class'];
    $attendance_data = $_POST['attendance'];

    $count = 0;
    foreach ($attendance_data as $student_id => $days_present) {
        if ($days_present !== '') {
            // Check if record exists
            $check_sql = "SELECT id FROM exam_attendance WHERE school_id = ? AND student_id = ? AND year = ? AND exam_type = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$school_id, $student_id, $year, $exam_type]);

            if ($row = $check_stmt->fetch()) {
                // Update existing record
                $update_sql = "UPDATE exam_attendance SET days_present = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->execute([$days_present, $row['id']]);
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO exam_attendance (school_id, student_id, year, exam_type, days_present) 
                              VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->execute([$school_id, $student_id, $year, $exam_type, $days_present]);
            }
            $count++;
        }
    }

    $msg = "Attendance for $count students saved successfully!";
    $msg_type = 'success';
}

// Fetch academic years
$years_sql = "SELECT DISTINCT year FROM exam_marks WHERE school_id = ? ORDER BY year DESC";
$years_stmt = $conn->prepare($years_sql);
$years_stmt->execute([$school_id]);
$years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch classes
$classes_sql = "SELECT DISTINCT class FROM students WHERE school_id = ? ORDER BY class";
$classes_stmt = $conn->prepare($classes_sql);
$classes_stmt->execute([$school_id]);
$classes = $classes_stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch students if filters are set
$students = [];
$selected_year = isset($_GET['year']) ? $_GET['year'] : '';
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$selected_exam = isset($_GET['exam_type']) ? $_GET['exam_type'] : '';

if ($selected_year && $selected_class && $selected_exam) {
    $students_sql = "SELECT s.id, s.full_name, s.symbol_no, ea.days_present
                     FROM students s
                     LEFT JOIN exam_attendance ea ON s.id = ea.student_id 
                         AND ea.school_id = ? AND ea.year = ? AND ea.exam_type = ?
                     WHERE s.school_id = ? AND s.class = ?
                     ORDER BY s.full_name";
    $students_stmt = $conn->prepare($students_sql);
    $students_stmt->execute([$school_id, $selected_year, $selected_exam, $school_id, $selected_class]);
    $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
}

$exam_titles = [
    'first_terminal' => 'First Terminal Examination',
    'second_terminal' => 'Second Terminal Examination',
    'third_terminal' => 'Third Terminal Examination',
    'final' => 'Final Examination',
    'monthly' => 'Monthly Examination'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Attendance Entry - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 1.25rem 2rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-left: 6px solid #10b981;
        }

        .header-content h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 800;
            color: #111827;
        }

        .header-content p {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .form-select {
            padding: 0.625rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.9rem;
            background: #f9fafb;
            transition: all 0.2s;
        }

        .form-select:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .attendance-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }

        .attendance-table thead th {
            background: #f8fafc;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        .attendance-table tbody td {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .attendance-table tbody tr:hover {
            background: #f1f5f9;
        }

        .attendance-input {
            width: 80px;
            padding: 0.4rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .attendance-input:focus {
            border-color: #10b981;
            outline: none;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .info-box {
            background: #f0f9ff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #0c4a6e;
            border: 1px dashed #7dd3fc;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>Exam Attendance</h1>
                <p>Record scholar presence for evaluation reports</p>
            </div>
            <button class="btn btn-secondary" onclick="window.location.href='exams.php'">
                <i class="fas fa-arrow-left"></i> Back to Portal
            </button>
        </div>

        <div class="card">
            <?php if ($msg): ?>
                <div class="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <div class="info-box-title">
                    <i class="fas fa-info-circle"></i>
                    Important Information
                </div>
                <div class="info-box-text">
                    • Select academic year, exam type, and class to load students<br>
                    • Enter the number of days each student was present during the exam period<br>
                    • This attendance will appear in the Mark Ledger report<br>
                    • Leave blank if no attendance to record
                </div>
            </div>

            <form method="GET" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt"></i> Academic Year
                        </label>
                        <select name="year" class="form-select" required onchange="this.form.submit()">
                            <option value="">Select Academic Year</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clipboard-list"></i> Exam Type
                        </label>
                        <select name="exam_type" class="form-select" required onchange="this.form.submit()">
                            <option value="">Select Exam Type</option>
                            <option value="first_terminal" <?php echo ($selected_exam == 'first_terminal') ? 'selected' : ''; ?>>First Terminal Exam</option>
                            <option value="second_terminal" <?php echo ($selected_exam == 'second_terminal') ? 'selected' : ''; ?>>Second Terminal Exam</option>
                            <option value="third_terminal" <?php echo ($selected_exam == 'third_terminal') ? 'selected' : ''; ?>>Third Terminal Exam</option>
                            <option value="final" <?php echo ($selected_exam == 'final') ? 'selected' : ''; ?>>Final
                                Exam</option>
                            <option value="monthly" <?php echo ($selected_exam == 'monthly') ? 'selected' : ''; ?>>Monthly
                                Exam</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-users"></i> Class
                        </label>
                        <select name="class" class="form-select" required onchange="this.form.submit()">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>" <?php echo ($selected_class == $class) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (!empty($students)): ?>
                <form method="POST" action="">
                    <input type="hidden" name="year" value="<?php echo htmlspecialchars($selected_year); ?>">
                    <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($selected_exam); ?>">
                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($selected_class); ?>">

                    <h3 style="color: #1f2937; margin-top: 2rem; margin-bottom: 1rem;">
                        <?php echo $exam_titles[$selected_exam] ?? $selected_exam; ?> - Class
                        <?php echo htmlspecialchars($selected_class); ?>
                    </h3>

                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">S.N.</th>
                                <th style="width: 120px;">Symbol No</th>
                                <th>Student Name</th>
                                <th style="width: 150px; text-align: center;">Days Present</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $student): ?>
                                <tr>
                                    <td>
                                        <?php echo $index + 1; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student['symbol_no'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="student-name">
                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="number" name="attendance[<?php echo $student['id']; ?>]"
                                            class="attendance-input" min="0" max="365"
                                            value="<?php echo htmlspecialchars($student['days_present'] ?? ''); ?>"
                                            placeholder="0">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="btn-group">
                        <button type="submit" name="save_attendance" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='exams.php'">
                            <i class="fas fa-arrow-left"></i> Back to Exams
                        </button>
                    </div>
                </form>
            <?php elseif ($selected_year && $selected_class && $selected_exam): ?>
                <div class="no-data">
                    <i class="fas fa-inbox"></i><br>
                    No students found for this selection
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>