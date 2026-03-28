<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$school_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM schools WHERE id = ?");
$stmt->execute([$school_id]);
$school = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subscription - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            background: #f9fafb;
            padding: 2rem;
            min-height: calc(100vh - 65px);
        }

        .sub-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 2rem auto;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-expired {
            background: #fee2e2;
            color: #b91c1c;
        }

        .expiry-date {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            margin: 1rem 0;
        }

        .plan-name {
            color: #6b7280;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: #1f2937;">
                Subscription Details
            </h1>
            <p style="color: #6b7280;">Manage your school's plan and billing</p>
        </div>

        <div class="sub-card">
            <div
                class="status-badge <?php echo ($school['subscription_status'] == 'active') ? 'status-active' : 'status-expired'; ?>">
                <?php echo ucfirst($school['subscription_status']); ?>
            </div>

            <div class="plan-name">
                Current Plan: <strong>
                    <?php echo ucfirst(str_replace('_', ' ', $school['subscription_plan'])); ?>
                </strong>
            </div>

            <div class="expiry-date">
                <?php
                $days_left = ceil((strtotime($school['subscription_expiry']) - time()) / (60 * 60 * 24));
                echo $days_left . " Days Left";
                ?>
            </div>
            <p style="color: #6b7280; margin-bottom: 2rem;">
                Expires on:
                <?php echo date('F j, Y', strtotime($school['subscription_expiry'])); ?>
            </p>

            <a href="subscription_plans.php" class="btn btn-primary" style="width: 100%; padding: 12px;">
                <i class="fas fa-arrow-up" style="margin-right: 8px;"></i> Upgrade / Renew Plan
            </a>
        </div>
    </div>

</body>

</html>