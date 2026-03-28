<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Plan - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .plan-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            transition: transform 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            border-color: #4f46e5;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            margin: 1rem 0;
        }

        .duration {
            font-size: 1.2rem;
            color: #6b7280;
            font-weight: 500;
        }

        .features {
            list-style: none;
            margin: 1.5rem 0;
            text-align: left;
            color: #4b5563;
        }

        .features li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .best-value {
            position: absolute;
            top: -15px;
            right: -15px;
            background: #f59e0b;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            transform: rotate(10deg);
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }
    </style>
</head>

<body>

    <div class="fullscreen-bg-container"
        style="background: url('assets/images/payment_bg.png'); background-size: cover; display: block; padding: 4rem 2rem; min-height: 100vh;">
        <div style="text-align: center; color: white; margin-bottom: 3rem;">
            <h1 style="font-size: 3rem; font-weight: 800; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">Choose Your
                Subscription</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">Unlock the full power of Smart विद्यालय Management System.</p>
        </div>

        <div class="grid-3">
            <!-- Plan 1 -->
            <div class="plan-card">
                <div>
                    <div class="duration">1 Year Access</div>
                    <div class="price">Rs. 5,000</div>
                    <ul class="features">
                        <li>✓ Student Management</li>
                        <li>✓ Teacher Management</li>
                        <li>✓ Exams & Results</li>
                    </ul>
                </div>
                <a href="payment_gateway_select.php?plan=1_year&amount=5000" class="btn btn-primary"
                    style="width: 100%;">Select Plan</a>
            </div>

            <!-- Plan 2 -->
            <div class="plan-card"
                style="position: relative; border-color: #4f46e5; box-shadow: 0 0 30px rgba(79, 70, 229, 0.4);">
                <div class="best-value">POPULAR</div>
                <div>
                    <div class="duration">2 Years Access</div>
                    <div class="price">Rs. 8,000</div>
                    <ul class="features">
                        <li>✓ Student & Teacher Management</li>
                        <li>✓ Exams & Marksheets</li>
                        <li>✓ Billing & Accounts</li>
                        <li>✓ ID Card Generation</li>
                    </ul>
                </div>
                <a href="payment_gateway_select.php?plan=2_years&amount=8000" class="btn btn-primary"
                    style="width: 100%; background: #4f46e5;">Select Plan</a>
            </div>

            <!-- Plan 3 -->
            <div class="plan-card">
                <div>
                    <div class="duration">5 Years Access</div>
                    <div class="price">Rs. 20,000</div>
                    <ul class="features">
                        <li>✓ Student Management</li>
                        <li>✓ Teacher Management</li>
                        <li>✓ Exams & Results</li>
                        <li>✓ Billing System</li>
                        <li>✓ Smart Attendance</li>
                        <li>✓ ID Card Generation</li>
                    </ul>
                </div>
                <a href="payment_gateway_select.php?plan=5_years&amount=20000" class="btn btn-primary"
                    style="width: 100%;">Select Plan</a>
            </div>
        </div>

        <!-- SMS Note -->
        <div
            style="max-width: 800px; margin: 3rem auto 0; text-align: center; padding: 1.5rem; background: rgba(255, 255, 255, 0.9); border-radius: 12px; border: 2px solid #4f46e5;">
            <p style="color: #1e2937; font-weight: 600; font-size: 1rem; margin: 0;">
                Note: For attendance tracking, the cost of SMS is to be borne by the school itself, and we will assist
                in its integration.
            </p>
        </div>
    </div>

</body>

</html>