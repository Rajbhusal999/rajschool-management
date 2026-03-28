<?php
require 'includes/auth_school.php';
restrictFeature('teacher_salary');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Organization Type | Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Animated background orbs */
        body::before {
            content: '';
            position: fixed;
            top: -200px;
            left: -200px;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: float1 12s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -200px;
            right: -200px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float1 15s ease-in-out infinite reverse;
        }

        @keyframes float1 {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(40px, -40px);
            }
        }

        .back-link {
            position: fixed;
            top: 1.5rem;
            left: 2rem;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s;
            z-index: 10;
        }

        .back-link:hover {
            color: white;
        }

        .content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 800px;
            text-align: center;
        }

        .top-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.4);
            color: #a5b4fc;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .dot {
            width: 7px;
            height: 7px;
            background: #6366f1;
            border-radius: 50%;
            box-shadow: 0 0 8px #6366f1;
        }

        h1 {
            font-size: 2.8rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.75rem;
            line-height: 1.2;
        }

        h1 span {
            background: linear-gradient(to right, #818cf8, #ec4899);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.55);
            font-size: 1.05rem;
            margin-bottom: 3.5rem;
            font-weight: 500;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .org-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 2.5rem 2rem;
            cursor: pointer;
            text-decoration: none;
            color: white;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .org-card::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 0.35s;
            border-radius: 28px;
        }

        .org-card.gov::before {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.06));
        }

        .org-card.priv::before {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(79, 70, 229, 0.06));
        }

        .org-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
        }

        .org-card.gov:hover {
            border-color: rgba(16, 185, 129, 0.5);
            box-shadow: 0 25px 60px rgba(16, 185, 129, 0.15);
        }

        .org-card.priv:hover {
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 25px 60px rgba(99, 102, 241, 0.15);
        }

        .org-card:hover::before {
            opacity: 1;
        }

        .card-icon {
            width: 90px;
            height: 90px;
            border-radius: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            margin-bottom: 1.75rem;
            transition: transform 0.35s, box-shadow 0.35s;
        }

        .org-card.gov .card-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.3);
        }

        .org-card.priv .card-icon {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.3);
        }

        .org-card:hover .card-icon {
            transform: scale(1.1) translateY(-4px);
        }

        .org-card.gov:hover .card-icon {
            box-shadow: 0 20px 45px rgba(16, 185, 129, 0.45);
        }

        .org-card.priv:hover .card-icon {
            box-shadow: 0 20px 45px rgba(99, 102, 241, 0.45);
        }

        .card-title {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .card-desc {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            line-height: 1.6;
            font-weight: 500;
            margin-bottom: 1.75rem;
        }

        .card-features {
            width: 100%;
            list-style: none;
            text-align: left;
        }

        .card-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.65);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-features li:last-child {
            border-bottom: none;
        }

        .card-features li i {
            width: 16px;
            font-size: 0.8rem;
        }

        .org-card.gov .card-features li i {
            color: #34d399;
        }

        .org-card.priv .card-features li i {
            color: #818cf8;
        }

        .card-cta {
            margin-top: 2rem;
            padding: 12px 28px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            width: 100%;
            justify-content: center;
        }

        .org-card.gov .card-cta {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .org-card.priv .card-cta {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }

        .card-cta:hover {
            transform: translateY(-1px);
            filter: brightness(1.1);
        }

        @media (max-width: 640px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>

    <a href="dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="content">
        <div class="top-badge"><span class="dot"></span> Teacher Salary Management</div>
        <h1>Select Your <span>Organization Type</span></h1>
        <p class="subtitle">Choose the type that matches your school to apply the correct salary structure.</p>

        <div class="cards-grid">

            <!-- Government -->
            <a href="teacher_salary.php?type=government" class="org-card gov">
                <div class="card-icon"><i class="fas fa-landmark"></i></div>
                <div class="card-title">Government</div>
                <p class="card-desc">Public / Government-funded schools following the official pay scale & grade system.
                </p>
                <ul class="card-features">
                    <li><i class="fas fa-check-circle"></i> Grade-based salary structure</li>
                    <li><i class="fas fa-check-circle"></i> PF & CIT deductions</li>
                    <li><i class="fas fa-check-circle"></i> Government allowances</li>
                    <li><i class="fas fa-check-circle"></i> Service grade tracking</li>
                </ul>
                <div class="card-cta"><i class="fas fa-landmark"></i> Select Government</div>
            </a>

            <!-- Private -->
            <a href="teacher_salary.php?type=private" class="org-card priv">
                <div class="card-icon"><i class="fas fa-school"></i></div>
                <div class="card-title">Private</div>
                <p class="card-desc">Private / Community schools with custom salary packages set by management.</p>
                <ul class="card-features">
                    <li><i class="fas fa-check-circle"></i> Custom salary packages</li>
                    <li><i class="fas fa-check-circle"></i> Flexible bonus & deductions</li>
                    <li><i class="fas fa-check-circle"></i> Performance-based pay</li>
                    <li><i class="fas fa-check-circle"></i> TDS deduction support</li>
                </ul>
                <div class="card-cta"><i class="fas fa-school"></i> Select Private</div>
            </a>

        </div>
    </div>

</body>

</html>