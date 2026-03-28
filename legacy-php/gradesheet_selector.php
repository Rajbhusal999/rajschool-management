<?php
require 'includes/auth_school.php';
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];

// Get school info
$school_sql = "SELECT school_name, address, school_logo FROM schools WHERE id = ?";
$school_stmt = $conn->prepare($school_sql);
$school_stmt->execute([$school_id]);
$school_info = $school_stmt->fetch(PDO::FETCH_ASSOC);

// Get unique classes
$class_sql = "SELECT DISTINCT class FROM students WHERE school_id = ? ORDER BY class";
$class_stmt = $conn->prepare($class_sql);
$class_stmt->execute([$school_id]);
$classes = $class_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get current Nepali year
$current_nepali_year = date('Y') + 56;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Sheet Selector</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            display: flex;
            height: calc(100vh - 65px);
            overflow: hidden;
        }

        .sidebar {
            width: 320px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 1.5rem;
            overflow-y: auto;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.4rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 0.9rem;
            background: #f8fafc;
            transition: 0.2s;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #f8fafc;
        }

        .content-header {
            padding: 1.25rem 2rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #111827;
            margin: 0;
        }

        .scroll-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .info-card {
            background: #eef2ff;
            border-left: 4px solid #4f46e5;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            color: #4338ca;
        }

        /* Student Selection Grid */
        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }

        .student-item {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: 0.2s;
            cursor: pointer;
        }

        .student-item:hover {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .student-checkbox {
            width: 18px;
            height: 18px;
            accent-color: #4f46e5;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 700;
            color: #1e293b;
            display: block;
        }

        .student-symbol {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
        }

        .action-bar {
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 800;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: #6366f1;
            color: white;
        }

        .btn-primary:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                border-right: none;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container">
        <!-- Filter Sidebar -->
        <div class="sidebar">
            <h2 class="sidebar-title"><i class="fas fa-file-pdf"></i> Grade Sheet</h2>

            <form id="gradeSheetForm" method="GET" action="gradesheet_print.php">
                <div class="form-group">
                    <label class="form-label">Academic Year</label>
                    <input type="number" name="year" class="form-input" value="<?php echo $current_nepali_year; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label">Term / Examination</label>
                    <select name="terminal" class="form-select" required>
                        <option value="">Select Exam Type</option>
                        <option value="first_terminal">First Terminal Exam</option>
                        <option value="second_terminal">Second Terminal Exam</option>
                        <option value="third_terminal">Third Terminal Exam</option>
                        <option value="final">Final Exam</option>
                        <option value="monthly">Monthly Exam</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Select Class</label>
                    <select name="class" id="classSelect" class="form-select" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>">Class
                                <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-top: 2rem;">
                    <a href="exams.php" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-arrow-left"></i> Back to Portal
                    </a>
                </div>
            </form>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1 class="content-title">Student Selection & Bulk Actions</h1>
                <div id="selectCount"
                    style="font-size: 0.85rem; font-weight: 700; color: #64748b; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0; display: none;">
                    0 Students Selected
                </div>
            </div>

            <div class="scroll-container">
                <div class="info-card">
                    <i class="fas fa-info-circle"></i> Grade sheets are generated 2 per page (A4). Ensure marks are
                    entered before generating results.
                </div>

                <div id="studentSelection" style="display: none;">
                    <div
                        style="margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
                        <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b;">Select Students to Print</h3>
                        <label
                            style="font-size: 0.85rem; font-weight: 700; color: #4f46e5; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="selectAll" class="student-checkbox"> Select All
                        </label>
                    </div>
                    <div class="student-grid" id="studentList">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>

                <div id="emptyState" style="text-align: center; padding: 4rem 2rem;">
                    <i class="fas fa-users-viewfinder"
                        style="font-size: 3.5rem; color: #cbd5e1; margin-bottom: 1.5rem;"></i>
                    <h3 style="color: #64748b; font-weight: 700;">No Class Selected</h3>
                    <p style="color: #94a3b8; font-size: 0.9rem;">Choose a class from the sidebar to load student
                        roster.</p>
                </div>
            </div>

            <div class="action-bar">
                <button type="submit" form="gradeSheetForm" name="print_type" value="all" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Print All Class
                </button>
                <button type="submit" form="gradeSheetForm" name="print_type" value="selected" class="btn btn-primary"
                    id="printSelectedBtn" disabled>
                    <i class="fas fa-check-double"></i> Print Selected
                </button>
            </div>
        </div>
    </div>

    <script>
        const classSelect = document.getElementById('classSelect');
        const studentSelection = document.getElementById('studentSelection');
        const emptyState = document.getElementById('emptyState');
        const studentList = document.getElementById('studentList');
        const selectAll = document.getElementById('selectAll');
        const printSelectedBtn = document.getElementById('printSelectedBtn');
        const selectCount = document.getElementById('selectCount');
        const gradeSheetForm = document.getElementById('gradeSheetForm');

        classSelect.addEventListener('change', function () {
            const selectedClass = this.value;
            if (selectedClass) {
                loadStudents(selectedClass);
            } else {
                studentSelection.style.display = 'none';
                emptyState.style.display = 'block';
            }
        });

        function loadStudents(classValue) {
            fetch(`get_students_ajax.php?class=${encodeURIComponent(classValue)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.students.length > 0) {
                        studentList.innerHTML = data.students.map(student => `
                            <div class="student-item" onclick="toggleStudent('${student.id}')">
                                <input type="checkbox" name="students[]" value="${student.id}" id="chk_${student.id}" class="student-checkbox" onclick="event.stopPropagation(); updateSelection();">
                                <div class="student-info">
                                    <span class="student-name">${student.full_name}</span>
                                    <span class="student-symbol">Roll: ${student.symbol_no || 'N/A'}</span>
                                </div>
                            </div>
                        `).join('');
                        studentSelection.style.display = 'block';
                        emptyState.style.display = 'none';
                    } else {
                        studentList.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #6b7280; padding: 2rem;">No students found in this class.</p>';
                        studentSelection.style.display = 'block';
                        emptyState.style.display = 'none';
                    }
                    updateSelection();
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    studentList.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #ef4444; padding: 2rem;">Error loading students. Please try again.</p>';
                    studentSelection.style.display = 'block';
                    emptyState.style.display = 'none';
                });
        }

        function toggleStudent(id) {
            const chk = document.getElementById('chk_' + id);
            chk.checked = !chk.checked;
            updateSelection();
        }

        selectAll.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelection();
        });

        // Removed the old studentList.addEventListener as click handling is now on student-item and checkbox directly.

        function updateSelection() {
            const checkboxes = document.querySelectorAll('input[name="students[]"]');
            const checked = document.querySelectorAll('input[name="students[]"]:checked');
            const total = checkboxes.length;

            printSelectedBtn.disabled = checked.length === 0;
            selectCount.innerText = `${checked.length} Students Selected`;
            selectCount.style.display = checked.length > 0 ? 'block' : 'none';

            if (total > 0) {
                selectAll.checked = checked.length === total;
            } else {
                selectAll.checked = false; // No students, so select all should be unchecked
            }
        }

        gradeSheetForm.addEventListener('submit', function (e) {
            // Include dynamically added checkboxes manually if needed, 
            // but since they are in a Different DOM branch, we must move them or handle them.
            // Better: Append the hidden inputs to the form before subbmitting.

            const printType = e.submitter.value;
            if (printType === 'selected') {
                const checked = document.querySelectorAll('input[name="students[]"]:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Please select students');
                    return;
                }

                // Add hidden inputs to the main form because they are outside
                checked.forEach(cb => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'students[]';
                    hidden.value = cb.value;
                    gradeSheetForm.appendChild(hidden);
                });

                const hiddenType = document.createElement('input');
                hiddenType.type = 'hidden';
                hiddenType.name = 'print_type';
                hiddenType.value = 'selected';
                gradeSheetForm.appendChild(hiddenType);
            }
        });

        // Initial call to update selection state if students are pre-loaded (though not in this scenario)
        // or to set initial button state.
        updateSelection();
    </script>
</body>

</html>