<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';
require_once 'includes/nepali_date_helper.php';

$current_ad_date = date('Y-m-d');
$current_bs_date = NepaliDateHelper::convertToNepali($current_ad_date);

// Fetch quick stats
$school_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE school_id = ?");
$stmt->execute([$school_id]);
$student_count = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM teachers WHERE school_id = ?");
$stmt->execute([$school_id]);
$teacher_count = $stmt->fetchColumn();

// Fetch subscription expiry information
$stmt = $conn->prepare("SELECT subscription_expiry, subscription_plan FROM schools WHERE id = ?");
$stmt->execute([$school_id]);
$subscription_info = $stmt->fetch(PDO::FETCH_ASSOC);
$subscription_expiry = $subscription_info['subscription_expiry'];
$subscription_plan = $subscription_info['subscription_plan'];

// Calculate days left
$days_left = 0;
$show_expiry_alert = false;
if ($subscription_expiry) {
    $days_left = ceil((strtotime($subscription_expiry) - time()) / (60 * 60 * 24));
    if ($days_left > 0 && $days_left <= 7) {
        $show_expiry_alert = true;
    }
}

// Fetch Class Distribution
$class_labels = [];
$class_counts = [];
try {
    $stmt = $conn->query("SELECT class, COUNT(*) as c FROM students WHERE school_id = $school_id GROUP BY class ORDER BY length(class), class");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $class_labels[] = "Class " . $row['class'];
        $class_counts[] = $row['c'];
    }
} catch (Exception $e) {
}

// Fetch Gender Distribution
$gender_labels = [];
$gender_counts = [];
try {
    $stmt = $conn->query("SELECT gender, COUNT(*) as c FROM students WHERE school_id = $school_id GROUP BY gender");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gender_labels[] = ucfirst($row['gender']);
        $gender_counts[] = $row['c'];
    }
} catch (Exception $e) {
}

// Fetch Activity Trends (Last 15 Days)
$activity_labels = [];
$student_growth = [];
$exam_activity = [];
$financial_activity = [];

