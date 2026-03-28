<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';
restrictFeature('teacher_salary');

$school_id = $_SESSION['user_id'];
$org_type = $_GET['type'] ?? 'private';
$selected_month = $_GET['month'] ?? '';
if (empty($selected_month)) {
    $chk_latest = $conn->query("SELECT MAX(salary_month) FROM teacher_salaries WHERE school_id=$school_id")->fetchColumn();
    if ($chk_latest) {
        $selected_month = $chk_latest;
    } else {
        $ny = date('Y') + 56;
        $nm = date('m');
        $selected_month = $ny . '-' . $nm;
    }
}
$is_gov = ($org_type === 'government');

// ── 1. Ensure tables ────────────────────────────────────────────────────────
$conn->exec("CREATE TABLE IF NOT EXISTS salary_topics (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    school_id  INT NOT NULL,
    org_type   VARCHAR(20) NOT NULL DEFAULT 'government',
    tab_key    VARCHAR(80) NOT NULL,
    label      VARCHAR(120) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_topic (school_id, org_type, tab_key)
)");

$conn->exec("CREATE TABLE IF NOT EXISTS salary_column_labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    org_type VARCHAR(20) NOT NULL DEFAULT 'government',
    col_key VARCHAR(50) NOT NULL,
    custom_label VARCHAR(120) NOT NULL,
    UNIQUE KEY uniq_label (school_id, org_type, col_key)
)");

$conn->exec("CREATE TABLE IF NOT EXISTS teacher_salaries (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    school_id       INT NOT NULL,
    teacher_id      INT NOT NULL,
    salary_month    VARCHAR(7) NOT NULL,
    tab_key         VARCHAR(80) NOT NULL DEFAULT 'default',
    grade_count     INT DEFAULT 0,
    salary_scale    DECIMAL(10,2) DEFAULT 0,
    grade_amount    DECIMAL(10,2) DEFAULT 0,
    total_salary    DECIMAL(10,2) DEFAULT 0,
    pf_addition     DECIMAL(10,2) DEFAULT 0,
    cit_addition    DECIMAL(10,2) DEFAULT 0,
    dearness_allowance DECIMAL(10,2) DEFAULT 0,
    principal_allowance DECIMAL(10,2) DEFAULT 0,
    gross_salary    DECIMAL(10,2) DEFAULT 0,
    base_salary     DECIMAL(10,2) DEFAULT 0,
    allowance       DECIMAL(10,2) DEFAULT 0,
    pf_deduction    DECIMAL(10,2) DEFAULT 0,
    cit_deduction   DECIMAL(10,2) DEFAULT 0,
    total_deduction DECIMAL(10,2) DEFAULT 0,
    payable_amount  DECIMAL(10,2) DEFAULT 0,
    tax_deduction   DECIMAL(10,2) DEFAULT 0,
    other_deduction DECIMAL(10,2) DEFAULT 0,
    bonus           DECIMAL(10,2) DEFAULT 0,
    net_salary      DECIMAL(10,2) DEFAULT 0,
    status          ENUM('unpaid','paid') DEFAULT 'unpaid',
    paid_date       DATE NULL,
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_salary (school_id, teacher_id, salary_month, tab_key)
)");

// Pivot table for assigning teachers to tabs
$conn->exec("CREATE TABLE IF NOT EXISTS teacher_salary_assignments (
    school_id INT NOT NULL,
    teacher_id INT NOT NULL,
    tab_key VARCHAR(80) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (school_id, teacher_id, tab_key)
)");

// Migration: add missing columns
$existing = $conn->query("SHOW COLUMNS FROM teacher_salaries")->fetchAll(PDO::FETCH_COLUMN);
$need = [
    'tab_key' => "ALTER TABLE teacher_salaries ADD COLUMN tab_key VARCHAR(80) NOT NULL DEFAULT 'default' AFTER salary_month",
    'grade_count' => "ALTER TABLE teacher_salaries ADD COLUMN grade_count INT DEFAULT 0",
    'salary_scale' => "ALTER TABLE teacher_salaries ADD COLUMN salary_scale DECIMAL(10,2) DEFAULT 0",
    'grade_amount' => "ALTER TABLE teacher_salaries ADD COLUMN grade_amount DECIMAL(10,2) DEFAULT 0",
    'total_salary' => "ALTER TABLE teacher_salaries ADD COLUMN total_salary DECIMAL(10,2) DEFAULT 0",
    'pf_addition' => "ALTER TABLE teacher_salaries ADD COLUMN pf_addition DECIMAL(10,2) DEFAULT 0",
    'cit_addition' => "ALTER TABLE teacher_salaries ADD COLUMN cit_addition DECIMAL(10,2) DEFAULT 0",
    'dearness_allowance' => "ALTER TABLE teacher_salaries ADD COLUMN dearness_allowance DECIMAL(10,2) DEFAULT 0",
    'principal_allowance' => "ALTER TABLE teacher_salaries ADD COLUMN principal_allowance DECIMAL(10,2) DEFAULT 0",
    'gross_salary' => "ALTER TABLE teacher_salaries ADD COLUMN gross_salary DECIMAL(10,2) DEFAULT 0",
    'allowance' => "ALTER TABLE teacher_salaries ADD COLUMN allowance DECIMAL(10,2) DEFAULT 0",
    'custom_add_1' => "ALTER TABLE teacher_salaries ADD COLUMN custom_add_1 DECIMAL(10,2) DEFAULT 0",
    'custom_add_2' => "ALTER TABLE teacher_salaries ADD COLUMN custom_add_2 DECIMAL(10,2) DEFAULT 0",
    'custom_ded_1' => "ALTER TABLE teacher_salaries ADD COLUMN custom_ded_1 DECIMAL(10,2) DEFAULT 0",
    'custom_ded_2' => "ALTER TABLE teacher_salaries ADD COLUMN custom_ded_2 DECIMAL(10,2) DEFAULT 0",
    'pf_deduction' => "ALTER TABLE teacher_salaries ADD COLUMN pf_deduction DECIMAL(10,2) DEFAULT 0",
    'cit_deduction' => "ALTER TABLE teacher_salaries ADD COLUMN cit_deduction DECIMAL(10,2) DEFAULT 0",
    'total_deduction' => "ALTER TABLE teacher_salaries ADD COLUMN total_deduction DECIMAL(10,2) DEFAULT 0",
    'payable_amount' => "ALTER TABLE teacher_salaries ADD COLUMN payable_amount DECIMAL(10,2) DEFAULT 0",
    'tax_deduction' => "ALTER TABLE teacher_salaries ADD COLUMN tax_deduction DECIMAL(10,2) DEFAULT 0",
    'other_deduction' => "ALTER TABLE teacher_salaries ADD COLUMN other_deduction DECIMAL(10,2) DEFAULT 0",
];
foreach ($need as $col => $sql) {
    if (!in_array($col, $existing)) {
        try {
            $conn->exec($sql);
        } catch (Exception $e) {
        }
    }
}
try {
    $conn->exec("ALTER TABLE teacher_salaries DROP INDEX uniq_salary");
} catch (Exception $e) {
}
try {
    $conn->exec("ALTER TABLE teacher_salaries ADD UNIQUE KEY uniq_salary (school_id, teacher_id, salary_month, tab_key)");
} catch (Exception $e) {
}

// ── 2. Seed default government topics (only once) ───────────────────────────
if ($is_gov) {
    $chk = $conn->prepare("SELECT COUNT(*) FROM salary_topics WHERE school_id=? AND org_type='government'");
    $chk->execute([$school_id]);
    if ($chk->fetchColumn() == 0) {
        $defaults = [
            ['sanghiya_darbandi', 'संघीय दरबन्दी', 1],
            ['sanghiya_anudan', 'संघीय अनुदान', 2],
            ['mahanagar', 'महानगर', 3],
            ['niji_srot', 'निजी स्रोत', 4],
            ['samagra_talab', 'समग्र तलब', 5],
            ['samashtigat_vivaran', 'समष्टिगत विवरण', 6],
        ];
        $ins = $conn->prepare("INSERT IGNORE INTO salary_topics (school_id,org_type,tab_key,label,sort_order) VALUES (?,?,?,?,?)");
        foreach ($defaults as $d)
            $ins->execute([$school_id, 'government', $d[0], $d[1], $d[2]]);
    }
}

