<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

// Auto-create table if missing
$conn->exec("CREATE TABLE IF NOT EXISTS exam_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT,
    class VARCHAR(50),
    exam_type VARCHAR(50),
    year VARCHAR(10),
    shift VARCHAR(50),
    time VARCHAR(50),
    subject_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$school_id = $_SESSION['user_id'];

// Get unique classes
$class_sql = "SELECT DISTINCT class FROM students WHERE school_id = ? ORDER BY class";
$class_stmt = $conn->prepare($class_sql);
$class_stmt->execute([$school_id]);
$classes = $class_stmt->fetchAll(PDO::FETCH_COLUMN);

$current_nepali_year = date('Y') + 56;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card Generator - Smart विद्यालय</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #f43f5e;
            --primary-gradient: linear-gradient(135deg, #f43f5e, #e11d48);
            --bg-body: #fdf2f2;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            margin: 0;
            color: #1e293b;
        }

        .main-content {
            padding: 3rem 1.5rem;
            max-width: 900px;
            margin: 0 auto;
            min-height: calc(100vh - 70px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .selector-card {
            background: white;
            border-radius: 35px;
            padding: 4rem;
            box-shadow: 0 25px 50px -12px rgba(225, 29, 72, 0.1);
            border: 1px solid rgba(225, 29, 72, 0.05);
            position: relative;
            overflow: hidden;
        }

        .selector-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: var(--primary-gradient);
        }

        .header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .header-icon {
            width: 100px;
            height: 100px;
            background: #fff1f2;
            color: var(--primary);
            font-size: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 30px;
            margin: 0 auto 2rem;
            box-shadow: 0 10px 20px rgba(244, 63, 94, 0.1);
            transition: transform 0.3s ease;
        }

        .selector-card:hover .header-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        .header h1 {
            font-size: 2.75rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.75rem;
            letter-spacing: -1.5px;
        }

        .header p {
            color: #64748b;
            font-size: 1.15rem;
            font-weight: 500;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .label {
            display: block;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .select,
        .input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            font-size: 1.1rem;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 600;
            transition: all 0.3s;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        .select:focus,
        .input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 5px rgba(244, 63, 94, 0.1);
        }

        .btn-setup {
            width: 100%;
            padding: 1.25rem;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 1.25rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 15px 30px -5px rgba(244, 63, 94, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn-setup:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -8px rgba(244, 63, 94, 0.4);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2.5rem;
            font-weight: 700;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 2rem 1rem;
            }

            .selector-card {
                padding: 2.5rem 2rem;
                border-radius: 25px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
    </style>
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div class="breadcrumb">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 0.7rem; color: #cbd5e1;"></i>
            <span style="color: #64748b;">Assessment Nexus</span>
        </div>

        <div class="selector-card">
            <div class="header">
                <div class="header-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h1>Admit Card Gateway</h1>
                <p>Configure and generate official examination entrance credentials.</p>
            </div>

            <form action="admit_card_setup.php" method="GET">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="label">Academic Year</label>
                        <input type="number" name="year" class="input" value="<?php echo $current_nepali_year; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="label">Class</label>
                        <select name="class" class="select" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>">Class
                                    <?php echo htmlspecialchars($class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="label">Examination Type</label>
                        <select name="exam" class="select" required>
                            <option value="first_terminal">First Terminal Examination</option>
                            <option value="second_terminal">Second Terminal Examination</option>
                            <option value="third_terminal">Third Terminal Examination</option>
                            <option value="final">Final Examination</option>
                            <option value="monthly">Monthly Assessment</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-setup">
                    Next: Configure Dates & Subjects <i class="fas fa-chevron-right"></i>
                </button>
            </form>
        </div>
    </div>
</body>

</html>