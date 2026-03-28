<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? $_GET['year'] : '';
$class_val = isset($_GET['class']) ? $_GET['class'] : '';
$exam_type = isset($_GET['exam']) ? $_GET['exam'] : '';
$student_ids_raw = isset($_GET['students']) ? $_GET['students'] : '';

if (empty($year) || empty($class_val) || empty($exam_type) || empty($student_ids_raw)) {
    die("Missing parameters.");
}

$student_ids = explode(',', $student_ids_raw);

$exam_names = [
    'first_terminal' => 'FIRST TERMINAL EXAMINATION',
    'second_terminal' => 'SECOND TERMINAL EXAMINATION',
    'third_terminal' => 'THIRD TERMINAL EXAMINATION',
    'final' => 'FINAL EXAMINATION',
    'monthly' => 'MONTHLY ASSESSMENT'
];

$exam_title = isset($exam_names[$exam_type]) ? $exam_names[$exam_type] : 'EXAMINATION';

// Fetch Schedule
$schedule_sql = "SELECT * FROM exam_schedules WHERE school_id = ? AND class = ? AND exam_type = ? AND year = ?";
$schedule_stmt = $conn->prepare($schedule_sql);
$schedule_stmt->execute([$school_id, $class_val, $exam_type, $year]);
$schedule = $schedule_stmt->fetch(PDO::FETCH_ASSOC);

$shift = $schedule ? $schedule['shift'] : 'DAY';
$time = $schedule ? $schedule['time'] : '10:15 - 01:15';
$subject_data = $schedule ? json_decode($schedule['subject_data'], true) : [];

// Fetch Students
$placeholders = implode(',', array_fill(0, count($student_ids), '?'));
$student_sql = "SELECT id, full_name, symbol_no FROM students WHERE id IN ($placeholders) ORDER BY full_name ASC";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->execute($student_ids);
$students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);

