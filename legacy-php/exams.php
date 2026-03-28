<?php
require 'includes/auth_school.php';
restrictFeature('exams');
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            background: #f0f2f5;
            padding: 3rem 2rem;
            min-height: calc(100vh - 65px);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .header-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 3rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border-left: 6px solid #4f46e5;
        }

        .exam-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .exam-card {
            background: white;
            border-radius: 30px;
            padding: 2.5rem 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .exam-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--color-primary);
            opacity: 0.1;
        }

        .exam-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 40px -10px rgba(0, 0, 0, 0.1);
            border-color: var(--color-primary);
        }

        .exam-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            background: var(--bg-soft);
            color: var(--color-primary);
            transition: all 0.4s ease;
        }

        .exam-card:hover .exam-icon-wrapper {
            background: var(--color-primary);
            color: white;
            transform: scale(1.1) rotate(8deg);
            box-shadow: 0 10px 25px -5px var(--color-primary);
        }

        .exam-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.75rem;
            letter-spacing: -0.5px;
        }

        .exam-description {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .card-footer {
            margin-top: auto;
            color: var(--color-primary);
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .exam-card:hover .card-footer {
            gap: 12px;
        }

        /* Var Color Schemes */
        .color-blue {
            --color-primary: #3b82f6;
            --bg-soft: #eff6ff;
        }

        .color-indigo {
            --color-primary: #6366f1;
            --bg-soft: #eef2ff;
        }

        .color-emerald {
            --color-primary: #10b981;
            --bg-soft: #ecfdf5;
        }

        .color-orange {
            --color-primary: #f59e0b;
            --bg-soft: #fffbeb;
        }

        .color-rose {
            --color-primary: #f43f5e;
            --bg-soft: #fff1f2;
        }

        .color-cyan {
            --color-primary: #06b6d4;
            --bg-soft: #ecfeff;
        }

        .color-violet {
            --color-primary: #8b5cf6;
            --bg-soft: #f5f3ff;
        }

        @media (max-width: 640px) {
            .exam-cards-container {
                grid-template-columns: 1fr;
            }

            .header-section {
                text-align: center;
                border-left: 0;
                border-top: 6px solid #4f46e5;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div style="text-align: center; margin-bottom: 4rem;">
            <div class="hero-badge" style="margin: 0 auto 1.5rem;">
                <span class="badge-dot" style="background: #ef4444; box-shadow: 0 0 10px #ef4444;"></span> Global Exam
                Center
            </div>
            <h1 style="font-size: 3rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem; letter-spacing: -1px;">
                Examination Portal
            </h1>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Unified environment for mark entry, result generation, and academic performance tracking.
            </p>
        </div>

        <div class="exam-cards-container"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; max-width: 1300px; margin: 0 auto;">

            <!-- Mark Entry -->
            <div class="exam-card color-indigo" onclick="window.location.href='mark_entry.php'">
                <div class="exam-icon-wrapper">
                    <i class="fas fa-pencil-alt"></i>
                </div>
                <h3 class="exam-title">Mark Entry</h3>
                <p class="exam-description">Fast and efficient interface for entering student scores across all exams.
                </p>
                <div class="card-footer">Go to Entry <i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- Admit Card -->
            <div class="exam-card color-rose" onclick="window.location.href='admit_card_selector.php'">
                <div class="exam-icon-wrapper">
                    <i class="fas fa-id-badge"></i>
                </div>
                <h3 class="exam-title">Admit Cards</h3>
                <p class="exam-description">Generate bulk admit cards for students with automatic exam scheduling.</p>
                <div class="card-footer">Print Cards <i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- Mark Slip -->
            <div class="exam-card color-emerald" onclick="window.location.href='markslip_selector.php'">
                <div class="exam-icon-wrapper">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="exam-title">Subject Slips</h3>
                <p class="exam-description">Extract detailed subject-wise performance slips for internal records.</p>
                <div class="card-footer">Generate Slips <i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- Grade Sheet -->
            <div class="exam-card color-orange" onclick="window.location.href='gradesheet_selector.php'">
                <div class="exam-icon-wrapper">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3 class="exam-title">Grade Sheets</h3>
                <p class="exam-description">Prepare official terminal and annual grade sheets in high-resolution print
                    format.</p>
                <div class="card-footer">Prepare Sheets <i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- Mark Ledger -->
            <div class="exam-card color-cyan" onclick="window.location.href='mark_ledger.php'">
                <div class="exam-icon-wrapper">
                    <i class="fas fa-th-list"></i>
                </div>
                <h3 class="exam-title">Consolidated Ledger</h3>
                <p class="exam-description">Analyze class-wide results, GPA distributions, and rank calculations in one
                    view.</p>
                <div class="card-footer">View Ledger <i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- Exam Attendance -->
            <div class="exam-card color-emerald" onclick="window.location.href='exam_attendance_entry.php'">
                <div class="exam-icon-wrapper">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="exam-title">Attendance Tracking</h3>
                <p class="exam-description">Record student presence specifically during the examination period.</p>
                <div class="card-footer">Track Now <i class="fas fa-arrow-right"></i></div>
            </div>

            <!-- Subject Management -->
            <div class="exam-card color-violet" onclick="window.location.href='subjects.php'">
                <div class="exam-icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="exam-title">Subjects Config</h3>
                <p class="exam-description">Configure your academic curriculum, assign weights, and manage credit hours.
                </p>
                <div class="card-footer">Settings <i class="fas fa-arrow-right"></i></div>
            </div>

        </div>
    </div>
    </div>
    </div>

</body>

</html>