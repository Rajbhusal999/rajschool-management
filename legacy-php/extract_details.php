<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];

// Get unique classes for student data filtering
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
    <title>Extract Data - Smart विद्यालय</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-gradient: linear-gradient(135deg, #10b981, #059669);
            --secondary: #6366f1;
            --secondary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
            --bg-body: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            margin: 0;
            color: #1e293b;
        }

        .main-content {
            padding: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 70px);
        }

        .header-section {
            margin-bottom: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .header-text h1 {
            font-size: 2.75rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 0.5rem 0;
            letter-spacing: -1.5px;
        }

        .header-text p {
            color: #64748b;
            font-size: 1.15rem;
            font-weight: 500;
            margin: 0;
        }

        .extraction-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
            gap: 2.5rem;
        }

        .extraction-card {
            background: white;
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .extraction-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .student-card .card-icon {
            background: #eef2ff;
            color: var(--secondary);
        }

        .teacher-card .card-icon {
            background: #ecfdf5;
            color: var(--primary);
        }

        .extraction-card h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .extraction-card p {
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 2.5rem;
            font-size: 1.05rem;
            font-weight: 500;
        }

        .form-section {
            margin-top: auto;
            background: #f8fafc;
            padding: 2.25rem;
            border-radius: 24px;
            border: 1px solid #f1f5f9;
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

        .select {
            width: 100%;
            padding: 14px 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1rem;
            background: white;
            color: #0f172a;
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 1.5rem;
            font-family: 'Outfit', sans-serif;
        }

        .select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-extract {
            width: 100%;
            padding: 1.1rem;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .student-btn {
            background: var(--secondary-gradient);
            color: white;
        }

        .teacher-btn {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-extract:hover {
            transform: translateY(-3px) scale(1.02);
            filter: brightness(1.1);
            box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.15);
        }

        .tool-link {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.2s;
            padding: 10px 15px;
            border-radius: 12px;
            background: white;
            border: 1px solid #f1f5f9;
        }

        .student-tool-link {
            color: var(--secondary);
        }

        .student-tool-link:hover {
            background: #eef2ff;
            transform: translateX(5px);
        }

        .teacher-tool-link {
            color: var(--primary);
        }

        .teacher-tool-link:hover {
            background: #ecfdf5;
            transform: translateX(5px);
        }

        .caste-box {
            margin-top: 2rem;
            padding: 2rem;
            background: white;
            border-radius: 24px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2.5rem;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .breadcrumb a {
            color: var(--secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }

            .extraction-grid {
                grid-template-columns: 1fr;
            }

            .header-text h1 {
                font-size: 2.25rem;
            }

            .extraction-card {
                padding: 2rem;
                border-radius: 24px;
            }

            .form-section {
                padding: 1.5rem;
            }
        }
    </style>

    @media (max-width: 768px) {
    .extraction-grid {
    grid-template-columns: 1fr;
    }

    .header-section h1 {
    font-size: 2rem;
    }

    .main-content {
    padding: 2rem 1rem;
    }

    .extraction-card {
    padding: 1.5rem;
    }
    }
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div class="breadcrumb">
            <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-chevron-right" style="font-size: 0.7rem; color: #94a3b8;"></i>
            <span style="color: #64748b;">Extract Data Bridge</span>
        </div>

        <div class="header-section">
            <div class="header-text">
                <h1>Data Extraction</h1>
                <p>Securely export institutional records and generate official documentation.</p>
            </div>
        </div>

        <div class="extraction-grid">
            <!-- Student Data Card -->
            <div class="extraction-card student-card">
                <div class="card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h2>Student Intelligence</h2>
                <p>Extract comprehensive student rosters, performance metrics, and academic profiles.</p>

                <?php if (hasFeature('students')): ?>
                    <div class="form-section">
                        <form action="export_students.php" method="GET">
                            <div class="form-group">
                                <label class="label">Academic Class</label>
                                <select name="class" class="select">
                                    <option value="">All Institutional Classes</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo htmlspecialchars($class); ?>">Class
                                            <?php echo htmlspecialchars($class); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-extract student-btn">
                                <i class="fas fa-file-excel"></i> Export Student Ledger
                            </button>
                        </form>

                        <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 10px;">
                            <?php if (hasFeature('id_cards')): ?>
                                <a href="id_card_selector.php" class="tool-link student-tool-link">
                                    <i class="fas fa-id-badge"></i> Generate Identification Cards
                                </a>
                            <?php endif; ?>

                            <?php if (hasFeature('exams')): ?>
                                <a href="admit_card_selector.php" class="tool-link student-tool-link">
                                    <i class="fas fa-file-signature"></i> Generate Terminal Admit Cards
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="caste-box">
                            <h4
                                style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 1.25rem 0; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-users-viewfinder" style="color: var(--secondary);"></i> Demographic
                                Extraction
                            </h4>
                            <form action="export_caste_data.php" method="GET">
                                <div class="form-group">
                                    <label class="label" style="font-size: 0.75rem;">Target Class</label>
                                    <select name="class" class="select" style="margin-bottom: 1rem;">
                                        <option value="">Full Demographic View</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo htmlspecialchars($class); ?>">Class
                                                <?php echo htmlspecialchars($class); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-extract student-btn"
                                    style="padding: 12px; font-size: 0.95rem;">
                                    <i class="fas fa-filter"></i> Extract Caste Analytics
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Teacher Data Card -->
            <div class="extraction-card teacher-card">
                <div class="card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h2>Faculty Intelligence</h2>
                <p>Export staff directories, professional profiles, and departmental assignments.</p>

                <?php if (hasFeature('teachers')): ?>
                    <div class="form-section">
                        <form action="export_teachers.php" method="GET">
                            <div class="form-group">
                                <label class="label">Staff Designation</label>
                                <select name="type_filter" class="select">
                                    <option value="">Entire Faculty Roster</option>
                                    <option value="Permanent">Permanent Establishment</option>
                                    <option value="Temporary">Temporary Engagement</option>
                                    <option value="Internal source">Internal Resource</option>
                                    <option value="School source">Institutional Source</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-extract teacher-btn">
                                <i class="fas fa-file-invoice"></i> Export Faculty Directory
                            </button>
                        </form>

                        <div style="margin-top: 1.5rem;">
                            <?php if (hasFeature('id_cards')): ?>
                                <a href="id_card_selector.php?type=teacher" class="tool-link teacher-tool-link">
                                    <i class="fas fa-id-badge"></i> Generate Faculty ID Cards
                                </a>
                            <?php endif; ?>
                        </div>

                        <div
                            style="margin-top: 2rem; padding: 2rem; background: rgba(16, 185, 129, 0.03); border: 1px dashed rgba(16, 185, 129, 0.2); border-radius: 24px;">
                            <p style="font-size: 0.85rem; color: #64748b; margin: 0; font-weight: 500; line-height: 1.5;">
                                <i class="fas fa-info-circle" style="color: var(--primary);"></i> Staff extractions include
                                personal details, contact information, and academic qualifications.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>