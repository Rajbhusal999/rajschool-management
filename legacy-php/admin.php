<?php
session_start();
require 'includes/db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: super_admin.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } catch (PDOException $e) {
        // Check if error is due to missing table
        if ($e->getCode() == '42S02') {
            die("<div style='text-align:center; padding:20px; font-family:sans-serif;'>
                <h2>System Not Initialized</h2>
                <p>The admin table was not found.</p>
                <p>Please run the <a href='setup.php'>Setup Script</a> first to create the necessary database tables.</p>
            </div>");
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        :root {
            --core-accent: #0ea5e9;
            --core-bg: #020617;
            --glass-bg: rgba(15, 23, 42, 0.7);
            --glass-border: rgba(56, 189, 248, 0.2);
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--core-bg);
            overflow: hidden;
            color: #f8fafc;
        }

        /* Tech Background Elements */
        .bg-glow {
            position: absolute;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 20%, rgba(14, 165, 233, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(56, 189, 248, 0.05) 0%, transparent 50%);
            z-index: 1;
        }

        .admin-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .admin-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 35px;
            padding: 4rem 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .admin-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .core-icon {
            width: 70px;
            height: 70px;
            background: rgba(14, 165, 233, 0.1);
            color: var(--core-accent);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            border: 1px solid var(--glass-border);
            box-shadow: 0 0 30px rgba(14, 165, 233, 0.2);
        }

        .admin-title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: white;
            margin-bottom: 0.5rem;
        }

        .admin-subtitle {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #38bdf8;
            margin-bottom: 0.75rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .form-control {
            width: 100%;
            padding: 16px 1.5rem;
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            font-size: 1rem;
            background: rgba(0, 0, 0, 0.3);
            color: #bae6fd;
            font-weight: 600;
            transition: all 0.3s;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
            text-align: center;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--core-accent);
            background: rgba(14, 165, 233, 0.05);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .btn-access {
            width: 100%;
            padding: 18px;
            background: var(--core-accent);
            color: white;
            border: none;
            border-radius: 18px;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.4s;
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-access:hover {
            transform: translateY(-3px);
            background: #38bdf8;
            box-shadow: 0 15px 30px rgba(14, 165, 233, 0.4);
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 14px;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid rgba(239, 68, 68, 0.2);
            font-size: 0.9rem;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .admin-card {
                padding: 3rem 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="bg-glow"></div>
    <div class="admin-wrapper">
        <div class="admin-card fade-in">
            <div class="admin-header">
                <div class="core-icon">
                    <i class="fas fa-terminal"></i>
                </div>
                <h1 class="admin-title">System Core</h1>
                <p class="admin-subtitle">High-Level Access Node</p>
            </div>

            <?php if ($error): ?>
                <div class="error-msg">
                    <i class="fas fa-shield-virus"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Architect Identifier</label>
                    <input type="text" name="username" class="form-control" autocomplete="off" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Security Protocol</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-access">Execute Access</button>
            </form>
        </div>
    </div>
</body>
</body>

</html>