for ($i = 14; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $bs_date = NepaliDateHelper::convertToNepali($date);
    $activity_labels[] = $bs_date;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE school_id = ? AND DATE(created_at) = ?");
    $stmt->execute([$school_id, $date]);
    $student_growth[] = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM exam_marks WHERE school_id = ? AND DATE(created_at) = ?");
    $stmt->execute([$school_id, $date]);
    $exam_activity[] = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT 
        (SELECT COUNT(*) FROM student_receipts WHERE school_id = ? AND DATE(created_at) = ?) + 
        (SELECT COUNT(*) FROM donor_receipts WHERE school_id = ? AND DATE(created_at) = ?) as total");
    $stmt->execute([$school_id, $date, $school_id, $date]);
    $financial_activity[] = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
            --secondary-gradient: linear-gradient(135deg, #0f172a, #1e293b);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-main);
            margin: 0;
            overflow-x: hidden;
        }

        .dashboard-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2.5rem;
        }

        .header-welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .welcome-text p {
            color: #1e293b !important;
            font-size: 1.15rem;
            font-weight: 800;
        }

        html[data-theme="dark"] .welcome-text p {
            color: var(--text-muted);
        }

        .live-timer-panel {
            background: var(--panel-bg);
            padding: 1.25rem 2.5rem;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 2rem;
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }

        .clock-display {
            font-size: 2.25rem;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: -1px;
            min-width: 180px;
            text-shadow: 0 2px 10px rgba(79, 70, 229, 0.1);
        }

        .date-display {
            text-align: right;
            border-left: 2px solid var(--border-color);
            padding-left: 2rem;
        }

        .date-display div:first-child {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
            /* Darker for light mode */
            margin-bottom: 2px;
        }

        .date-display div:last-child {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            /* Darker for light mode */
        }

        html[data-theme="dark"] .date-display div:first-child {
            color: var(--text-main);
        }

        html[data-theme="dark"] .date-display div:last-child {
            color: var(--text-muted);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card-premium {
            background: var(--panel-bg);
            padding: 2rem;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 1.75rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.03);
        }

        [data-theme="light"] .stat-card-premium {
            border-color: #cbd5e1;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
        }

        .stat-card-premium:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 75px;
            height: 75px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .stat-info h3 {
            color: #475569 !important;
            font-weight: 800 !important;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            opacity: 1 !important;
        }

        .stat-info .num {
            font-size: 2.75rem;
            font-weight: 900;
            color: #0f172a !important;
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }

        html[data-theme="dark"] .stat-info h3 {
            color: var(--text-muted) !important;
            opacity: 0.7 !important;
        }

        html[data-theme="dark"] .stat-info .num {
            color: var(--text-main) !important;
        }

        .stat-meta {
            font-size: 0.85rem;
            font-weight: 800;
            color: #4f46e5;
            /* Distinct color for meta */
            opacity: 1;
        }

        html[data-theme="dark"] .stat-meta {
            color: var(--text-muted);
            opacity: 0.8;
        }

        /* Charts Layouts */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        .glass-panel-v2 {
            background: var(--panel-bg);
            border-radius: 30px;
            padding: 2.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        [data-theme="light"] .glass-panel-v2 {
            border-color: #cbd5e1;
            /* Stronger border for light mode */
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            /* More attractive shadow */
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.25rem;
            border-bottom: 1.5px solid var(--border-color);
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1e293b;
            /* Dark slate for high contrast */
        }

        html[data-theme="dark"] .section-header h2 {
            color: var(--text-main);
        }

        /* Extract Tool Premium */
        .extract-tool-box {
            background: var(--panel-bg);
            color: #78350f;
            padding: 3.5rem 2.5rem;
            border-radius: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }

        [data-theme="light"] .extract-tool-box {
            border-color: #cbd5e1;
            background: linear-gradient(to bottom right, #ffffff, #f8fafc);
        }

        .extract-tool-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            z-index: 1;
        }

        .extract-tool-box h3 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            position: relative;
            z-index: 2;
        }

        .extract-tool-box p {
            color: var(--text-muted);
            font-size: 1.05rem;
            margin-bottom: 2.5rem;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        .btn-premium-action {
            background: var(--panel-bg);
            color: var(--text-main);
            padding: 16px 32px;
            border-radius: 18px;
            font-weight: 800;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--border-color);
            position: relative;
            z-index: 2;
            width: 100%;
        }

        .btn-premium-action:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(255, 255, 255, 0.2);
        }

        .extract-tool-box i.bg-icon {
            position: absolute;
            bottom: -30px;
            right: -30px;
            font-size: 15rem;
            opacity: 0.05;
            transform: rotate(15deg);
            z-index: 0;
        }

        /* Activity Transitions */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.25rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            margin-bottom: 0.75rem;
            border: 1px solid transparent;
        }

        .activity-title {
            font-weight: 700;
            color: var(--text-main);
            font-size: 1rem;
        }

        .activity-time {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .activity-amount {
            font-weight: 800;
            color: var(--primary);
            margin-left: auto;
        }

        .activity-item:hover {
            background: var(--body-bg);
            border-color: var(--border-color);
            transform: translateX(10px);
        }

        .insight-badge {
            font-size: 0.85rem;
            padding: 6px 14px;
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
            /* Darker green */
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 20px;
            font-weight: 800;
        }

        html[data-theme="dark"] .insight-badge {
            color: #10b981;
            background: rgba(16, 185, 129, 0.1);
        }

        @media (max-width: 1280px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1.5rem;
            }

            .welcome-text h1 {
                font-size: 2rem;
            }

            .live-timer-panel {
                width: 100%;
                flex-direction: column;
                text-align: center;
                padding: 2rem;
                gap: 1.5rem;
            }

            .date-display {
                border-left: none;
                padding-left: 0;
                border-top: 1px solid var(--border-color);
                padding-top: 1.5rem;
                width: 100%;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .glass-panel-v2 {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <main class="dashboard-container">

        <!-- Unauthorized Access Alert -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 'unauthorized_feature'): ?>
            <div class="alert-premium"
                style="background: rgba(239, 68, 68, 0.1); border-color: #ef4444; margin-bottom: 2rem; border-style: solid; border-width: 1px; padding: 1.5rem; border-radius: 20px;">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div
                        style="background: #fee2e2; color: #b91c1c; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 1.1rem; color: #ef4444; font-weight: 800;">Access Restricted</h4>
                        <p style="margin: 4px 0 0 0; color: var(--text-main); opacity: 0.8; font-weight: 500;">The feature
                            you
                            attempted to access is not included in your current subscription plan. Please upgrade to unlock
                            more modules.</p>
                    </div>
                    <a href="school_subscription.php" class="btn btn-primary-gradient"
                        style="margin-left: auto; text-decoration: none; padding: 12px 20px;">Upgrade Plan</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alerts Area -->
        <?php if ($show_expiry_alert): ?>
            <div class="alert-premium"
                style="background: rgba(245, 158, 11, 0.1); border-color: <?php echo $days_left <= 2 ? '#ef4444' : '#f59e0b'; ?>; <?php echo $days_left <= 2 ? 'animation: pulse 2s infinite;' : ''; ?>">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div
                        style="background: rgba(239, 68, 68, 0.2); color: #ef4444; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fas <?php echo $days_left <= 2 ? 'fa-exclamation-triangle' : 'fa-clock'; ?>"></i>
                    </div>
                    <div>
                        <h4
                            style="margin:0; font-weight:700; color: <?php echo $days_left <= 2 ? '#ef4444' : '#f59e0b'; ?>;">
                            Subscription <?php echo $days_left <= 2 ? 'Critical' : 'Warning'; ?></h4>
                        <p style="margin:0; font-size:0.95rem; color: var(--text-main);">
                            Expires in <b><?php echo $days_left; ?> days</b>. Renew now to maintain service.</p>
                    </div>
                </div>
                <a href="subscription_plans.php" class="btn btn-primary-gradient" style="padding: 10px 24px;">Renew
                    Account</a>
            </div>
        <?php endif; ?>

        <!-- Welcome Header -->
        <header class="header-welcome">
            <div class="welcome-text">
                <h1>School Overview</h1>
                <p>Welcome back! Here's what's happening today.</p>
            </div>
            <div class="live-timer-panel">
                <div class="clock-display" id="liveClock">--:--:--</div>
                <div class="date-display">
                    <div><?php echo $current_bs_date; ?> (B.S.)</div>
                    <div><?php echo date('l, d M Y'); ?> (A.D.)</div>
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card-premium">
                <div class="stat-icon" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Students</h3>
                    <div class="num"><?php echo number_format($student_count); ?></div>
                    <div class="stat-meta" style="color: #10b981;">Active Enrollment</div>
                </div>
            </div>
            <div class="stat-card-premium">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Teachers</h3>
                    <div class="num"><?php echo number_format($teacher_count); ?></div>
                    <div class="stat-meta" style="color: #4f46e5;">Academic Staff</div>
                </div>
            </div>
            <div class="stat-card-premium">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3>Plan Level</h3>
                    <div class="num" style="font-size:1.8rem;"><?php echo $subscription_plan; ?></div>
                    <div class="stat-meta" style="color: #f59e0b;">Premium Status</div>
                </div>
            </div>
        </div>

        <?php if (hasFeature('teacher_salary')): ?>
            <!-- Teacher Salary Panel (5-Year Plan Exclusive) -->
            <div class="glass-panel-v2"
                style="margin-bottom: 3rem; border-left: 6px solid #6366f1; padding: 2.5rem; background: linear-gradient(135deg, rgba(99,102,241,0.05), rgba(79,70,229,0.02));">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <div
                            style="width: 65px; height: 65px; border-radius: 20px; background: linear-gradient(135deg, #6366f1, #4f46e5); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: white; box-shadow: 0 10px 25px rgba(99,102,241,0.3); flex-shrink: 0;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">Teacher Salary
                                </h2>
                                <span
                                    style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; font-size: 0.7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; letter-spacing: 0.5px;">5-YEAR
                                    EXCLUSIVE</span>
                            </div>
                            <p style="color: #475569; font-size: 1rem; font-weight: 500; margin: 0;">Manage, track, and
                                process monthly teacher salary records.</p>
                        </div>
                    </div>
                    <a href="teacher_salary_select.php"
                        style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; text-decoration: none; padding: 1rem 2rem; border-radius: 16px; font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(99,102,241,0.35); transition: all 0.3s ease; white-space: nowrap;">
                        <i class="fas fa-wallet"></i> Manage Salaries
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Dashboard Content -->
        <div class="main-grid">
            <!-- Trend Chart -->
            <div class="glass-panel-v2">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line" style="color:var(--success);"></i> Activity Trend</h2>
                    <span class="insight-badge">Live Insight</span>
                </div>
                <div style="height: 400px;">
                    <canvas id="activityTrendChart"></canvas>
                </div>
            </div>

            <!-- Extract Tool -->
            <?php if (hasFeature('id_cards') || hasFeature('exams')): ?>
                <div class="extract-tool-box">
                    <i class="fas fa-file-export bg-icon"></i>
                    <h3>Data Extraction Tools</h3>
                    <p>Generate bulk admit cards, student IDs, and custom reports in just a few clicks.</p>
                    <a href="extract_details.php" class="btn-premium-action">
                        <i class="fas fa-rocket"></i> Launch Extraction Bridge
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Second Row Charts & Activity -->
        <div class="main-grid">
            <!-- Recent Activity -->
            <div class="glass-panel-v2" style="padding: 1.5rem;">
                <div class="section-header">
                    <h2><i class="fas fa-history" style="color:var(--primary);"></i> Recent Operations</h2>
                </div>
                <div class="activity-list">
                    <?php
                    $activities = [];
                    try {
                        if (hasFeature('billing')) {
                            $stmt = $conn->query("SELECT 'fee' as type, student_name as title, total_amount as amount, created_at as date FROM student_receipts WHERE school_id = $school_id ORDER BY id DESC LIMIT 5");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $activities[] = $row;
                            }
                        }
                    } catch (Exception $e) {
                    }

                    try {
                        if (hasFeature('students')) {
                            $stmt = $conn->query("SELECT 'student' as type, full_name as title, 0 as amount, created_at as date FROM students WHERE school_id = $school_id ORDER BY id DESC LIMIT 5");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $activities[] = $row;
                            }
                        }
                    } catch (Exception $e) {
                    }

                    usort($activities, function ($a, $b) {
                        return strtotime($b['date']) - strtotime($a['date']);
                    });
                    $recent_activities = array_slice($activities, 0, 6);

                    foreach ($recent_activities as $act):
                        $timeAgo = (strtotime('now') - strtotime($act['date'])) / 60;
                        if ($timeAgo < 60)
                            $t = round($timeAgo) . "m ago";
                        elseif ($timeAgo < 1440)
                            $t = round($timeAgo / 60) . "h ago";
                        else
                            $t = round($timeAgo / 1440) . "d ago";
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon"
                                style="background:<?php echo $act['type'] == 'fee' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(79, 70, 229, 0.1)'; ?>; color:<?php echo $act['type'] == 'fee' ? '#10b981' : '#4f46e5'; ?>; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                <i
                                    class="fas <?php echo $act['type'] == 'fee' ? 'fa-file-invoice-dollar' : 'fa-user-plus'; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?php echo htmlspecialchars($act['title']); ?></div>
                                <div class="activity-time"><?php echo $t; ?> • <?php echo ucfirst($act['type']); ?></div>
                            </div>
                            <?php if ($act['amount'] > 0 && hasFeature('billing')): ?>
                                <div class="activity-amount">Rs. <?php echo number_format($act['amount']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Composition Charts -->
            <div class="glass-panel-v2">
                <div class="section-header">
                    <h2><i class="fas fa-chart-pie" style="color:var(--info);"></i> Composition</h2>
                </div>
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <div style="text-align: center;">
                        <h4 style="font-size:0.9rem; color: #475569; font-weight: 800; margin-bottom:1rem;">Ratio:
                            Students vs Staff</h4>
                        <canvas id="ratioChart" height="200"></canvas>
                    </div>
                    <div style="text-align: center;">
                        <h4 style="font-size:0.9rem; color: #475569; font-weight: 800; margin-bottom:1rem;">Gender
                            Distribution</h4>
                        <canvas id="genderChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- External Tools / Government School Extension -->
        <div class="glass-panel-v2" style="margin-top: 2rem; border-left: 6px solid #10b981; padding: 2.5rem;">
            <div
                style="display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap;">
                <div>
                    <h2
                        style="font-size: 1.5rem; font-weight: 800; color: #78350f; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-landmark" style="color: #10b981;"></i> <?php echo t('accounting_ext'); ?>
                    </h2>
                    <p style="color: #475569; font-size: 1.1rem; font-weight: 700; margin: 0;">
                        <?php echo t('gov_school_note'); ?>
                    </p>
                </div>
                <a href="http://localhost/income%20and%20expenses/index.php" target="_blank" class="btn"
                    style="background: linear-gradient(135deg, #10b981, #059669); color: #78350f; text-decoration: none; padding: 1.25rem 2.5rem; border-radius: 18px; font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; gap: 12px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2); transition: 0.3s; white-space: nowrap;">
                    <?php echo t('open_system'); ?> <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
        </div>
    </main>

    <script>
        // Live Clock
        function startClock() {
            const el = document.getElementById('liveClock');
            function update() {
                const now = new Date();
                let h = now.getHours();
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                el.innerText = `${String(h).padStart(2, '0')}:${m}:${s} ${ampm}`;
            }
            update(); setInterval(update, 1000);
        }
        document.addEventListener('DOMContentLoaded', startClock);

        // Charts
        const ctxTrend = document.getElementById('activityTrendChart').getContext('2d');
        const gradP = ctxTrend.createLinearGradient(0, 0, 0, 400);
        gradP.addColorStop(0, 'rgba(99, 102, 241, 0.1)'); gradP.addColorStop(1, 'rgba(99, 102, 241, 0)');

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($activity_labels); ?>,
                datasets: [
                    {
                        label: 'Admissions',
                        data: <?php echo json_encode($student_growth); ?>,
                        borderColor: '#10b981',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Exam Activity',
                        data: <?php echo json_encode($exam_activity); ?>,
                        borderColor: '#6366f1',
                        backgroundColor: gradP,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }
                    <?php if (hasFeature('billing')): ?>,
                        {
                            label: 'Billing',
                            data: <?php echo json_encode($financial_activity); ?>,
                            borderColor: '#9333ea',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: false
                        }
                    <?php endif; ?>
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#94a3b8' : '#1e293b',
                            font: { family: 'Outfit', size: 12, weight: '700' }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: document.documentElement.getAttribute('data-theme') === 'dark' ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.08)',
                            drawBorder: false
                        },
                        ticks: {
                            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#94a3b8' : '#334155',
                            font: { family: 'Outfit', weight: '700' }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#94a3b8' : '#334155',
                            font: { family: 'Outfit', weight: '700' }
                        }
                    }
                }
            }
        });

        // Ratio Chart
        new Chart(document.getElementById('ratioChart'), {
            type: 'doughnut',
            data: {
                labels: ['Students', 'Teachers'],
                datasets: [{
                    data: [<?php echo $student_count; ?>, <?php echo $teacher_count; ?>],
                    backgroundColor: ['#6366f1', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: { cutout: '75%', plugins: { legend: { display: false } } }
        });

        // Gender Chart
        new Chart(document.getElementById('genderChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($gender_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($gender_counts); ?>,
                    backgroundColor: ['#3b82f6', '#ec4899', '#a855f7'],
                    borderWidth: 0
                }]
            },
            options: { plugins: { legend: { position: 'right', labels: { usePointStyle: true, font: { family: 'Outfit' } } } } }
        });
    </script>
</body>

</html>