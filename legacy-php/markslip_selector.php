<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

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
    <title>Generate Mark Slip - Smart विद्यालय</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .main-content {
            padding: 3rem 1.5rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .selector-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .icon-banner {
            width: 80px;
            height: 80px;
            background: #ecfdf5;
            color: #10b981;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 0px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .select,
        .input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 0.95rem;
            background: #f8fafc;
            transition: 0.2s;
            color: #1e293b;
            font-weight: 500;
        }

        .select:focus,
        .input:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .btn-generate {
            width: 100%;
            padding: 1rem;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-generate:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
        }

        .footer-link {
            text-align: center;
            margin-top: 2rem;
        }

        .footer-link a {
            color: #64748b;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div class="selector-card">
            <div class="header">
                <div class="icon-banner"><i class="fas fa-file-invoice"></i></div>
                <h1>Generate Mark Slip</h1>
                <p>Specific subject and class level report generation</p>
            </div>

            <form action="mark_slip.php" method="GET" id="markslipForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Academic Year</label>
                        <input type="number" name="year" class="input" value="<?php echo $current_nepali_year; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Exam Type</label>
                        <select name="exam" class="select" required>
                            <option value="first_terminal">First Terminal</option>
                            <option value="second_terminal">Second Terminal</option>
                            <option value="third_terminal">Third Terminal</option>
                            <option value="final">Final Exam</option>
                            <option value="monthly">Monthly Exam</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Class</label>
                        <select name="class" id="classSelect" class="select" required onchange="fetchSubjects()">
                            <option value="">Choose Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>">Class
                                    <?php echo htmlspecialchars($class); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Subject</label>
                        <select name="subject" id="subjectSelect" class="select" required disabled>
                            <option value="">Wait for Class...</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-generate">
                    <i class="fas fa-wand-sparkles"></i>
                    Generate Report
                </button>
            </form>
        </div>

        <div class="footer-link">
            <a href="exams.php"><i class="fas fa-chevron-left"></i> Back to Exam Portal</a>
        </div>
    </div>

    <script>
        function fetchSubjects() {
            const classVal = document.getElementById('classSelect').value;
            const subjectSelect = document.getElementById('subjectSelect');
            if (!classVal) {
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                subjectSelect.disabled = true;
                return;
            }

            fetch(`get_subjects.php?class=${encodeURIComponent(classVal)}`)
                .then(response => response.json())
                .then(data => {
                    subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                    if (data.length > 0) {
                        data.forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub;
                            option.textContent = sub;
                            subjectSelect.appendChild(option);
                        });
                        subjectSelect.disabled = false;
                    } else {
                        subjectSelect.innerHTML = '<option value="">No subjects found</option>';
                        subjectSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching subjects:', error);
                    subjectSelect.innerHTML = '<option value="">Error loading</option>';
                });
        }
    </script>
</body>

</html>