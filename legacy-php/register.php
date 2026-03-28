<?php
require 'includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_name = $_POST['school_name'];
    $emis_code = $_POST['emis_code'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Handle School Photo Upload
        $school_photo_path = null;
        if (isset($_FILES['school_photo']) && $_FILES['school_photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['school_photo']['name'];
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $file_size = $_FILES['school_photo']['size'];

            if (in_array($file_ext, $allowed)) {
                if ($file_size <= 5 * 1024 * 1024) { // 5MB limit
                    $upload_dir = 'uploads/school_photos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $new_filename = uniqid('school_bg_', true) . '.' . $file_ext;
                    if (move_uploaded_file($_FILES['school_photo']['tmp_name'], $upload_dir . $new_filename)) {
                        $school_photo_path = $upload_dir . $new_filename;
                    } else {
                        $error = "Failed to upload school photo.";
                    }
                } else {
                    $error = "Photo size too large. Max 5MB allowed.";
                }
            } else {
                $error = "Invalid file type. Only JPG and PNG allowed.";
            }
        }

        // Handle School Logo Upload
        $school_logo_path = null;
        if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['school_logo']['name'];
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $file_size = $_FILES['school_logo']['size'];

            if (in_array($file_ext, $allowed)) {
                if ($file_size <= 2 * 1024 * 1024) { // 2MB limit for logo
                    $upload_dir = 'uploads/school_logos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $new_filename = uniqid('logo_', true) . '.' . $file_ext;
                    if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $upload_dir . $new_filename)) {
                        $school_logo_path = $upload_dir . $new_filename;
                    } else {
                        $error = "Failed to upload school logo.";
                    }
                } else {
                    $error = "Logo size too large. Max 2MB allowed.";
                }
            } else {
                $error = "Invalid logo file type. Only JPG and PNG allowed.";
            }
        }

        // Get Estd Date
        $estd_date = $_POST['estd_date'] ?? '';

        if (empty($error)) {
            // Check if EMIS exists
            $stmt = $conn->prepare("SELECT id FROM schools WHERE emis_code = ?");
            $stmt->execute([$emis_code]);
            if ($stmt->rowCount() > 0) {
                $error = "EMIS Code already registered.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO schools (school_name, emis_code, email, phone, address, password_hash, school_photo, school_logo, estd_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$school_name, $emis_code, $email, $phone, $address, $password_hash, $school_photo_path, $school_logo_path, $estd_date])) {
                    $success = "Registration successful! Redirecting to login...";
                    header("refresh:2;url=login.php");
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Registration - Smart विद्यालय</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 1000px;
            height: 1000px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, rgba(248, 250, 252, 0) 70%);
            top: -300px;
            right: -300px;
            z-index: 0;
        }

        .register-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 900px;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 50px;
            padding: 4rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.1);
        }

        .register-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            color: white;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            margin: 0 auto 1.5rem;
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.25);
        }

        .register-title {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.75rem;
            letter-spacing: -1.5px;
        }

        .register-subtitle {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 1.15rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
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
            border: 2px solid #f1f5f9;
            border-radius: 20px;
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

        .file-info {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 8px;
            font-weight: 600;
        }

        .btn-register {
            width: 100%;
            padding: 20px;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight:
                800;
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.4s;
            box-shadow: 0 20px 40px -10px rgba(79, 70, 229, 0.3);
            margin-top: 2rem;
        }

        .btn-register:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(79, 70, 229, 0.4);
        }

        .alert-banner {
            padding: 1.25rem;
            border-radius: 22px;
            margin-bottom: 2.5rem;
            font-weight: 700;
            text-align: center;
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

        .form-footer {
            text-align: center;
            margin-top: 3rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .register-card {
                padding: 3rem 2rem;
                border-radius: 40px;
            }

            .register-title {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="register-wrapper">
        <div class="register-card fade-in">
            <div class="register-header">
                <div class="brand-icon">
                    <i class="fas fa-university"></i>
                </div>
                <h1 class="register-title">Initialize Institution</h1>
                <p class="register-subtitle">Construct your digital ecosystem within our zero-gravity framework.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-banner alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-banner alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">School Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-school"></i>
                        <input type="text" class="form-control" name="school_name" placeholder="Institutional Title"
                            required autofocus>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">EMIS Code</label>
                        <div class="input-wrapper">
                            <i class="fas fa-fingerprint"></i>
                            <input type="text" class="form-control" name="emis_code" placeholder="Identifier"
                                pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Direct</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone-alt"></i>
                            <input type="text" class="form-control" name="phone" placeholder="98XXXXXXXX"
                                pattern="(97|98)[0-9]{8}" maxlength="10"
                                title="Phone number must be 10 digits starting with 97 or 98"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address Matrix</label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" class="form-control" name="address" placeholder="Physical location"
                                required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Establishment (B.S.)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="text" class="form-control" name="estd_date" placeholder="Year B.S."
                                pattern="[0-9]{4}" maxlength="4"
                                oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(parseInt(this.value) > 2082) this.setCustomValidity('Year cannot be in the future (current year: 2082 B.S.)'); else this.setCustomValidity('');">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Background Artifact</label>
                        <div class="input-wrapper">
                            <i class="fas fa-image"></i>
                            <input type="file" class="form-control" name="school_photo" accept=".jpg, .jpeg, .png"
                                required>
                        </div>
                        <div class="file-info">MAX 5MB. Visual identity backdrop.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Institution Logo</label>
                        <div class="input-wrapper">
                            <i class="fas fa-stamp"></i>
                            <input type="file" class="form-control" name="school_logo" accept=".jpg, .jpeg, .png"
                                required>
                        </div>
                        <div class="file-info">MAX 2MB. Credentials & receipts.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Administrative Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-control" name="email" placeholder="admin@institution.edu"
                            pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Master Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" name="password" id="regPassword"
                                placeholder="••••••••" style="padding-right: 50px;"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                                minlength="8" required>
                            <i class="fas fa-eye-slash" id="toggleRegPassword"
                                style="left: auto; right: 20px; cursor: pointer; color: #94a3b8;"></i>
                        </div>
                        <div class="file-info">8+ chars: Upper, Lower, Symbol, Int.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Verify Secret</label>
                        <div class="input-wrapper">
                            <i class="fas fa-check-double"></i>
                            <input type="password" class="form-control" name="confirm_password" id="regConfirmPassword"
                                placeholder="••••••••" style="padding-right: 50px;" required>
                            <i class="fas fa-eye-slash" id="toggleRegConfirmPassword"
                                style="left: auto; right: 20px; cursor: pointer; color: #94a3b8;"></i>
                        </div>
                    </div>
                </div>

                <div
                    style="margin-bottom: 2rem; font-size: 0.9rem; color: var(--text-muted); font-weight: 500; text-align: center;">
                    Submitting this form confirms agreement to our <a href="terms_of_service.php"
                        style="color: var(--primary); font-weight: 700;">Governance Protocol</a>.
                </div>

                <button type="submit" class="btn-register">Deploy Institutional Portal</button>
            </form>

            <div class="form-footer">
                Already orchestrated? <a href="login.php" style="font-weight: 700;">Authenticate Portal</a>
            </div>
        </div>
    </div>

    <script>
        function setupToggle(btnId, inputId) {
            document.getElementById(btnId).addEventListener('click', function () {
                const input = document.getElementById(inputId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
        setupToggle('toggleRegPassword', 'regPassword');
        setupToggle('toggleRegConfirmPassword', 'regConfirmPassword');
    </script>
</body>

</body>

</html>