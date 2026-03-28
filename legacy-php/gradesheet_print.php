<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? $_GET['year'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$print_type = isset($_GET['print_type']) ? $_GET['print_type'] : 'all';
$terminal = isset($_GET['terminal']) ? $_GET['terminal'] : '';

// Validate terminal parameter
$valid_terminals = ['first_terminal', 'second_terminal', 'third_terminal', 'final', 'monthly'];
if (!in_array($terminal, $valid_terminals)) {
    header("Location: gradesheet_selector.php");
    exit();
}

if (empty($year) || empty($class) || empty($terminal)) {
    header("Location: gradesheet_selector.php");
    exit();
}

// Function to determine class group
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

    return '1-3';
}

$is_class_1_to_3 = in_array((string) $class, ['1', '2', '3']);
$is_pre_primary = in_array(strtoupper((string) $class), ['NURSERY', 'PG', 'KG', 'LKG', 'UKG']);

// Get school info
$school_sql = "SELECT school_name, address, school_logo, estd_date FROM schools WHERE id = ?";
$school_stmt = $conn->prepare($school_sql);
$school_stmt->execute([$school_id]);
$school_info = $school_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch students
$students = [];
if ($print_type === 'selected' && isset($_GET['students']) && is_array($_GET['students'])) {
    $student_ids = $_GET['students'];
    $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
    $sql = "SELECT id, full_name, symbol_no, class FROM students 
            WHERE school_id = ? AND class = ? AND id IN ($placeholders) 
            ORDER BY full_name ASC";
    $params = array_merge([$school_id, $class], $student_ids);
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT id, full_name, symbol_no, class FROM students 
            WHERE school_id = ? AND class = ? 
            ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($students)) {
    echo "<script>alert('No students found.'); window.location.href='gradesheet_selector.php';</script>";
    exit();
}

// Fetch subjects
$class_group = getClassGroup($class);
$sql = "SELECT subject_name, credit_hour FROM subjects 
        WHERE school_id = ? AND class_group = ? 
        ORDER BY subject_name";
$stmt = $conn->prepare($sql);
$stmt->execute([$school_id, $class_group]);
$subject_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = [];
$subject_credits = [];
foreach ($subject_data as $subject) {
    $subjects[] = $subject['subject_name'];
    $subject_credits[$subject['subject_name']] = $subject['credit_hour'];
}

// Fetch marks for selected exam type
$marks_data = [];
if (!empty($students) && !empty($subjects)) {
    $student_ids = array_column($students, 'id');
    $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';

    // Fetch marks for the selected exam type
    $sql = "SELECT student_id, subject, participation, practical, terminal, external, la_total, la_obtained, remarks 
            FROM exam_marks 
            WHERE school_id = ? AND exam_type = ? AND year = ? AND student_id IN ($placeholders)";
    $params = array_merge([$school_id, $terminal, $year], $student_ids);
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $exam_marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($exam_marks as $mark) {
        $marks_data[$mark['student_id']][$mark['subject']] = $mark;
    }

    // Fetch attendance for selected exam type
    $attendance_data = [];
    $att_sql = "SELECT student_id, days_present FROM exam_attendance 
                WHERE school_id = ? AND exam_type = ? AND year = ? AND student_id IN ($placeholders)";
    $att_params = array_merge([$school_id, $terminal, $year], $student_ids);
    $att_stmt = $conn->prepare($att_sql);
    $att_stmt->execute($att_params);
    $att_records = $att_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($att_records as $rec) {
        $attendance_data[$rec['student_id']] = $rec['days_present'];
    }
}

// Grade calculation functions
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