$school_name = $_SESSION['school_name'] ?? 'YOUR SCHOOL NAME';
$school_logo = $_SESSION['school_logo'] ?? '';
$school_photo = $_SESSION['school_photo'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Print Admit Cards - <?php echo htmlspecialchars($school_name); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #555;
        }

        .page {
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            background: #fff;
            box-sizing: border-box;
            page-break-after: always;
            position: relative;
        }

        .admit-card-outer {
            height: 50%;
            width: 100%;
            padding: 1.5rem;
            box-sizing: border-box;
            position: relative;
        }

        .admit-card-outer:first-child {
            border-bottom: 2px dashed #999;
        }

        .card-inner {
            border: 3px solid #1a1a1a;
            height: 100%;
            width: 100%;
            padding: 1.25rem;
            box-sizing: border-box;
            background: #fffdf7;
            /* Light cream for premium look */
            position: relative;
            display: flex;
            flex-direction: column;
            border-radius: 4px;
        }

        .card-inner::before {
            content: "";
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            border: 1px solid #c5a059;
            /* Gold accent */
            pointer-events: none;
            border-radius: 2px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        .school-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 2px;
            background: #fff;
        }

        .center-heading {
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }

        .center-heading h1 {
            margin: 0;
            font-size: 22px;
            color: #1a1a1a;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .center-heading .sub-text {
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-top: 3px;
        }

        .center-heading .exam-name {
            font-size: 16px;
            font-weight: 700;
            color: #dc2626;
            /* Red for emphasis */
            margin-top: 5px;
            text-decoration: underline;
        }

        .saraswati-img {
            width: 75px;
            height: 75px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 2px;
            background: #fff;
        }

        .badge-row {
            text-align: center;
            margin-bottom: 1rem;
        }

        .badge {
            background: #1a1a1a;
            color: #fff;
            padding: 4px 30px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 4px;
            display: inline-block;
            box-shadow: 2px 2px 0 #c5a059;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            border: 1.5px solid #1a1a1a;
            margin-bottom: 10px;
        }

        .info-cell {
            padding: 6px 10px;
            border-right: 1.5px solid #1a1a1a;
            border-bottom: 1.5px solid #1a1a1a;
            font-size: 14px;
        }

        .info-cell:nth-child(even),
        .info-cell:last-child {
            border-right: none;
        }

        .info-cell.no-border-bottom {
            border-bottom: none;
        }

        .label {
            font-weight: 800;
            color: #000;
            margin-right: 8px;
            font-size: 12px;
        }

        .value {
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #1a1a1a;
            background: #fff;
            margin-bottom: 10px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid #1a1a1a;
            padding: 5px 8px;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
        }

        .schedule-table th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
        }

        .footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
        }

        .sig-block {
            text-align: center;
            width: 30%;
            position: relative;
        }

        .sig-line {
            border-top: 1.5px solid #1a1a1a;
            margin-top: 35px;
        }

        .sig-text {
            font-size: 10px;
            font-weight: 800;
            margin-top: 5px;
            color: #000;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
            opacity: 0.1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .watermark img {
            width: 300px;
            height: 300px;
            object-fit: contain;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff;
            }

            .page {
                margin: 0;
                border: none;
            }
        }

        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1f2937;
            color: white;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .btn-print {
            background: #ef4444;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-print:hover {
            background: #dc2626;
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <div class="no-print-bar no-print">
        <div style="font-weight: 700;">
            <i class="fas fa-print"></i> Admit Card Printing - <?php echo count($students); ?> Cards Selected
        </div>
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-file-pdf"></i> PRINT NOW
        </button>
    </div>

    <div style="height: 60px;" class="no-print"></div>

    <?php
    $chunks = array_chunk($students, 2);
    foreach ($chunks as $chunk):
        ?>
        <div class="page">
            <?php foreach ($chunk as $index => $student): ?>
                <div class="admit-card-outer">
                    <div class="card-inner">
                        <div class="watermark">
                            <?php if ($school_logo): ?>
                                <img src="<?php echo htmlspecialchars($school_logo); ?>">
                            <?php else: ?>
                                <img src="assets/images/logo.png"
                                    onerror="this.src='https://img.freepik.com/free-vector/hexagram-book-concept_23-2148817711.jpg'">
                            <?php endif; ?>
                        </div>

                        <div class="header">
                            <?php if ($school_logo): ?>
                                <img src="<?php echo htmlspecialchars($school_logo); ?>" class="school-logo">
                            <?php else: ?>
                                <img src="assets/images/logo.png" class="school-logo"
                                    onerror="this.src='https://img.freepik.com/free-vector/hexagram-book-concept_23-2148817711.jpg'">
                            <?php endif; ?>

                            <div class="center-heading">
                                <h1><?php echo htmlspecialchars($school_name); ?></h1>
                                <div class="sub-text">Bharatpur-11, Jagritichowk, Chitwan</div>
                                <div class="exam-name"><?php echo $exam_title; ?> - <?php echo $year; ?></div>
                            </div>

                            <img src="assets/images/saraswati.png" class="saraswati-img"
                                onerror="this.src='https://cdntcm.m.clvrcncpt.com/assets/misc/saraswati_devi.png'">
                        </div>

                        <div class="badge-row">
                            <div class="badge"><i class="fas fa-id-card"></i> ADMIT CARD</div>
                        </div>

                        <div class="info-grid">
                            <div class="info-cell no-border-bottom" style="grid-column: span 1;">
                                <span class="label">STUDENT NAME:</span>
                                <span class="value"
                                    style="font-size: 15px;"><?php echo htmlspecialchars($student['full_name']); ?></span>
                            </div>
                            <div class="info-cell no-border-bottom">
                                <span class="label">CLASS:</span>
                                <span class="value"><?php echo htmlspecialchars($class_val); ?></span>
                            </div>
                            <div class="info-cell">
                                <span class="label">SYMBOL/ROLL:</span>
                                <span class="value"
                                    style="background: #eee; padding: 2px 8px; border-radius: 4px;"><?php echo htmlspecialchars($student['symbol_no'] ?: '---'); ?></span>
                            </div>
                            <div class="info-cell" style="display: flex; justify-content: space-between;">
                                <span><span class="label">SHIFT:</span> <span
                                        class="value"><?php echo htmlspecialchars($shift); ?></span></span>
                                <span><span class="label">TIME:</span> <span
                                        class="value"><?php echo htmlspecialchars($time); ?></span></span>
                            </div>
                        </div>

                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Date</th>
                                    <th style="width: 32%;">Subject</th>
                                    <th style="width: 13%;">Sign</th>
                                    <th style="width: 20%;">Date</th>
                                    <th style="width: 32%;">Subject</th>
                                    <th style="width: 13%;">Sign</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $left = array_slice($subject_data, 0, 4);
                                $right = array_slice($subject_data, 4, 4);
                                for ($i = 0; $i < 4; $i++):
                                    ?>
                                    <tr>
                                        <td><?php echo isset($left[$i]) ? $left[$i]['date'] : ''; ?></td>
                                        <td><?php echo isset($left[$i]) ? $left[$i]['subject'] : ''; ?></td>
                                        <td></td>
                                        <td><?php echo isset($right[$i]) ? $right[$i]['date'] : ''; ?></td>
                                        <td><?php echo isset($right[$i]) ? $right[$i]['subject'] : ''; ?></td>
                                        <td></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>

                        <div class="footer">
                            <div class="sig-block">
                                <div class="sig-line"></div>
                                <div class="sig-text">CLASS TEACHER</div>
                            </div>
                            <div class="sig-block">
                                <div class="sig-line"></div>
                                <div class="sig-text">EXAM CO-ORDINATOR</div>
                            </div>
                            <div class="sig-block">
                                <div class="sig-line"></div>
                                <div class="sig-text">PRINCIPAL</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

</body>

</html>