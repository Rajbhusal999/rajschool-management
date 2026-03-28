<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$exam_type = isset($_GET['exam']) ? $_GET['exam'] : '';
$exam_names = [
    'first_terminal' => 'First Terminal Exam',
    'second_terminal' => 'Second Terminal Exam',
    'third_terminal' => 'Third Terminal Exam',
    'final' => 'Final Exam',
    'monthly' => 'Monthly Exam'
];

$exam_name = isset($exam_names[$exam_type]) ? $exam_names[$exam_type] : 'Exam';

$class_groups = [
    ['id' => 'pg_kg', 'name' => 'Nursery / PG / LKG / UKG / KG', 'icon' => 'fas fa-child', 'color1' => '#ec4899', 'color2' => '#f472b6'],
    ['id' => '1_3', 'name' => 'Class 1 - 3', 'icon' => 'fas fa-book-reader', 'color1' => '#10b981', 'color2' => '#34d399'],
    ['id' => '4_8', 'name' => 'Class 4 - 8', 'icon' => 'fas fa-user-graduate', 'color1' => '#3b82f6', 'color2' => '#60a5fa'],
    ['id' => '9_10', 'name' => 'Class 9 - 10', 'icon' => 'fas fa-microscope', 'color1' => '#8b5cf6', 'color2' => '#a78bfa'],
    ['id' => '11_12', 'name' => 'Class 11 - 12', 'icon' => 'fas fa-university', 'color1' => '#f59e0b', 'color2' => '#fbbf24']
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class Group -
        <?php echo $exam_name; ?>
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

        .header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .header p {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .group-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .group-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .group-card:hover {
            transform: translateY(-8px);
            border-color: var(--c1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .group-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--c1), var(--c2));
            color: white;
            border-radius: 20px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            transition: 0.3s;
        }

        .group-card:hover .group-icon {
            transform: scale(1.1);
        }

        .group-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .group-desc {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.5;
        }

        .btn-select {
            margin-top: 1.5rem;
            padding: 0.625rem;
            background: #f8fafc;
            color: #475569;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.2s;
        }

        .group-card:hover .btn-select {
            background: var(--c1);
            color: white;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="main-content">
        <div class="header">
            <div>
                <h1><?php echo $exam_name; ?>: Select Level</h1>
                <p>Choose the class group to manage examination records</p>
            </div>
            <a href="exams.php"
                style="color: #64748b; text-decoration: none; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-chevron-left"></i> Back to Exams
            </a>
        </div>

        <div class="group-grid">
            <?php foreach ($class_groups as $group): ?>
                <div class="group-card"
                    style="--c1: <?php echo $group['color1']; ?>; --c2: <?php echo $group['color2']; ?>;"
                    onclick="window.location.href='exam_marks.php?exam=<?php echo $exam_type; ?>&group=<?php echo $group['id']; ?>'">
                    <div class="group-icon"><i class="<?php echo $group['icon']; ?>"></i></div>
                    <div class="group-name"><?php echo $group['name']; ?></div>
                    <p class="group-desc">Manage marks, ledgers and reports for <?php echo $group['name']; ?> students.</p>
                    <div class="btn-select">Select Category</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>