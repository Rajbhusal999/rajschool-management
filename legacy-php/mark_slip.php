<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$year = isset($_GET['year']) ? $_GET['year'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$exam_type = isset($_GET['exam']) ? $_GET['exam'] : 'first_terminal';

if (empty($year) || empty($class) || empty($subject)) {
    die("Required parameters missing.");
}

$exam_names = [
    'first_terminal' => 'प्रथम त्रैमासिक परीक्षा',
    'second_terminal' => 'दोस्रो त्रैमासिक परीक्षा',
    'third_terminal' => 'तेस्रो त्रैमासिक परीक्षा',
    'final' => 'अन्तिम परीक्षा',
    'monthly' => 'मासिक परीक्षा'
];

$exam_display_name = isset($exam_names[$exam_type]) ? $exam_names[$exam_type] : 'परीक्षा';

$is_primary = in_array((string) $class, ['1', '2', '3']);
$is_pg_kg = in_array(strtoupper((string) $class), ['PG', 'LKG', 'UKG', 'NURSERY', 'KG']);

// Handle Export to Excel
if (isset($_GET['export']) && $_GET['export'] == 'xlsx') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Markslip_{$class}_{$subject}_{$year}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "\xEF\xBB\xBF";
}

// Fetch Students
$sql_students = "SELECT id, full_name, symbol_no FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->execute([$school_id, $class]);
$students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

$school_name = $_SESSION['school_name'] ?? 'School Name';
$school_logo = $_SESSION['school_logo'] ?? '';
$school_photo = $_SESSION['school_photo'] ?? '';

// Calculate total students to adjust row density
$total_students = count($students);
$is_crowded = ($total_students > 35);
?>
<!DOCTYPE html>
<html lang="ne">

<head>
    <meta charset="UTF-8">
    <title>Mark Slip - <?php echo htmlspecialchars("$subject ($class) - $year"); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }

        .no-print {
            margin: 20px;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 12px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-print {
            background: #10b981;
            color: white;
        }

        .btn-excel {
            background: #3b82f6;
            color: white;
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }

        .a4-page {
            width: 21cm;
            min-height: 29.7cm;
            margin: 0.5cm auto;
            padding: 0.8cm;
            background: white;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .report-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            position: relative;
            min-height: 70px;
        }

        .school-logo {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 65px;
            width: 65px;
            object-fit: contain;
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
            width: 400px;
            height: 400px;
            object-fit: contain;
        }

        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 0;
            border-bottom: 2px solid #000;
            display: inline-block;
            padding: 0 40px 5px 40px;
            text-transform: uppercase;
        }

        .info-header {
            width: 100%;
            border: 2px solid #000;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            padding: 5px 15px;
            font-weight: bold;
            font-size: 16px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size:
                <?php echo $is_crowded ? '11px' : '13px'; ?>
            ;
        }

        th,
        td {
            border: 1.5px solid #000;
            padding:
                <?php echo $is_crowded ? '4px 2px' : '8px 4px'; ?>
            ;
            text-align: center;
        }

        thead th {
            background-color: #fff;
            font-weight: bold;
        }

        .student-name-col {
            text-align: left;
            padding-left: 10px;
            min-width: 220px;
        }

        .symbol-no {
            font-size: 0.8em;
            color: #444;
            float: right;
            border-left: 1px solid #ccc;
            padding-left: 5px;
            margin-left: 5px;
        }

        .footer-section {
            margin-top: 20px;
            display: flex;
            justify-content: flex-start;
            padding: 0 10px;
        }

        .teacher-box {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }

        .sign-line {
            border-bottom: 1px solid #000;
            min-width: 180px;
            height: 15px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: none;
                margin: 0;
            }

            .a4-page {
                width: 100%;
                margin: 0;
                padding: 0.5cm;
                box-shadow: none;
                min-height: auto;
            }

            @page {
                size: A4 portrait;
                margin: 0.5cm;
            }

            table {
                font-size:
                    <?php echo $is_crowded ? '10px' : '12px'; ?>
                ;
            }

            th,
            td {
                padding:
                    <?php echo $is_crowded ? '2px 1px' : '5px 2px'; ?>
                ;
            }
        }
    </style>
</head>

