<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Define class groups
$class_groups = ['PG', 'LKG', 'NURSERY', '1-3', '4-5', '6-8', '9-10'];

// Get selected class group
$selected_class_group = isset($_GET['class_group']) ? $_GET['class_group'] : '';

// Handle Add Subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $class_group = $_POST['class_group'];
    $subject_name = $_POST['subject_name'];
    $credit_hour = $_POST['credit_hour'];
    $subject_code = $_POST['subject_code'];

    $sql = "INSERT INTO subjects (school_id, class_group, subject_name, credit_hour, subject_code) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$school_id, $class_group, $subject_name, $credit_hour, $subject_code])) {
        header("Location: subjects.php?class_group=$class_group&msg=" . urlencode("Subject added successfully!"));
        exit();
    } else {
        $msg = "Error adding subject.";
    }
}

// Handle Edit Subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_subject'])) {
    $id = $_POST['subject_id'];
    $class_group = $_POST['class_group'];
    $subject_name = $_POST['subject_name'];
    $credit_hour = $_POST['credit_hour'];
    $subject_code = $_POST['subject_code'];

    $sql = "UPDATE subjects SET class_group = ?, subject_name = ?, credit_hour = ?, subject_code = ? WHERE id = ? AND school_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$class_group, $subject_name, $credit_hour, $subject_code, $id, $school_id])) {
        header("Location: subjects.php?class_group=$class_group&msg=" . urlencode("Subject updated successfully!"));
        exit();
    } else {
        $msg = "Error updating subject.";
    }
}

// Handle Delete Subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_subject'])) {
    $id = $_POST['subject_id'];
    $class_group = $_POST['class_group'];
    $sql = "DELETE FROM subjects WHERE id = ? AND school_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$id, $school_id])) {
        header("Location: subjects.php?class_group=$class_group&msg=" . urlencode("Subject deleted successfully!"));
        exit();
    } else {
        $msg = "Error deleting subject.";
    }
}

