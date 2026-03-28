<?php
session_start();
// Forgot Password Logic with Email Simulation
require 'includes/db_connect.php';
$email_simulation_content = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id, school_name FROM schools WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user) {
        // Generate a token (in production, store this in DB with expiry)
        $token = bin2hex(random_bytes(32));
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token . "&email=" . urlencode($email);

        // Try to send real email
        require_once 'includes/email_helper.php';
        $email_result = sendPasswordResetEmail($email, $user['school_name'], $reset_link);

        if ($email_result['success']) {
            // Email sent successfully
            $message = "✓ Password reset link has been sent to your email.";
        } else {
            // Email failed, show simulation
            $email_simulation_content = "
                <div style='background: #fee2e2; color: #991b1b; padding: 15px; border: 2px solid #dc2626; margin-bottom: 15px; border-radius: 8px;'>
                    <strong>⚠️ Email not configured</strong><br>
                    Error: {$email_result['message']}
                </div>
                <div style='background: white; color: black; padding: 20px; border: 1px dashed #4f46e5; margin-bottom: 20px; text-align: left;'>
                    <strong>[EMAIL PREVIEW - Not Sent] To: $email</strong><br><br>
                    Subject: Password Reset Request<br><br>
                    Hi " . htmlspecialchars($user['school_name']) . ",<br><br>
                    We received a request to reset your password. Click the link below:<br><br>
                    <a href='$reset_link' style='color: #4f46e5; font-weight: bold;'>Reset Password Link</a><br><br>
                    If you didn't ask for this, you can ignore this email.
                </div>
            ";
            $message = "Reset link generated (email not sent - check configuration).";
        }
    } else {
        // Debugging: Check count of users
        $count = $conn->query("SELECT COUNT(*) FROM schools")->fetchColumn();
        $message = "No account found with email: '" . htmlspecialchars($email) . "'. (Total users in DB: $count)";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary: #4f46e5;
            --primary-gradient: linear-gradient(135deg, #4f46e5, #6366f1);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.08) 0%, rgba(248, 250, 252, 0) 70%);
            top: -200px;
            right: -200px;
            z-index: 0;
        }

        .recovery-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }

        .recovery-card {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 40px;
            padding: 4rem 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }

        .brand-icon {
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            color: white;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }

        .recovery-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            text-align: center;
            letter-spacing: -1px;
        }

        .recovery-subtitle {
            color: var(--text-muted);
            font-weight: 500;
            text-align: center;
            margin-bottom: 3rem;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 16px 1.5rem 16px 3.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 18px;
            font-size: 1.1rem;
            background: white;
            color: var(--text-main);
            font-weight: 600;
            transition: all 0.3s;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.1);
        }

        .btn-recovery {
            width: 100%;
            padding: 18px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 20px;
            font-weight: 800;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.4s;
            box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.3);
        }

        .btn-recovery:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -8px rgba(79, 70, 229, 0.4);
        }

        .alert-banner {
            padding: 1.25rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .back-to-login {
            text-align: center;
            margin-top: 2.5rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .back-to-login a {
            color: var(--primary);
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .recovery-card {
                padding: 3rem 2rem;
                border-radius: 30px;
            }

            .recovery-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="recovery-wrapper">
        <div class="recovery-card fade-in">
            <div class="brand-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="recovery-title">Credential Recovery</h1>
            <p class="recovery-subtitle">Restore institutional access. Enter your registered administrative email.</p>

            <?php if ($email_simulation_content): ?>
                <div style="margin-bottom: 2rem;">
                    <?php echo $email_simulation_content; ?>
                </div>
            <?php endif; ?>

            <?php if ($message && !$email_simulation_content): ?>
                <?php
                $is_error = strpos($message, 'No account') !== false;
                $alert_class = $is_error ? 'alert-error' : 'alert-success';
                $icon = $is_error ? 'fa-exclamation-circle' : 'fa-check-circle';
                ?>
                <div class="alert-banner <?php echo $alert_class; ?>">
                    <i class="fas <?php echo $icon; ?>"></i>
                    <span><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label">Administrative Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-control" name="email" placeholder="admin@institution.edu"
                            required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn-recovery">Initiate Recovery</button>
            </form>

            <div class="back-to-login">
                Recall your credentials? <a href="login.php">Return to Portal</a>
            </div>
        </div>
    </div>
</body>

</html>