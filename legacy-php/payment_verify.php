<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$plan = $_GET['plan'] ?? '1_year';
$amount = $_GET['amount'] ?? '4000';
$method = $_GET['method'] ?? 'esewa';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = $_POST['verification_code'];
    $school_id = $_SESSION['user_id'];

    // Check if the code matches the one assigned by Admin in DB
    $stmt = $conn->prepare("SELECT payment_verification_code FROM schools WHERE id = ?");
    $stmt->execute([$school_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['payment_verification_code'] === $input_code) {
        // Activate Subscription
        $years = 1;
        if (strpos($plan, '2') !== false)
            $years = 2;
        if (strpos($plan, '5') !== false)
            $years = 5;

        $expiry_date = date('Y-m-d', strtotime("+$years years"));

        $update_sql = "UPDATE schools SET subscription_status = 'active', subscription_plan = ?, subscription_expiry = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt->execute([$plan, $expiry_date, $school_id])) {
            $_SESSION['subscription_status'] = 'active';
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "System error during activation.";
        }
    } else {
        $error = "Invalid Verification Code. Please contact Admin.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Payment</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .qr-placeholder {
            width: 200px;
            height: 200px;
            background: white;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #ccc;
            border-radius: 12px;
        }
    </style>
</head>

<body>

    <div class="fullscreen-bg-container"
        style="background: url('assets/images/payment_bg.png'); background-size: cover;">
        <div class="glass-panel fade-in" style="width: 100%; max-width: 500px; padding: 2.5rem; text-align: center;">
            <?php if ($method == 'khalti'): ?>
                <div style="padding: 2rem 0;">
                    <div style="width: 80px; height: 80px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; color: #ef4444;"></i>
                    </div>
                    <img src="https://khalti.s3.ap-south-1.amazonaws.com/Khalti+Logo.png" style="height: 40px; margin-bottom: 1.5rem;" alt="Khalti">
                    <h2 style="color: #1f2937; margin-bottom: 1rem; font-weight: 800;">Payment System Unavailable</h2>
                    <p style="color: #6b7280; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2.5rem;">
                        Khalti payment is currently unavailable. Please go back and select <b>eSewa</b> or <b>Mobile Banking</b> to complete your subscription.
                    </p>
                    <a href="payment_gateway_select.php?plan=<?php echo $plan; ?>&amount=<?php echo $amount; ?>" class="btn btn-primary" style="height: 60px; font-size: 1.25rem; font-weight: 800; border-radius: 15px;">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </a>
                </div>
            <?php else: ?>
                <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 2rem;">
                    <?php if ($method == 'esewa'): ?>
                        <img src="https://esewa.com.np/common/images/esewa_logo.png" style="height: 40px;" alt="eSewa">
                    <?php else: ?>
                        <i class="fas fa-university" style="font-size: 2.5rem; color: #1e293b;"></i>
                    <?php endif; ?>
                    <h2 style="margin: 0; color: #1f2937;">Scan & Pay</h2>
                </div>

                <div
                    style="background: #f8fafc; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 2rem;">
                    <p style="color: #6b7280; font-size: 0.95rem; margin-bottom: 0.5rem;">Total Amount to Pay</p>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #4f46e5; letter-spacing: -1px;">
                        Rs. <?php echo number_format($amount); ?>
                    </div>
                </div>

                <div class="qr-placeholder"
                    style="width: 280px; height: 280px; background: white; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin: 0 auto 2rem;">
                    <?php if ($method == 'esewa'): ?>
                        <img src="assets/images/qr_esewa.png" alt="eSewa QR"
                            style="width: 100%; height: 100%; object-fit: contain;"
                            onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=RAJ_BHUSAL_9861079061';">
                    <?php elseif ($method == 'banking'): ?>
                        <img src="assets/images/qr_banking.png" alt="Banking QR"
                            style="width: 100%; height: 100%; object-fit: contain;"
                            onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=RAJ_BHUSAL_GARIMA_BANK_03701701020032000001';">
                    <?php endif; ?>
                </div>

                <div
                    style="text-align: left; background: #fffbeb; border: 1px solid #fde68a; padding: 1.25rem; border-radius: 16px; margin-bottom: 2rem;">
                    <p
                        style="font-weight: 800; color: #92400e; margin-bottom: 0.75rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        Account Details:</p>
                    <?php if ($method == 'esewa'): ?>
                        <div style="display: grid; gap: 8px; color: #1f2937; font-weight: 700;">
                            <div>Name: Raj Bhusal</div>
                            <div>eSewa ID: 9861079061</div>
                        </div>
                    <?php elseif ($method == 'banking'): ?>
                        <div style="display: grid; gap: 8px; color: #1f2937; font-weight: 700;">
                            <div>Bank: Garima Bikas Bank Ltd.</div>
                            <div>Name: RAJ BHUSAL</div>
                            <div>A/C: 03701701020032000001</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 2rem;">
                    <p style="font-size: 0.95rem; color: #4b5563; margin-bottom: 1rem;">
                        After payment, enter your <strong>Verification Code</strong> from Admin.
                    </p>

                    <?php if ($error): ?>
                        <div
                            style="background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 10px; margin-bottom: 1.5rem; font-weight: 600;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form
                        action="payment_verify.php?plan=<?php echo $plan; ?>&amount=<?php echo $amount; ?>&method=<?php echo $method; ?>"
                        method="POST">
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <input type="text" name="verification_code" class="form-control" placeholder="Verification Code"
                                style="height: 60px; text-align: center; font-size: 1.5rem; font-weight: 800; letter-spacing: 4px; border-radius: 15px; border: 2px solid #e2e8f0;"
                                required>
                        </div>
                        <button type="submit" class="btn btn-primary"
                            style="height: 60px; font-size: 1.25rem; font-weight: 800; border-radius: 15px; box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);">
                            Verify & Activate Now
                        </button>
                    </form>
                </div>

                <a href="payment_gateway_select.php?plan=<?php echo $plan; ?>&amount=<?php echo $amount; ?>"
                    style="color: #64748b; font-weight: 600; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Back to methods
                </a>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>