// Fetch Subjects for selected class group
$subjects = [];
if (!empty($selected_class_group)) {
    $sql = "SELECT * FROM subjects WHERE school_id = ? AND class_group = ? ORDER BY subject_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $selected_class_group]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$is_early_years = in_array(strtoupper($selected_class_group), ['PG', 'LKG', 'NURSERY', 'KG', 'UKG']);
$credit_label = $is_early_years ? "Total Marks" : "Credit Hour";
$input_attrs = $is_early_years ? 'min="1" step="1"' : 'min="0.5" step="0.5"';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .main-content {
            padding: 1.5rem 1rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 1.25rem 2rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-left: 6px solid #8b5cf6;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
        }

        .class-selector {
            background: white;
            padding: 1.25rem;
            border-radius: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .class-groups {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .class-group-btn {
            padding: 0.625rem 1.25rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 600;
            font-size: 0.9rem;
            color: #475569;
        }

        .class-group-btn:hover {
            border-color: #8b5cf6;
            background: #f5f3ff;
            color: #8b5cf6;
        }

        .class-group-btn.active {
            background: #8b5cf6;
            color: white;
            border-color: #8b5cf6;
        }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }

        .subject-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            border: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: #ddd6fe;
        }

        .subject-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }

        .credit-badge {
            background: #f5f3ff;
            color: #7c3aed;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-action {
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-edit {
            background: #eff6ff;
            color: #3b82f6;
        }

        .btn-delete {
            background: #fff1f2;
            color: #e11d48;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            width: 95%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            transform: translateY(20px);
            transition: 0.3s;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 1rem;
        }

        .modal.show {
            display: flex;
        }

        .modal-header {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: 0.2s;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #8b5cf6;
            background: white;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-cancel {
            flex: 1;
            padding: 0.75rem;
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .btn-submit {
            flex: 2;
            background: #8b5cf6;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.3);
        }

        .btn-submit:hover {
            background: #7c3aed;
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.4);
        }

        .success-message {
            background: #f0fdf4;
            color: #166534;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: 1px solid #bbf7d0;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .empty-state {
            background: white;
            padding: 4rem 2rem;
            border-radius: 24px;
            text-align: center;
            border: 2px dashed #e2e8f0;
        }

        .empty-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .subject-code {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .subject-header {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .subject-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .selector-title {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <div class="main-content">
            <div class="header">
                <div>
                    <h1 class="page-title"><i class="fas fa-book-open"></i> Curriculum Management</h1>
                </div>
                <?php if (!empty($selected_class_group)): ?>
                    <button onclick="document.getElementById('addModal').classList.add('show')" class="btn-action"
                        style="background: #8b5cf6; color: white;">
                        <i class="fas fa-plus"></i> Add New Subject
                    </button>
                <?php endif; ?>
            </div>

            <!-- Class Group Selector -->
            <div class="class-selector">
                <h3 class="selector-title">
                    <i class="fas fa-layer-group"></i> Select Class Group
                </h3>
                <div class="class-groups">
                    <?php foreach ($class_groups as $group): ?>
                        <button onclick="window.location.href='subjects.php?class_group=<?php echo urlencode($group); ?>'"
                            class="class-group-btn <?php echo ($selected_class_group == $group) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($group); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="success-message" id="successMessage">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php endif; ?>

            <?php if (empty($selected_class_group)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-hand-pointer"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #4b5563; margin-bottom: 0.5rem;">Select a Class
                        Group</h3>
                    <p style="color: #6b7280;">Choose a class group above to view and manage subjects</p>
                </div>
            <?php elseif (empty($subjects)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #4b5563; margin-bottom: 0.5rem;">No Subjects for
                        <?php echo htmlspecialchars($selected_class_group); ?>
                    </h3>
                    <p style="color: #6b7280;">Click "Add Subject" to create subjects for this class group</p>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #1f2937;">
                        Subjects for Class <?php echo htmlspecialchars($selected_class_group); ?>
                    </h3>
                </div>
                <div class="subjects-grid">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="subject-card">
                            <div class="subject-header">
                                <div class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                                <?php if (!empty($subject['subject_code'])): ?>
                                    <div class="subject-code"><?php echo htmlspecialchars($subject['subject_code']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="subject-details">
                                <div class="credit-badge">
                                    <i class="fas fa-<?php echo $is_early_years ? 'star' : 'clock'; ?>"></i>
                                    <?php echo htmlspecialchars($subject['credit_hour']); ?>
                                    <?php echo $is_early_years ? "Total Marks" : "Credit Hour(s)"; ?>
                                </div>
                            </div>

                            <div class="subject-actions">
                                <button class="btn-action btn-edit"
                                    onclick='openEditModal(<?php echo json_encode($subject, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this subject?')">
                                    <input type="hidden" name="delete_subject" value="1">
                                    <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                    <input type="hidden" name="class_group"
                                        value="<?php echo htmlspecialchars($selected_class_group); ?>">
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Add Subject for <?php echo htmlspecialchars($selected_class_group); ?></h2>
            <form method="POST">
                <input type="hidden" name="add_subject" value="1">
                <input type="hidden" name="class_group" value="<?php echo htmlspecialchars($selected_class_group); ?>">

                <div class="form-group">
                    <label class="form-label">Subject Name *</label>
                    <input type="text" name="subject_name" class="form-input" placeholder="e.g., Mathematics" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Subject Code</label>
                    <input type="text" name="subject_code" class="form-input" placeholder="e.g., MATH101">
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo $credit_label; ?> *</label>
                    <input type="number" name="credit_hour" class="form-input"
                        placeholder="e.g., <?php echo $is_early_years ? '100' : '4'; ?>" <?php echo $input_attrs; ?>
                        required>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="document.getElementById('addModal').classList.remove('show')"
                        class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-submit">Add Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Edit Subject</h2>
            <form method="POST">
                <input type="hidden" name="edit_subject" value="1">
                <input type="hidden" name="subject_id" id="edit_subject_id">
                <input type="hidden" name="class_group" id="edit_class_group">

                <div class="form-group">
                    <label class="form-label">Subject Name *</label>
                    <input type="text" name="subject_name" id="edit_subject_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Subject Code</label>
                    <input type="text" name="subject_code" id="edit_subject_code" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo $credit_label; ?> *</label>
                    <input type="number" name="credit_hour" id="edit_credit_hour" class="form-input" <?php echo $input_attrs; ?> required>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="document.getElementById('editModal').classList.remove('show')"
                        class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-submit">Update Subject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(data) {
            document.getElementById('edit_subject_id').value = data.id;
            document.getElementById('edit_class_group').value = data.class_group;
            document.getElementById('edit_subject_name').value = data.subject_name;
            document.getElementById('edit_subject_code').value = data.subject_code || '';
            document.getElementById('edit_credit_hour').value = data.credit_hour;
            document.getElementById('editModal').classList.add('show');
        }

        // Auto-dismiss success message
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.transition = 'opacity 0.5s ease';
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 500);
            }, 3000);
        }
    </script>
</body>

</html>