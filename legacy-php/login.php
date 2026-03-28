<?php
session_start();
require 'includes/db_connect.php';

// Force Logout behavior for new tabs/direct access to login page
// This ensures that even if a session exists on the server, the user MUST re-authenticate
if (!isset($_GET['source']) || $_GET['source'] !== 'auth_redirect') {
    // Optional: We could unset everything, but better to just force the login form
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emis_code = $_POST['emis_code'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM schools WHERE emis_code = ?");
    $stmt->execute([$emis_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Refresh session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['school_name'] = $user['school_name'];
        $_SESSION['school_name_ne'] = isset($user['school_name_ne']) ? $user['school_name_ne'] : $user['school_name'];
        $_SESSION['school_address'] = $user['address'];
        $_SESSION['estd_date'] = $user['estd_date'];
        $_SESSION['school_logo'] = $user['school_logo'];
        $_SESSION['subscription_status'] = $user['subscription_status'];
        $_SESSION['subscription_plan'] = $user['subscription_plan'];
        $_SESSION['school_photo'] = $user['school_photo'];

        // Detect if this is a Demo School account
        if (strpos($user['emis_code'], 'DEMO') === 0 || $user['subscription_plan'] === 'Trial (1 Day)') {
            $_SESSION['is_demo'] = true;
        } else {
            $_SESSION['is_demo'] = false;
        }

        // KEY: Set the Tab Verification flag so the dashboard allows entry for this tab
        echo "<script>
            sessionStorage.setItem('smart_portal_tab_verified', 'true');
            window.location.href = '" . ($user['subscription_status'] == 'active' ? 'dashboard.php' : 'subscription_plans.php') . "';
        </script>";
        exit();
    } else {
        $error = "Invalid EMIS Code or Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart विद्यालय</title>
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

        /* Abstract Background Decoration */
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

        body::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, rgba(248, 250, 252, 0) 70%);
            bottom: -100px;
            left: -100px;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 40px;
            padding: 4rem 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 3rem;
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

        .login-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }

        .login-subtitle {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 1.05rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
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

        .btn-login {
            width: 100%;
            padding: 18px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 20px;
            font-weight: 800;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.3);
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -8px rgba(79, 70, 229, 0.4);
        }

        .error-alert {
            background: #fee2e2;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            font-weight: 700;
            text-align: center;
            border: 1px solid rgba(185, 28, 28, 0.1);
            font-size: 0.95rem;
        }

        .footer-links {
            text-align: center;
            margin-top: 2.5rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.2s;
        }

        .footer-links a:hover {
            color: #4338ca;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 3rem 2rem;
                border-radius: 30px;
            }

            .login-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card fade-in">
            <div class="login-header">
                <div class="brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="login-title">School Portal</h1>
                <p class="login-subtitle">Secure gateway to institutional intelligence.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Standard Login Form -->
            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label">EMIS Code</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key"></i>
                        <input type="text" class="form-control" name="emis_code" placeholder="Institutional Identity"
                            pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required
                            autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Access Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" name="password" id="loginPassword"
                            placeholder="••••••••" style="padding-right: 50px;" required>
                        <i class="fas fa-eye-slash" id="toggleLoginPassword"
                            style="left: auto; right: 20px; cursor: pointer; color: #94a3b8;"></i>
                    </div>
                </div>

                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; font-size: 0.9rem; font-weight: 600;">
                    <label
                        style="color: var(--text-muted); display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" style="width: 16px; height: 16px; border-radius: 6px;"> Remember
                    </label>
                    <a href="forgot_password.php" style="color: var(--primary); text-decoration: none;">Forgot Key?</a>
                </div>

                <button type="submit" class="btn-login">Unlock Portal</button>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="admin_login.php" class="btn"
                        style="width: 100%; filter: grayscale(1); opacity: 0.8; border: 2px dashed #94a3b8; background: transparent; color: #64748b; font-size: 0.9rem; border-radius: 18px; padding: 12px;">
                        <i class="fas fa-user-shield" style="margin-right: 8px;"></i> System Administrator Access
                    </a>
                </div>
            </form>

            <div class="footer-links" style="margin-top: 2rem; border-top: 1px solid rgba(0,0,0,0.05); pt-4;">
                New Institution? <a href="register.php">Initialize Registration</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('toggleLoginPassword').addEventListener('click', function (e) {
            const password = document.getElementById('loginPassword');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</body>

</html>