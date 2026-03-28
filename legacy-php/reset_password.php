<?php
session_start();
require 'includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // In a real app, verifying the token is critical here.
        // For this simulation, we update the password for the email directly.
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE schools SET password_hash = ? WHERE email = ?");
        if ($stmt->execute([$hashed_password, $email])) {
            $success = "Password updated successfully. You can now login.";
        } else {
            $error = "Failed to update password.";
        }
    }
}

$email_from_url = isset($_GET['email']) ? $_GET['email'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-bg {
            background-image: url('assets/images/login_bg.png');
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            color: #1f2937;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #4f46e5;
        }

        .success-banner {
            background: #d1fae5;
            color: #065f46;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #10b981;
            text-align: center;
        }

        .error-banner {
            background: #fee2e2;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #fecaca;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="fullscreen-bg-container login-bg">
        <div class="login-container glass-panel fade-in">
            <div style="text-align: center;">
                <h1 class="login-title">Set New Password</h1>
                <p class="login-subtitle" style="margin-bottom: 2rem; color: #6b7280;">Choose a strong password.</p>
            </div>

            <?php if ($success): ?>
                <div class="success-banner">
                    <?php echo $success; ?>
                    <br><a href="login.php" style="font-weight: bold; color: inherit;">Go to Login</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-banner">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form action="" method="POST">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_from_url); ?>">

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" placeholder="New Password" required
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" minlength="8"
                            title="Password must be at least 8 characters and include: uppercase letter, lowercase letter, number, and special character (@$!%*?&)">
                        <small style="color: #6b7280; font-size: 11px;">Min 8 chars: upper, lower, num, special
                            (@$!%*?&)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Update Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>