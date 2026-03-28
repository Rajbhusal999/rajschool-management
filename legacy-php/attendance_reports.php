<?php
require 'includes/auth_school.php';
restrictFeature('attendance');
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// ---------------------------------------------------------
// SCHEMA MIGRATION: Ensure principal_password exists
// ---------------------------------------------------------
try {
    $conn->exec("ALTER TABLE schools ADD COLUMN principal_password VARCHAR(255) DEFAULT NULL");
} catch (PDOException $e) {
    // Column likely exists
}

// Handle Logout from Report Section
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['report_verified']);
    header("Location: attendance_reports.php");
    exit();
}

// ---------------------------------------------------------
// GATE KEEPER LOGIC
// ---------------------------------------------------------

// Fetch Principal Password Status
$stmt = $conn->prepare("SELECT principal_password FROM schools WHERE id = ?");
$stmt->execute([$school_id]);
$school_data = $stmt->fetch(PDO::FETCH_ASSOC);
$has_password = !empty($school_data['principal_password']);

// Handle Password Setting / Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['set_password'])) {
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass === $confirm_pass && !empty($new_pass)) {
            // Store as plain text per user preference pattern in this project so far, 
            // but strict security suggests hash. Given previous request for plain text teacher password "save by user",
            // I'll stick to simple storage but hashing is better.
            // Let's use simple text for now to align with "set by principal" and easy recovery 
            // if admin panel doesn't have reset. Or just hash it. 
            // I'll hash it for "principal" security.
            // Actually, let's keep it consistent. If teacher password is plain, this might be expected plain.
            // But 'password_hash' column in schools table implies hashing exists.
            // I'll use plain text to be safe with "save by user" requirement interpretation 
            // (user wants to see it maybe?), but for security I should hash.
            // I'll use plain text to avoid complexity if they want to view it later.
            // Wait, "password section which is set by principal".
            // I'll hash it to be professional.
            // Actually, if I hash it, I can't show it back.
            // I'll just hash it.

            // Edit: User said "save into a .xlsx form... make a page... make it have a password section".
            // I'll hash it.
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE schools SET principal_password = ? WHERE id = ?");
            $update->execute([$hashed, $school_id]);
            $msg = "Password set successfully. Please login.";
            $has_password = true;
        } else {
            $msg = "Passwords do not match!";
        }
    } elseif (isset($_POST['login_report'])) {
        $input_pass = $_POST['report_password'];
        if ($has_password && password_verify($input_pass, $school_data['principal_password'])) {
            $_SESSION['report_verified'] = true;
            header("Location: attendance_reports.php");
            exit();
        } else {
            $msg = "Invalid Password!";
        }
    }
}

if (!isset($_SESSION['report_verified'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Principal Login - Reports</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            :root {
                --primary: #6366f1;
                --primary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
                --bg-body: #f8fafc;
            }

            body {
                font-family: 'Outfit', sans-serif;
                background: var(--bg-body);
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }

            .auth-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                padding: 3.5rem;
                border-radius: 35px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 480px;
                border: 1px solid rgba(255, 255, 255, 0.5);
                text-align: center;
            }

            .auth-icon {
                width: 80px;
                height: 80px;
                background: var(--primary-gradient);
                color: white;
                border-radius: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2.25rem;
                margin: 0 auto 2rem;
                box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
            }

            .auth-title {
                font-size: 2rem;
                font-weight: 800;
                color: #0f172a;
                margin-bottom: 0.75rem;
                letter-spacing: -1px;
            }

            .auth-subtitle {
                color: #64748b;
                margin-bottom: 2.5rem;
                font-weight: 500;
                line-height: 1.6;
            }

            .form-group {
                margin-bottom: 1.5rem;
                text-align: left;
            }

            .form-label {
                display: block;
                font-weight: 700;
                color: #475569;
                margin-bottom: 0.75rem;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .form-control {
                width: 100%;
                padding: 14px 1.25rem;
                border: 2.5px solid #e2e8f0;
                border-radius: 16px;
                font-size: 1.1rem;
                background: white;
                color: #0f172a;
                font-weight: 600;
                transition: all 0.3s;
                box-sizing: border-box;
                font-family: 'Outfit', sans-serif;
            }

            .form-control:focus {
                outline: none;
                border-color: #6366f1;
                box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.1);
            }

            .btn-auth {
                width: 100%;
                background: var(--primary-gradient);
                color: white;
                padding: 16px;
                border: none;
                border-radius: 18px;
                cursor: pointer;
                font-weight: 800;
                font-size: 1.2rem;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
                margin-top: 1rem;
            }

            .btn-auth:hover {
                transform: translateY(-4px) scale(1.02);
                box-shadow: 0 15px 30px rgba(99, 102, 241, 0.3);
            }

            .alert-auth {
                background: #fee2e2;
                color: #b91c1c;
                padding: 14px;
                border-radius: 16px;
                margin-bottom: 2rem;
                font-weight: 700;
                font-size: 0.95rem;
                border: 1px solid rgba(185, 28, 28, 0.1);
            }

            .back-link {
                display: inline-block;
                margin-top: 2rem;
                color: #94a3b8;
                text-decoration: none;
                font-weight: 700;
                transition: all 0.2s;
            }

            .back-link:hover {
                color: #6366f1;
            }
        </style>
    </head>

    <body>
        <div class="auth-card">
            <div class="auth-icon">
                <i class="fas fa-fingerprint"></i>
            </div>
            <h2 class="auth-title">Principal Verification</h2>

            <?php if ($msg): ?>
                <div class="alert-auth">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!$has_password): ?>
                <p class="auth-subtitle">Establish a secure analytical perimeter. Set your principal access credentials.</p>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="set_password" class="btn-auth">Establish Security</button>
                </form>
            <?php else: ?>
                <p class="auth-subtitle">Please provide your security credentials to access institutional analytics.</p>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Principal Password</label>
                        <input type="password" name="report_password" class="form-control" placeholder="••••••••" required
                            autofocus>
                    </div>
                    <button type="submit" name="login_report" class="btn-auth">Unlock Analytics</button>
                </form>
            <?php endif; ?>

            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Return to Command Center
            </a>
        </div>
    </body>

    </html>
    <?php
    exit();
}

