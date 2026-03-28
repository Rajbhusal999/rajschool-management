<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';
$exam_type = isset($_GET['exam']) ? $_GET['exam'] : '';
$group = isset($_GET['group']) ? $_GET['group'] : '4_8';

$exam_names = [
    'first_terminal' => 'First Terminal Exam',
    'second_terminal' => 'Second Terminal Exam',
    'third_terminal' => 'Third Terminal Exam',
    'final' => 'Final Exam',
    'monthly' => 'Monthly Exam'
];

$group_names = [
    'pg_kg' => 'PG / Nursery / LKG / UKG / KG',
    '1_3' => 'Class 1 - 3',
    '4_8' => 'Class 4 - 8',
    '9_10' => 'Class 9 - 10',
    '11_12' => 'Class 11 - 12'
];

$exam_name = isset($exam_names[$exam_type]) ? $exam_names[$exam_type] : 'Exam';
$group_name = isset($group_names[$group]) ? $group_names[$group] : 'Class 4 - 8';

// Determine file suffixes based on group
$suffix = ($group === '4_8') ? '' : '_' . $group;
// For now, if files don't exist, they will 404, but this follows the user's architectural request
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $exam_name; ?> - Smart विद्यालय
    </title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .header-left p {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .group-badge {
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.5rem 1rem;
            border-radius: 99px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .option-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid #e2e8f0;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .option-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--color);
        }

        .option-card:hover {
            transform: translateY(-5px);
            border-color: var(--color);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            background: var(--bg);
            color: var(--color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .info h3 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .info p {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .features li {
            font-size: 0.8rem;
            color: #475569;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .features li i {
            color: #10b981;
            font-size: 0.7rem;
        }

        .btn-go {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            color: var(--color);
            font-weight: 800;
            font-size: 0.85rem;
            transition: 0.2s;
        }

        .option-card:hover .btn-go {
            background: var(--color);
            color: white;
        }

        @media (max-width: 640px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h1><?php echo $exam_name; ?> Management</h1>
                <p>Manage scores, reports, and class statistics</p>
            </div>
            <div class="group-badge">
                <i class="fas fa-layer-group"></i> <?php echo $group_name; ?>
            </div>
        </div>

        <div class="options-grid">
            <!-- Mark Entry -->
            <div class="option-card" style="--color: #3b82f6; --bg: #eff6ff;"
                onclick="window.location.href='mark_entry<?php echo $suffix; ?>.php?exam=<?php echo $exam_type; ?>&group=<?php echo $group; ?>'">
                <div class="icon-box"><i class="fas fa-edit"></i></div>
                <div class="info">
                    <h3>Mark Entry</h3>
                    <p>Enter and manage student marks for this exam terminal.</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Subject-wise entry</li>
                        <li><i class="fas fa-check"></i> Real-time calculation</li>
                        <li><i class="fas fa-check"></i> Support for all formats</li>
                    </ul>
                </div>
                <div class="btn-go">
                    <span>Enter Sub-System</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>

            <!-- Grade Sheet -->
            <div class="option-card" style="--color: #10b981; --bg: #ecfdf5;"
                onclick="window.location.href='grade_sheet<?php echo $suffix; ?>.php?exam=<?php echo $exam_type; ?>&group=<?php echo $group; ?>'">
                <div class="icon-box"><i class="fas fa-file-invoice"></i></div>
                <div class="info">
                    <h3>Grade Sheet</h3>
                    <p>Generate and view detailed grade sheets for students.</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Individual reports</li>
                        <li><i class="fas fa-check"></i> Performance analytics</li>
                        <li><i class="fas fa-check"></i> Print ready layouts</li>
                    </ul>
                </div>
                <div class="btn-go">
                    <span>Generate Reports</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>

            <!-- Mark Ledger -->
            <div class="option-card" style="--color: #8b5cf6; --bg: #f5f3ff;"
                onclick="window.location.href='mark_ledger<?php echo $suffix; ?>.php?exam=<?php echo $exam_type; ?>&group=<?php echo $group; ?>'">
                <div class="icon-box"><i class="fas fa-book-open"></i></div>
                <div class="info">
                    <h3>Mark Ledger</h3>
                    <p>Comprehensive tabular view of all student marks.</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Export to Excel</li>
                        <li><i class="fas fa-check"></i> Bulk mark review</li>
                        <li><i class="fas fa-check"></i> Attendance summary</li>
                    </ul>
                </div>
                <div class="btn-go">
                    <span>Open Ledger</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>

        <div style="margin-top: 3rem; text-align: center;">
            <a href="exam_class_selector.php?exam=<?php echo $exam_type; ?>"
                style="color: #64748b; text-decoration: none; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-chevron-left"></i> Change Class Group
            </a>
        </div>
    </div>

</body>

</html>