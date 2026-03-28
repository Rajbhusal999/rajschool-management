<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$exam_type = isset($_GET['exam']) ? $_GET['exam'] : 'first_terminal';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y') + 56;
$class = isset($_GET['class']) ? $_GET['class'] : '';

$exam_names = [
    'first_terminal' => 'FIRST TERMINAL EXAMINATION',
    'second_terminal' => 'SECOND TERMINAL EXAMINATION',
    'third_terminal' => 'THIRD TERMINAL EXAMINATION',
    'final' => 'FINAL EXAMINATION',
    'monthly' => 'MONTHLY EXAMINATION'
];
$exam_name = isset($exam_names[$exam_type]) ? $exam_names[$exam_type] : strtoupper($exam_type);

// Extract School Info from Login Session Data
$school = [
    'name' => $_SESSION['school_name'] ?? 'School Name Not Set',
    'address' => $_SESSION['school_address'] ?? 'Address Not Found',
    'estd_date' => $_SESSION['estd_date'] ?? '20XX',
    'logo_path' => $_SESSION['school_logo'] ?? ''
];

// Fetch Students
$students = [];
if ($class) {
    $sql = "SELECT * FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch available classes for dropdown
$class_sql = "SELECT DISTINCT class FROM students WHERE school_id = ? AND UPPER(class) IN ('PG', 'LKG', 'UKG', 'NURSERY', 'KG') ORDER BY class";
$class_stmt = $conn->prepare($class_sql);
$class_stmt->execute([$school_id]);
$classes = $class_stmt->fetchAll(PDO::FETCH_COLUMN);

// Stats Calculation (Registered)
$total_students = count($students);
$total_boys = 0;
$total_girls = 0;

foreach ($students as $s) {
    if (strtolower($s['gender']) == 'male')
        $total_boys++;
    elseif (strtolower($s['gender']) == 'female')
        $total_girls++;
}

// Fetch Subjects
$subjects = [];
$class_group = 'PG';
$grp_map = [
    'PG' => 'PG',
    'LKG' => 'LKG',
    'UKG' => 'UKG',
    'NURSERY' => 'NURSERY',
    'KG' => 'KG'
];
$c_upper = strtoupper($class);
$group_search = isset($grp_map[$c_upper]) ? $grp_map[$c_upper] : 'PG';

$subj_sql = "SELECT subject_name, credit_hour FROM subjects WHERE school_id = ? AND class_group = ? ORDER BY subject_name";
$subj_stmt = $conn->prepare($subj_sql);
$subj_stmt->execute([$school_id, $group_search]);
$subjects_raw = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($subjects_raw)) {
    $subj_stmt->execute([$school_id, 'PG_KG']);
    $subjects_raw = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Organize subjects
$subject_list = [];
$total_full_marks = 0;
foreach ($subjects_raw as $s) {
    $credit = floatval($s['credit_hour']);
    if ($credit == 100) {
        $full_marks = 100;
    } else if ($credit == 50) {
        $full_marks = 50;
    } else {
        $full_marks = ($credit >= 3) ? 100 : 50;
    }

    $subject_list[] = [
        'name' => $s['subject_name'],
        'full_marks' => $full_marks
    ];
    $total_full_marks += $full_marks;
}

// Fetch Marks
$marks_data = [];
$participated = 0;
if (!empty($students)) {
    $s_ids = array_column($students, 'id');
    if (!empty($s_ids)) {
        $placeholders = str_repeat('?,', count($s_ids) - 1) . '?';
        $m_sql = "SELECT student_id, subject, practical, terminal FROM exam_marks 
                  WHERE school_id = ? AND exam_type = ? AND year = ? AND student_id IN ($placeholders)";
        $params = array_merge([$school_id, $exam_type, $year], $s_ids);
        $m_stmt = $conn->prepare($m_sql);
        $m_stmt->execute($params);
        $fetched_marks = $m_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($fetched_marks as $row) {
            $marks_data[$row['student_id']][$row['subject']] = $row;
        }
        $participated = count($marks_data); // Count distinct students in marks table
    }
}

// Fetch Attendance
$attendance_data = [];
if (!empty($students) && !empty($s_ids)) {
    $att_sql = "SELECT student_id, days_present FROM exam_attendance WHERE school_id = ? AND exam_type = ? AND year = ? AND student_id IN ($placeholders)";
    $att_stmt = $conn->prepare($att_sql);
    $att_stmt->execute($params);
    $att_rows = $att_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($att_rows as $a) {
        $attendance_data[$a['student_id']] = $a['days_present'];
    }
}

function getGrade($percentage)
{
    // The GPA values asked by user: >=0.9:4.0, >=0.8:3.6, >=0.7:3.2, >=0.6:2.8, >=0.5:2.4, >=0.4:2.0, >=0.35:1.6, <0.35:"0.0"
    // $percentage here is actually the 0-100 value. E.g 90 instead of 0.9.
    // So 0.9 = 90%, 0.8 = 80%, etc.
    if ($percentage >= 90)
        return ['A+', '4.0'];
    if ($percentage >= 80)
        return ['A', '3.6'];
    if ($percentage >= 70)
        return ['B+', '3.2'];
    if ($percentage >= 60)
        return ['B', '2.8'];
    if ($percentage >= 50)
        return ['C+', '2.4'];
    if ($percentage >= 40)
        return ['C', '2.0'];
    if ($percentage >= 35)
        return ['D', '1.6'];
    return ['NG', '0.0'];
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

// Process Rows
$ledger_rows = [];
foreach ($students as $student) {
    $sid = $student['id'];
    $row_total = 0;
    $row_full_marks = 0;
    $total_gp = 0;
    $subject_count = 0;
    $has_ng = false;
    $has_marks = isset($marks_data[$sid]); // Check if student has ANY marks

    $subj_cells = [];
    foreach ($subject_list as $subj) {
        $sname = $subj['name'];
        $full = $subj['full_marks'];
        $mark_row = isset($marks_data[$sid][$sname]) ? $marks_data[$sid][$sname] : null;

        if ($mark_row) {
            $rw = floatval($mark_row['practical']);
            $ls = ($full == 100) ? floatval($mark_row['terminal']) : 0;
            $om = $rw + $ls;

            $perc = ($om / $full) * 100;
            list($grade, $gp) = getGrade($perc);

            $row_total += $om;
            $row_full_marks += $full;
            $total_gp += floatval($gp);
            $subject_count++;

            if ($grade == 'NG')
                $has_ng = true;

            $subj_cells[$sname] = [
                'rw' => $rw,
                'ls' => ($full == 100) ? $ls : '-',
                'om' => $om,
                'perc' => round($perc, 0),
                'gp' => $gp,
                'grade' => $grade
            ];
        } else {
            $subj_cells[$sname] = [
                'rw' => '-',
                'ls' => '-',
                'om' => '-',
                'perc' => '-',
                'gp' => '-',
                'grade' => '-'
            ];
            // If enrolled but no marks for this subject:
            if ($has_marks) {
                // Student present but missed this subject -> NG
                $has_ng = true;
            } else {
                // Student absent from exam entirely -> handle later
            }
        }
    }

    if ($subject_count > 0) {
        $final_perc = ($row_full_marks > 0) ? ($row_total / $row_full_marks) * 100 : 0;
        if ($has_ng) {
            $final_gpa = 0.0;
            $remarks = "NOT GRADED";
        } else {
            $final_gpa = round($total_gp / $subject_count, 2);
            $remarks = getRemarks($final_gpa);
        }
    } else {
        $final_perc = 0;
        $final_gpa = 0.0;
        $remarks = "ABSENT";
    }

    $ledger_rows[] = [
        'student' => $student,
        'subjects' => $subj_cells,
        'total_om' => $row_total,
        'final_perc' => number_format($final_perc, 2),
        'final_gpa' => number_format($final_gpa, 2),
        'att' => isset($attendance_data[$sid]) ? $attendance_data[$sid] : '-',
        'remarks' => $remarks,
        'has_marks' => $has_marks
    ];
}

// Calculate Stats for Table
$part_boys = 0;
$part_girls = 0;
$pass_boys = 0;
$pass_girls = 0;

foreach ($ledger_rows as $row) {
    if ($row['has_marks']) {
        $g = strtolower($row['student']['gender']);
        if ($g == 'male')
            $part_boys++;
        else
            $part_girls++;

        if ($row['remarks'] != "NOT GRADED" && $row['remarks'] != "ABSENT") {
            if ($g == 'male')
                $pass_boys++;
            else
                $pass_girls++;
        }
    }
}
$part_total = $part_boys + $part_girls;
$pass_total = $pass_boys + $pass_girls;
$pass_percent = ($part_total > 0) ? round(($pass_total / $part_total) * 100, 2) : 0;


// Sort rows by Name
usort($ledger_rows, function ($a, $b) {
    return strcmp($a['student']['full_name'], $b['student']['full_name']);
});

// Rank Calculation (only for passed?)
$rank = 0;
$prev_gpa = -1;
// Filter for ranking - simpler to just rank everyone with marks based on GPA
$ranking_rows = [];
foreach ($ledger_rows as $k => $r) {
    if ($r['has_marks'])
        $ranking_rows[$k] = $r['final_gpa'];
}
arsort($ranking_rows);
$rank = 1;
foreach ($ranking_rows as $k => $gpa) {
    $ledger_rows[$k]['rank'] = $rank++;
}

// Prepare Data for Averages
$subject_om_totals = [];
foreach ($subject_list as $s) {
    $subject_om_totals[$s['name']] = 0;
}
$students_with_marks_count = 0;
$class_total_om = 0;
foreach ($ledger_rows as $row) {
    if ($row['has_marks']) {
        $students_with_marks_count++;
        $class_total_om += $row['total_om'];
        foreach ($subject_list as $s) {
            $sname = $s['name'];
            if ($row['subjects'][$sname]['om'] !== '-') {
                $subject_om_totals[$sname] += $row['subjects'][$sname]['om'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mark Ledger - <?php echo $exam_name; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: 'Arial Narrow', sans-serif;
            background: #eee;
            padding: 20px;
        }

        .sheet {
            background: white;
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .school-info {
            text-align: center;
        }

        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .school-address {
            font-size: 14px;
            margin: 5px 0;
        }

        .exam-title {
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        /* Stats Table from Image */
        .stats-table {
            border-collapse: collapse;
            font-size: 11px;
            float: right;
            margin-bottom: 5px;
        }

        .stats-table th,
        .stats-table td {
            border: 1px solid black;
            padding: 3px 6px;
            text-align: center;
        }

        .stats-table th {
            background: #f0f0f0;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .ledger-table th,
        .ledger-table td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }

        .ledger-table th {
            background: #e0e0e0;
            font-weight: bold;
            vertical-align: middle;
        }

        .th-yellow {
            background-color: #ffecb3 !important;
        }

        .th-blue {
            background-color: #b2dfdb !important;
        }

        .th-gray {
            background-color: #e0e0e0 !important;
        }

        .sub-col {
            font-size: 9px;
        }

        .rotate-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }

        .no-print {
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            color: white;
            margin-right: 10px;
        }

        .btn-print {
            background: #2563eb;
        }

        .btn-excel {
            background: #10b981;
        }

        @media print {
            @page {
                size: A3 landscape;
                margin: 5mm;
            }

            body {
                background: white;
                padding: 0;
            }

            .sheet {
                box-shadow: none;
                max-width: none;
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .th-yellow {
                background-color: #ffecb3 !important;
                -webkit-print-color-adjust: exact;
            }

            .th-blue {
                background-color: #b2dfdb !important;
                -webkit-print-color-adjust: exact;
            }

            .th-gray {
                background-color: #e0e0e0 !important;
                -webkit-print-color-adjust: exact;
            }

            .ledger-table th {
                -webkit-print-color-adjust: exact;
            }

            .stats-table th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="no-print"
        style="background: white; padding: 15px; border-bottom: 1px solid #ddd; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <form method="GET" action="" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">

            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="font-weight: bold; font-size: 14px; color: #4b5563;">Exam Type</label>
                <select name="exam"
                    style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; min-width: 150px;">
                    <?php foreach ($exam_names as $key => $val): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($exam_type == $key) ? 'selected' : ''; ?>>
                            <?php echo $val; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="font-weight: bold; font-size: 14px; color: #4b5563;">Year (B.S.)</label>
                <input type="number" name="year" value="<?php echo htmlspecialchars($year); ?>"
                    style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; width: 100px;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label style="font-weight: bold; font-size: 14px; color: #4b5563;">Class</label>
                <select name="class"
                    style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; min-width: 120px;" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($class == $c) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn"
                    style="background: #ec4899; margin: 0; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-filter"></i> Load Ledger
                </button>
                <button type="button" onclick="window.print()" class="btn btn-print"
                    style="margin: 0; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" onclick="exportExcel()" class="btn btn-excel"
                    style="margin: 0; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-file-excel"></i> Export
                </button>
                <a href="exam_marks.php?exam=<?php echo $exam_type; ?>&group=pg_kg" class="btn"
                    style="background: #6b7280; text-decoration: none; margin: 0; display: flex; align-items: center; gap: 5px;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </form>
    </div>

    <div class="sheet" id="printArea">
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td style="width: 25%;">
                    <div style="font-weight: bold; border: 1px solid #ccc; padding: 5px; display: inline-block;">
                        Class : <?php echo htmlspecialchars($class); ?>
                    </div>
                </td>
                <td style="width: 50%;" class="school-info">
                    <?php if ($school['logo_path']): ?>
                        <img src="<?php echo $school['logo_path']; ?>" style="height: 60px; margin-bottom: 5px;">
                    <?php endif; ?>
                    <h1 class="school-name"><?php echo htmlspecialchars($school['name']); ?></h1>
                    <div class="school-address"><?php echo htmlspecialchars($school['address']); ?></div>
                    <div style="font-weight: bold; margin-top: 5px;">ESTD:
                        <?php echo htmlspecialchars($school['estd_date'] ?? '20XX'); ?>
                    </div>
                    <div class="exam-title"><?php echo $exam_name . ' ' . $year; ?></div>
                </td>
                <td style="width: 25%; vertical-align: top;">
                    <!-- Header Stats Table matching image -->
                    <table class="stats-table">
                        <tr>
                            <th colspan="10">विद्यार्थी विवरण</th>
                        </tr>
                        <tr>
                            <th colspan="3">जम्मा विद्यार्थी</th>
                            <th colspan="3">परीक्षामा सहभागी</th>
                            <th colspan="3">उत्तीर्ण विद्यार्थी</th>
                            <th rowspan="3" style="vertical-align: middle;">
                                <div
                                    style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; height: 50px;">
                                    उत्तीर्ण &nbsp; प्रतिशत</div>
                            </th>
                        </tr>
                        <tr>
                            <th style="font-size: 9px;">छात्रा</th>
                            <th style="font-size: 9px;">छात्र</th>
                            <th style="font-size: 9px;">जम्मा</th>
                            <th style="font-size: 9px;">छात्रा</th>
                            <th style="font-size: 9px;">छात्र</th>
                            <th style="font-size: 9px;">जम्मा</th>
                            <th style="font-size: 9px;">छात्रा</th>
                            <th style="font-size: 9px;">छात्र</th>
                            <th style="font-size: 9px;">जम्मा</th>
                        </tr>
                        <tr>
                            <td><?php echo $total_girls; ?></td>
                            <td><?php echo $total_boys; ?></td>
                            <td><?php echo $total_students; ?></td>

                            <td><?php echo $part_girls; ?></td>
                            <td><?php echo $part_boys; ?></td>
                            <td><?php echo $part_total; ?></td>

                            <td><?php echo $pass_girls; ?></td>
                            <td><?php echo $pass_boys; ?></td>
                            <td><?php echo $pass_total; ?></td>
                        </tr>
                        <tr>
                            <td colspan="9" style="border: none;"></td>
                            <td style="border: 1px solid black; border-top: none;">
                                <?php echo $pass_percent; ?>%
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Ledger Table -->
        <table class="ledger-table" id="ledgerTable">
            <thead>
                <tr>
                    <th rowspan="3" style="width: 30px;" class="th-yellow">S.N</th>
                    <th rowspan="3" style="width: 150px;" class="th-yellow">Name Of Students</th>

                    <?php
                    $colors = ['#f5cba7', '#f9e79f', '#d5f5e3', '#aed6f1', '#e8daef'];
                    foreach ($subject_list as $key => $subj):
                        $bg = $colors[$key % count($colors)];
                        ?>
                        <th colspan="<?php echo ($subj['full_marks'] == 100) ? 6 : 5; ?>"
                            style="background-color: <?php echo $bg; ?> !important; padding: 4px; border: 1px solid black;">
                            <?php echo htmlspecialchars($subj['name']); ?>
                        </th>
                    <?php endforeach; ?>

                    <th rowspan="2" class="th-gray">Total</th>
                    <th rowspan="3" style="width: 30px;" class="th-gray">Percentage</th>
                    <th rowspan="3" style="width: 30px;" class="th-gray">GPA</th>
                    <th rowspan="3" style="width: 30px;" class="th-gray">Att</th>
                    <th rowspan="3" style="width: 30px;" class="th-gray">Rank</th>
                    <th rowspan="3" style="width: 80px;" class="th-gray">Remarks</th>
                </tr>
                <tr>
                    <!-- Subject Sub-headers row 1 -->
                    <?php foreach ($subject_list as $key => $subj):
                        $is_100 = ($subj['full_marks'] == 100);
                        $bg = $colors[$key % count($colors)];
                        ?>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">RW</th>
                        <?php if ($is_100): ?>
                            <th class="sub-col"
                                style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">LS</th>
                        <?php endif; ?>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; font-weight: bold; border: 1px solid black;">
                            OM</th>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">Percentage
                        </th>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">GP</th>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">Grade</th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <!-- Subject Sub-headers row 2 (Full Marks) -->
                    <?php foreach ($subject_list as $key => $subj):
                        $is_100 = ($subj['full_marks'] == 100);
                        $bg = $colors[$key % count($colors)];
                        ?>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">50</th>
                        <?php if ($is_100): ?>
                            <th class="sub-col"
                                style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">50</th>
                        <?php endif; ?>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; font-weight: bold; border: 1px solid black;">
                            <?php echo $subj['full_marks']; ?>
                        </th>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">100</th>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;">4.0</th>
                        <th class="sub-col"
                            style="background-color: <?php echo $bg; ?> !important; border: 1px solid black;"></th>
                    <?php endforeach; ?>
                    <th class="sub-col th-gray" style="font-weight: bold; border: 1px solid black;">
                        <?php echo $total_full_marks; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sn = 1;
                foreach ($ledger_rows as $row):
                    ?>
                    <tr>
                        <td><?php echo $sn++; ?></td>
                        <td style="text-align: left; padding-left: 5px; font-weight: bold;">
                            <?php echo htmlspecialchars($row['student']['full_name']); ?>
                        </td>

                        <?php foreach ($subject_list as $subj):
                            $sname = $subj['name'];
                            $data = $row['subjects'][$sname];
                            $is_100 = ($subj['full_marks'] == 100);
                            ?>
                            <td><?php echo $data['rw']; ?></td>
                            <?php if ($is_100): ?>
                                <td><?php echo $data['ls']; ?></td><?php endif; ?>
                            <td style="font-weight: bold; background: #fffde7;"><?php echo $data['om']; ?></td>
                            <td><?php echo $data['perc']; ?></td>
                            <td><?php echo $data['gp']; ?></td>
                            <td style="font-weight: bold;"><?php echo $data['grade']; ?></td>
                        <?php endforeach; ?>

                        <!-- Grand Total Cols -->
                        <td style="font-weight: bold; background: #e0f2f1;"><?php echo $row['total_om']; ?></td>
                        <td style="font-weight: bold; background: #e0f2f1;"><?php echo $row['final_perc']; ?></td>
                        <td style="font-weight: bold; background: #e0f2f1;"><?php echo $row['final_gpa']; ?></td>
                        <td><?php echo $row['att']; ?></td>
                        <td style="font-weight: bold;"><?php echo isset($row['rank']) ? $row['rank'] : '-'; ?></td>
                        <td
                            style="font-size: 10px; font-weight: bold; <?php echo ($row['remarks'] == 'NOT GRADED') ? 'color: red;' : 'color: green;'; ?>">
                            <?php echo $row['remarks']; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if ($students_with_marks_count > 0): ?>
                    <!-- Average In Subject Row -->
                    <tr>
                        <td colspan="2" style="text-align: center; font-weight: bold; background: #fff;">Average In Subject
                        </td>
                        <?php foreach ($subject_list as $subj):
                            $is_100 = ($subj['full_marks'] == 100);
                            $avg = $subject_om_totals[$subj['name']] / $students_with_marks_count;
                            ?>
                            <td></td>
                            <?php if ($is_100): ?>
                                <td></td><?php endif; ?>
                            <td style="font-weight: bold; background: #fafafa;"><?php echo round($avg, 2); ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        <?php endforeach; ?>

                        <td colspan="6" style="background: #fff;"></td>
                    </tr>

                    <!-- Average Of Class Row -->
                    <tr>
                        <td colspan="2" style="text-align: center; font-weight: bold; background: #fff;">Average Of Class
                        </td>
                        <?php
                        $class_avg = $class_total_om / $students_with_marks_count;
                        $total_subject_cols = 0;
                        foreach ($subject_list as $s) {
                            $total_subject_cols += ($s['full_marks'] == 100) ? 6 : 5;
                        }
                        ?>
                        <td colspan="<?php echo $total_subject_cols; ?>"
                            style="text-align: center; font-weight: bold; background: #fafafa;">
                            <?php echo round($class_avg, 6); ?>
                        </td>
                        <td colspan="6" style="background: #fff;"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div
            style="display: flex; justify-content: space-between; margin-top: 60px; font-weight: bold; padding: 0 50px;">
            <div style="text-align: center;">
                -------------------------------------<br><br>
                Class Teacher
            </div>
            <div style="text-align: center;">
                -------------------------------------<br><br>
                Exam Co-ordinator
            </div>
            <div style="text-align: center;">
                -------------------------------------<br><br>
                Head Teacher
            </div>
        </div>
    </div>

    <script>
        function exportExcel() {
            var table = document.getElementById("ledgerTable");
            var wb = XLSX.utils.table_to_book(table, { sheet: "Le dger" });
    XL SX.writeFile(wb, "Mark_Ledger_<?php echo $class . '_' . $exam_type; ?>.xlsx");
        }
    </script>

</body>

</html>