// Calculate ranks for selected exam type
function calculateRanks($students, $subjects, $marks_data, $subject_credits, $exam_type, $is_class_1_to_3)
{
    $student_ranks = [];
    $gpa_list = [];

    foreach ($students as $student) {
        $st_total_wgp = 0;
        $st_total_credits = 0;
        $has_failed_subject = false;

        foreach ($subjects as $subject) {
            $marks = isset($marks_data[$student['id']][$subject]) ? $marks_data[$student['id']][$subject] : null;
            $credit_hour = isset($subject_credits[$subject]) ? floatval($subject_credits[$subject]) : 1;

            $is_class_4_to_8_final = in_array((string) $student['class'], ['4', '5', '6', '7', '8']) && $exam_type == 'final';
            $is_pre_prim = in_array(strtoupper((string) $student['class']), ['NURSERY', 'PG', 'KG', 'LKG', 'UKG']);

            if ($is_class_1_to_3 || $is_class_4_to_8_final || $is_pre_prim) {
                $calc_c = $is_pre_prim ? 1 : $credit_hour;
                $st_total_credits += $calc_c;
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

                    if ($th_gp == 0.0 || $pr_gp == 0.0) {
                        $has_failed_subject = true;
                    }
                } else {
                    if ($is_pre_prim) {
                        $f_marks = ($credit_hour == 100 || $credit_hour == 50) ? $credit_hour : (($credit_hour >= 3) ? 100 : 50);
                        $rw = floatval($marks['practical'] ?? 0);
                        $ls = ($f_marks == 100) ? floatval($marks['terminal'] ?? 0) : 0;
                        $subj_total = $rw + $ls;
                        $gp = getGradePoint($subj_total, $f_marks);
                    } else {
                        if ($exam_type == 'final') {
                            $subj_total = ($marks['participation'] ?? 0) + ($marks['practical'] ?? 0) + ($marks['terminal'] ?? 0) + ($marks['external'] ?? 0);
                            $gp = getGradePoint($subj_total, 100);
                        } else {
                            $subj_total = ($marks['participation'] ?? 0) + ($marks['practical'] ?? 0) + ($marks['terminal'] ?? 0);
                            $gp = getGradePoint($subj_total, 50);
                        }
                    }
                    if ($is_pre_prim && $gp == '0.0') {
                        $has_failed_subject = true;
                    }
                }

                if (!$is_class_1_to_3 && !$is_class_4_to_8_final) {
                    $calc_c = $is_pre_prim ? 1 : $credit_hour;
                    if (!$is_pre_prim) {
                        $st_total_credits += $calc_c;
                    }
                    $st_total_wgp += floatval($gp) * $calc_c;
                }
            } else {
                if ($is_class_1_to_3 || $is_class_4_to_8_final || $is_pre_prim) {
                    $has_failed_subject = true;
                }
            }
        }

        $gpa = $st_total_credits > 0 ? ($st_total_wgp / $st_total_credits) : 0;
        if (($is_class_1_to_3 || $is_class_4_to_8_final || $is_pre_prim) && $has_failed_subject) {
            $gpa = 0;
        }

        if ($st_total_credits > 0) {
            $gpa_list[$student['id']] = round($gpa, 2);
        }
    }

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

    return $student_ranks;
}

$student_ranks = calculateRanks($students, $subjects, $marks_data, $subject_credits, $terminal, $is_class_1_to_3);

// Get exam title based on type
function getExamTitle($terminal)
{
    $titles = [
        'first_terminal' => 'FIRST TERMINAL EXAMINATION',
        'second_terminal' => 'SECOND TERMINAL EXAMINATION',
        'third_terminal' => 'THIRD TERMINAL EXAMINATION',
        'final' => 'FINAL EXAMINATION',
        'monthly' => 'MONTHLY EXAMINATION'
    ];
    return $titles[$terminal] ?? 'EXAMINATION';
}