// ── 3. Handle POST actions ───────────────────────────────────────────────────
$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Add topic
    if ($action === 'add_topic') {
        $label = trim($_POST['topic_label'] ?? '');
        $tab_key = 'topic_' . time() . '_' . rand(100, 999);
        $st = $conn->prepare("SELECT MAX(sort_order) FROM salary_topics WHERE school_id=? AND org_type=?");
        $st->execute([$school_id, $org_type]);
        $next_order = ($st->fetchColumn() ?? 0) + 1;
        if ($label !== '') {
            $conn->prepare("INSERT INTO salary_topics (school_id,org_type,tab_key,label,sort_order) VALUES (?,?,?,?,?)")
                ->execute([$school_id, $org_type, $tab_key, $label, $next_order]);
            $message = '"' . $label . '" विषय थपियो!';
            $msg_type = 'success';
            // Redirect to new tab
            header("Location: teacher_salary.php?type=$org_type&tab=$tab_key&month=$selected_month");
            exit();
        } else {
            $message = 'विषयको नाम खाली हुन सक्दैन।';
            $msg_type = 'error';
        }
    }

    // Delete topic
    if ($action === 'delete_topic') {
        $del_key = $_POST['del_tab_key'] ?? '';
        if ($del_key) {
            $conn->prepare("DELETE FROM salary_topics WHERE school_id=? AND org_type=? AND tab_key=?")
                ->execute([$school_id, $org_type, $del_key]);
            $conn->prepare("DELETE FROM teacher_salaries WHERE school_id=? AND tab_key=?")
                ->execute([$school_id, $del_key]);
            $message = 'विषय मेटाइयो।';
            $msg_type = 'info';
            header("Location: teacher_salary.php?type=$org_type&month=$selected_month");
            exit();
        }
    }

    // Save column labels
    if ($action === 'save_labels') {
        $labels = $_POST['labels'] ?? [];
        $stmt = $conn->prepare("INSERT INTO salary_column_labels (school_id, org_type, col_key, custom_label) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE custom_label=VALUES(custom_label)");
        foreach ($labels as $k => $v) {
            $stmt->execute([$school_id, $org_type, $k, trim($v)]);
        }
        $message = 'शीर्षकहरूको नाम सुरक्षित गरियो!';
        $msg_type = 'success';
    }

    // Salary data actions
    $tid = intval($_POST['teacher_id'] ?? 0);
    $month = $_POST['salary_month'] ?? $selected_month;
    $tab = $_POST['tab_key'] ?? 'default';

    if ($action === 'save_salary') {
        $grade_count = intval($_POST['grade_count'] ?? 0);
        $salary_scale = floatval($_POST['salary_scale'] ?? 0);
        $grade_amount = floatval($_POST['grade_amount'] ?? 0);
        $total_salary = floatval($_POST['total_salary'] ?? 0);
        $pf_addition = floatval($_POST['pf_addition'] ?? 0);
        $cit_addition = floatval($_POST['cit_addition'] ?? 0);
        $dearness_allowance = floatval($_POST['dearness_allowance'] ?? 0);
        $principal_allowance = floatval($_POST['principal_allowance'] ?? 0);
        $custom_add_1 = floatval($_POST['custom_add_1'] ?? 0);
        $custom_add_2 = floatval($_POST['custom_add_2'] ?? 0);
        $gross_salary = floatval($_POST['gross_salary'] ?? 0);

        $pf_deduction = floatval($_POST['pf_deduction'] ?? 0);
        $cit_deduction = floatval($_POST['cit_deduction'] ?? 0);
        $custom_ded_1 = floatval($_POST['custom_ded_1'] ?? 0);
        $custom_ded_2 = floatval($_POST['custom_ded_2'] ?? 0);
        $total_deduction = floatval($_POST['total_deduction'] ?? 0);

        $payable_amount = floatval($_POST['payable_amount'] ?? 0);
        $tax_deduction = floatval($_POST['tax_deduction'] ?? 0);
        $net_salary = floatval($_POST['net_salary'] ?? 0);

        // Backward compatibility for private schools
        $base = floatval($_POST['base_salary'] ?? 0);
        $allow = floatval($_POST['allowance'] ?? 0);
        $othd = floatval($_POST['other_deduction'] ?? 0);
        $bon = floatval($_POST['bonus'] ?? 0);

        if ($salary_scale == 0 && $base > 0)
            $salary_scale = $base;
        $notes = trim($_POST['notes'] ?? '');

        $conn->prepare("INSERT INTO teacher_salaries
            (school_id,teacher_id,salary_month,tab_key,
             grade_count,salary_scale,grade_amount,total_salary,pf_addition,cit_addition,dearness_allowance,principal_allowance,custom_add_1,custom_add_2,gross_salary,
             pf_deduction,cit_deduction,custom_ded_1,custom_ded_2,total_deduction,payable_amount,tax_deduction,
             base_salary,allowance,other_deduction,bonus,net_salary,notes)
            VALUES(?,?,?,?, ?,?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?, ?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
            grade_count=VALUES(grade_count),salary_scale=VALUES(salary_scale),grade_amount=VALUES(grade_amount),total_salary=VALUES(total_salary),
            pf_addition=VALUES(pf_addition),cit_addition=VALUES(cit_addition),dearness_allowance=VALUES(dearness_allowance),principal_allowance=VALUES(principal_allowance),
            custom_add_1=VALUES(custom_add_1),custom_add_2=VALUES(custom_add_2),gross_salary=VALUES(gross_salary),
            pf_deduction=VALUES(pf_deduction),cit_deduction=VALUES(cit_deduction),custom_ded_1=VALUES(custom_ded_1),custom_ded_2=VALUES(custom_ded_2),total_deduction=VALUES(total_deduction),
            payable_amount=VALUES(payable_amount),tax_deduction=VALUES(tax_deduction),
            base_salary=VALUES(base_salary),allowance=VALUES(allowance),other_deduction=VALUES(other_deduction),bonus=VALUES(bonus),
            net_salary=VALUES(net_salary),notes=VALUES(notes)")
            ->execute([
                $school_id,
                $tid,
                $month,
                $tab,
                $grade_count,
                $salary_scale,
                $grade_amount,
                $total_salary,
                $pf_addition,
                $cit_addition,
                $dearness_allowance,
                $principal_allowance,
                $custom_add_1,
                $custom_add_2,
                $gross_salary,
                $pf_deduction,
                $cit_deduction,
                $custom_ded_1,
                $custom_ded_2,
                $total_deduction,
                $payable_amount,
                $tax_deduction,
                $base,
                $allow,
                $othd,
                $bon,
                $net_salary,
                $notes
            ]);
        $message = 'तलब रेकर्ड सुरक्षित!';
        $msg_type = 'success';
    }
    if ($action === 'mark_paid') {
        $conn->prepare("UPDATE teacher_salaries SET status='paid',paid_date=CURDATE() WHERE school_id=? AND teacher_id=? AND salary_month=? AND tab_key=?")->execute([$school_id, $tid, $month, $tab]);
        $message = 'तलब भुक्तान भयो!';
        $msg_type = 'success';
    }
    if ($action === 'mark_unpaid') {
        $conn->prepare("UPDATE teacher_salaries SET status='unpaid',paid_date=NULL WHERE school_id=? AND teacher_id=? AND salary_month=? AND tab_key=?")->execute([$school_id, $tid, $month, $tab]);
        $message = 'तलब बाँकी चिन्ह लगाइयो।';
        $msg_type = 'info';
    }

    // Assign teachers to current tab
    if ($action === 'assign_teachers') {
        $assigned_tab = $_POST['tab_key'] ?? '';
        $teacher_ids = $_POST['teacher_ids'] ?? [];
        if ($assigned_tab) {
            // Drop existing assignments for this tab
            $conn->prepare("DELETE FROM teacher_salary_assignments WHERE school_id=? AND tab_key=?")->execute([$school_id, $assigned_tab]);
            // Insert new ones
            if (!empty($teacher_ids)) {
                $ins_stmt = $conn->prepare("INSERT INTO teacher_salary_assignments (school_id, teacher_id, tab_key) VALUES (?, ?, ?)");
                foreach ($teacher_ids as $p_tid) {
                    $ins_stmt->execute([$school_id, intval($p_tid), $assigned_tab]);
                }
            }
            $message = 'शिक्षक/कर्मचारी असाइन गरियो।';
            $msg_type = 'success';
        }
    }
}

// ── 4. Load topics from DB ───────────────────────────────────────────────────
$topics_stmt = $conn->prepare("SELECT * FROM salary_topics WHERE school_id=? AND org_type=? ORDER BY sort_order ASC");
$topics_stmt->execute([$school_id, $org_type]);
$topics = $topics_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fallback: if no topics, first key
$active_tab = $_GET['tab'] ?? ($topics[0]['tab_key'] ?? 'default');

// ── 5. Teachers & salary records ─────────────────────────────────────────────
// Fetch ALL school teachers/staff for the assignment modal
$all_t_stmt = $conn->prepare("SELECT id, full_name, staff_role FROM teachers WHERE school_id=? ORDER BY full_name ASC");
$all_t_stmt->execute([$school_id]);
$all_teachers = $all_t_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch assigned teachers for current tab to pre-check boxes
$assigned_stmt = $conn->prepare("SELECT teacher_id FROM teacher_salary_assignments WHERE school_id=? AND tab_key=?");
$assigned_stmt->execute([$school_id, $active_tab]);
$assigned_ids = $assigned_stmt->fetchAll(PDO::FETCH_COLUMN);

// Display ONLY teachers explicitly assigned to this specific tab
$stmt = $conn->prepare("
    SELECT t.id, t.full_name, t.subject, t.tah, t.bank_name, t.account_number
    FROM teachers t
    JOIN teacher_salary_assignments tsa ON t.id = tsa.teacher_id
    WHERE t.school_id = ? AND tsa.tab_key = ?
    ORDER BY t.full_name ASC
");
$stmt->execute([$school_id, $active_tab]);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM teacher_salaries WHERE school_id=? AND salary_month=? AND tab_key=?");
$stmt->execute([$school_id, $selected_month, $active_tab]);
$salary_records = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC))
    $salary_records[$r['teacher_id']] = $r;

