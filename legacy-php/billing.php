<?php
require 'includes/auth_school.php';
restrictFeature('billing');
require 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Selection - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            background: #f9fafb;
            padding: 2rem;
            min-height: calc(100vh - 65px);
        }

        .options-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .option-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .option-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--card-color-1), var(--card-color-2));
        }

        .option-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: var(--card-color-1);
        }

        .student-option {
            --card-color-1: #3b82f6;
            --card-color-2: #60a5fa;
        }

        .other-option {
            --card-color-1: #10b981;
            --card-color-2: #34d399;
        }

        .subscription-option {
            --card-color-1: #8b5cf6;
            --card-color-2: #a78bfa;
        }

        .option-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--card-color-1), var(--card-color-2));
            color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .option-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.75rem;
        }

        .option-description {
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .option-btn {
            padding: 0.8rem 2rem;
            background: white;
            color: var(--card-color-1);
            border: 2px solid var(--card-color-1);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .option-card:hover .option-btn {
            background: var(--card-color-1);
            color: white;
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div style="text-align: center; margin-bottom: 4rem;">
            <div class="hero-badge" style="margin: 0 auto 1.5rem;">
                <span class="badge-dot"></span> Secure Financial Management
            </div>
            <h1 style="font-size: 3rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem; letter-spacing: -1px;">
                Billing & Accounts
            </h1>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Select a specialized module to manage student fees, external donations, or school-wide subscriptions.
            </p>
        </div>

        <div class="options-container"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2.5rem; max-width: 1200px; margin: 0 auto;">
            <!-- For Students -->
            <div class="glass-card-light student-option" onclick="window.location.href='student_billing.php'"
                style="padding: 3rem 2rem; text-align: center; cursor: pointer; min-height: auto;">
                <div class="icon-wrapper"
                    style="margin: 0 auto 1.5rem; background: #eef2ff; color: #4f46e5; width: 80px; height: 80px; font-size: 2rem; border-radius: 20px;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem;">Student Fees</h3>
                <p style="color: #64748b; margin-bottom: 2rem; line-height: 1.6;">
                    Collect term fees, issue digital invoices, and track outstanding payments for all students.
                </p>
                <div class="btn btn-primary-gradient" style="width: 100%;">Select Module</div>
            </div>

            <!-- For Others -->
            <div class="glass-card-light other-option" onclick="window.location.href='donor_billing.php'"
                style="padding: 3rem 2rem; text-align: center; cursor: pointer; min-height: auto;">
                <div class="icon-wrapper"
                    style="margin: 0 auto 1.5rem; background: #ecfdf5; color: #10b981; width: 80px; height: 80px; font-size: 2rem; border-radius: 20px;">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem;">Donors & Grants
                </h3>
                <p style="color: #64748b; margin-bottom: 2rem; line-height: 1.6;">
                    Record external funding, government grants, and miscellaneous school income.
                </p>
                <div class="btn" style="width: 100%; background: #10b981; color: white;">Select Module</div>
            </div>

            <!-- School Subscription -->
            <div class="glass-card-light subscription-option" onclick="window.location.href='school_subscription.php'"
                style="padding: 3rem 2rem; text-align: center; cursor: pointer; min-height: auto;">
                <div class="icon-wrapper"
                    style="margin: 0 auto 1.5rem; background: #fff7ed; color: #ea580c; width: 80px; height: 80px; font-size: 2rem; border-radius: 20px;">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem;">My Subscription
                </h3>
                <p style="color: #64748b; margin-bottom: 2rem; line-height: 1.6;">
                    Review your service plan, Manage billing cycles, and upgrade institute features.
                </p>
                <div class="btn" style="width: 100%; background: #f1f5f9; color: #475569;">Manage Plan</div>
            </div>
        </div>
    </div>

</body>

</html>