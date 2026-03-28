<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

// Auto-create table if missing
$conn->exec("CREATE TABLE IF NOT EXISTS exam_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT,
    class VARCHAR(50),
    exam_type VARCHAR(50),
    year VARCHAR(10),
    shift VARCHAR(50),
    time VARCHAR(50),
    subject_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$school_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? $_GET['year'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$exam_type = isset($_GET['exam']) ? $_GET['exam'] : '';

if (empty($year) || empty($class) || empty($exam_type)) {
    header("Location: admit_card_selector.php");
    exit();
}

$exam_names = [
    'first_terminal' => 'First Terminal Examination',
    'second_terminal' => 'Second Terminal Examination',
    'third_terminal' => 'Third Terminal Examination',
    'final' => 'Final Examination',
    'monthly' => 'Monthly Assessment'
];

// Handle Save Schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_schedule'])) {
    $shift = $_POST['shift'];
    $time = $_POST['time'];
    $subject_data = [];

    // Process subject and date rows
    for ($i = 0; $i < count($_POST['subject_names']); $i++) {
        if (!empty($_POST['subject_names'][$i])) {
            $subject_data[] = [
                'subject' => $_POST['subject_names'][$i],
                'date' => $_POST['exam_dates'][$i]
            ];
        }
    }

    $subject_json = json_encode($subject_data);

    // Check if schedule exists
    $check_sql = "SELECT id FROM exam_schedules WHERE school_id = ? AND class = ? AND exam_type = ? AND year = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$school_id, $class, $exam_type, $year]);

    if ($row = $check_stmt->fetch()) {
        $sql = "UPDATE exam_schedules SET shift = ?, time = ?, subject_data = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$shift, $time, $subject_json, $row['id']]);
    } else {
        $sql = "INSERT INTO exam_schedules (school_id, class, exam_type, year, shift, time, subject_data) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$school_id, $class, $exam_type, $year, $shift, $time, $subject_json]);
    }
    $success_msg = "Schedule updated successfully!";
}

// Fetch Existing Schedule
$schedule_sql = "SELECT * FROM exam_schedules WHERE school_id = ? AND class = ? AND exam_type = ? AND year = ?";
$schedule_stmt = $conn->prepare($schedule_sql);
$schedule_stmt->execute([$school_id, $class, $exam_type, $year]);
$schedule = $schedule_stmt->fetch(PDO::FETCH_ASSOC);

$existing_shift = $schedule ? $schedule['shift'] : 'DAY';
$existing_time = $schedule ? $schedule['time'] : '10:00 - 01:00';
$existing_subjects = $schedule ? json_decode($schedule['subject_data'], true) : [];

// Fetch Students
$student_sql = "SELECT id, full_name, symbol_no FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->execute([$school_id, $class]);
$students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card Configuration - Smart विद्यालय</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding-bottom: 50px;
        }

        .main-content {
            padding: 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .config-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .label {
            display: block;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .input {
            width: 100%;
            padding: 0.75rem;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .schedule-table th,
        .schedule-table td {
            padding: 10px;
            text-align: left;
        }

        .schedule-table input {
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            width: 100%;
            box-sizing: border-box;
        }

        .student-list {
            max-height: 500px;
            overflow-y: auto;
            margin-top: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }

        .student-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            transition: 0.2s;
        }

        .student-item:hover {
            background: #f9fafb;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: #10b981;
            color: white;
            width: 100%;
            justify-content: center;
        }

        .sticky-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="main-content">
        <div class="breadcrumb">
            <a href="admit_card_selector.php"><i class="fas fa-chevron-left"></i> Change Selection</a>
            <span>/</span>
            <span>Configure Details</span>
        </div>

        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; font-weight: 800; color: #1f2937; margin-bottom: 0.5rem;">Admit Card Setup</h1>
            <p style="color: #6b7280;">
                <?php echo $exam_names[$exam_type]; ?> • Class
                <?php echo $class; ?> •
                <?php echo $year; ?>
            </p>
        </div>

        <form method="POST" id="configForm">
            <div class="config-grid">
                <!-- Schedule Configuration -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-calendar-alt"></i> Exam Schedule & Subjects</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="label">Shift (e.g., DAY / MORNING)</label>
                            <input type="text" name="shift" class="input"
                                value="<?php echo htmlspecialchars($existing_shift); ?>" placeholder="DAY" required>
                        </div>
                        <div class="form-group">
                            <label class="label">Exam Time (e.g., 10:00 - 01:00)</label>
                            <input type="text" name="time" class="input"
                                value="<?php echo htmlspecialchars($existing_time); ?>" placeholder="10:00 - 01:00"
                                required>
                        </div>
                    </div>

                    <table class="schedule-table" id="scheduleTable">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Subject Name</th>
                                <th>Exam Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            for ($i = 0; $i < 10; $i++):
                                $sub = isset($existing_subjects[$i]) ? $existing_subjects[$i]['subject'] : '';
                                $date = isset($existing_subjects[$i]) ? $existing_subjects[$i]['date'] : '';
                                ?>
                                <tr>
                                    <td><input type="text" name="subject_names[]"
                                            value="<?php echo htmlspecialchars($sub); ?>" placeholder="Enter Subject"></td>
                                    <td><input type="text" name="exam_dates[]" class="exam-date-input" maxlength="10"
                                            value="<?php echo htmlspecialchars($date); ?>"
                                            placeholder="<?php echo $year; ?>/MM/DD"></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    <p style="font-size: 0.85rem; color: #6b7280; margin-top: 1rem;">* Enter subjects in the order you
                        want them to appear on the admit card.</p>
                </div>

                <!-- Student Selection -->
                <div class="card">
                    <div class="card-title"><i class="fas fa-users"></i> Select Students</div>
                    <div style="margin-bottom: 1rem; display: flex; gap: 10px;">
                        <button type="button" class="btn" style="background:#e5e7eb; font-size: 0.85rem;"
                            onclick="toggleAll(true)">Select All</button>
                        <button type="button" class="btn" style="background:#e5e7eb; font-size: 0.85rem;"
                            onclick="toggleAll(false)">Deselect All</button>
                    </div>

                    <div class="student-list" id="studentSelectionList">
                        <?php foreach ($students as $student): ?>
                            <label class="student-item">
                                <input type="checkbox" name="selected_students[]" value="<?php echo $student['id']; ?>"
                                    checked>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: #374151;">
                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #6b7280;">Symbol No:
                                        <?php echo htmlspecialchars($student['symbol_no'] ?: 'N/A'); ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="sticky-bar">
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <div style="font-weight: 700; color: #1f2937;">Configuring: <span style="color:var(--primary);">
                            <?php echo count($students); ?> Students
                        </span></div>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="save_schedule" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Schedule
                    </button>
                    <button type="button" onclick="generateAdmitCards()" class="btn btn-success" style="width: auto;">
                        <i class="fas fa-print"></i> Generate & Print Admit Cards
                    </button>
                </div>
            </div>

            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <input type="hidden" name="class" value="<?php echo $class; ?>">
            <input type="hidden" name="exam" value="<?php echo $exam_type; ?>">
        </form>
    </div>

    <script>
        // Auto-format slashes while typing
        document.addEventListener('keydown', function (e) {
            if (e.target.classList.contains('exam-date-input')) {
                const val = e.target.value;
                if (e.key !== 'Backspace') {
                    if (val.length === 4 || val.length === 7) {
                        e.target.value = val + '/';
                    }
                }
            }
        });

        // Auto-propagate dates (Improved for Nepali Year / Slahses)
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('exam-date-input')) {
                const inputs = Array.from(document.querySelectorAll('.exam-date-input'));
                const index = inputs.indexOf(e.target);

                if (index === 0 && e.target.value.length === 10) {
                    const parts = e.target.value.split('/');
                    if (parts.length === 3) {
                        let y = parseInt(parts[0]);
                        let m = parseInt(parts[1]);
                        let d = parseInt(parts[2]);

                        for (let i = 1; i < inputs.length; i++) {
                            if (inputs[i].value === '') {
                                d++;
                                // Basic overflow check (approximate for Nepali months)
                                if (d > 32) { d = 1; m++; }
                                if (m > 12) { m = 1; y++; }

                                const pad = (num) => num.toString().padStart(2, '0');
                                inputs[i].value = `${y}/${pad(m)}/${pad(d)}`;
                            }
                        }
                    }
                }
            }
        });

        function toggleAll(checked) {
            const checkboxes = document.querySelectorAll('input[name="selected_students[]"]');
            checkboxes.forEach(cb => cb.checked = checked);
        }

        function generateAdmitCards() {
            const form = document.getElementById('configForm');
            const selected = Array.from(document.querySelectorAll('input[name="selected_students[]"]:checked')).map(cb => cb.value);

            if (selected.length === 0) {
                alert('Please select at least one student.');
                return;
            }

            // Open print page in new window
            const queryParams = new URLSearchParams({
                year: '<?php echo $year; ?>',
                class: '<?php echo $class; ?>',
                exam: '<?php echo $exam_type; ?>',
                students: selected.join(',')
            });
            window.open('admit_card_print.php?' + queryParams.toString(), '_blank');
        }
    </script>
</body>

</html>