$total_paid = 0;
$total_unpaid = 0;
$paid_count = 0;
$unpaid_count = 0;
foreach ($salary_records as $r) {
    if ($r['status'] === 'paid') {
        $total_paid += $r['net_salary'];
        $paid_count++;
    } else {
        $total_unpaid += $r['net_salary'];
        $unpaid_count++;
    }
}

// Active topic label
$active_label = 'विषय';
foreach ($topics as $t) {
    if ($t['tab_key'] === $active_tab) {
        $active_label = $t['label'];
        break;
    }
}

// ── 6. Load Custom Column Labels ─────────────────────────────────────────────
$stmt = $conn->prepare("SELECT col_key, custom_label FROM salary_column_labels WHERE school_id=? AND org_type=?");
$stmt->execute([$school_id, $org_type]);
$custom_labels_db = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

$default_gov_labels = [
    'grade_count' => 'ग्रेड संख्या',
    'salary_scale' => 'तलब स्केल',
    'grade_amount' => 'जम्मा ग्रेड',
    'total_salary' => 'जम्मा तलब',
    'pf_addition' => 'क. स कोष थप',
    'cit_addition' => 'ना. ल. कोष थप',
    'dearness_allowance' => 'महंगी भत्ता',
    'principal_allowance' => 'प्र. अ. भत्ता',
    'custom_add_1' => '',
    'custom_add_2' => '',
    'gross_salary' => 'कुल तलब',
    'pf_deduction' => 'क. स. कोष कट्टी',
    'cit_deduction' => 'ना.ल. कोष कट्टी',
    'custom_ded_1' => '',
    'custom_ded_2' => '',
    'tax_deduction' => '१ % सा. सु. कर',
    'other_deduction' => 'अन्य कट्टी',
    'total_deduction' => 'जम्मा कट्टी',
    'payable_amount' => 'कुल भुक्तानी',
    'net_salary' => 'खुद भुक्तानी'
];

