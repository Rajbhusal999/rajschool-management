<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['plan'])) {
    header("Location: subscription_plans.php");
    exit();
}
$plan = $_GET['plan'];
$amount = $_GET['amount'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Payment Method</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .method-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }

        .method-card:hover {
            transform: translateX(5px);
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .method-icon {
            width: 50px;
            height: 50px;
            background: #e0e7ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #4338ca;
        }

        .payment-container {
            max-width: 500px;
            margin: 100px auto;
        }
    </style>
</head>

<body>

    <div class="fullscreen-bg-container"
        style="background: url('assets/images/payment_bg.png'); background-size: cover;">
        <div class="payment-container glass-panel fade-in" style="padding: 2rem;">
            <h2 style="margin-bottom: 1.5rem; color: #1f2937;">Pay Rs.
                <?php echo number_format($amount); ?>
            </h2>
            <p style="margin-bottom: 2rem; color: #6b7280;">Select your preferred payment method.</p>

            <a href="payment_verify.php?method=esewa&plan=<?php echo $plan; ?>&amount=<?php echo $amount; ?>"
                style="text-decoration: none; color: inherit;">
                <div class="method-card">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="method-icon" style="background: #62cf62; color: white;">eS</div>
                        <div style="font-weight: 600;">eSewa Wallet</div>
                    </div>
                    <div>&rarr;</div>
                </div>
            </a>

            <a href="payment_verify.php?method=khalti&plan=<?php echo $plan; ?>&amount=<?php echo $amount; ?>"
                style="text-decoration: none; color: inherit;">
                <div class="method-card">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="method-icon" style="background: #5c2d91; color: white;">Kh</div>
                        <div style="font-weight: 600;">Khalti Digital Wallet</div>
                    </div>
                    <div>&rarr;</div>
                </div>
            </a>

            <a href="payment_verify.php?method=banking&plan=<?php echo $plan; ?>&amount=<?php echo $amount; ?>"
                style="text-decoration: none; color: inherit;">
                <div class="method-card">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="method-icon" style="background: #1f2937; color: white;">MB</div>
                        <div style="font-weight: 600;">Mobile Banking</div>
                    </div>
                    <div>&rarr;</div>
                </div>
            </a>

            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="subscription_plans.php" style="color: #6b7280; font-size: 0.9rem;">Cancel</a>
            </div>
        </div>
    </div>

</body>

</html>