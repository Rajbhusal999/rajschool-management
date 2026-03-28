<?php
session_start();
require 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

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
        body {
            background-color: #0f172a;
            color: white;
        }
    </style>
</head>

<body>
    <div class="fullscreen-bg-container">
        <div class="glass-panel fade-in" style="padding: 3rem; background: rgba(15, 23, 42, 0.8);">
            <h1 style="text-align: center; margin-bottom: 2rem; color: #38bdf8;">Admin Command</h1>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control"
                        style="background: rgba(255,255,255,0.1); color: white;" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control"
                        style="background: rgba(255,255,255,0.1); color: white;" required>
                </div>
                <button type="submit" class="btn btn-primary" id="loginBtn"
                    style="width: 100%; background: #0ea5e9;">Access Control</button>
            </form>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        });
    </script>
</body>

</html>