// ---------------------------------------------------------
// EXPORT LOGIC
// ---------------------------------------------------------
if (isset($_POST['export_xlsx'])) {
    $year = $_POST['year'];
    $month = str_pad($_POST['month'], 2, '0', STR_PAD_LEFT);
    $class = $_POST['class'];
    $date_prefix = "$year-$month";

    // Fetch Days in this month (1-32 max for Nepali)
    // We'll just generate columns 1-32 to be safe or query distinct dates
    $days_in_month = 32;

    // Fetch Students
    $students = $conn->query("SELECT id, full_name, symbol_no FROM students WHERE school_id = $school_id AND class = '$class' ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Attendance
    $attendance_raw = $conn->query("SELECT student_id, attendance_date, status, session FROM student_attendance 
                                  WHERE school_id = $school_id AND class = '$class' AND attendance_date LIKE '$date_prefix-%'")->fetchAll(PDO::FETCH_ASSOC);

    // Organize Attendance: [student_id][day][session] = status
    $attendance = [];
    foreach ($attendance_raw as $row) {
        $day = (int) substr($row['attendance_date'], 8, 2);
        $attendance[$row['student_id']][$day][$row['session']] = $row['status'];
    }

    // Generate Excel (Native XML Spreadsheet)
    require_once 'includes/XLSXWriter.php';
    $filename = "Attendance_Class_{$class}_{$year}_{$month}.xls";

    $rows = [];

    // Header Row 1: School Info
    $rows[] = [['value' => $_SESSION['school_name'], 'style' => 1]]; // Center

    // Header Row 2: Title
    $rows[] = [['value' => 'Monthly Attendance Ledger - Class ' . $class . ' (' . $year . '/' . $month . ')', 'style' => 1]];

    // Header Row 3: Columns
    $headerr = [];
    $headerr[] = ['value' => 'Symbol No', 'style' => 1];
    $headerr[] = ['value' => 'Student Name', 'style' => 1];
    for ($d = 1; $d <= $days_in_month; $d++) {
        $headerr[] = ['value' => $d, 'style' => 1];
    }
    $headerr[] = ['value' => 'Total Present', 'style' => 1];
    $rows[] = $headerr;

    // Data Rows
    foreach ($students as $s) {
        $row = [];
        $total_present = 0;

        $row[] = ['value' => $s['symbol_no'], 'style' => 1];
        $row[] = ['value' => $s['full_name'], 'style' => 0]; // Name Left aligned (default)

        for ($d = 1; $d <= $days_in_month; $d++) {
            $m_status = isset($attendance[$s['id']][$d]['Morning']) ? $attendance[$s['id']][$d]['Morning'] : '-';
            $e_status = isset($attendance[$s['id']][$d]['Evening']) ? $attendance[$s['id']][$d]['Evening'] : '-';

            // Abbreviate
            $abbr = ['Present' => 'P', 'Absent' => 'A', 'Leave' => 'L', 'Late' => 'Lt', 'Excused' => 'E', 'Extra Class' => 'Ex', '-' => '-'];
            $m = $abbr[$m_status] ?? $m_status;
            $e = $abbr[$e_status] ?? $e_status;

            $cell_val = "$m / $e";
            if ($m == '-' && $e == '-')
                $cell_val = "";
            elseif ($m == $e)
                $cell_val = $m;

            // Style Logic
            $style = 1; // Center default
            if (strpos($cell_val, 'A') !== false)
                $style = 2; // Reddish
            elseif (strpos($cell_val, 'L') !== false)
                $style = 3; // Yellowish
            elseif (strpos($cell_val, 'Ex') !== false)
                $style = 4; // Blueish (Extra Class)

            $row[] = ['value' => $cell_val, 'style' => $style];

            if ($m == 'P')
                $total_present += 1;
        }
        $row[] = ['value' => $total_present, 'style' => 1]; // Center total
        $rows[] = $row;
    }

    $writer = new XLSXWriter();
    $writer->writeSheet($rows);
    $writer->download($filename);
    exit();
}

// ---------------------------------------------------------
// VIEW REPORT LOGIC
// ---------------------------------------------------------
$report_data = null;
if (isset($_POST['view_report'])) {
    $year = $_POST['year'];
    $month = str_pad($_POST['month'], 2, '0', STR_PAD_LEFT);
    $class = $_POST['class'];
    $date_prefix = "$year-$month";

    // Fetch Data (Same as Export)
    $days_in_month = 32;
    $students = $conn->query("SELECT id, full_name, symbol_no FROM students WHERE school_id = $school_id AND class = '$class' ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $attendance_raw = $conn->query("SELECT student_id, attendance_date, status, session FROM student_attendance 
                                  WHERE school_id = $school_id AND class = '$class' AND attendance_date LIKE '$date_prefix-%'")->fetchAll(PDO::FETCH_ASSOC);

    $attendance = [];
    foreach ($attendance_raw as $row) {
        $day = (int) substr($row['attendance_date'], 8, 2);
        $attendance[$row['student_id']][$day][$row['session']] = $row['status'];
    }

    $report_data = [
        'students' => $students,
        'attendance' => $attendance,
        'year' => $year,
        'month' => $month,
        'class' => $class,
        'days_in_month' => $days_in_month
    ];
}

// ---------------------------------------------------------
// REPORT UI
// ---------------------------------------------------------
// Fetch Classes
$classes = $conn->query("SELECT DISTINCT class FROM students WHERE school_id = $school_id")->fetchAll(PDO::FETCH_COLUMN);
natsort($classes);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Attendance Reports</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
            --bg-body: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            margin: 0;
            color: #1e293b;
        }

        .workspace-container {
            padding: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .report-nexus {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2.5rem;
            align-items: start;
        }

        .control-hub {
            background: white;
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
            position: sticky;
            top: 2rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-select,
        .form-input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1rem;
            background: #fcfdfe;
            color: #0f172a;
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 1.5rem;
            font-family: 'Outfit', sans-serif;
            box-sizing: border-box;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-action {
            width: 100%;
            padding: 16px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 18px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 15px rgba(99, 102, 241, 0.2);
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(99, 102, 241, 0.3);
        }

        .btn-download {
            background: linear-gradient(135deg, #10b981, #059669);
            padding: 10px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .report-previewer {
            background: white;
            border-radius: 30px;
            padding: 2.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.04);
            border: 1px solid #f1f5f9;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1.5px solid #f1f5f9;
        }

        .table-nexus {
            overflow-x: auto;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
        }

        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .analytics-table th {
            background: #0f172a;
            color: white;
            padding: 12px 8px;
            font-weight: 700;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .analytics-table td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #f1f5f9;
            font-weight: 600;
            color: #334155;
        }

        .name-cell {
            text-align: left !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            position: sticky;
            left: 0;
            background: white;
            z-index: 1;
        }

        .top-command {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -1.5px;
            margin: 0;
        }

        @media (max-width: 1024px) {
            .report-nexus {
                grid-template-columns: 1fr;
            }

            .control-hub {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .workspace-container {
                padding: 1.5rem;
            }

            .page-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="workspace-container">
        <div class="top-command">
            <h1 class="page-title">Attendance Analytics</h1>
            <a href="attendance_reports.php?action=logout"
                style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 700; color: #ef4444; background: #fef2f2; padding: 10px 20px; border-radius: 12px; transition: 0.3s;">
                <i class="fas fa-lock"></i> Secure Lock
            </a>
        </div>

        <div class="report-nexus">
            <!-- Sidebar Controls -->
            <div class="control-hub">
                <h3 class="card-title"><i class="fas fa-filter" style="color: var(--primary);"></i> Nexus Parameters
                </h3>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Academic Year (BS)</label>
                        <select name="year" class="form-select" required>
                            <?php
                            $curr_y = date('Y') + 56;
                            for ($y = 2080; $y <= 2090; $y++) {
                                $sel = ($y == $curr_y) ? 'selected' : '';
                                echo "<option value='$y' $sel>$y</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Month</label>
                        <select name="month" class="form-select" required>
                            <?php
                            $months = [1 => 'Baishakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan', 5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir', 9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra'];
                            $curr_m = (int) (date('m') + 8) % 12;
                            if ($curr_m == 0)
                                $curr_m = 12;
                            foreach ($months as $num => $name) {
                                $sel = ($num == $curr_m) ? 'selected' : '';
                                echo "<option value='$num' $sel>$name ($num)</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructional Class</label>
                        <select name="class" class="form-select" required>
                            <option value="">Select Target Class</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="view_report" class="btn-action">
                        <i class="fas fa-satellite-dish"></i> Sync Analytics View
                    </button>
                </form>
            </div>

            <!-- Report Display -->
            <div class="report-workspace">
                <?php if ($report_data): ?>
                    <div class="report-previewer">
                        <div class="preview-header">
                            <div>
                                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a;">
                                    Class <?php echo $report_data['class']; ?> Ledger
                                </h3>
                                <p style="margin: 5px 0 0 0; color: #64748b; font-weight: 500;">
                                    Dataset for Period: <?php echo $report_data['year']; ?> /
                                    <?php echo $report_data['month']; ?>
                                </p>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="year" value="<?php echo $report_data['year']; ?>">
                                <input type="hidden" name="month" value="<?php echo (int) $report_data['month']; ?>">
                                <input type="hidden" name="class" value="<?php echo $report_data['class']; ?>">
                                <button type="submit" name="export_xlsx" class="btn-action btn-download"
                                    style="width: auto;">
                                    <i class="fas fa-file-excel"></i> Export Digital Ledger
                                </button>
                            </form>
                        </div>

                        <div class="table-nexus">
                            <table class="analytics-table">
                                <thead>
                                    <tr>
                                        <th>Symbol</th>
                                        <th class="name-cell">Student Matrix</th>
                                        <?php for ($d = 1; $d <= $report_data['days_in_month']; $d++): ?>
                                            <th><?php echo $d; ?></th>
                                        <?php endfor; ?>
                                        <th style="background: #4f46e5;">Present</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data['students'] as $s): ?>
                                        <?php $total_present = 0; ?>
                                        <tr>
                                            <td style="background: #f8fafc; color: #6366f1; font-weight: 700;">
                                                <?php echo $s['symbol_no']; ?>
                                            </td>
                                            <td class="name-cell"><?php echo htmlspecialchars($s['full_name']); ?></td>

                                            <?php for ($d = 1; $d <= $report_data['days_in_month']; $d++): ?>
                                                <?php
                                                $m_status = isset($report_data['attendance'][$s['id']][$d]['Morning']) ? $report_data['attendance'][$s['id']][$d]['Morning'] : '-';
                                                $e_status = isset($report_data['attendance'][$s['id']][$d]['Evening']) ? $report_data['attendance'][$s['id']][$d]['Evening'] : '-';

                                                $abbr = ['Present' => 'P', 'Absent' => 'A', 'Leave' => 'L', 'Late' => 'Lt', 'Excused' => 'E', 'Extra Class' => 'Ex', '-' => '-'];
                                                $m = $abbr[$m_status] ?? $m_status;
                                                $e = $abbr[$e_status] ?? $e_status;
                                                $cell_val = ($m == $e) ? $m : "$m / $e";
                                                if ($m == '-' && $e == '-')
                                                    $cell_val = "";
                                                if ($m == 'P')
                                                    $total_present += 1;

                                                $bg = "";
                                                if (strpos($m, 'A') !== false || strpos($e, 'A') !== false)
                                                    $bg = "#fef2f2";
                                                elseif ($m == 'L' || $e == 'L')
                                                    $bg = "#fffbeb";
                                                elseif ($m == 'Ex' || $e == 'Ex')
                                                    $bg = "#eef2ff";
                                                elseif ($m == 'P')
                                                    $bg = "#f0fdf4";
                                                ?>
                                                <td style="background: <?php echo $bg; ?>; font-size: 0.75rem;">
                                                    <?php echo $cell_val; ?>
                                                </td>
                                            <?php endfor; ?>
                                            <td style="background: #f8fafc; font-weight: 800; color: #0f172a;">
                                                <?php echo $total_present; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div
                        style="height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: white; border-radius: 30px; border: 2px dashed #e2e8f0; color: #94a3b8;">
                        <i class="fas fa-layer-group" style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;"></i>
                        <h3 style="margin: 0;">Specify Analysis Parameters</h3>
                        <p>Select academic year, month, and class to generate intelligence reports.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
</div>
</body>

</html>