<body>
    <?php if (!isset($_GET['export'])): ?>
        <div class="no-print">
            <a href="markslip_selector.php" class="btn btn-back">Back</a>
            <button onclick="window.print()" class="btn btn-print">Print Markslip</button>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'xlsx'])); ?>"
                class="btn btn-excel">Extract to Excel</a>
        </div>
    <?php endif; ?>

    <div class="a4-page">
        <div class="watermark">
            <?php if ($school_logo): ?>
                <img src="<?php echo htmlspecialchars($school_logo); ?>" alt="Watermark">
            <?php endif; ?>
        </div>
        <div class="report-header">
            <?php if ($school_logo): ?>
                <img src="<?php echo htmlspecialchars($school_logo); ?>" class="school-logo" alt="Logo">
            <?php endif; ?>
            <div class="school-name"><?php echo htmlspecialchars($school_name); ?></div>
        </div>

        <?php if ($is_primary): ?>
            <div style="text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 16px; line-height: 1.5;">
                <div><?php echo htmlspecialchars($_SESSION['school_address'] ?? ''); ?></div>
                <div><?php echo htmlspecialchars($exam_display_name . ' - ' . $year); ?></div>
                <div
                    style="display: flex; justify-content: space-between; margin-top: 10px; padding: 0 10px; text-align: left;">
                    <span>कक्षा : <?php echo htmlspecialchars($class); ?></span>
                    <span>विषय : <?php echo htmlspecialchars($subject); ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="info-header">
                <span>विषय : <?php echo htmlspecialchars($subject); ?></span>
                <span>कक्षा : <?php echo htmlspecialchars($class); ?></span>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <?php if ($is_primary): ?>
                    <tr>
                        <th style="width: 40px;">क्र.स</th>
                        <th>विद्यार्थीको नाम थर</th>
                        <th style="width: 60px;">रोल</th>
                        <th style="width: 90px;">जम्मा सिकाइ<br>उपलब्धि</th>
                        <th style="width: 90px;">प्राप्त<br>सिकाइ<br>उपलब्धि</th>
                        <th style="width: 80px;">उपलब्धि<br>प्रतिशत</th>
                        <th style="width: 80px;">कैफियत</th>
                    </tr>
                <?php elseif ($is_pg_kg): ?>
                    <tr>
                        <th style="width: 40px;">क्र.सं. (S.N.)</th>
                        <th>विद्यार्थीको नाम (Student Name)</th>
                        <th style="width: 120px;">RW<br><small>(Max: 50 Marks)</small></th>
                        <th style="width: 120px;">LS<br><small>(For 100 Marks)</small></th>
                        <th style="width: 120px;">Total Marks<br><small>Obtained</small></th>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th rowspan="2" style="width: 30px;">क्र.सं.</th>
                        <th rowspan="2">विद्यार्थीको नाम</th>
                        <th colspan="3">सहभागीता</th>
                        <th colspan="3">परियोजना / प्रयोगात्मक</th>
                        <?php if ($exam_type == 'final'): ?>
                            <th rowspan="2">प्रथम त्रैमासिक (५)</th>
                            <th rowspan="2">दोस्रो त्रैमासिक (५)</th>
                            <th rowspan="2">लिखित (५०)</th>
                            <th rowspan="2">जम्मा (१००)</th>
                        <?php else: ?>
                            <th rowspan="2"><?php echo $exam_display_name; ?> (१०)</th>
                            <th rowspan="2">जम्मा (५०)</th>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <th style="width: 45px;">हाजिरी (२)</th>
                        <th style="width: 45px;">सक्रियता (२)</th>
                        <th style="width: 50px;">जम्मा (४)</th>
                        <th style="width: 45px;">१६</th>
                        <th style="width: 45px;">२०</th>
                        <th style="width: 55px;">जम्मा (३६)</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php foreach ($students as $index => $student): ?>
                    <?php if ($is_primary): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td class="student-name-col">
                                <?php echo htmlspecialchars($student['full_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($student['symbol_no']); ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php elseif ($is_pg_kg): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td class="student-name-col">
                                <?php echo htmlspecialchars($student['full_name']); ?>
                                <span class="symbol-no"><?php echo htmlspecialchars($student['symbol_no']); ?></span>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td class="student-name-col">
                                <?php echo htmlspecialchars($student['full_name']); ?>
                                <span class="symbol-no"><?php echo htmlspecialchars($student['symbol_no']); ?></span>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <?php if ($exam_type == 'final'): ?>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php else: ?>
                                <td></td>
                                <td></td>
                            <?php endif; ?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($is_primary): ?>
            <div style="margin-top: 30px; font-size: 14px; line-height: 2; padding-left: 10px; font-weight: bold;">
                <div>हस्ताक्षर :</div>
                <div>शिक्षकको नाम :</div>
                <div>सम्पर्क नं :</div>
            </div>
        <?php else: ?>
            <div class="footer-section">
                <div class="teacher-box">
                    <span style="font-weight: bold; font-size: 14px;">विषय शिक्षक :</span>
                    <div class="sign-line"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>