$exam_title = getExamTitle($terminal);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Sheet -
        <?php echo htmlspecialchars($school_info['school_name']); ?>
    </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f5f5f5;
            padding: 10mm;
        }

        .page {
            width: 297mm;
            height: 210mm;
            background: white;
            padding: 10mm;
            margin: 0 auto 10mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            page-break-after: always;
            display: flex;
            gap: 5mm;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .gradesheet {
            flex: 1;
            border: 3px solid #000;
            padding: 5mm;
            display: flex;
            flex-direction: column;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .school-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-bottom: 2mm;
        }

        .school-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }

        .school-address {
            font-size: 10pt;
            margin-bottom: 1mm;
        }

        .exam-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 2mm;
            text-decoration: underline;
        }

        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2mm;
            margin-bottom: 3mm;
            font-size: 9pt;
        }

        .info-row {
            display: flex;
            gap: 2mm;
        }

        .info-label {
            font-weight: bold;
            min-width: 60px;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 8pt;
        }

        .marks-table th,
        .marks-table td {
            border: 1px solid #000;
            padding: 1.5mm;
            text-align: center;
        }

        .marks-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 7pt;
        }

        .marks-table td {
            font-size: 8pt;
        }

        .subject-name {
            text-align: left !important;
            font-weight: bold;
        }

        .footer-info {
            margin-top: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3mm;
            font-size: 8pt;
            padding-top: 3mm;
            border-top: 1px solid #000;
        }

        .footer-item {
            display: flex;
            gap: 2mm;
        }

        .footer-label {
            font-weight: bold;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 5mm;
            font-size: 8pt;
        }

        .signature-line {
            text-align: center;
            min-width: 80px;
        }

        .signature-line::before {
            content: '';
            display: block;
            width: 100%;
            border-top: 1px solid #000;
            margin-bottom: 1mm;
        }

        .no-print {
            text-align: center;
            margin: 20px 0;
        }

        .btn {
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-print {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .page {
                width: 297mm;
                height: 210mm;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .no-print {
                display: none !important;
            }
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        /* New Layout Styles */
        .new-layout {
            flex: 1;
            display: flex;
            flex-direction: column;
            border: 2px solid #000;
            background-color: #fdf5e6;
            padding: 5px;
            box-sizing: border-box;
            position: relative;
        }

        .new-layout-divider {
            width: 0;
            border-right: 2px dashed #000;
            margin: 0 10px;
        }

        .nl-header-top {
            display: flex;
            border: 1px solid #000;
            background: #fff;
        }

        .nl-logo-box {
            padding: 5px;
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
        }

        .nl-school-text {
            flex: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .nl-blue-row {
            background-color: #b8d1f3;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
            padding: 3px;
        }

        .nl-white-row {
            background-color: #fff;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
            font-weight: bold;
            padding: 2px;
        }

        .nl-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 9pt;
            background: #fff;
        }

        .nl-table td,
        .nl-table th {
            border: 1px solid #000;
            padding: 3px 5px;
        }

        .nl-marks-table {
            background: transparent;
            position: relative;
            z-index: 2;
        }

        .nl-marks-table th {
            background: rgba(255, 255, 255, 0.9);
            text-align: center;
        }

        .nl-marks-table td {
            text-align: center;
            background: rgba(255, 255, 255, 0.4);
        }

        .nl-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            width: 250px;
            height: 250px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            z-index: 1;
            pointer-events: none;
        }

        .nl-vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            margin: auto;
            white-space: nowrap;
            font-weight: bold;
            letter-spacing: 2px;
        }

        /* Final Exam Portrait Layout */
        .final-layout {
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            border: 2px solid #000;
            background-color: #ffe4d6;
            /* Peach background matching user request */
            padding: 20px;
            box-sizing: border-box;
            position: relative;
        }

        .fl-header {
            display: flex;
            border-bottom: 2px solid #000;
            background: #fff;
            padding: 10px;
            text-align: center;
        }

        .fl-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .fl-title {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-weight: bold;
        }

        .fl-details-box {
            border: 2px solid #000;
            padding: 5px;
            margin: 10px 0;
            background: #fff;
            font-weight: bold;
            font-size: 11pt;
            display: flex;
            justify-content: space-between;
        }

        .fl-row-flex {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .fl-small-box {
            border: 2px solid #000;
            padding: 5px 20px;
            background: #fff;
            font-weight: bold;
        }

        .fl-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 10px 0;
        }

        .fl-gradesheet-badge {
            border: 2px solid #000;
            border-radius: 10px;
            padding: 5px 20px;
            font-weight: bold;
            font-size: 13pt;
            background: #fff;
            display: inline-block;
            margin: 0 auto 15px auto;
        }

        .fl-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            background: #fff;
            font-weight: bold;
            font-size: 10pt;
        }

        .fl-table th,
        .fl-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .fl-reference-box {
            margin-top: auto;
            margin-bottom: 20px;
        }

        .fl-reference-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
        }

        .fl-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .fl-sig-line {
            border-top: 2px solid #000;
            width: 30%;
            text-align: center;
            font-weight: bold;
            padding-top: 5px;
        }

        .fl-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            width: 500px;
            height: 500px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            z-index: 1;
            pointer-events: none;
        }

        .fl-subject-col {
            text-align: left !important;
            padding-left: 10px !important;
        }
    </style>
    <style type="text/css" media="print">
        @page {
            size: A4
                <?php echo ($terminal == 'final') ? 'portrait' : 'landscape'; ?>
            ;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Print Grade Sheets
        </button>
        <button onclick="window.location.href='gradesheet_selector.php'" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>

    <?php
    // Group students in pairs (2 per page) for normal exams, or 1 per page for final
    $chunk_size = ($terminal == 'final') ? 1 : 2;
    $student_pairs = array_chunk($students, $chunk_size);

    // Generate grade sheets for selected exam type
    foreach ($student_pairs as $pair):
        ?>
        <div class="page">
            <?php foreach ($pair as $idx => $student):
                $student_id = $student['id'];

                // Pre-calculate GPA and subjects to decide layout params
                $total_wgp = 0;
                $total_credits = 0;
                $has_failed_subject = false;
                $subject_results = [];

                foreach ($subjects as $subject) {
                    $marks = isset($marks_data[$student_id][$subject]) ? $marks_data[$student_id][$subject] : null;
                    $credit = isset($subject_credits[$subject]) ? floatval($subject_credits[$subject]) : 1;

                    $is_class_4_to_8_final = in_array((string) $student['class'], ['4', '5', '6', '7', '8']) && $terminal == 'final';

                    if ($is_class_1_to_3 || $is_class_4_to_8_final) {
                        $total_credits += $credit;
                    }

                    if ($marks) {
                        if ($is_class_1_to_3) {
                            $la_total = floatval($marks['la_total'] ?? 0);
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
                                $grade = 'ABS';
                                $gp = 'ABS';
                                $has_failed_subject = true;
                            }
                            $display_gp = ($grade === 'ABS') ? 'ABS' : (($grade === 'NG') ? '-' : ($gp !== '' ? number_format((float) $gp, 2) : ''));

                            $subject_results[] = ['name' => $subject, 'credit' => $credit, 'gp' => $gp, 'display_gp' => $display_gp, 'grade' => $grade];
                        } elseif ($is_class_4_to_8_final) {
                            $th_marks = floatval($marks['external'] ?? 0);
                            $pr_marks = floatval($marks['participation'] ?? 0) + floatval($marks['practical'] ?? 0) + floatval($marks['terminal'] ?? 0);

                            $th_gp_val = floatval(getGradePoint($th_marks, 50));
                            $pr_gp_val = floatval(getGradePoint($pr_marks, 50));

                            $th_c = $credit / 2;
                            $pr_c = $credit / 2;

                            $th_wgp_val = $th_gp_val * $th_c;
                            $pr_wgp_val = $pr_gp_val * $pr_c;

                            $total_wgp += ($th_wgp_val + $pr_wgp_val);

                            if ($th_gp_val == 0.0 || $pr_gp_val == 0.0) {
                                $has_failed_subject = true;
                                $tot_gp_val = 0.0;
                                $gp = '0.0';
                            } else {
                                $subj_total = $th_marks + $pr_marks;
                                $tot_gp_val = floatval(getGradePoint($subj_total, 100));
                                $gp = strval($tot_gp_val);
                            }

                            $grade = getGrade(number_format($tot_gp_val, 1));
                            $display_gp = ($grade === 'ABS') ? 'ABS' : (($grade === 'NG') ? '-' : ($gp !== '' ? number_format((float) $gp, 2) : ''));

                            $subject_results[] = [
                                'name' => $subject,
                                'credit' => $credit,
                                'gp' => $gp,
                                'display_gp' => $display_gp,
                                'grade' => $grade,
                                'th_gp' => number_format($th_gp_val, 1),
                                'pr_gp' => number_format($pr_gp_val, 1)
                            ];
                        } else {
                            if ($is_pre_primary) {
                                $f_marks = ($credit == 100 || $credit == 50) ? $credit : (($credit >= 3) ? 100 : 50);
                                $rw = floatval($marks['practical'] ?? 0);
                                $ls = ($f_marks == 100) ? floatval($marks['terminal'] ?? 0) : 0;
                                $marks_total = $rw + $ls;
                                $gp = getGradePoint($marks_total, $f_marks);
                            } else {
                                $marks_total = ($marks['participation'] ?? 0) + ($marks['practical'] ?? 0) + ($marks['terminal'] ?? 0);
                                if ($terminal == 'final') {
                                    $marks_total += ($marks['external'] ?? 0);
                                    $gp = getGradePoint($marks_total, 100);
                                } else {
                                    $gp = getGradePoint($marks_total, 50);
                                }
                            }
                            $grade = getGrade($gp);
                            if ($is_pre_primary && ($gp == '0.0' || $grade == 'NG')) {
                                $has_failed_subject = true;
                            }

                            $calc_credit = $is_pre_primary ? 1 : $credit;
                            if ($gp !== 'ABS' && $gp !== '-') {
                                $total_wgp += floatval($gp) * $calc_credit;
                            }
                            $total_credits += $calc_credit;

                            $display_gp = ($grade === 'ABS') ? 'ABS' : (($grade === 'NG') ? '-' : ($gp !== '' ? number_format((float) $gp, 2) : ''));

                            $perc_val = 0;
                            if ($marks) {
                                $perc_val = ($marks_total / $f_marks) * 100;
                            }

                            $subject_results[] = [
                                'name' => $subject,
                                'credit' => $credit,
                                'gp' => $gp,
                                'display_gp' => $display_gp,
                                'grade' => $grade,
                                'perc' => ($is_pre_primary ? round($perc_val, 0) : number_format($perc_val, 2)) . '%'
                            ];
                        }
                    } else {
                        if ($is_class_1_to_3 || $is_class_4_to_8_final) {
                            $gp = 'ABS';
                            $grade = 'ABS';
                            $has_failed_subject = true;

                            $display_gp = 'ABS';
                            $subject_results[] = [
                                'name' => $subject,
                                'credit' => $credit,
                                'gp' => $gp,
                                'display_gp' => $display_gp,
                                'grade' => $grade,
                                'th_gp' => 'ABS',
                                'pr_gp' => 'ABS'
                            ];
                        } else {
                            $gp = '0.0';
                            $grade = 'NG';
                            if ($is_pre_primary) {
                                $has_failed_subject = true;
                            }
                            $display_gp = '-';
                            $subject_results[] = [
                                'name' => $subject,
                                'credit' => $credit,
                                'gp' => $gp,
                                'display_gp' => $display_gp,
                                'grade' => $grade,
                                'perc' => '0%' // Default for no marks
                            ];
                        }
                    }
                }

                $gpa = $total_credits > 0 ? round($total_wgp / $total_credits, 2) : 0;
                if (($is_class_1_to_3 || $is_class_4_to_8_final || $is_pre_primary) && $has_failed_subject) {
                    $gpa = 0.00;
                }
                $remark_text = ($gpa > 0) ? getRemarks($gpa) : 'NOT GRADED';

                // Add dashed divider between the two halves
                if ($idx > 0) {
                    echo '<div class="new-layout-divider"></div>';
                }

                if ($terminal !== 'final'):
                    ?>
                    <!-- NEW PHOTO MATCHING LAYOUT -->
                    <div class="new-layout">
                        <div class="nl-header-top">
                            <div class="nl-logo-box">
                                <?php if (!empty($school_info['school_logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($school_info['school_logo']); ?>"
                                        style="width: 50px; height: 50px; object-fit: contain;">
                                <?php endif; ?>
                            </div>
                            <div class="nl-school-text">
                                <h2 style="font-size: 13pt; margin:0; padding:0;">
                                    <?php echo htmlspecialchars($school_info['school_name']); ?>
                                </h2>
                                <div style="font-size: 9pt; padding-top: 2px; font-weight: bold;">
                                    <?php echo htmlspecialchars($school_info['address'] ?? ''); ?>
                                </div>
                            </div>
                        </div>
                        <div class="nl-blue-row"><?php echo $exam_title; ?>-<?php echo $year; ?></div>
                        <div class="nl-white-row"><u>GRADESHEET</u></div>

                        <table class="nl-table" style="border-top: 0; margin-bottom: 0;">
                            <tr>
                                <td style="width: 15%;">Roll No.:</td>
                                <td style="width: 35%; font-weight: bold;">
                                    <?php echo htmlspecialchars($student['symbol_no'] ?? 'N/A'); ?>
                                </td>
                                <td style="width: 15%;">Class :</td>
                                <td style="width: 35%; font-weight: bold;"><?php echo htmlspecialchars($student['class']); ?></td>
                            </tr>
                            <tr>
                                <td>Students Name :</td>
                                <td colspan="3" style="font-weight: bold; text-align: center;">
                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                </td>
                            </tr>
                        </table>

                        <div style="position: relative; flex: 1; display: flex; flex-direction: column;">
                            <?php if (!empty($school_info['school_logo'])): ?>
                                <div class="nl-watermark"
                                    style="background-image: url('<?php echo htmlspecialchars($school_info['school_logo']); ?>');">
                                </div>
                            <?php endif; ?>

                            <table class="nl-table nl-marks-table" style="border-top: 0; border-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th style="width: 8%;">S.N.</th>
                                        <th style="width: 40%;">SUBJECTS</th>
                                        <?php if ($is_pre_primary): ?>
                                            <th style="width: 12%;">Obtained<br>Percentage</th>
                                        <?php else: ?>
                                            <th style="width: 12%;">CREDIT<br>HOUR</th>
                                        <?php endif; ?>
                                        <th style="width: 12%;">GRADE<br>POINT</th>
                                        <th style="width: 12%;">GRADE</th>
                                        <th style="width: 16%;">REMARKS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sn = 1;
                                    $sub_count = count($subject_results);
                                    foreach ($subject_results as $index => $s_res):
                                        ?>
                                        <tr>
                                            <td><?php echo $sn++; ?></td>
                                            <td style="text-align: left; padding-left: 10px;">
                                                <?php echo htmlspecialchars($s_res['name']); ?>
                                            </td>
                                            <?php if ($is_pre_primary): ?>
                                                <td style="font-weight: bold; color: #1e40af;"><?php echo $s_res['perc']; ?></td>
                                            <?php else: ?>
                                                <td><?php echo number_format($s_res['credit'], 1); ?></td>
                                            <?php endif; ?>
                                            <td><?php echo $s_res['display_gp']; ?></td>
                                            <td><?php echo $s_res['grade']; ?></td>
                                            <?php if ($index === 0): ?>
                                                <td rowspan="<?php echo $sub_count; ?>"
                                                    style="background: rgba(255,255,255,0.85); vertical-align: middle;">
                                                    <div class="nl-vertical-text"><?php echo $remark_text; ?></div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <table class="nl-table" style="border-top: 1px solid #000; margin-top: auto;">
                                <tr>
                                    <td style="font-weight: bold;">GPA :</td>
                                    <td style="text-align: center; font-weight: bold; width: 12%;">
                                        <?php echo number_format($gpa, 2); ?>
                                    </td>
                                    <td style="font-weight: bold;">Rank :</td>
                                    <td style="text-align: center; font-weight: bold; width: 12%;">
                                        <?php echo isset($student_ranks[$student_id]) ? $student_ranks[$student_id] : '-'; ?>
                                    </td>
                                    <td style="font-weight: bold;">Attendance :</td>
                                    <td style="text-align: center; font-weight: bold; width: 12%;">
                                        <?php echo isset($attendance_data[$student_id]) ? $attendance_data[$student_id] : '-'; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; margin-top: 15px; padding-bottom: 5px; font-size: 9pt;">
                            <div style="text-align: center; border-top: 1px dashed #000; width: 30%; padding-top: 3px;">Class
                                Teacher</div>
                            <div style="text-align: center; border-top: 1px dashed #000; width: 30%; padding-top: 3px;">Exam
                                Coordinator</div>
                            <div style="text-align: center; border-top: 1px dashed #000; width: 30%; padding-top: 3px;">Head Teacher
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- FINAL EXAM PORTRAIT LAYOUT -->
                    <div class="final-layout">
                        <?php if (!empty($school_info['school_logo'])): ?>
                            <div class="fl-watermark"
                                style="background-image: url('<?php echo htmlspecialchars($school_info['school_logo']); ?>');"></div>
                        <?php endif; ?>

                        <div class="fl-header">
                            <div>
                                <?php if (!empty($school_info['school_logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($school_info['school_logo']); ?>" class="fl-logo">
                                <?php endif; ?>
                            </div>
                            <div class="fl-title">
                                <span style="font-size: 16pt;"><?php echo htmlspecialchars($school_info['school_name']); ?></span>
                                <span style="font-size: 11pt;"><?php echo htmlspecialchars($school_info['address'] ?? ''); ?></span>
                                <?php if (!empty($school_info['estd_date'])): ?>
                                    <span
                                        style="font-size: 11pt;">ESTD:<?php echo htmlspecialchars($school_info['estd_date']); ?></span>
                                <?php endif; ?>
                            </div>
                            <!-- Mirror logo on right side to balance layout if desired, user image has a crest on both sides -->
                            <div>
                                <?php if (!empty($school_info['school_logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($school_info['school_logo']); ?>" class="fl-logo">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="fl-details-box">
                            <span style="padding-left: 10px;">THE GRADES SECURED BY:</span>
                            <span style="padding-right: 20px; text-decoration: underline; flex: 1; text-align: right;">
                                <?php echo htmlspecialchars($student['full_name']); ?>
                            </span>
                        </div>

                        <div class="fl-row-flex">
                            <div class="fl-small-box" style="width: 45%; display: flex; justify-content: space-between;">
                                <span>OF CLASS</span>
                                <span style="color: blue;"><?php echo htmlspecialchars($student['class']); ?></span>
                            </div>
                            <div class="fl-small-box" style="width: 45%; display: flex; justify-content: space-between;">
                                <span>ROLL NO</span>
                                <span style="color: blue;"><?php echo htmlspecialchars($student['symbol_no'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="fl-subtitle">
                            IN THE ANNUAL EXAMINATION OF <?php echo htmlspecialchars($year); ?> B.S ARE GIVEN BELOW
                        </div>

                        <div style="text-align: center;">
                            <div class="fl-gradesheet-badge">GRADE-SHEET</div>
                        </div>

                        <table class="fl-table" style="position: relative; z-index: 2;">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 5%;">S.N.</th>
                                    <th rowspan="2" style="width: 30%;">SUBJECTS</th>
                                    <?php if ($is_pre_primary): ?>
                                        <th rowspan="2" style="width: 10%;">Obtained<br>Percentage</th>
                                    <?php else: ?>
                                        <th rowspan="2" style="width: 10%;">CREDIT<br>HOURS</th>
                                    <?php endif; ?>

                                    <?php if ($is_class_4_to_8_final): ?>
                                        <th colspan="2" style="width: 20%;">GRADE OBTAINED</th>
                                    <?php else: ?>
                                        <th rowspan="2" style="width: 20%;">GRADE<br>OBTAINED</th>
                                    <?php endif; ?>

                                    <th rowspan="2" style="width: 10%;">GRADE<br>POINT</th>
                                    <th rowspan="2" style="width: 10%;">FINAL<br>GRADE</th>
                                    <th rowspan="2" style="width: 15%;">REMARKS</th>
                                </tr>
                                <?php if ($is_class_4_to_8_final): ?>
                                    <tr>
                                        <th>TH</th>
                                        <th>PR</th>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody>
                                <?php
                                $sn = 1;
                                $sub_count = count($subject_results);
                                foreach ($subject_results as $index => $s_res):
                                    ?>
                                    <tr>
                                        <td><?php echo $sn++; ?></td>
                                        <td class="fl-subject-col"><?php echo htmlspecialchars($s_res['name']); ?></td>
                                        <?php if ($is_pre_primary): ?>
                                            <td style="font-weight: bold; color: #1e40af;"><?php echo $s_res['perc']; ?></td>
                                        <?php else: ?>
                                            <td><?php echo number_format($s_res['credit'], 1); ?></td>
                                        <?php endif; ?>

                                        <?php if ($is_class_4_to_8_final): ?>
                                            <td><?php echo isset($s_res['th_gp']) ? $s_res['th_gp'] : '-'; ?></td>
                                            <td><?php echo isset($s_res['pr_gp']) ? $s_res['pr_gp'] : '-'; ?></td>
                                        <?php else: ?>
                                            <td><?php echo $s_res['grade']; ?></td>
                                        <?php endif; ?>

                                        <td><?php echo $s_res['display_gp']; ?></td>
                                        <td><?php echo $s_res['grade']; ?></td>

                                        <?php if ($index === 0): ?>
                                            <td rowspan="<?php echo $sub_count; ?>" style="vertical-align: middle;">
                                                <div class="nl-vertical-text"><?php echo $remark_text; ?></div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- GPA and Attendance Footer Row inside Table -->
                                <tr style="background: rgba(255,255,255,0.8);">
                                    <td colspan="2" style="text-align: right; padding-right: 15px;">GPA :</td>
                                    <td><?php echo number_format($gpa, 2); ?></td>
                                    <td style="text-align: right; padding-right: 5px;"
                                        colspan="<?php echo ($is_class_4_to_8_final) ? '2' : '1'; ?>">RANK :</td>
                                    <td><?php echo isset($student_ranks[$student_id]) ? $student_ranks[$student_id] : '-'; ?></td>
                                    <td style="font-size: 8pt; text-align: right;">ATTN :</td>
                                    <td><?php echo isset($attendance_data[$student_id]) ? $attendance_data[$student_id] : '-'; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="fl-reference-box">
                            <div class="fl-reference-title">DETAILS OF GRADE SHEET</div>
                            <table class="fl-table">
                                <thead>
                                    <tr>
                                        <th>S.N</th>
                                        <th>INTERVAL IN MARKS</th>
                                        <th>GRADE POINT</th>
                                        <th>GRADE LETTERS</th>
                                        <th>DESCRIPTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>90 TO 100</td>
                                        <td>4.0</td>
                                        <td>A+</td>
                                        <td>OUTSTANDING</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>80 TO BELOW 90</td>
                                        <td>3.6</td>
                                        <td>A</td>
                                        <td>EXCELLENT</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>70 TO BELOW 80</td>
                                        <td>3.2</td>
                                        <td>B+</td>
                                        <td>VERY GOOD</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>60 TO BELOW 70</td>
                                        <td>2.8</td>
                                        <td>B</td>
                                        <td>GOOD</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>50 TO BELOW 60</td>
                                        <td>2.4</td>
                                        <td>C+</td>
                                        <td>SATISFACTORY</td>
                                    </tr>
                                    <tr>
                                        <td>6</td>
                                        <td>40 TO BELOW 50</td>
                                        <td>2.0</td>
                                        <td>C</td>
                                        <td>ACCEPTABLE</td>
                                    </tr>
                                    <tr>
                                        <td>7</td>
                                        <td>35 TO BELOW 40</td>
                                        <td>1.6</td>
                                        <td>D</td>
                                        <td>BASIC</td>
                                    </tr>
                                    <tr>
                                        <td>8</td>
                                        <td>BELOW 35</td>
                                        <td>-</td>
                                        <td>NG</td>
                                        <td>NOT GRADED</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="fl-signatures">
                            <div class="fl-sig-line">CLASS TEACHER</div>
                            <div class="fl-sig-line">PREPARED BY</div>
                            <div class="fl-sig-line">PRINCIPAL</div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</body>

</html>