$lbl = function ($key) use ($default_gov_labels, $custom_labels_db) {
    if (isset($custom_labels_db[$key]) && trim($custom_labels_db[$key]) !== '') {
        return $custom_labels_db[$key];
    }
    return $default_gov_labels[$key] ?? $key;
};
?>
<!DOCTYPE html>
<html lang="ne">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_gov ? 'सरकारी शिक्षक तलब' : 'Teacher Salary' ?> | Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', 'Noto Sans Devanagari', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }

        .wrap {
            max-width: 1480px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* ── Header ── */
        .ph {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .ph-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-box {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
            background:
                <?= $is_gov ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#6366f1,#4f46e5)' ?>
            ;
            box-shadow: 0 8px 20px
                <?= $is_gov ? 'rgba(16,185,129,0.3)' : 'rgba(99,102,241,0.3)' ?>
            ;
        }

        .ph h1 {
            font-size: 1.7rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .ph p {
            color: #64748b;
            margin: 2px 0 0;
            font-size: 0.88rem;
        }

        .badge {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: white;
            margin-left: 8px;
            vertical-align: middle;
            background:
                <?= $is_gov ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#6366f1,#4f46e5)' ?>
            ;
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.87rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #0f172a;
        }

        @media print {
            body {
                background: white;
            }

            .sidebar,
            .navbar-container,
            .ph-right,
            .tab-bar,
            .month-filter,
            .ba,
            .btn-save,
            .btn-cancel,
            .back-link {
                display: none !important;
            }

            .wrap {
                padding: 0;
                margin: 0;
                max-width: 100%;
            }

            .tbl-wrap {
                border: none;
                overflow: visible !important;
                box-shadow: none;
            }

            table {
                min-width: 100% !important;
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 5px !important;
                font-size: 9px !important;
                border: 1px solid #ccc;
                white-space: normal;
            }

            /* hide action and status column */
            th:nth-last-child(1),
            td:nth-last-child(1),
            th:nth-last-child(2),
            td:nth-last-child(2) {
                display: none !important;
            }

            .sig-col {
                display: table-cell !important;
                width: 80px;
            }

            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }

            .print-header h2 {
                margin: 0;
                font-size: 16px;
                font-weight: bold;
            }

            .print-header p {
                margin: 3px 0;
                font-size: 12px;
            }

            .print-header .desc {
                font-size: 11px;
                margin-top: 10px;
                text-align: justify;
            }

            th:last-child,
            td:last-child {
                display: none !important;
            }

            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }

        /* ── Alert ── */
        .alert {
            padding: .9rem 1.4rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-info {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* ── Tab Container ── */
        .tab-container {
            background: white;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* ── Tab Bar ── */
        .tab-bar {
            display: flex;
            align-items: stretch;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .tab-bar::-webkit-scrollbar {
            display: none;
        }

        .tab-btn {
            padding: 13px 22px;
            font-size: 0.93rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            white-space: nowrap;
            border: none;
            background: none;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.22s;
            display: flex;
            align-items: center;
            gap: 7px;
            position: relative;
            text-decoration: none;
        }

        .tab-btn:hover {
            color:
                <?= $is_gov ? '#10b981' : '#6366f1' ?>
            ;
            background: rgba(<?= $is_gov ? '16,185,129' : '99,102,241' ?>, 0.05);
        }

        .tab-btn.active {
            color:
                <?= $is_gov ? '#059669' : '#4f46e5' ?>
            ;
            font-weight: 700;
            border-bottom-color:
                <?= $is_gov ? '#10b981' : '#6366f1' ?>
            ;
            background: white;
        }

        .tab-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            font-size: 0.6rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-family: 'Outfit', sans-serif;
            flex-shrink: 0;
        }

        .tab-delete:hover {
            background: #ef4444;
            color: white;
        }

        /* ── Add Topic Button in tab bar ── */
        .btn-add-topic {
            padding: 10px 18px;
            margin: 6px 10px 6px auto;
            border-radius: 12px;
            border: 2px dashed
                <?= $is_gov ? '#10b981' : '#6366f1' ?>
            ;
            background: transparent;
            color:
                <?= $is_gov ? '#10b981' : '#6366f1' ?>
            ;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            transition: all 0.22s;
            flex-shrink: 0;
        }

        .btn-add-topic:hover {
            background:
                <?= $is_gov ? 'rgba(16,185,129,0.08)' : 'rgba(99,102,241,0.08)' ?>
            ;
            transform: scale(1.03);
        }

        /* ── Tab Content ── */
        .tab-body {
            padding: 1.75rem 2rem;
        }

        .tab-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .tab-header-row h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .month-filter {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .month-filter label {
            font-weight: 600;
            color: #475569;
            font-size: 0.87rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .month-filter input[type="month"] {
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 7px 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.88rem;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            cursor: pointer;
            transition: border 0.2s;
        }

        .month-filter input[type="month"]:focus {
            border-color:
                <?= $is_gov ? '#10b981' : '#6366f1' ?>
            ;
        }

        /* ── Summary ── */
        .sum-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .s-card {
            background: #f8fafc;
            border-radius: 14px;
            padding: 1.1rem;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: .9rem;
        }

        .s-ico {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .s-lbl {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-family: 'Outfit', sans-serif;
        }

        .s-val {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
        }

        /* ── Table ── */
        .tbl-wrap {
            background: white;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        thead th {
            padding: .85rem 1.1rem;
            font-weight: 700;
            font-size: 0.8rem;
            text-align: left;
            white-space: nowrap;
            color: white;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
            background:
                <?= $is_gov ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#6366f1,#4f46e5)' ?>
            ;
        }

        tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody td {
            padding: .85rem 1.1rem;
            font-size: 0.88rem;
            color: #374151;
            vertical-align: middle;
        }

        .sbadge {
            padding: 3px 11px;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .s-paid {
            background: #dcfce7;
            color: #166534;
        }

        .s-unpaid {
            background: #fee2e2;
            color: #b91c1c;
        }

        .s-none {
            background: #f1f5f9;
            color: #94a3b8;
        }

        .ba {
            padding: 6px 12px;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.78rem;
            font-family: 'Outfit', sans-serif;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .b-edit {
            background: rgba(<?= $is_gov ? '16,185,129' : '99,102,241' ?>, 0.1);
            color:
                <?= $is_gov ? '#059669' : '#4f46e5' ?>
            ;
        }

        .b-edit:hover {
            background:
                <?= $is_gov ? '#10b981' : '#6366f1' ?>
            ;
            color: white;
        }

        .b-pay {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .b-pay:hover {
            background: #10b981;
            color: white;
        }

        .b-unpay {
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
        }

        .b-unpay:hover {
            background: #ef4444;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 2.8rem;
            margin-bottom: .8rem;
            display: block;
        }

        /* ── Modals ── */
        .mo {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .mo.active {
            display: flex;
        }

        .mc {
            background: white;
            border-radius: 22px;
            padding: 2.25rem;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            max-height: 92vh;
            overflow-y: auto;
        }

        .mo-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .fg {
            margin-bottom: 1rem;
        }

        .fg label {
            display: block;
            font-weight: 600;
            color: #374151;
            font-size: 0.85rem;
            margin-bottom: 4px;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .fg input,
        .fg textarea {
            width: 100%;
            padding: 9px 13px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
            font-size: 0.92rem;
            color: #1e293b;
            background: #f8fafc;
            box-sizing: border-box;
            outline: none;
            transition: border 0.2s;
        }

        .fg input:focus,
        .fg textarea:focus {
            border-color:
                <?= $is_gov ? '#10b981' : '#6366f1' ?>
            ;
            background: white;
        }

        .fg-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .9rem;
        }

        .fg-row3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .8rem;
        }

        .net-box {
            background: linear-gradient(135deg, rgba(<?= $is_gov ? '16,185,129' : '99,102,241' ?>, 0.1), rgba(<?= $is_gov ? '5,150,105' : '79,70,229' ?>, 0.05));
            border: 1px solid rgba(<?= $is_gov ? '16,185,129' : '99,102,241' ?>, 0.25);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            margin: .9rem 0;
        }

        .net-lbl {
            font-size: 0.8rem;
            color: #64748b;
            margin: 0 0 2px;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .net-val {
            font-size: 1.85rem;
            font-weight: 800;
            color:
                <?= $is_gov ? '#059669' : '#4f46e5' ?>
            ;
        }

        .ma {
            display: flex;
            gap: .8rem;
            margin-top: 1.4rem;
        }

        .btn-save {
            flex: 1;
            padding: 11px;
            border: none;
            border-radius: 11px;
            font-weight: 700;
            font-size: .93rem;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
            color: white;
            transition: .2s;
            background:
                <?= $is_gov ? 'linear-gradient(135deg,#10b981,#059669)' : 'linear-gradient(135deg,#6366f1,#4f46e5)' ?>
            ;
        }

        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(<?= $is_gov ? '16,185,129' : '99,102,241' ?>, 0.35);
        }

        .btn-cancel {
            padding: 11px 18px;
            background: #f1f5f9;
            color: #64748b;
            border: none;
            border-radius: 11px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
        }

        /* Add topic modal special */
        .add-topic-hint {
            font-size: .82rem;
            color: #94a3b8;
            margin-top: 5px;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .topic-input-wrap {
            position: relative;
        }

        .sig-col {
            display: none;
        }

        .print-header {
            display: none;
        }

        .topic-input-wrap input {
            font-size: 1.05rem;
            padding: 11px 14px;
            font-family: 'Noto Sans Devanagari', 'Outfit', sans-serif;
        }

        .nepali-tip {
            font-size: .78rem;
            color: #10b981;
            margin-top: 4px;
        }

        .calc-field[readonly] {
            background-color: #f1f5f9;
            color: #64748b;
            border-color: #cbd5e1;
            cursor: not-allowed;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="wrap">

        <!-- Page Header -->
        <div class="ph">
            <div class="ph-left">
                <div class="icon-box"><i class="fas fa-<?= $is_gov ? 'landmark' : 'school' ?>"></i></div>
                <div>
                    <h1><?= $is_gov ? 'सरकारी शिक्षक तलब' : 'Teacher Salary' ?> <span
                            class="badge"><?= $is_gov ? 'GOVERNMENT' : 'PRIVATE' ?></span></h1>
                    <p><?= $is_gov ? 'सरकारी विद्यालयको तलब व्यवस्थापन प्रणाली' : 'Private school salary management system' ?>
                    </p>
                </div>
            </div>
            <a href="teacher_salary_select.php" class="back-link"><i class="fas fa-arrow-left"></i> संगठन छनोट</a>
        </div>

        <!-- Alert -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $msg_type ?>">
                <i
                    class="fas fa-<?= $msg_type === 'success' ? 'check-circle' : ($msg_type === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Tab Container -->
        <div class="tab-container">

            <!-- Tab Bar -->
            <div class="tab-bar" id="tabBar">
                <?php if (empty($topics)): ?>
                    <span
                        style="padding:14px 20px;color:#94a3b8;font-size:.9rem;font-family:'Noto Sans Devanagari','Outfit',sans-serif;">कुनै
                        विषय छैन</span>
                <?php else: ?>
                    <?php foreach ($topics as $t): ?>
                        <a class="tab-btn <?= $active_tab === $t['tab_key'] ? 'active' : '' ?>"
                            href="teacher_salary.php?type=<?= $org_type ?>&tab=<?= urlencode($t['tab_key']) ?>&month=<?= $selected_month ?>">
                            <?= htmlspecialchars($t['label']) ?>
                            <?php if ($is_gov): ?>
                                <form method="POST" style="display:inline;margin:0;"
                                    onsubmit="return confirm('«<?= htmlspecialchars($t['label']) ?>» विषय मेट्ने?')">
                                    <input type="hidden" name="action" value="delete_topic">
                                    <input type="hidden" name="del_tab_key" value="<?= htmlspecialchars($t['tab_key']) ?>">
                                    <button type="submit" class="tab-delete" title="मेट्नुहोस्"><i
                                            class="fas fa-times"></i></button>
                                </form>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Add Topic Button -->
                <button class="btn-add-topic"
                    onclick="document.getElementById('addTopicModal').classList.add('active')">
                    <i class="fas fa-plus"></i> विषय थप्नुहोस्
                </button>
            </div>

            <!-- Tab Body -->
            <div class="tab-body">
                <div class="tab-header-row">
                    <h2><?= htmlspecialchars($active_label) ?> — <?= htmlspecialchars($selected_month) ?>
                    </h2>
                    <div style="display:flex; align-items:center; gap: 15px;">
                        <?php if ($is_gov): ?>
                            <button class="btn btn-outline"
                                onclick="document.getElementById('renameColumnsModal').classList.add('active')"
                                style="padding: 10px 18px; font-size: 0.95rem; border-radius:10px; font-weight: 600;">
                                <i class="fas fa-edit"></i> शीर्षकहरू (Columns) सम्पादन
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-primary"
                            onclick="document.getElementById('assignTeachersModal').classList.add('active')"
                            style="padding: 10px 18px; font-size: 0.95rem; border-radius:10px; cursor: pointer;">
                            <i class="fas fa-users-cog"></i>
                            <?= $is_gov ? 'शिक्षक/कर्मचारी छान्नुहोस्' : 'Assign Staff' ?>
                        </button>
                        <button class="btn btn-primary" onclick="exportToExcel()"
                            style="padding: 10px 18px; font-size: 0.95rem; border-radius:10px; background: #2563eb; color: white; border: none;">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                        <button class="btn btn-primary" onclick="window.print()"
                            style="padding: 10px 18px; font-size: 0.95rem; border-radius:10px; background: #4b5563; color: white; border: none;">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <div class="month-filter">
                            <label><i class="fas fa-calendar-alt"
                                    style="color:<?= $is_gov ? '#10b981' : '#6366f1' ?>;"></i>
                                <?= $is_gov ? 'महिना (BS):' : 'Month (BS):' ?></label>
                            <?php
                            $s_parts = explode('-', $selected_month);
                            $s_y = $s_parts[0] ?? (date('Y') + 56);
                            $s_m = str_pad($s_parts[1] ?? date('m'), 2, '0', STR_PAD_LEFT);
                            ?>
                            <select id="nepY" onchange="updateNMonth()"
                                style="padding:7px; border:1px solid #e2e8f0; border-radius:8px; font-family:'Outfit'; outline:none; background:#f8fafc; font-weight:600; cursor:pointer;">
                                <?php for ($y = 2075; $y <= 2090; $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == $s_y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="nepM" onchange="updateNMonth()"
                                style="padding:7px; border:1px solid #e2e8f0; border-radius:8px; font-family:'Noto Sans Devanagari','Outfit',sans-serif; outline:none; background:#f8fafc; font-weight:600; cursor:pointer;">
                                <?php
                                $n_months = [
                                    '01' => 'वैशाख (Baisakh)',
                                    '02' => 'जेठ (Jestha)',
                                    '03' => 'असार (Ashadh)',
                                    '04' => 'साउन (Shrawan)',
                                    '05' => 'भदौ (Bhadra)',
                                    '06' => 'असोज (Ashwin)',
                                    '07' => 'कार्तिक (Kartik)',
                                    '08' => 'मंसिर (Mangsir)',
                                    '09' => 'पुष (Poush)',
                                    '10' => 'माघ (Magh)',
                                    '11' => 'फागुन (Falgun)',
                                    '12' => 'चैत (Chaitra)'
                                ];
                                foreach ($n_months as $num => $name): ?>
                                    <option value="<?= $num ?>" <?= $num == $s_m ? 'selected' : '' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <script>
                                function updateNMonth() {
                                    let y = document.getElementById('nepY').value;
                                    let m = document.getElementById('nepM').value;
                                    location.href = '?type=<?= $org_type ?>&tab=<?= urlencode($active_tab) ?>&month=' + y + '-' + m;
                                }
                            </script>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="sum-grid">
                    <div class="s-card">
                        <div class="s-ico"
                            style="background:rgba(<?= $is_gov ? '16,185,129' : '99,102,241' ?>,0.1);color:<?= $is_gov ? '#10b981' : '#6366f1' ?>;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div>
                            <div class="s-lbl"><?= $is_gov ? 'जम्मा शिक्षक' : 'Teachers' ?></div>
                            <div class="s-val"><?= count($teachers) ?></div>
                        </div>
                    </div>
                    <div class="s-card">
                        <div class="s-ico" style="background:rgba(16,185,129,0.1);color:#10b981;"><i
                                class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="s-lbl"><?= $is_gov ? 'भुक्तान' : 'Paid' ?></div>
                            <div class="s-val"><?= $paid_count ?></div>
                        </div>
                    </div>
                    <div class="s-card">
                        <div class="s-ico" style="background:rgba(239,68,68,0.1);color:#ef4444;"><i
                                class="fas fa-clock"></i></div>
                        <div>
                            <div class="s-lbl"><?= $is_gov ? 'बाँकी' : 'Unpaid' ?></div>
                            <div class="s-val"><?= $unpaid_count ?></div>
                        </div>
                    </div>
                    <div class="s-card">
                        <div class="s-ico" style="background:rgba(16,185,129,0.1);color:#10b981;"><i
                                class="fas fa-wallet"></i></div>
                        <div>
                            <div class="s-lbl"><?= $is_gov ? 'भुक्तान रकम (रु.)' : 'Paid (Rs.)' ?></div>
                            <div class="s-val"><?= number_format($total_paid) ?></div>
                        </div>
                    </div>
                    <div class="s-card">
                        <div class="s-ico" style="background:rgba(239,68,68,0.1);color:#ef4444;"><i
                                class="fas fa-exclamation-circle"></i></div>
                        <div>
                            <div class="s-lbl"><?= $is_gov ? 'बाँकी रकम (रु.)' : 'Pending (Rs.)' ?></div>
                            <div class="s-val"><?= number_format($total_unpaid) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Teacher Table -->
                <?php if (empty($topics)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open" style="color:#cbd5e1;"></i>
                        <p style="font-family:'Noto Sans Devanagari','Outfit',sans-serif;">पहिले माथि <strong>«विषय
                                थप्नुहोस्»</strong> बटन थिच्नुस्।</p>
                    </div>
                <?php elseif (empty($teachers)): ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard-teacher" style="color:#cbd5e1;"></i>
                        <p style="font-family:'Noto Sans Devanagari','Outfit',sans-serif;">कुनै शिक्षक फेला परेन।</p>
                        <a href="teachers.php" style="color:<?= $is_gov ? '#10b981' : '#6366f1' ?>;font-weight:700;">शिक्षक
                            थप्नुहोस् →</a>
                    </div>
                <?php else: ?>
                    <div class="print-header">
                        <?php
                        $stmt_sch = $conn->prepare("SELECT school_name, address FROM users WHERE id = ?");
                        $stmt_sch->execute([$school_id]);
                        $sch_info = $stmt_sch->fetch(PDO::FETCH_ASSOC);

                        // Convert JS dates roughly if we can, or just print standard
                        $nep_year = substr($selected_month, 0, 4);
                        $nep_month_num = substr($selected_month, 5, 2);
                        $nep_month_names = ['01' => 'वैशाख', '02' => 'जेठ', '03' => 'असार', '04' => 'साउन', '05' => 'भदौ', '06' => 'असोज', '07' => 'कार्तिक', '08' => 'मंसिर', '09' => 'पुष', '10' => 'माघ', '11' => 'फागुन', '12' => 'चैत'];
                        $nep_month_str = $nep_month_names[$nep_month_num] ?? '';
                        $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                        $nep = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
                        $nep_y_str = str_replace($eng, $nep, $nep_year);
                        $nep_m_str = str_replace($eng, $nep, $nep_month_num);
                        $next_y_str = str_replace($eng, $nep, substr($nep_year + 1, -2));
                        ?>
                        <h2>श्री <?= htmlspecialchars($sch_info['school_name'] ?? '') ?></h2>
                        <p><?= htmlspecialchars($sch_info['address'] ?? '') ?></p>
                        <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">मासिक तलब भरपाई
                            <?= $nep_y_str ?>     <?= $nep_month_str ?> महिनाको तलब भत्ता</p>
                        <div class="desc">
                            भरपाई दादै तपशिलमा उल्लेखित शिक्षकहरुको आर्थिक वर्ष <?= $nep_y_str ?>/<?= $next_y_str ?> को
                            <?= $nep_month_str ?> महिनाको तलब भत्ता <?= htmlspecialchars($active_label) ?> निम्न बमोजिम
                            आफ्नो बैंकको व्यक्तिगत खाता मार्फत भरपाई गरिदियौ <?= $nep_y_str ?>/<?= $nep_m_str ?>/
                        </div>
                    </div>
                    <div class="tbl-wrap" style="overflow-x:auto;">
                        <table id="salaryTable" style="min-width: 1400px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>शिक्षकको नाम थर</th>
                                    <th style="min-width:70px;">तह</th>
                                    <th style="min-width:60px;"><?= htmlspecialchars($lbl('grade_count')) ?></th>
                                    <th><?= htmlspecialchars($lbl('salary_scale')) ?></th>
                                    <th><?= htmlspecialchars($lbl('grade_amount')) ?></th>
                                    <th><?= htmlspecialchars($lbl('total_salary')) ?></th>
                                    <th><?= htmlspecialchars($lbl('pf_addition')) ?></th>
                                    <th><?= htmlspecialchars($lbl('cit_addition')) ?></th>
                                    <th><?= htmlspecialchars($lbl('dearness_allowance')) ?></th>
                                    <th><?= htmlspecialchars($lbl('principal_allowance')) ?></th>
                                    <?php if (!empty($lbl('custom_add_1'))): ?>
                                        <th><?= htmlspecialchars($lbl('custom_add_1')) ?></th><?php endif; ?>
                                    <?php if (!empty($lbl('custom_add_2'))): ?>
                                        <th><?= htmlspecialchars($lbl('custom_add_2')) ?></th><?php endif; ?>
                                    <th><?= htmlspecialchars($lbl('gross_salary')) ?></th>
                                    <th><?= htmlspecialchars($lbl('pf_deduction')) ?></th>
                                    <th><?= htmlspecialchars($lbl('cit_deduction')) ?></th>
                                    <?php if (!empty($lbl('custom_ded_1'))): ?>
                                        <th><?= htmlspecialchars($lbl('custom_ded_1')) ?></th><?php endif; ?>
                                    <?php if (!empty($lbl('custom_ded_2'))): ?>
                                        <th><?= htmlspecialchars($lbl('custom_ded_2')) ?></th><?php endif; ?>
                                    <th><?= htmlspecialchars($lbl('total_deduction')) ?></th>
                                    <th><?= htmlspecialchars($lbl('payable_amount')) ?></th>
                                    <th><?= htmlspecialchars($lbl('tax_deduction')) ?></th>
                                    <th><?= htmlspecialchars($lbl('net_salary')) ?></th>
                                    <!-- Keep status and action but we'll hide them in print -->
                                    <th>स्थिति</th>
                                    <th style="min-width:140px;">कार्य</th>
                                    <!-- Signature column for print/export only -->
                                    <th class="sig-col">हस्ताक्षर</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teachers as $i => $t):
                                    $rec = $salary_records[$t['id']] ?? null; ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($t['full_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($t['tah'] ?? '—') ?></td>
                                        <td><?= $rec ? intval($rec['grade_count']) : '—' ?></td>
                                        <td><?= $rec ? number_format($rec['salary_scale'], 2) : '—' ?></td>
                                        <td><?= $rec ? number_format($rec['grade_amount'], 2) : '—' ?></td>
                                        <td style="font-weight:600;"><?= $rec ? number_format($rec['total_salary'], 2) : '—' ?>
                                        </td>
                                        <td><?= $rec ? number_format($rec['pf_addition'], 2) : '—' ?></td>
                                        <td><?= $rec ? number_format($rec['cit_addition'], 2) : '—' ?></td>
                                        <td><?= $rec ? number_format($rec['dearness_allowance'], 2) : '—' ?></td>
                                        <td><?= $rec ? number_format($rec['principal_allowance'], 2) : '—' ?></td>
                                        <?php if (!empty($lbl('custom_add_1'))): ?>
                                            <td><?= $rec ? number_format($rec['custom_add_1'], 2) : '—' ?></td><?php endif; ?>
                                        <?php if (!empty($lbl('custom_add_2'))): ?>
                                            <td><?= $rec ? number_format($rec['custom_add_2'], 2) : '—' ?></td><?php endif; ?>
                                        <td style="font-weight:600; color:#0f172a;">
                                            <?= $rec ? number_format($rec['gross_salary'], 2) : '—' ?>
                                        </td>
                                        <td><?= $rec ? number_format($rec['pf_deduction'], 2) : '—' ?></td>
                                        <td><?= $rec ? number_format($rec['cit_deduction'], 2) : '—' ?></td>
                                        <?php if (!empty($lbl('custom_ded_1'))): ?>
                                            <td><?= $rec ? number_format($rec['custom_ded_1'], 2) : '—' ?></td><?php endif; ?>
                                        <?php if (!empty($lbl('custom_ded_2'))): ?>
                                            <td><?= $rec ? number_format($rec['custom_ded_2'], 2) : '—' ?></td><?php endif; ?>
                                        <td style="color:#ef4444;"><?= $rec ? number_format($rec['total_deduction'], 2) : '—' ?>
                                        </td>
                                        <td style="font-weight:600;">
                                            <?= $rec ? number_format($rec['payable_amount'], 2) : '—' ?>
                                        </td>
                                        <td><?= $rec ? number_format($rec['tax_deduction'], 2) : '—' ?></td>
                                        <td><strong
                                                style="color:<?= $is_gov ? '#059669' : '#4f46e5' ?>; font-size:1.05em;"><?= $rec ? number_format($rec['net_salary'], 2) : '—' ?></strong>
                                        </td>
                                        <td>
                                            <?php if (!$rec): ?><span class="sbadge s-none"><i class="fas fa-minus"></i> Not
                                                    Set</span>
                                            <?php elseif ($rec['status'] === 'paid'): ?><span class="sbadge s-paid"><i
                                                        class="fas fa-check"></i> <?= $is_gov ? 'भुक्तान' : 'Paid' ?></span>
                                            <?php else: ?><span class="sbadge s-unpaid"><i class="fas fa-clock"></i>
                                                    <?= $is_gov ? 'बाँकी' : 'Unpaid' ?></span><?php endif; ?>
                                        </td>
                                        <td style="display:flex;gap:5px;flex-wrap:wrap;">
                                            <button class="ba b-edit" style="padding: 6px 10px;"
                                                onclick='openSalaryModal(<?= $t["id"] ?>,"<?= htmlspecialchars(addslashes($t["full_name"])) ?>","<?= $selected_month ?>","<?= $active_tab ?>",<?= floatval($rec["grade_count"] ?? 0) ?>,<?= floatval($rec["salary_scale"] ?? 0) ?>,<?= floatval($rec["grade_amount"] ?? 0) ?>,<?= floatval($rec["cit_addition"] ?? 0) ?>,<?= floatval($rec["dearness_allowance"] ?? 0) ?>,<?= floatval($rec["principal_allowance"] ?? 0) ?>,<?= floatval($rec["cit_deduction"] ?? 0) ?>,<?= floatval($rec["tax_deduction"] ?? 0) ?>,"<?= addslashes($rec["notes"] ?? "") ?>", <?= floatval($rec["total_salary"] ?? 0) ?>, <?= floatval($rec["pf_addition"] ?? 0) ?>, <?= floatval($rec["gross_salary"] ?? 0) ?>, <?= floatval($rec["pf_deduction"] ?? 0) ?>, <?= floatval($rec["total_deduction"] ?? 0) ?>, <?= floatval($rec["payable_amount"] ?? 0) ?>, <?= floatval($rec["other_deduction"] ?? 0) ?>, <?= floatval($rec["custom_add_1"] ?? 0) ?>, <?= floatval($rec["custom_add_2"] ?? 0) ?>, <?= floatval($rec["custom_ded_1"] ?? 0) ?>, <?= floatval($rec["custom_ded_2"] ?? 0) ?>)'>
                                                <i class="fas fa-edit"></i> <?= $rec ? "सम्पादन" : "थप्नुस्" ?>
                                            </button>
                                            <?php if ($rec && $rec['status'] === 'unpaid'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="mark_paid">
                                                    <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
                                                    <input type="hidden" name="salary_month" value="<?= $selected_month ?>">
                                                    <input type="hidden" name="tab_key" value="<?= $active_tab ?>">
                                                    <button type="submit" class="ba b-pay" style="padding: 6px 10px;"><i
                                                            class="fas fa-check"></i></button>
                                                </form>
                                            <?php elseif ($rec && $rec['status'] === 'paid'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="mark_unpaid">
                                                    <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
                                                    <input type="hidden" name="salary_month" value="<?= $selected_month ?>">
                                                    <input type="hidden" name="tab_key" value="<?= $active_tab ?>">
                                                    <button type="submit" class="ba b-unpay" style="padding: 6px 10px;"><i
                                                            class="fas fa-undo"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td class="sig-col"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div><!-- /tab-container -->
    </div><!-- /wrap -->

    <!-- ── ASSIGN TEACHERS MODAL ──────────────────────────────────────── -->
    <div class="mo" id="assignTeachersModal">
        <div class="mc" style="max-width:550px;">
            <div class="mo-title"><i class="fas fa-users-cog" style="color:<?= $is_gov ? '#10b981' : '#6366f1' ?>;"></i>
                <?= htmlspecialchars($active_label) ?> मा असाइन गर्नुहोस्</div>
            <form method="POST">
                <input type="hidden" name="action" value="assign_teachers">
                <input type="hidden" name="tab_key" value="<?= htmlspecialchars($active_tab) ?>">

                <div
                    style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:15px; max-height: 400px; overflow-y:auto; margin-bottom: 20px;">
                    <?php if (empty($all_teachers)): ?>
                        <div style="text-align:center; color:#94a3b8; font-size:0.95rem; padding: 20px;">कुनै
                            शिक्षक/कर्मचारी फेला परेन।</div>
                    <?php else: ?>
                        <?php foreach ($all_teachers as $at): ?>
                            <label
                                style="display:flex; align-items:center; gap:12px; padding:10px; border-bottom:1px solid #f1f5f9; cursor:pointer; transition:background 0.2s;">
                                <input type="checkbox" name="teacher_ids[]" value="<?= $at['id'] ?>" <?= in_array($at['id'], $assigned_ids) ? 'checked' : '' ?>
                                    style="width: 18px; height: 18px; accent-color: <?= $is_gov ? '#10b981' : '#6366f1' ?>;">
                                <div style="flex:1;">
                                    <div style="font-weight:600; color:#1e293b; font-size:0.95rem;">
                                        <?= htmlspecialchars($at['full_name']) ?>
                                    </div>
                                    <div style="font-size:0.8rem; color:#64748b; font-weight:600;">
                                        <?= htmlspecialchars($at['staff_role']) ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="ma">
                    <button type="button" class="btn-cancel"
                        onclick="document.getElementById('assignTeachersModal').classList.remove('active')">रद्द
                        गर्नुहोस्</button>
                    <button type="submit" class="btn-save"><i class="fas fa-check"></i> सुरक्षित गर्नुहोस्</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── ADD TOPIC MODAL ──────────────────────────────────────────── -->
    <div class="mo" id="addTopicModal">
        <div class="mc" style="max-width:420px;">
            <div class="mo-title"><i class="fas fa-plus-circle"
                    style="color:<?= $is_gov ? '#10b981' : '#6366f1' ?>;"></i>
                नयाँ विषय थप्नुहोस्</div>
            <form method="POST">
                <input type="hidden" name="action" value="add_topic">
                <div class="fg">
                    <label>विषयको नाम <span style="color:#ef4444;">*</span></label>
                    <div class="topic-input-wrap">
                        <input type="text" name="topic_label" id="topicLabelInput" placeholder="जस्तै: संघीय दरबन्दी"
                            required autofocus>
                    </div>
                    <div class="nepali-tip"><i class="fas fa-info-circle"></i> यूनिकोड नेपाली वा अंग्रेजी दुवैमा लेख्न
                        मिल्छ।</div>
                </div>
                <div
                    style="background:#f8fafc;border-radius:10px;padding:.9rem 1rem;margin-bottom:1rem;border:1px solid #e2e8f0;">
                    <div
                        style="font-size:.8rem;color:#64748b;font-family:'Noto Sans Devanagari','Outfit',sans-serif;line-height:1.6;">
                        <strong>उदाहरणहरू:</strong><br>
                        संघीय दरबन्दी &bull; महानगर भत्ता &bull; विशेष अनुदान &bull; स्थानीय स्तर
                    </div>
                </div>
                <div class="ma">
                    <button type="button" class="btn-cancel"
                        onclick="document.getElementById('addTopicModal').classList.remove('active')">रद्द
                        गर्नुहोस्</button>
                    <button type="submit" class="btn-save"><i class="fas fa-plus"></i> विषय थप्नुहोस्</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── RENAME COLUMNS MODAL ─────────────────────────────────────── -->
    <div class="mo" id="renameColumnsModal">
        <div class="mc" style="max-width:600px;">
            <div class="mo-title"><i class="fas fa-edit" style="color:<?= $is_gov ? '#10b981' : '#6366f1' ?>;"></i>
                शीर्षकहरू (Columns) सम्पादन गर्नुहोस्</div>
            <form method="POST">
                <input type="hidden" name="action" value="save_labels">
                <div style="max-height: 50vh; overflow-y:auto; padding-right:10px; margin-bottom: 5px;">
                    <div class="fg-row">
                        <?php foreach ($default_gov_labels as $k => $def): ?>
                            <div class="fg">
                                <label><?= htmlspecialchars($def) ?> (Default)</label>
                                <input type="text" name="labels[<?= $k ?>]" value="<?= htmlspecialchars($lbl($k)) ?>"
                                    placeholder="<?= htmlspecialchars($def) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="nepali-tip" style="margin-bottom: 15px;"><i class="fas fa-info-circle"></i> खाली छोडेमा
                    'Default' नाम नै प्रयोग हुनेछ।</div>
                <div class="ma">
                    <button type="button" class="btn-cancel"
                        onclick="document.getElementById('renameColumnsModal').classList.remove('active')">रद्द
                        गर्नुहोस्</button>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> सुरक्षित गर्नुहोस्</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── SALARY ENTRY MODAL ───────────────────────────────────────── -->
    <div class="mo" id="salaryModal">
        <div class="mc">
            <div class="mo-title"><i class="fas fa-money-bill-wave"
                    style="color:<?= $is_gov ? '#10b981' : '#6366f1' ?>;"></i> <span id="mName">तलब राख्नुहोस्</span>
            </div>
            <form method="POST" onsubmit="return chkForm()">
                <input type="hidden" name="action" value="save_salary">
                <input type="hidden" name="teacher_id" id="mTid">
                <input type="hidden" name="salary_month" id="mMon">
                <input type="hidden" name="tab_key" id="mTab">

                <?php if ($is_gov): ?>
                    <div style="margin-bottom:10px;text-align:right;">
                        <label
                            style="cursor:pointer;font-weight:700;color:<?= $is_gov ? '#10b981' : '#4f46e5' ?>;font-size:0.9rem;display:inline-flex;align-items:center;gap:6px;background:rgba(<?= $is_gov ? '16,185,129' : '99,102,241' ?>,0.1);padding:6px 12px;border-radius:8px;">
                            <input type="checkbox" id="mAutoCalc" checked onchange="toggleAutoCalc()"
                                style="width:16px;height:16px;accent-color:<?= $is_gov ? '#10b981' : '#4f46e5' ?>;cursor:pointer;">
                            स्वतः हिसाब (Auto Calculate)
                        </label>
                    </div>

                    <div class="fg-row3">
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('grade_count')) ?></label>
                            <input type="number" name="grade_count" id="mGradeCount" placeholder="0" min="0"
                                oninput="calcNet()">
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('salary_scale')) ?> (रु.) <span
                                    style="color:#ef4444;">*</span></label>
                            <input type="number" name="salary_scale" id="mSalaryScale" placeholder="0.00" min="0"
                                step="0.01" oninput="calcNet()" required>
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('grade_amount')) ?> (रु.)</label>
                            <input type="number" name="grade_amount" id="mGradeAmt" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()">
                        </div>
                    </div>

                    <div class="fg-row3">
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('total_salary')) ?></label>
                            <input type="number" name="total_salary" id="mTotalSalary" placeholder="0.00" step="0.01"
                                class="calc-field" readonly oninput="manualNet()">
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('pf_addition')) ?></label>
                            <input type="number" name="pf_addition" id="mPfAdd" placeholder="0.00" step="0.01"
                                class="calc-field" readonly oninput="manualNet()">
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('cit_addition')) ?></label>
                            <input type="number" name="cit_addition" id="mCitAdd" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()">
                        </div>
                    </div>

                    <div class="fg-row3">
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('dearness_allowance')) ?></label>
                            <input type="number" name="dearness_allowance" id="mMahangi" placeholder="0.00" min="0"
                                step="0.01" oninput="calcNet()">
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('principal_allowance')) ?></label>
                            <input type="number" name="principal_allowance" id="mPraA" placeholder="0.00" min="0"
                                step="0.01" oninput="calcNet()">
                        </div>
                        <?php if (!empty($lbl('custom_add_1'))): ?>
                            <div class="fg">
                                <label><?= htmlspecialchars($lbl('custom_add_1')) ?></label>
                                <input type="number" name="custom_add_1" id="mCAdd1" placeholder="0.00" min="0" step="0.01"
                                    oninput="calcNet()">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="fg-row3">
                        <?php if (!empty($lbl('custom_add_2'))): ?>
                            <div class="fg">
                                <label><?= htmlspecialchars($lbl('custom_add_2')) ?></label>
                                <input type="number" name="custom_add_2" id="mCAdd2" placeholder="0.00" min="0" step="0.01"
                                    oninput="calcNet()">
                            </div>
                        <?php endif; ?>
                        <div class="fg" style="flex:1;">
                            <label><?= htmlspecialchars($lbl('gross_salary')) ?> <small>(Gross)</small></label>
                            <input type="number" name="gross_salary" id="mGrossSalary" placeholder="0.00" step="0.01"
                                class="calc-field" readonly oninput="manualNet()">
                        </div>
                    </div>

                    <div class="fg-row3">
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('pf_deduction')) ?></label>
                            <input type="number" name="pf_deduction" id="mPfDed" placeholder="0.00" step="0.01"
                                class="calc-field" readonly oninput="manualNet()">
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('cit_deduction')) ?></label>
                            <input type="number" name="cit_deduction" id="mCitDed" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()">
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('tax_deduction')) ?> <span id="taxHint"
                                    style="font-size:0.75rem; color:#64748b; font-weight:normal;"></span></label>
                            <input type="number" name="tax_deduction" id="mTax" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()">
                        </div>
                    </div>

                    <div class="fg-row3">
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('other_deduction')) ?></label>
                            <input type="number" name="other_deduction" id="mOther" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()">
                        </div>
                        <?php if (!empty($lbl('custom_ded_1'))): ?>
                            <div class="fg">
                                <label><?= htmlspecialchars($lbl('custom_ded_1')) ?></label>
                                <input type="number" name="custom_ded_1" id="mCDed1" placeholder="0.00" min="0" step="0.01"
                                    oninput="calcNet()">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($lbl('custom_ded_2'))): ?>
                            <div class="fg">
                                <label><?= htmlspecialchars($lbl('custom_ded_2')) ?></label>
                                <input type="number" name="custom_ded_2" id="mCDed2" placeholder="0.00" min="0" step="0.01"
                                    oninput="calcNet()">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="fg-row3">
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('total_deduction')) ?></label>
                            <input type="number" name="total_deduction" id="mTotalDed" placeholder="0.00" step="0.01"
                                class="calc-field" readonly oninput="manualNet()">
                        </div>
                        <div class="fg">
                            <label><?= htmlspecialchars($lbl('payable_amount')) ?> <small>(Payable)</small></label>
                            <input type="number" name="payable_amount" id="mPayable" placeholder="0.00" step="0.01"
                                class="calc-field" readonly oninput="manualNet()">
                        </div>
                    </div>

                    <input type="hidden" name="net_salary" id="mNetHidden">
                <?php else: ?>
                    <!-- Private School Salary Form -->
                    <div class="fg-row">
                        <div class="fg">
                            <label>Base Salary (Rs.) <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="base_salary" id="mBase" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()" required>
                        </div>
                        <div class="fg">
                            <label>Allowance (Rs.)</label>
                            <input type="number" name="allowance" id="mAllow" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()">
                        </div>
                    </div>
                    <div class="fg-row">
                        <div class="fg">
                            <label>Other Deduction (Rs.)</label>
                            <input type="number" name="other_deduction_p" id="mOtherP" placeholder="0.00" min="0"
                                step="0.01" oninput="calcNet()">
                        </div>
                        <div class="fg">
                            <label>Bonus (Rs.)</label>
                            <input type="number" name="bonus" id="mBonus" placeholder="0.00" min="0" step="0.01"
                                oninput="calcNet()">
                        </div>
                    </div>
                    <input type="hidden" name="net_salary" id="mNetHidden">
                <?php endif; ?>

                <div class="net-box">
                    <p class="net-lbl"><?= $is_gov ? htmlspecialchars($lbl('net_salary')) : 'Net Salary' ?></p>
                    <div class="net-val" id="mNet">रु. 0.00</div>
                </div>

                <div class="fg">
                    <label><?= $is_gov ? 'टिप्पणी (वैकल्पिक)' : 'Notes (optional)' ?></label>
                    <textarea name="notes" id="mNotes" rows="2"
                        placeholder="<?= $is_gov ? 'कुनै टिप्पणी...' : 'Any remarks...' ?>"></textarea>
                </div>

                <div class="ma">
                    <button type="button" class="btn-cancel"
                        onclick="closeSalary()"><?= $is_gov ? 'रद्द' : 'Cancel' ?></button>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i>
                        <?= $is_gov ? 'सुरक्षित गर्नुहोस्' : 'Save' ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleAutoCalc() {
            const isAuto = document.getElementById('mAutoCalc').checked;
            const fields = document.querySelectorAll('.calc-field');
            fields.forEach(f => {
                f.readOnly = isAuto;
            });
            if (isAuto) calcNet();
        }

        function manualNet() {
            const isAuto = document.getElementById('mAutoCalc');
            if (isAuto && isAuto.checked) return;

            const gross = parseFloat(document.getElementById('mGrossSalary').value) || 0;
            const totalDed = parseFloat(document.getElementById('mTotalDed').value) || 0;
            const tax = parseFloat(document.getElementById('mTax').value) || 0;
            const payable = parseFloat(document.getElementById('mPayable').value) || (gross - totalDed);
            const net = payable - tax;

            document.getElementById('mNet').textContent = 'रु. ' + net.toFixed(2);
            document.getElementById('mNetHidden').value = net;
        }

        // Salary Modal
        function openSalaryModal(tid, name, month, tab, gCount, scale, gAmt, citAdd, mahangi, praA, citDed, tax, notes, totalSal, pfAdd, gross, pfDed, totalDed, payable, other, cA1, cA2, cD1, cD2) {
            document.getElementById('mTid').value = tid;
            document.getElementById('mName').textContent = '<?= $is_gov ? "तलब — " : "Salary — " ?>' + name;
            document.getElementById('mMon').value = month;
            document.getElementById('mTab').value = tab;

            <?php if ($is_gov): ?>
                if (document.getElementById('mGradeCount')) document.getElementById('mGradeCount').value = gCount || '';
                if (document.getElementById('mSalaryScale')) document.getElementById('mSalaryScale').value = scale || '';
                if (document.getElementById('mGradeAmt')) document.getElementById('mGradeAmt').value = gAmt || '';
                if (document.getElementById('mCitAdd')) document.getElementById('mCitAdd').value = citAdd || '';
                if (document.getElementById('mMahangi')) document.getElementById('mMahangi').value = mahangi || '';
                if (document.getElementById('mPraA')) document.getElementById('mPraA').value = praA || '';
                if (document.getElementById('mCAdd1')) document.getElementById('mCAdd1').value = cA1 || '';
                if (document.getElementById('mCAdd2')) document.getElementById('mCAdd2').value = cA2 || '';
                if (document.getElementById('mCitDed')) document.getElementById('mCitDed').value = citDed || '';
                if (document.getElementById('mTax')) document.getElementById('mTax').value = tax || '';
                if (document.getElementById('mOther')) document.getElementById('mOther').value = other || '';
                if (document.getElementById('mCDed1')) document.getElementById('mCDed1').value = cD1 || '';
                if (document.getElementById('mCDed2')) document.getElementById('mCDed2').value = cD2 || '';

                // Set calculated fields
                if (document.getElementById('mTotalSalary')) document.getElementById('mTotalSalary').value = totalSal || '';
                if (document.getElementById('mPfAdd')) document.getElementById('mPfAdd').value = pfAdd || '';
                if (document.getElementById('mGrossSalary')) document.getElementById('mGrossSalary').value = gross || '';
                if (document.getElementById('mPfDed')) document.getElementById('mPfDed').value = pfDed || '';
                if (document.getElementById('mTotalDed')) document.getElementById('mTotalDed').value = totalDed || '';
                if (document.getElementById('mPayable')) document.getElementById('mPayable').value = payable || '';

                // Let's reset the auto calculation to YES on open unless logic indicates otherwise
                // For simplicity, always enforce auto calc unless the user explicitly unticks it during editing
                document.getElementById('mAutoCalc').checked = true;
                toggleAutoCalc();
            <?php else: ?>
                if (document.getElementById('mBase')) document.getElementById('mBase').value = scale || '';
                if (document.getElementById('mAllow')) document.getElementById('mAllow').value = gAmt || '';
                if (document.getElementById('mOther')) document.getElementById('mOther').value = citDed || '';
                if (document.getElementById('mBonus')) document.getElementById('mBonus').value = tax || '';
            <?php endif; ?>

            document.getElementById('mNotes').value = notes || '';
            calcNet();
            document.getElementById('salaryModal').classList.add('active');
        }

        function closeSalary() { document.getElementById('salaryModal').classList.remove('active'); }

        function calcNet() {
            <?php if ($is_gov): ?>
                const isAuto = document.getElementById('mAutoCalc').checked;

                const scale = parseFloat(document.getElementById('mSalaryScale').value) || 0;
                const gAmt = parseFloat(document.getElementById('mGradeAmt').value) || 0;
                const citAdd = parseFloat(document.getElementById('mCitAdd').value) || 0;
                const mahangi = parseFloat(document.getElementById('mMahangi').value) || 0;
                const praA = parseFloat(document.getElementById('mPraA').value) || 0;
                const cA1 = document.getElementById('mCAdd1') ? (parseFloat(document.getElementById('mCAdd1').value) || 0) : 0;
                const cA2 = document.getElementById('mCAdd2') ? (parseFloat(document.getElementById('mCAdd2').value) || 0) : 0;
                const citDed = parseFloat(document.getElementById('mCitDed').value) || 0;
                const tax = parseFloat(document.getElementById('mTax').value) || 0;
                const other = parseFloat(document.getElementById('mOther').value) || 0;
                const cD1 = document.getElementById('mCDed1') ? (parseFloat(document.getElementById('mCDed1').value) || 0) : 0;
                const cD2 = document.getElementById('mCDed2') ? (parseFloat(document.getElementById('mCDed2').value) || 0) : 0;

                let totalSalary = parseFloat(document.getElementById('mTotalSalary').value) || 0;
                let pfAdd = parseFloat(document.getElementById('mPfAdd').value) || 0;
                let gross = parseFloat(document.getElementById('mGrossSalary').value) || 0;
                let pfDed = parseFloat(document.getElementById('mPfDed').value) || 0;
                let totalDed = parseFloat(document.getElementById('mTotalDed').value) || 0;
                let payable = parseFloat(document.getElementById('mPayable').value) || 0;

                if (isAuto) {
                    totalSalary = +(scale + gAmt).toFixed(2);
                    pfAdd = totalSalary > 0 ? +(totalSalary * 0.10).toFixed(2) : 0;
                    pfDed = totalSalary > 0 ? +(totalSalary * 0.20).toFixed(2) : 0;

                    gross = +(totalSalary + pfAdd + citAdd + mahangi + praA + cA1 + cA2).toFixed(2);
                    totalDed = +(pfDed + citDed + other + cD1 + cD2).toFixed(2);
                    payable = +(gross - totalDed).toFixed(2);

                    document.getElementById('mTotalSalary').value = totalSalary;
                    document.getElementById('mPfAdd').value = pfAdd;
                    document.getElementById('mGrossSalary').value = gross;
                    document.getElementById('mPfDed').value = pfDed;
                    document.getElementById('mTotalDed').value = totalDed;
                    document.getElementById('mPayable').value = payable;
                }

                const net = +(payable - tax).toFixed(2);

                document.getElementById('mNet').textContent = 'रु. ' + net.toFixed(2);
                document.getElementById('mNetHidden').value = net;

                if (document.getElementById('taxHint')) document.getElementById('taxHint').textContent = '(सुझाव: ' + (payable * 0.01).toFixed(2) + ')';

            <?php else: ?>
                const b = parseFloat(document.getElementById('mBase').value) || 0;
                const a = parseFloat(document.getElementById('mAllow').value) || 0;
                const o = parseFloat(document.getElementById('mOther').value) || 0;
                const bon = parseFloat(document.getElementById('mBonus').value) || 0;
                const net = +(b + a + bon - o).toFixed(2);
                document.getElementById('mNet').textContent = 'Rs. ' + net.toFixed(2);
                if (document.getElementById('mNetHidden')) document.getElementById('mNetHidden').value = net;
            <?php endif; ?>
        }

        function chkForm() {
            <?php if ($is_gov): ?>
                const b = parseFloat(document.getElementById('mSalaryScale').value);
                if (!b || b <= 0) { alert('कृपया तलब स्केल राख्नुहोस्।'); return false; }
            <?php else: ?>
                const b = parseFloat(document.getElementById('mBase').value);
                if (!b || b <= 0) { alert('Please enter a base salary.'); return false; }
            <?php endif; ?>
            return true;
        }
        // Close on overlay click
        ['salaryModal', 'addTopicModal', 'assignTeachersModal', 'renameColumnsModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('click', e => { if (e.target === el) el.classList.remove('active'); });
            }
        });

        function exportToExcel() {
            var table = document.getElementById("salaryTable");
            if (!table) return alert("No data to export!");

            // Clone to avoid modifying the UI
            var tc = table.cloneNode(true);

            // For Excel, we want to REMOVE "Status" and "Action", but KEEP "Signature".
            // Currently Status is 2nd from last, Action is 1st from last (in the original DOM, before sig-col was added).
            // Wait, now the columns are: ..., Net Salary, Status, Action, Signature.
            // So indices from end: 0=Signature, 1=Action, 2=Status.
            // We want to remove index 1 and 2 from the end.

            var rows = tc.querySelectorAll("tr");
            for (var i = 0; i < rows.length; i++) {
                if (rows[i].cells.length >= 3) {
                    rows[i].deleteCell(rows[i].cells.length - 2); // Delete Action
                    rows[i].deleteCell(rows[i].cells.length - 2); // Delete Status (which shifts to n-2)
                }
            }

            // We also need to add the Header text above the table in Excel.
            // Excel HTML export handles merged cells if we prepend a row!

            // Let's create a temporary table that holds our Header Div + Table Clone.
            // Since we're using table_to_sheet, it only parses one table natively.
            // But we can just use the DOM elements into a worksheet.
            // A simpler way: just append the school data as a row at the top of 'tc':
            var headerRow = tc.insertRow(0);
            var headerCell = headerRow.insertCell(0);
            headerCell.colSpan = tc.rows[1].cells.length; // span all columns
            headerCell.innerHTML = "<b>" + document.querySelector('.print-header h2').innerText + " (" + document.querySelector('.print-header p').innerText + ")" + "</b> - " + document.querySelector('.print-header .desc').innerText;
            headerCell.style.textAlign = "center";

            var wb = XLSX.utils.book_new();
            var ws = XLSX.utils.table_to_sheet(tc, { raw: true });
            XLSX.utils.book_append_sheet(wb, ws, "Salary Ledger");
            XLSX.writeFile(wb, "Salary_Ledger_" + new Date().toISOString().slice(0, 10) + ".xlsx");
        }
    </script>
</body>

</html>