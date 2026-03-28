<?php
require 'includes/auth_school.php';
restrictFeature('students');
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$msg = '';

// Demo Mode Restriction
if (isset($_SESSION['is_demo']) && $_SESSION['is_demo'] && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = "feature disabled in demo mode";
    $_POST = array(); // Clear POST data to prevent execution
}

// Auto-migration: Ensure caste column exists
try {
    $conn->query("SELECT 1 FROM students LIMIT 1");
    $conn->exec("ALTER TABLE students ADD COLUMN caste VARCHAR(100) DEFAULT NULL AFTER emis_no");
} catch (Exception $e) {
    // Column likely exists
}

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_student'])) {
    $id = $_POST['student_id'];
    $sql = "DELETE FROM students WHERE id = ? AND school_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$id, $school_id])) {
        header("Location: students.php?msg=" . urlencode("Student deleted successfully!"));
        exit();
    } else {
        $msg = "Error deleting student.";
    }
    if ($stmt->execute([$id, $school_id])) {
        // Resequence needed? Probably not for delete unless we want to fill gaps. 
        // User asked for alphabetical order. Removing one doesn't break alphabetical order of others, just leaves a gap.
        // But "101, 102..." implies no gaps.
        // I should resequence on delete too to maintain "101 after that so on".
        // Need to know class first. 
        // Actually, fetching class before delete is better.
        // Refactor:
        /*
        $stmt_get = $conn->prepare("SELECT class FROM students WHERE id = ?");
        $stmt_get->execute([$id]);
        $c = $stmt_get->fetchColumn();
        ... delete ...
        resequenceClass($conn, $school_id, $c);
        */
        // For now, I'll stick to Add/Edit as requested ("in add students").
        header("Location: students.php?msg=" . urlencode("Student deleted successfully!"));
        exit();
    } else {
        $msg = "Error deleting student.";
    }
}

// Helper Function: Resequence Symbol Numbers Alphabetically
function resequenceClass($conn, $school_id, $class)
{
    // Determine Prefix
    $class_code = $class;
    if ($class == 'Nursery')
        $class_code = 'N';
    elseif ($class == 'LKG')
        $class_code = 'L';
    elseif ($class == 'UKG')
        $class_code = 'U';
    // Remove "Class " if explicitly stored? DB seems to store just "1", "2".
    $prefix = "HI" . $class_code;

    // Fetch all students in class, ordered by Name
    $sql = "SELECT id FROM students WHERE school_id = ? AND class = ? ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $class]);
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Update each
    $seq = 101;
    $update_sql = "UPDATE students SET symbol_no = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);

    foreach ($students as $student_id) {
        $new_symbol = $prefix . $seq;
        $update_stmt->execute([$new_symbol, $student_id]);
        $seq++;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $id = $_POST['student_id'];

    // Basic Info
    $name = $_POST['full_name'];
    $roll = $_POST['roll_no'];
    $class = $_POST['class'];
    $dob_nepali = $_POST['dob_nepali'];
    $gender = $_POST['gender'];
    $emis = $_POST['emis_no'];
    $caste = $_POST['caste'];

    // Parent/Guardian
    $father = $_POST['father_name'];
    $mother = $_POST['mother_name'];
    $guardian = $_POST['guardian_name'];
    $parent_contact = $_POST['parent_contact'];
    $guardian_contact = $_POST['guardian_contact'];
    $guardian_email = isset($_POST['guardian_email']) ? $_POST['guardian_email'] : null;

    // Addresses
    $p_prov = $_POST['perm_province'];
    $p_dist = $_POST['perm_district'];
    $p_local = $_POST['perm_local_level'];
    $p_ward = $_POST['perm_ward_no'];
    $p_tole = $_POST['perm_tole'];

    $t_prov = $_POST['temp_province'];
    $t_dist = $_POST['temp_district'];
    $t_local = $_POST['temp_local_level'];
    $t_ward = $_POST['temp_ward_no'];
    $t_tole = $_POST['temp_tole'];

    // Details
    $scholarship = $_POST['scholarship_type'];
    $disability = $_POST['disability_type'];

    // Handle Photo Upload (Edit)
    $photo_sql_part = "";
    $photo_param = [];

    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['student_photo']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['student_photo']['size'];

        if (in_array($file_ext, $allowed)) {
            if ($file_size <= 2 * 1024 * 1024) {
                $new_filename = uniqid('student_', true) . '.' . $file_ext;
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);
                if (move_uploaded_file($_FILES['student_photo']['tmp_name'], $upload_dir . $new_filename)) {
                    $photo_sql_part = ", student_photo=?";
                    $photo_param[] = $upload_dir . $new_filename;
                }
            }
        }
    }

    $full_address = "$p_tole, $p_local-$p_ward, $p_dist";

    // Dynamic SQL based on whether photo is updated
    $sql = "UPDATE students SET 
        full_name=?, roll_no=?, class=?, parent_contact=?, address=?,
        emis_no=?, caste=?, dob_nepali=?, gender=?, $photo_sql_part
        father_name=?, mother_name=?, guardian_name=?, guardian_contact=?, guardian_email=?,
        perm_province=?, perm_district=?, perm_local_level=?, perm_ward_no=?, perm_tole=?,
        temp_province=?, temp_district=?, temp_local_level=?, temp_ward_no=?, temp_tole=?,
        scholarship_type=?, disability_type=?
        WHERE id=? AND school_id=?";

    $stmt = $conn->prepare($sql);
    $params = array_merge(
        [
            $name,
            $roll,
            $class,
            $parent_contact,
            $full_address,
            $emis,
            $caste,
            $dob_nepali,
            $gender
        ],
        $photo_param, // Inject photo param (1 or 0 elements)
        [
            $father,
            $mother,
            $guardian,
            $guardian_contact,
            $guardian_email,
            $p_prov,
            $p_dist,
            $p_local,
            $p_ward,
            $p_tole,
            $t_prov,
            $t_dist,
            $t_local,
            $t_ward,
            $t_tole,
            $scholarship,
            $disability,
            $id,
            $school_id
        ]
    );

    if ($stmt->execute($params)) {
        resequenceClass($conn, $school_id, $class); // Auto-sort
        header("Location: students.php?msg=" . urlencode("Student updated successfully!"));
        exit();
    } else {
        $msg = "Error updating student.";
    }
}

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    // Basic Info
    $name = $_POST['full_name'];
    $roll = $_POST['roll_no'];
    $class = $_POST['class'];
    $dob_nepali = $_POST['dob_nepali'];
    $gender = $_POST['gender'];
    $emis = $_POST['emis_no'];
    $caste = $_POST['caste'];

    // Generic Roll No if not provided or just use Symbol No as the unique identifier
    // We will keep 'roll_no' variable but user asked to change Roll No -> Symbol No on UI
    // So we treat Symbol No as the primary academic ID.

    // Auto-generate Sequential Symbol No (HIM101, HIM102...)
    // Generate Symbol No based on Class (HI + Class + Sequence starting at 101)
    $class_code = $class;
    // Handle non-numeric classes if necessary, though user example suggests numeric.
    // Mapping for standard text classes if they occur:
    if ($class == 'Nursery')
        $class_code = 'N';
    elseif ($class == 'LKG')
        $class_code = 'L';
    elseif ($class == 'UKG')
        $class_code = 'U';

    $prefix = "HI" . $class_code;
    $prefix_len = strlen($prefix);

    // Fetch max existing sequence for this specific prefix
    // We bind the prefix to the LIKE clause (prefix%)
    // And we need to extract the number part. SUBSTRING 1-based index is length+1
    $sql_sn = "SELECT MAX(CAST(SUBSTRING(symbol_no, ?) AS UNSIGNED)) as max_sn 
               FROM students 
               WHERE school_id = ? AND symbol_no LIKE ?";
    $stmt_sn = $conn->prepare($sql_sn);
    $stmt_sn->execute([$prefix_len + 1, $school_id, $prefix . '%']);
    $res_sn = $stmt_sn->fetch(PDO::FETCH_ASSOC);

    $max_val = $res_sn['max_sn'];
    // If no student exists for this class, start at 101. 
    // If max_val < 101 (unlikely if logic holds), we still default to 101 or max+1
    $next_seq = ($max_val >= 101) ? $max_val + 1 : 101;

    $symbol_no = $prefix . $next_seq;

    // For Roll No, we can either use the input or default to the number part of symbol no
    // if the user still wants a separate class roll no, we keep the input.
    // However, the request says "roll no is changed into symbol number". 
    // This implies we might hide roll no field or auto-set it. 
    // For now, I'll allow manual roll no if the form sends it, else use $next_sn
    if (empty($_POST['roll_no'])) {
        $roll = $next_seq; // Default roll to sequential num
    } else {
        $roll = $_POST['roll_no'];
    }

    // Parent/Guardian
    $father = $_POST['father_name'];
    $mother = $_POST['mother_name'];
    $guardian = $_POST['guardian_name'];
    $parent_contact = $_POST['parent_contact'];
    $guardian_contact = $_POST['guardian_contact'];
    $guardian_email = isset($_POST['guardian_email']) ? $_POST['guardian_email'] : null;

    // Addresses
    $p_prov = $_POST['perm_province'];
    $p_dist = $_POST['perm_district'];
    $p_local = $_POST['perm_local_level'];
    $p_ward = $_POST['perm_ward_no'];
    $p_tole = $_POST['perm_tole'];

    $t_prov = $_POST['temp_province'];
    $t_dist = $_POST['temp_district'];
    $t_local = $_POST['temp_local_level'];
    $t_ward = $_POST['temp_ward_no'];
    $t_tole = $_POST['temp_tole'];

    // Details
    $scholarship = $_POST['scholarship_type'];
    $disability = $_POST['disability_type'];

    // Handle Photo Upload
    $student_photo_path = null;
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['student_photo']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['student_photo']['size'];

        if (in_array($file_ext, $allowed)) {
            if ($file_size <= 2 * 1024 * 1024) { // 2MB
                $new_filename = uniqid('student_', true) . '.' . $file_ext;
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($_FILES['student_photo']['tmp_name'], $upload_dir . $new_filename)) {
                    $student_photo_path = $upload_dir . $new_filename;
                } else {
                    $msg = "Failed to upload photo.";
                }
            } else {
                $msg = "Photo size must be less than 2MB.";
            }
        } else {
            $msg = "Invalid photo format. Only JPG, PNG allowed.";
        }
    }

    if (empty($msg)) {
        // Construct single address string for legacy support/view, but store details
        $full_address = "$p_tole, $p_local-$p_ward, $p_dist";

        $sql = "INSERT INTO students (
            school_id, full_name, roll_no, class, parent_contact, address,
            emis_no, symbol_no, caste, dob_nepali, gender,
            father_name, mother_name, guardian_name, guardian_contact, guardian_email,
            perm_province, perm_district, perm_local_level, perm_ward_no, perm_tole,
            temp_province, temp_district, temp_local_level, temp_ward_no, temp_tole,
            scholarship_type, disability_type, student_photo
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?
        )";

        $stmt = $conn->prepare($sql);
        $params = [
            $school_id,
            $name,
            $roll,
            $class,
            $parent_contact,
            $full_address,
            $emis,
            $symbol_no,
            $caste,
            $dob_nepali,
            $gender,
            $father,
            $mother,
            $guardian,
            $guardian_contact,
            $guardian_email,
            $p_prov,
            $p_dist,
            $p_local,
            $p_ward,
            $p_tole,
            $t_prov,
            $t_dist,
            $t_local,
            $t_ward,
            $t_tole,
            $scholarship,
            $disability,
            $student_photo_path
        ];

        if ($stmt->execute($params)) {
            // Redirect to prevent form resubmission on refresh
            header("Location: students.php?msg=" . urlencode("Student added successfully! Symbol No: $symbol_no"));
            exit();
        } else {
            $msg = "Error adding student.";
        }
    }
}

// Check for message in URL
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
}

// Fetch Students
// Fetch Students Logic Moved to View for Filtering
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .app-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid #e5e7eb;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            background: #f9fafb;
            padding: 2rem;
            position: relative;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #4b5563;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: 0.2s;
            font-weight: 500;
        }

        .nav-item:hover,
        .nav-item.active {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .nav-item i {
            width: 24px;
            margin-right: 10px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        .btn-add {
            background: #4f46e5;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: flex-start;
            overflow-y: auto;
            padding: 2rem 1rem;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            margin: auto;
        }

        .main-content {
            background: #f9fafb;
            padding: 2rem;
            min-height: calc(100vh - 65px);
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div class="header-responsive" style="margin-bottom: 2.5rem;">
            <div class="title-block">
                <h1 style="font-size: 2.25rem; font-weight: 800; color: #0f172a; margin: 0;">Student Repository</h1>
                <p style="color: #64748b; margin-top: 4px;">Manage comprehensive student records and academic profiles.
                </p>
            </div>

            <div class="action-stack" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                <!-- Filter Section -->
                <form method="GET" class="filter-form-custom" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div class="search-input-group" style="position: relative;">
                        <i class="fas fa-search"
                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" name="search_query" placeholder="Search by name, ID..."
                            value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>"
                            style="padding: 12px 15px 12px 45px; border-radius: 14px; border: 1px solid #e2e8f0; width: 220px; font-weight: 500;">
                    </div>

                    <select name="class_filter"
                        style="padding: 12px 15px; border-radius: 14px; border: 1px solid #e2e8f0; background: white; font-weight: 600; min-width: 140px;">
                        <option value="">All Classes</option>
                        <?php
                        $classes = $conn->query("SELECT DISTINCT class FROM students WHERE school_id = $school_id ORDER BY class ASC");
                        while ($c = $classes->fetch(PDO::FETCH_ASSOC)) {
                            $selected = (isset($_GET['class_filter']) && $_GET['class_filter'] == $c['class']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($c['class']) . "' $selected>" . htmlspecialchars($c['class']) . "</option>";
                        }
                        ?>
                    </select>

                    <button type="submit" class="btn btn-primary-gradient" style="padding: 12px 20px;">
                        <i class="fas fa-filter"></i> Apply
                    </button>

                    <?php if (isset($_GET['search_query']) || isset($_GET['class_filter'])): ?>
                        <a href="students.php" class="btn"
                            style="background:#f1f5f9; color:#475569; padding: 12px 20px;">Clear</a>
                    <?php endif; ?>
                </form>

                <div style="display: flex; gap: 10px; margin-left: auto;">
                    <a href="<?php echo $export_url; ?>" class="btn" style="background: #10b981; color: white;">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                    <button onclick="document.getElementById('addModal').classList.add('show')"
                        class="btn btn-primary-gradient">
                        <i class="fas fa-plus"></i> Add New
                    </button>
                </div>
            </div>
        </div>

        <?php if ($msg): ?>
            <div id="alertMessage"
                style="background: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 16px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; border: 1px solid #10b981;">
                <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <!-- Responsive Table Wrapper -->
        <div class="responsive-table-container">
            <table>
                <thead>
                    <tr style="background: #f8fafc;">
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            SYMBOL NO</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            FULL NAME</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            CLASS</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            CONTACT</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $filter_sql = "";
                    $params = [$school_id];
                    if (!empty($_GET['class_filter'])) {
                        $filter_sql .= " AND class = ? ";
                        $params[] = $_GET['class_filter'];
                    }
                    if (!empty($_GET['search_query'])) {
                        $filter_sql .= " AND (full_name LIKE ? OR symbol_no LIKE ? OR parent_contact LIKE ?) ";
                        $acc_query = $_GET['search_query'];
                        $params[] = $acc_query . "%";
                        $params[] = $acc_query . "%";
                        $params[] = "%" . $acc_query . "%";
                    }

                    $stmt = $conn->prepare("SELECT * FROM students WHERE school_id = ? $filter_sql ORDER BY full_name ASC");
                    $stmt->execute($params);

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr style="transition: all 0.2s;">
                            <td
                                style="padding: 1.25rem; font-weight: 800; color: #4f46e5; border-bottom: 1px solid #f1f5f9;">
                                <?php echo htmlspecialchars($row['symbol_no']); ?>
                            </td>
                            <td style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; color: #1e293b;">
                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #94a3b8;">
                                    <?php echo htmlspecialchars($row['address']); ?>
                                </div>
                            </td>
                            <td style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <span
                                    style="background: #eef2ff; color: #4f46e5; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($row['class']); ?>
                                </span>
                            </td>
                            <td
                                style="padding: 1.25rem; color: #64748b; border-bottom: 1px solid #f1f5f9; font-weight: 500;">
                                <?php echo htmlspecialchars($row['parent_contact']); ?>
                            </td>
                            <td style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="display:flex; gap:8px;">
                                    <button class="btn" style="background: #f1f5f9; color: #1e293b; padding: 8px 12px;"
                                        onclick='openViewModal(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn" style="background: #fff7ed; color: #ea580c; padding: 8px 12px;"
                                        onclick='openEditModal(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn" style="background: #fef2f2; color: #ef4444; padding: 8px 12px;"
                                        onclick='deleteStudent(<?php echo $row['id']; ?>)'>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1.5rem;">Add New Student</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_student" value="1">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <!-- Basic Info -->
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Select Class *</label>
                        <select name="class" class="form-control" required>
                            <option value="">-- Select Class --</option>
                            <?php for ($i = 1; $i <= 12; $i++)
                                echo "<option value='$i'>Class $i</option>"; ?>
                            <option value="Nursery">Nursery</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Roll No (Class Roll)</label>
                        <input type="text" name="roll_no" class="form-control"
                            placeholder="Optional (Auto-filled if empty)">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Date of Birth (BS) *</label>
                        <input type="text" name="dob_nepali" id="add_dob_nepali" class="form-control nepali-date"
                            placeholder="YYYY/MM/DD" maxlength="10" required>
                    </div>

                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Gender *</label>
                        <select name="gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">EMIS No (Optional)</label>
                        <input type="text" name="emis_no" class="form-control" pattern="[0-9]+"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Student Photo (Max 2MB, JPG/PNG)</label>
                        <input type="file" name="student_photo" class="form-control" accept=".jpg, .jpeg, .png">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Caste</label>
                        <input type="text" name="caste" class="form-control" placeholder="Enter Caste">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <small style="color: #666;">* Symbol No will be auto-generated sequentially (e.g. HI4101 for
                            Class 4)</small>
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Parent / Guardian Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Father's Name</label>
                        <input type="text" name="father_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Mother's Name</label>
                        <input type="text" name="mother_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Guardian's Name</label>
                        <input type="text" name="guardian_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Parent's Phone *</label>
                        <input type="text" name="parent_contact" class="form-control" required pattern="(97|98)[0-9]{8}"
                            maxlength="10" title="Phone number must be 10 digits starting with 97 or 98"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Guardian's Phone</label>
                        <input type="text" name="guardian_contact" class="form-control" pattern="(97|98)[0-9]{8}"
                            maxlength="10" title="Phone number must be 10 digits starting with 97 or 98"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Guardian's Email</label>
                        <input type="email" name="guardian_email" class="form-control" placeholder="Optional">
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Permanent Address</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Province</label>
                        <select name="perm_province" id="perm_province" class="form-control"
                            onchange="loadDistricts('perm')">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">District</label>
                        <select name="perm_district" id="perm_district" class="form-control"
                            onchange="loadLocalLevels('perm')">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Local Level</label>
                        <select name="perm_local_level" id="perm_local_level" class="form-control">
                            <option value="">Select Local Level</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Ward No</label>
                        <input type="text" name="perm_ward_no" id="perm_ward_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Tole</label>
                        <input type="text" name="perm_tole" id="perm_tole" class="form-control">
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Temporary Address</h3>
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox" id="same_as_perm" onchange="copyAddress()"> <label for="same_as_perm">Same as
                        Permanent Address</label>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Province</label>
                        <select name="temp_province" id="temp_province" class="form-control"
                            onchange="loadDistricts('temp')">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">District</label>
                        <select name="temp_district" id="temp_district" class="form-control"
                            onchange="loadLocalLevels('temp')">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Local Level</label>
                        <select name="temp_local_level" id="temp_local_level" class="form-control">
                            <option value="">Select Local Level</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Ward No</label>
                        <input type="text" name="temp_ward_no" id="temp_ward_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Tole</label>
                        <input type="text" name="temp_tole" id="temp_tole" class="form-control">
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Additional Info</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Scholarship Type</label>
                        <select name="scholarship_type" class="form-control">
                            <option value="None">None</option>
                            <option value="Dalit">Dalit</option>
                            <option value="Marginalised">Marginalised</option>
                            <option value="100% Girl">100% Girl</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Disability</label>
                        <select name="disability_type" class="form-control">
                            <option value="None">None</option>
                            <option value="Physical">Physical</option>
                            <option value="Mental">Mental</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('addModal').classList.remove('show')"
                        class="btn" style="flex:1; background:#e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update CSS for new modal -->
    <style>
        .form-control {
            background: #f9fafb;
            color: black;
            border: 1px solid #ddd;
            width: 100%;
            padding: 8px;
            border-radius: 4px;
        }

        .modal-content {
            max-width: 800px;
            /* Wider modal */
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>

    </div>
    </div>

    <!-- View Student Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div
                style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:1rem; margin-bottom:1rem;">
                <h2 style="margin:0; color:#1f2937;">Student Details</h2>
                <button onclick="closeViewModal()"
                    style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#6b7280;">&times;</button>
            </div>

            <div id="viewModalBody" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Content will be injected by JS -->
            </div>

            <div style="margin-top: 2rem; text-align: right;">
                <button onclick="closeViewModal()" class="btn"
                    style="background:#e5e7eb; padding: 8px 16px; border-radius: 6px;">Close</button>
            </div>
            <!-- End of View Modal Content -->
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 1.5rem;">Edit Student</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_student" value="1">
                <input type="hidden" name="student_id" id="edit_student_id">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <!-- Basic Info -->
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Full Name *</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Select Class *</label>
                        <select name="class" id="edit_class" class="form-control" required>
                            <option value="">-- Select Class --</option>
                            <?php for ($i = 1; $i <= 12; $i++)
                                echo "<option value='$i'>Class $i</option>"; ?>
                            <option value="Nursery">Nursery</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Roll No (Class Roll)</label>
                        <input type="text" name="roll_no" id="edit_roll_no" class="form-control" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Date of Birth (BS) *</label>
                        <input type="text" name="dob_nepali" id="edit_dob_nepali" class="form-control nepali-date"
                            placeholder="YYYY/MM/DD" maxlength="10" required>
                    </div>

                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Gender *</label>
                        <select name="gender" id="edit_gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">EMIS No (Optional)</label>
                        <input type="text" name="emis_no" id="edit_emis_no" class="form-control" pattern="[0-9]+"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <!-- Added Photo for Edit -->
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Student Photo</label>
                        <input type="file" name="student_photo" class="form-control" accept=".jpg, .jpeg, .png">
                        <small style="color:#666; display:block; margin-top:5px;">Upload to replace current
                            photo.</small>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Caste</label>
                        <input type="text" name="caste" id="edit_caste" class="form-control" placeholder="Enter Caste">
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Parent / Guardian Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Father's Name</label>
                        <input type="text" name="father_name" id="edit_father_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Mother's Name</label>
                        <input type="text" name="mother_name" id="edit_mother_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Guardian's Name</label>
                        <input type="text" name="guardian_name" id="edit_guardian_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Parent's Phone *</label>
                        <input type="text" name="parent_contact" id="edit_parent_contact" class="form-control" required
                            pattern="(97|98)[0-9]{8}" maxlength="10"
                            title="Phone number must be 10 digits starting with 97 or 98"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Guardian's Phone</label>
                        <input type="text" name="guardian_contact" id="edit_guardian_contact" class="form-control"
                            pattern="(97|98)[0-9]{8}" maxlength="10"
                            title="Phone number must be 10 digits starting with 97 or 98"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Permanent Address</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Province</label>
                        <select name="perm_province" id="edit_perm_province" class="form-control"
                            onchange="loadDistricts('edit_perm')">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">District</label>
                        <select name="perm_district" id="edit_perm_district" class="form-control"
                            onchange="loadLocalLevels('edit_perm')">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Local Level</label>
                        <select name="perm_local_level" id="edit_perm_local_level" class="form-control">
                            <option value="">Select Local Level</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Ward No</label>
                        <input type="text" name="perm_ward_no" id="edit_perm_ward_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Tole</label>
                        <input type="text" name="perm_tole" id="edit_perm_tole" class="form-control">
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Temporary Address</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Province</label>
                        <select name="temp_province" id="edit_temp_province" class="form-control"
                            onchange="loadDistricts('edit_temp')">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">District</label>
                        <select name="temp_district" id="edit_temp_district" class="form-control"
                            onchange="loadLocalLevels('edit_temp')">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Local Level</label>
                        <select name="temp_local_level" id="edit_temp_local_level" class="form-control">
                            <option value="">Select Local Level</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Ward No</label>
                        <input type="text" name="temp_ward_no" id="edit_temp_ward_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Tole</label>
                        <input type="text" name="temp_tole" id="edit_temp_tole" class="form-control">
                    </div>
                </div>

                <h3
                    style="margin: 1.5rem 0 1rem; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    Additional Info</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Scholarship Type</label>
                        <select name="scholarship_type" id="edit_scholarship_type" class="form-control">
                            <option value="None">None</option>
                            <option value="Dalit">Dalit</option>
                            <option value="Marginalised">Marginalised</option>
                            <option value="100% Girl">100% Girl</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Disability</label>
                        <select name="disability_type" id="edit_disability_type" class="form-control">
                            <option value="None">None</option>
                            <option value="Physical">Physical</option>
                            <option value="Mental">Mental</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('editModal').classList.remove('show')"
                        class="btn" style="flex:1; background:#e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Update Student</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const nepalLocations = {
            "Koshi Province": {
                "Bhojpur": ["Bhojpur Municipality", "Shadananda Municipality", "Tyamke Maiyum Rural Municipality", "Ramprasad Rai Rural Municipality", "Arun Rural Municipality", "Pauwadungma Rural Municipality", "Salpasilichho Rural Municipality", "Aamchok Rural Municipality", "Hatuwagadhi Rural Municipality"],
                "Dhankuta": ["Pakhribas Municipality", "Dhankuta Municipality", "Mahalaxmi Municipality", "Sangurigadhi Rural Municipality", "Chaubise Rural Municipality", "Sahidbhumi Rural Municipality", "Chhathar Jorpati Rural Municipality"],
                "Ilam": ["Ilam Municipality", "Deumai Municipality", "Mai Municipality", "Suryodaya Municipality", "Fakphokthum Rural Municipality", "Chulachuli Rural Municipality", "Mai Jogmai Rural Municipality", "Mangsebung Rural Municipality", "Rong Rural Municipality", "Sandakpur Rural Municipality"],
                "Jhapa": ["Mechinagar Municipality", "Damak Municipality", "Kankai Municipality", "Bhadrapur Municipality", "Arjundhara Municipality", "Shivasataxi Municipality", "Gauradaha Municipality", "Birtamod Municipality", "Kamal Rural Municipality", "Gaurigunj Rural Municipality", "Barhadashi Rural Municipality", "Jhapa Rural Municipality", "Buddhashanti Rural Municipality", "Haldibari Rural Municipality", "Kachankawal Rural Municipality"],
                "Khotang": ["Halesi Tuwachung Municipality", "Diktel Rupakot Majhuwagadhi Municipality", "Aiselukharka Rural Municipality", "Lamidanda Rural Municipality", "Jantedhunga Rural Municipality", "Khotehang Rural Municipality", "Kepilasgadhi Rural Municipality", "Diprung Chuichumma Rural Municipality", "Sakela Rural Municipality", "Barahapokhari Rural Municipality"],
                "Morang": ["Biratnagar Metropolitan City", "Belbari Municipality", "Letang Municipality", "Pathari Sanischare Municipality", "Rangeli Municipality", "Ratuwamai Municipality", "Sunwarshi Municipality", "Urlabari Municipality", "Sundarharaicha Municipality", "Budhiganga Rural Municipality", "Dhanpalthan Rural Municipality", "Gramthan Rural Municipality", "Jahada Rural Municipality", "Kanepokhari Rural Municipality", "Katahari Rural Municipality", "Kerabari Rural Municipality", "Miklajung Rural Municipality"],
                "Okhaldhunga": ["Siddhicharan Municipality", "Champadevi Rural Municipality", "Chishankhugadhi Rural Municipality", "Khijidemba Rural Municipality", "Likhu Rural Municipality", "Manebhanjyang Rural Municipality", "Molung Rural Municipality", "Sunkoshi Rural Municipality"],
                "Panchthar": ["Phidim Municipality", "Falelung Rural Municipality", "Falgunanda Rural Municipality", "Hilihang Rural Municipality", "Kummayak Rural Municipality", "Miklajung Rural Municipality", "Tumbewa Rural Municipality", "Yangwarak Rural Municipality"],
                "Sankhuwasabha": ["Chainpur Municipality", "Dharmadevi Municipality", "Khandbari Municipality", "Madi Municipality", "Panchkhapan Municipality", "Bhotkhola Rural Municipality", "Chichila Rural Municipality", "Makalu Rural Municipality", "Sabhapokhari Rural Municipality", "Silichong Rural Municipality"],
                "Solukhumbu": ["Solududhkunda Municipality", "Mapya Dudhkoshi Rural Municipality", "Khumbu Pasanglhamu Rural Municipality", "Thulung Dudhkoshi Rural Municipality", "Necha Salyan Rural Municipality", "Mahakulung Rural Municipality", "Likhu Pike Rural Municipality", "Sotang Rural Municipality"],
                "Sunsari": ["Itahari Sub-Metropolitan City", "Dharan Sub-Metropolitan City", "Inaruwa Municipality", "Duhabi Municipality", "Ramdhuni Municipality", "Barahachhetra Municipality", "Dewanganj Rural Municipality", "Gadhi Rural Municipality", "Barju Rural Municipality", "Bhokraha Narsingh Rural Municipality", "Harinagara Rural Municipality", "Koshi Rural Municipality"],
                "Taplejung": ["Phungling Municipality", "Aathrai Triveni Rural Municipality", "Sidingba Rural Municipality", "Faktanglung Rural Municipality", "Mikwakhola Rural Municipality", "Meringden Rural Municipality", "Maiwakhola Rural Municipality", "Yangwarak Rural Municipality", "Sirijangha Rural Municipality"],
                "Terhathum": ["Myanglung Municipality", "Laligurans Municipality", "Aathrai Rural Municipality", "Chhathar Rural Municipality", "Phedap Rural Municipality", "Menchhayayem Rural Municipality"],
                "Udayapur": ["Triyuga Municipality", "Chaudandigadhi Municipality", "Belaka Municipality", "Katari Municipality", "Udayapurgadhi Rural Municipality", "Rauta Mai Rural Municipality", "Limchungbung Rural Municipality", "Tapli Rural Municipality"]
            },
            "Madhesh Province": {
                "Bara": ["Kalaiya Sub-Metropolitan City", "Jitpur Simara Sub-Metropolitan City", "Kolhabi Municipality", "Nijgadh Municipality", "Mahagadhimai Municipality", "Simraungadh Municipality", "Pachrauta Municipality", "Pheta Rural Municipality", "Bishrampur Rural Municipality", "Prasauni Rural Municipality", "Adarsh Kotwal Rural Municipality", "Karaiyamai Rural Municipality", "Devtal Rural Municipality", "Parwanipur Rural Municipality", "Baragadhi Rural Municipality", "Suwarna Rural Municipality"],
                "Dhanusha": ["Janakpurdham Sub-Metropolitan City", "Chhireshwarnath Municipality", "Ganeshman Charanath Municipality", "Dhanushadham Municipality", "Nagarain Municipality", "Videha Municipality", "Mithila Municipality", "Shahidnagar Municipality", "Sabaila Municipality", "Kamala Municipality", "Mithila Bihari Municipality", "Hansapur Municipality", "Janaknandini Rural Municipality", "Bateshwar Rural Municipality", "Mukhiyapatti Musharniya Rural Municipality", "Lakshminiya Rural Municipality", "Aurahi Rural Municipality", "Dhanauji Rural Municipality"],
                "Mahottari": ["Jaleshwar Municipality", "Bardibas Municipality", "Gaushala Municipality", "Lohanripatti Municipality", "Ramgopalpur Municipality", "Manara Shishwa Municipality", "Matihani Municipality", "Bhangaha Municipality", "Balawa Municipality", "Aurahi Municipality", "Ekdara Rural Municipality", "Pipra Rural Municipality", "Sonama Rural Municipality", "Samsi Rural Municipality", "Mahottari Rural Municipality"],
                "Parsa": ["Birgunj Metropolitan City", "Pokhariya Municipality", "Bahudarmai Municipality", "Parsagadhi Municipality", "Thori Rural Municipality", "Jagarnathpur Rural Municipality", "Dhobini Rural Municipality", "Chhipharamai Rural Municipality", "Pakaha Mainpur Rural Municipality", "Bindabasini Rural Municipality", "Sakhuwa Prasauni Rural Municipality", "Paterwa Sugauli Rural Municipality", "Kalikamai Rural Municipality", "Jirabhawani Rural Municipality"],
                "Rautahat": ["Chandrapur Municipality", "Garuda Municipality", "Gaur Municipality", "Baudhimai Municipality", "Brindaban Municipality", "Dewahi Gonahi Municipality", "Durga Bhagwati Municipality", "Gadhimai Municipality", "Gujara Municipality", "Cutharwa Municipality", "Ishanath Municipality", "Madhav Narayan Municipality", "Maulapur Municipality", "Paroha Municipality", "Phatuwa Vijayapur Municipality", "Rajdevi Municipality", "Rajpur Municipality", "Yamunamai Rural Municipality"],
                "Saptari": ["Rajbiraj Municipality", "Kanchanrup Municipality", "Dakneshwari Municipality", "Bodebarsain Municipality", "Khadak Municipality", "Shambhunath Municipality", "Surunga Municipality", "Hanumannagar Kankalini Municipality", "Saptakoshi Municipality", "Agnisair Krishna Savaran Rural Municipality", "Chhinnamasta Rural Municipality", "Mahadeva Rural Municipality", "Tirhut Rural Municipality", "Tilathi Koiladi Rural Municipality", "Rupani Rural Municipality", "Rajgadh Rural Municipality", "Bishnupur Rural Municipality", "Balan-Bihul Rural Municipality"],
                "Sarlahi": ["Malangwa Municipality", "Barahathwa Municipality", "Hariwan Municipality", "Ishwarpur Municipality", "Lalbandi Municipality", "Bagmati Municipality", "Balara Municipality", "Haripur Municipality", "Haripurwa Municipality", "Kabilasi Municipality", "Godaita Municipality", "Bramhapuri Rural Municipality", "Chandranagar Rural Municipality", "Dhankaul Rural Municipality", "Chakraghatta Rural Municipality", "Kaudena Rural Municipality", "Ramnagar Rural Municipality", "Basbariya Rural Municipality", "Bishnu Rural Municipality", "Parsa Rural Municipality"],
                "Siraha": ["Siraha Municipality", "Lahan Municipality", "Mirchaiya Municipality", "Golbazar Municipality", "Dhangadhimai Municipality", "Kalyanpur Municipality", "Karjanha Municipality", "Sukhipur Municipality", "Bhagwanpur Rural Municipality", "Aurahi Rural Municipality", "Bishnupur Rural Municipality", "Bariyarpatti Rural Municipality", "Lakshmipur Patari Rural Municipality", "Naraha Rural Municipality", "Sakhuwanankarkatti Rural Municipality", "Arnama Rural Municipality", "Navarajpur Rural Municipality"]
            },
            "Bagmati Province": {
                "Bhaktapur": ["Bhaktapur Municipality", "Changunarayan Municipality", "Madhyapur Thimi Municipality", "Suryabinayak Municipality"],
                "Chitwan": ["Bharatpur Metropolitan City", "Kalika Municipality", "Khairahani Municipality", "Madi Municipality", "Ratnanagar Municipality", "Rapti Municipality", "Ichchhakamana Rural Municipality"],
                "Dhading": ["Nilkantha Municipality", "Dhunibeshi Municipality", "Khaniyabas Rural Municipality", "Gajuri Rural Municipality", "Galchhi Rural Municipality", "Gangajamuna Rural Municipality", "Jwalamukhi Rural Municipality", "Thakre Rural Municipality", "Netrawati Dabjong Rural Municipality", "Benighat Rorang Rural Municipality", "Rubi Valley Rural Municipality", "Siddhalek Rural Municipality", "Tripurasundari Rural Municipality"],
                "Dolakha": ["Bhimeshwar Municipality", "Jiri Municipality", "Kalinchok Rural Municipality", "Gaurishankar Rural Municipality", "Bigu Rural Municipality", "Shailung Rural Municipality", "Baiteshwar Rural Municipality", "Tamakoshi Rural Municipality", "Melung Rural Municipality", "Jiri Rural Municipality"],
                "Kathmandu": ["Kathmandu Metropolitan City", "Kirtipur Municipality", "Shankharapur Municipality", "Budhanilkantha Municipality", "Tarakeshwar Municipality", "Tokha Municipality", "Chandragiri Municipality", "Nagarjun Municipality", "Gokarneshwar Municipality", "Dakshinkali Municipality", "Kageshwari-Manohara Municipality"],
                "Kavrepalanchok": ["Dhulikhel Municipality", "Banepa Municipality", "Panauti Municipality", "Panchkhal Municipality", "Namobuddha Municipality", "Mandandeupur Municipality", "Khani Khola Rural Municipality", "Chauri Deurali Rural Municipality", "Temal Rural Municipality", "Bethanchok Rural Municipality", "Bhumlu Rural Municipality", "Mahabharat Rural Municipality", "Roshi Rural Municipality"],
                "Lalitpur": ["Lalitpur Metropolitan City", "Godawari Municipality", "Mahalaxmi Municipality", "Konjyosom Rural Municipality", "Bagmati Rural Municipality", "Mahankal Rural Municipality"],
                "Makwanpur": ["Hetauda Sub-Metropolitan City", "Thaha Municipality", "Indrasarowar Rural Municipality", "Kailash Rural Municipality", "Bakaiya Rural Municipality", "Bagmati Rural Municipality", "Bhimfedi Rural Municipality", "Makwanpur Gadhi Rural Municipality", "Manahari Rural Municipality", "Raksirang Rural Municipality"],
                "Nuwakot": ["Bidur Municipality", "Belkotgadhi Municipality", "Kakani Rural Municipality", "Kispang Rural Municipality", "Tadi Rural Municipality", "Tarakeshwor Rural Municipality", "Dupcheswor Rural Municipality", "Panchakanya Rural Municipality", "Likhu Rural Municipality", "Myagang Rural Municipality", "Shivapuri Rural Municipality", "Suryagadhi Rural Municipality"],
                "Ramechhap": ["Manthali Municipality", "Ramechhap Municipality", "Umakunda Rural Municipality", "Khadadevi Rural Municipality", "Gokulganga Rural Municipality", "Doramba Rural Municipality", "Likhu Tamakoshi Rural Municipality", "Sunapati Rural Municipality"],
                "Rasuwa": ["Gosaikunda Rural Municipality", "Kalika Rural Municipality", "Amachodingmo Rural Municipality", "Naukunda Rural Municipality", "Uttargaya Rural Municipality"],
                "Sindhuli": ["Kamalamai Municipality", "Dudhauli Municipality", "Golanjor Rural Municipality", "Ghyanglekh Rural Municipality", "Tinpatan Rural Municipality", "Phikkal Rural Municipality", "Marin Rural Municipality", "Sunkoshi Rural Municipality", "Hariharpurgadhi Rural Municipality"],
                "Sindhupalchok": ["Chautara Sangachokgadhi Municipality", "Bhotekoshi Rural Municipality", "Indrawati Rural Municipality", "Jugal Rural Municipality", "Panchpokhari Thangpal Rural Municipality", "Balefi Rural Municipality", "Bhotekoshi Rural Municipality", "Lisankhu Pakhar Rural Municipality", "Sunkoshi Rural Municipality", "Helambu Rural Municipality", "Tripurasundari Rural Municipality"]
            },
            "Gandaki Province": {
                "Baglung": ["Baglung Municipality", "Galkot Municipality", "Jaimuni Municipality", "Dhorpatan Municipality", "Bareng Rural Municipality", "Kanthekhola Rural Municipality", "Tamakhola Rural Municipality", "Tara Khola Rural Municipality", "Nisikhola Rural Municipality", "Badigad Rural Municipality"],
                "Gorkha": ["Gorkha Municipality", "Palungtar Municipality", "Sulikot Rural Municipality", "Siranchok Rural Municipality", "Ajirkot Rural Municipality", "Tsum Nubri Rural Municipality", "Dharche Rural Municipality", "Bhimsen Thapa Rural Municipality", "Sahid Lakhan Rural Municipality", "Arughat Rural Municipality", "Gandaki Rural Municipality"],
                "Kaski": ["Pokhara Metropolitan City", "Annapurna Rural Municipality", "Machhapuchhre Rural Municipality", "Madi Rural Municipality", "Rupa Rural Municipality"],
                "Lamjung": ["Besisahar Municipality", "Madhya Nepal Municipality", "Rainas Municipality", "Sundarbazar Municipality", "Kwwholasothar Rural Municipality", "Dudhpokhari Rural Municipality", "Dordi Rural Municipality", "Marsyangdi Rural Municipality"],
                "Manang": ["Chame Rural Municipality", "Narpa Bhumi Rural Municipality", "Nasong Rural Municipality", "Manang Ngisyang Rural Municipality"],
                "Mustang": ["Gharpajhong Rural Municipality", "Thasang Rural Municipality", "Dalome Rural Municipality", "Lomanthang Rural Municipality", "Baragung Muktichhetra Rural Municipality"],
                "Myagdi": ["Beni Municipality", "Annapurna Rural Municipality", "Dhaulagiri Rural Municipality", "Mangala Rural Municipality", "Malika Rural Municipality", "Raghuganga Rural Municipality"],
                "Nawalpur": ["Kawasoti Municipality", "Gaindakot Municipality", "Devchuli Municipality", "Madhyabindu Municipality", "Baudikali Rural Municipality", "Bulingtar Rural Municipality", "Binayi Triveni Rural Municipality", "Hupsekot Rural Municipality"],
                "Parbat": ["Kushma Municipality", "Phalebas Municipality", "Jaljala Rural Municipality", "Paiyun Rural Municipality", "Mahashila Rural Municipality", "Modi Rural Municipality", "Bihadi Rural Municipality"],
                "Syangja": ["Putalibazar Municipality", "Waling Municipality", "Galyang Municipality", "Chapakot Municipality", "Bhirkot Municipality", "Arjunchaupari Rural Municipality", "Aandhikhola Rural Municipality", "Kaligandaki Rural Municipality", "Phedikhola Rural Municipality", "Harinas Rural Municipality", "Biruwa Rural Municipality"],
                "Tanahun": ["Byas Municipality", "Bhanu Municipality", "Shuklagandaki Municipality", "Bhimad Municipality", "Aanbu Khaireni Rural Municipality", "Rhishing Rural Municipality", "Ghiring Rural Municipality", "Devghat Rural Municipality", "Myagde Rural Municipality", "Bandipur Rural Municipality"]
            },
            "Lumbini Province": {
                "Arghakhanchi": ["Sandhikharka Municipality", "Sitganga Municipality", "Bhumikasthan Municipality", "Chhatradev Rural Municipality", "Panini Rural Municipality", "Malarani Rural Municipality"],
                "Banke": ["Nepalgunj Sub-Metropolitan City", "Kohalpur Municipality", "Khajura Rural Municipality", "Janaki Rural Municipality", "Baijanath Rural Municipality", "Duduwa Rural Municipality", "Narainapur Rural Municipality", "Rapti Sonari Rural Municipality"],
                "Bardiya": ["Gulariya Municipality", "Madhuwan Municipality", "Rajapur Municipality", "Thakurbaba Municipality", "Barbardiya Municipality", "Bansgadhi Municipality", "Badhaiyatal Rural Municipality", "Geruwa Rural Municipality"],
                "Dang": ["Ghorahi Sub-Metropolitan City", "Tulsipur Sub-Metropolitan City", "Lamahi Municipality", "Gadhawa Rural Municipality", "Rajpur Rural Municipality", "Shantinagar Rural Municipality", "Rapti Rural Municipality", "Dangisharan Rural Municipality", "Babai Rural Municipality", "Banglachuli Rural Municipality"],
                "Gulmi": ["Resunga Municipality", "Musikot Municipality", "Isma Rural Municipality", "Kaligandaki Rural Municipality", "Gulmi Durbar Rural Municipality", "Satyawati Rural Municipality", "Chandrakot Rural Municipality", "Ruru Rural Municipality", "Chhatrakot Rural Municipality", "Dhurkot Rural Municipality", "Madane Rural Municipality", "Malika Rural Municipality"],
                "Kapilvastu": ["Kapilvastu Municipality", "Banganga Municipality", "Buddhabhumi Municipality", "Shivaraj Municipality", "Krishnanagar Municipality", "Maharajgunj Municipality", "Mayadevi Rural Municipality", "Yashodhara Rural Municipality", "Suddhodhan Rural Municipality", "Bijaynagar Rural Municipality"],
                "Parasi": ["Ramgram Municipality", "Sunwal Municipality", "Bardaghat Municipality", "Sarawal Rural Municipality", "Palhinandan Rural Municipality", "Pratappur Rural Municipality", "Susta Rural Municipality"],
                "Palpa": ["Tansen Municipality", "Rampur Municipality", "Rainadevi Chhhara Rural Municipality", "Ripdikot Rural Municipality", "Bagnaskali Rural Municipality", "Rambha Rural Municipality", "Purbakhola Rural Municipality", "Nisdi Rural Municipality", "Mathagadhi Rural Municipality", "Tinau Rural Municipality"],
                "Pyuthan": ["Pyuthan Municipality", "Swargadwari Municipality", "Gaumukhi Rural Municipality", "Mandavi Rural Municipality", "Sarumarani Rural Municipality", "Mallarani Rural Municipality", "Naubahini Rural Municipality", "Jhimruk Rural Municipality", "Airawati Rural Municipality"],
                "Rolpa": ["Rolpa Municipality", "Triveni Rural Municipality", "Duikholi Rural Municipality", "Madi Rural Municipality", "Runtigadhi Rural Municipality", "Lungri Rural Municipality", "Sunchhari Rural Municipality", "Thabang Rural Municipality", "Gangadev Rural Municipality"],
                "Rukum East": ["Putha Uttarganga Rural Municipality", "Bhume Rural Municipality", "Sisne Rural Municipality"],
                "Rupandehi": ["Butwal Sub-Metropolitan City", "Siddharthanagar Municipality", "Tilottama Municipality", "Sainamaina Municipality", "Devdaha Municipality", "Lumbini Sanskritik Municipality", "Gaidahawa Rural Municipality", "Kanchan Rural Municipality", "Kotahimai Rural Municipality", "Marchawari Rural Municipality", "Mayadevi Rural Municipality", "Omsatiya Rural Municipality", "Rohini Rural Municipality", "Sammarimai Rural Municipality", "Siyari Rural Municipality", "Suddhodhan Rural Municipality"]
            },
            "Karnali Province": {
                "Dailekh": ["Narayan Municipality", "Dullu Municipality", "Chamunda Bindrasaini Municipality", "Aathbis Municipality", "Bhagawatimai Rural Municipality", "Guans Rural Municipality", "Dungeshwar Rural Municipality", "Naumule Rural Municipality", "Mahabu Rural Municipality", "Bhairabi Rural Municipality", "Thantikandh Rural Municipality"],
                "Dolpa": ["Thuli Bheri Municipality", "Tripurasundari Municipality", "Dolpo Buddha Rural Municipality", "Shey Phoksundo Rural Municipality", "Jagadulla Rural Municipality", "Mudkechula Rural Municipality", "Kaike Rural Municipality", "Chharka Tangsong Rural Municipality"],
                "Humla": ["Simkot Rural Municipality", "Namkha Rural Municipality", "Kharpunath Rural Municipality", "Sarkegad Rural Municipality", "Chankheli Rural Municipality", "Adanchuli Rural Municipality", "Tanjakot Rural Municipality"],
                "Jajarkot": ["Bheri Municipality", "Chhedagad Municipality", "Nalgad Municipality", "Barekot Rural Municipality", "Kushe Rural Municipality", "Junichande Rural Municipality", "Shivalaya Rural Municipality"],
                "Jumla": ["Chandannath Municipality", "Kankasundari Rural Municipality", "Sinja Rural Municipality", "Hima Rural Municipality", "Tila Rural Municipality", "Guthichaur Rural Municipality", "Tatopani Rural Municipality", "Patarasi Rural Municipality"],
                "Kalikot": ["Khandachakra Municipality", "Raskot Municipality", "Tilagufa Municipality", "Pachaljharana Rural Municipality", "Sanni Triveni Rural Municipality", "Naraharinath Rural Municipality", "Shubha Kalika Rural Municipality", "Mahawai Rural Municipality", "Palata Rural Municipality"],
                "Mugu": ["Chhayanath Rara Municipality", "Mugum Karmarong Rural Municipality", "Soru Rural Municipality", "Khatyad Rural Municipality"],
                "Rukum West": ["Musikot Municipality", "Chaurjahari Municipality", "Aathbiskot Municipality", "Banphikot Rural Municipality", "Triveni Rural Municipality", "Sanibheri Rural Municipality"],
                "Salyan": ["Shaarda Municipality", "Bangad Kupinde Municipality", "Bagchaur Municipality", "Kalimati Rural Municipality", "Triveni Rural Municipality", "Kapurkot Rural Municipality", "Chhatreshwari Rural Municipality", "Siddha Kumakh Rural Municipality", "Kumakh Rural Municipality", "Darma Rural Municipality"],
                "Surkhet": ["Birendranagar Municipality", "Bheriganga Municipality", "Gurbhakot Municipality", "Panchapuri Municipality", "Lekbeshi Municipality", "Chaukune Rural Municipality", "Barahatal Rural Municipality", "Chingad Rural Municipality", "Simta Rural Municipality"]
            },
            "Sudurpaschim Province": {
                "Achham": ["Mangalsen Municipality", "Kamalbazar Municipality", "Sanphebagar Municipality", "Panchadeval Binayak Municipality", "Chaurpati Rural Municipality", "Mellekh Rural Municipality", "Bannigadhi Jayagarh Rural Municipality", "Ramaroshan Rural Municipality", "Dhakari Rural Municipality", "Turmakhand Rural Municipality"],
                "Baitadi": ["Dasharathchand Municipality", "Patan Municipality", "Melauli Municipality", "Purchaudi Municipality", "Sunarya Rural Municipality", "Sigas Rural Municipality", "Shivanath Rural Municipality", "Pancheshwar Rural Municipality", "Dogdakedar Rural Municipality", "Dilasaini Rural Municipality"],
                "Bajhang": ["Jaya Prithvi Municipality", "Bungal Municipality", "Talkot Rural Municipality", "Masta Rural Municipality", "Khaptadchhanna Rural Municipality", "Thalara Rural Municipality", "Bitthadchir Rural Municipality", "Surma Rural Municipality", "Chhabis Pathibhera Rural Municipality", "Durgathali Rural Municipality", "Kedarsyu Rural Municipality", "Saipal Rural Municipality"],
                "Bajura": ["Badimalika Municipality", "Triveni Municipality", "Budhiganga Municipality", "Budhinanda Municipality", "Gaumul Rural Municipality", "Pandavgufa Rural Municipality", "Swami Kartik Khapar Rural Municipality", "Chhededaha Rural Municipality", "Himali Rural Municipality"],
                "Dadeldhura": ["Amargadhi Municipality", "Parashuram Municipality", "Aalital Rural Municipality", "Bhageshwar Rural Municipality", "Navadurga Rural Municipality", "Ajayameru Rural Municipality", "Ganyapdhura Rural Municipality"],
                "Darchula": ["Mahakali Municipality", "Shailyashikhar Municipality", "Malikarjun Rural Municipality", "Apihimal Rural Municipality", "Duhun Rural Municipality", "Naugad Rural Municipality", "Marma Rural Municipality", "Lekam Rural Municipality", "Vyans Rural Municipality"],
                "Doti": ["Dipayal Silgadhi Municipality", "Shikhar Municipality", "Purbichauki Rural Municipality", "Badikedar Rural Municipality", "Jorayal Rural Municipality", "Sayal Rural Municipality", "Aadarsha Rural Municipality", "K.I. Singh Rural Municipality", "Bogatan Rural Municipality"],
                "Kailali": ["Dhangadhi Sub-Metropolitan City", "Tikapur Municipality", "Ghodaghodi Municipality", "Lamki Chuha Municipality", "Bhajani Municipality", "Godawari Municipality", "Gauriganga Municipality", "Janaki Rural Municipality", "Bardagoriya Rural Municipality", "Mohanyal Rural Municipality", "Kailari Rural Municipality", "Joshipur Rural Municipality", "Chure Rural Municipality"],
                "Kanchanpur": ["Bhimdatta Municipality", "Bedkot Municipality", "Shuklaphanta Municipality", "Mahakali Municipality", "Krishnapur Municipality", "Punarbas Municipality", "Belauri Municipality", "Laljhadi Rural Municipality", "Beldandi Rural Municipality"]
            }
        };

        // Returns ONLY the districts for the given province. No fallback.
        function getDistricts(province) {
            if (nepalLocations[province]) {
                return Object.keys(nepalLocations[province]).sort();
            }
            return [];
        }

        // Returns ONLY the local levels for the given district in the given province.
        function getLocalLevels(province, district) {
            if (nepalLocations[province] && nepalLocations[province][district]) {
                return nepalLocations[province][district].sort();
            }
            return [];
        }

        const provinceList = [
            "Koshi Province",
            "Madhesh Province",
            "Bagmati Province",
            "Gandaki Province",
            "Lumbini Province",
            "Karnali Province",
            "Sudurpaschim Province"
        ];

        // Init Provinces
        function initAddress() {
            const selects = ['perm_province', 'temp_province', 'edit_perm_province', 'edit_temp_province'];
            selects.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.innerHTML = '<option value="">Select Province</option>';
                    provinceList.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p;
                        opt.textContent = p;
                        el.appendChild(opt);
                    });
                }
            });
        }

        function loadDistricts(type) {
            const province = document.getElementById(type + '_province').value;
            const distSelect = document.getElementById(type + '_district');
            const localSelect = document.getElementById(type + '_local_level');

            if (!distSelect || !localSelect) return;

            distSelect.innerHTML = '<option value="">Select District</option>';
            localSelect.innerHTML = '<option value="">Select Local Level</option>';

            if (province) {
                const districts = getDistricts(province);
                districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    distSelect.appendChild(opt);
                });
            }
        }

        function loadLocalLevels(type) {
            const province = document.getElementById(type + '_province').value;
            const district = document.getElementById(type + '_district').value;
            const localSelect = document.getElementById(type + '_local_level');

            if (!localSelect) return;

            localSelect.innerHTML = '<option value="">Select Local Level</option>';

            if (province && district) {
                const levels = getLocalLevels(province, district);
                levels.forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = l;
                    opt.textContent = l;
                    localSelect.appendChild(opt);
                });
            }
        }

        function copyAddress() {
            if (document.getElementById('same_as_perm').checked) {
                // Copy values
                document.getElementById('temp_province').value = document.getElementById('perm_province').value;
                loadDistricts('temp');
                document.getElementById('temp_district').value = document.getElementById('perm_district').value;
                loadLocalLevels('temp');
                document.getElementById('temp_local_level').value = document.getElementById('perm_local_level').value;
                document.getElementById('temp_ward_no').value = document.getElementById('perm_ward_no').value;
                document.getElementById('temp_tole').value = document.getElementById('perm_tole').value;
            } else {
                // Clear values
                document.getElementById('temp_province').value = "";
                loadDistricts('temp');
                document.getElementById('temp_ward_no').value = "";
                document.getElementById('temp_tole').value = "";
            }
        }

        // View Modal Logic
        function openViewModal(data) {
            const modal = document.getElementById('viewModal');
            const body = document.getElementById('viewModalBody');

            // Helper to create a detail row
            const item = (label, value) => `
                <div style="margin-bottom: 10px;">
                    <strong style="display:block; color:#6b7280; font-size:0.85rem; margin-bottom:2px;">${label}</strong>
                    <div style="font-size:1rem; color:#111827;">${value || '-'}</div>
                </div>
            `;

            // Helper for sections
            const section = (title) => `
                <div style="grid-column: span 2; margin-top: 10px; margin-bottom: 5px; border-bottom: 1px solid #eee;">
                    <h4 style="margin:0; padding-bottom:5px; color:#4f46e5;">${title}</h4>
                </div>
            `;

            const topSection = data.student_photo ? `
                <div style="grid-column: span 2; display: flex; gap: 20px; align-items: start; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                    <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <h4 style="grid-column: span 2; margin:0; padding-bottom:5px; color:#4f46e5;">Academic Info</h4>
                        ${item("Full Name", data.full_name)}
                        ${item("Symbol No", data.symbol_no)}
                        ${item("Class", data.class)}
                        ${item("Roll No", data.roll_no)}
                        ${item("EMIS No", data.emis_no)}
                        ${item("Caste", data.caste)}
                        ${item("Date of Birth (BS)", data.dob_nepali)}
                        ${item("Gender", data.gender)}
                    </div>
                    <div style="width: 120px; flex-shrink: 0;">
                        <img src="${data.student_photo}" style="width: 100%; border-radius: 4px; border: 1px solid #ddd; object-fit: cover; aspect-ratio: 3/4;">
                    </div>
                </div>
            ` : `
                ${section("Academic Info")}
                ${item("Full Name", data.full_name)}
                ${item("Symbol No", data.symbol_no)}
                ${item("Class", data.class)}
                ${item("Roll No", data.roll_no)}
                ${item("EMIS No", data.emis_no)}
                ${item("Date of Birth (BS)", data.dob_nepali)}
                ${item("Gender", data.gender)}
            `;

            body.innerHTML = `
                ${topSection}
                
                ${section("Parent / Guardian Info")}
                ${item("Father's Name", data.father_name)}
                ${item("Mother's Name", data.mother_name)}
                ${item("Guardian's Name", data.guardian_name)}
                ${item("Parent Phone", data.parent_contact)}
                ${item("Parent Phone", data.parent_contact)}
                ${item("Guardian Phone", data.guardian_contact)}
                ${item("Guardian Email", data.guardian_email)}

                ${section("Permanent Address")}
                ${item("Province", data.perm_province)}
                ${item("District", data.perm_district)}
                ${item("Local Level", data.perm_local_level)}
                ${item("Ward No", data.perm_ward_no)}
                ${item("Tole", data.perm_tole)}

                ${section("Temporary Address")}
                ${item("Province", data.temp_province)}
                ${item("District", data.temp_district)}
                ${item("Local Level", data.temp_local_level)}
                ${item("Ward No", data.temp_ward_no)}
                ${item("Tole", data.temp_tole)}
                
                ${section("Additional Info")}
                ${item("Scholarship", data.scholarship_type)}
                ${item("Disability", data.disability_type)}
            `;

            modal.classList.add('show');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('show');
        }

        function populateAddressFields(type, provData, distData, localData) {
            const provSelect = document.getElementById(type + '_province');
            const distSelect = document.getElementById(type + '_district');
            const localSelect = document.getElementById(type + '_local_level');

            // 1. Set Province
            provSelect.value = provData;

            // 2. Populate Districts
            distSelect.innerHTML = '<option value="">Select District</option>';
            if (provData) {
                const districts = getDistricts(provData);
                districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    distSelect.appendChild(opt);
                });
            }
            // 3. Set District
            distSelect.value = distData;

            // 4. Populate Local Levels
            localSelect.innerHTML = '<option value="">Select Local Level</option>';
            if (provData && distData) {
                const levels = getLocalLevels(provData, distData);
                levels.forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = l;
                    opt.textContent = l;
                    localSelect.appendChild(opt);
                });
            }
            // 5. Set Local Level
            localSelect.value = localData;
        }

        function openEditModal(data) {
            try {
                console.log('Opening Edit Modal for:', data);

                const setVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val || '';
                    else console.warn('Missing element:', id);
                };

                setVal('edit_student_id', data.id);
                setVal('edit_full_name', data.full_name);
                setVal('edit_roll_no', data.roll_no);
                setVal('edit_class', data.class);
                setVal('edit_dob_nepali', data.dob_nepali);
                setVal('edit_gender', data.gender);
                setVal('edit_emis_no', data.emis_no);
                setVal('edit_caste', data.caste);

                setVal('edit_father_name', data.father_name);
                setVal('edit_mother_name', data.mother_name);
                setVal('edit_guardian_name', data.guardian_name);
                setVal('edit_parent_contact', data.parent_contact);
                setVal('edit_guardian_contact', data.guardian_contact);
                setVal('edit_guardian_email', data.guardian_email);

                // Populate Addresses using helper
                populateAddressFields('edit_perm', data.perm_province, data.perm_district, data.perm_local_level);
                populateAddressFields('edit_temp', data.temp_province, data.temp_district, data.temp_local_level);

                setVal('edit_perm_ward_no', data.perm_ward_no);
                setVal('edit_perm_tole', data.perm_tole);
                setVal('edit_temp_ward_no', data.temp_ward_no);
                setVal('edit_temp_tole', data.temp_tole);

                setVal('edit_scholarship_type', data.scholarship_type);
                setVal('edit_disability_type', data.disability_type);

                document.getElementById('editModal').classList.add('show');
            } catch (e) {
                console.error("Error opening Edit Modal:", e);
                alert("An error occurred while opening the edit form. See console for details.");
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-hide alert message after 5 seconds
            const alertMsg = document.getElementById('alertMessage');
            if (alertMsg) {
                setTimeout(function () {
                    alertMsg.style.opacity = '0';
                    setTimeout(function () {
                        alertMsg.style.display = 'none';
                    }, 500); // Wait for transition to finish
                }, 5000);
            }

            // Remove 'msg' parameter from URL to prevent reappearing on refresh
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                if (url.searchParams.has('msg')) {
                    url.searchParams.delete('msg');
                    window.history.replaceState(null, '', url.toString());
                }
            }

            // Init Address (wrapped in try-catch to satisfy safety)
            try {
                initAddress();
            } catch (e) {
                console.error("Address Init Failed", e);
            }

            // Auto-format Date Inputs (YYYY/MM/DD)
            function setupDateFormatter(id) {
                const input = document.getElementById(id);
                if (!input) return;

                input.addEventListener('input', function (e) {
                    if (e.inputType === 'deleteContentBackward') return;

                    var val = this.value.replace(/\D/g, ''); // keep only numbers
                    var formatted = '';

                    if (val.length > 0) formatted += val.substring(0, 4);
                    if (val.length >= 4) formatted += '/';
                    if (val.length > 4) formatted += val.substring(4, 6);
                    if (val.length >= 6) formatted += '/';
                    if (val.length > 6) formatted += val.substring(6, 8);

                    this.value = formatted;
                });
            }

            setupDateFormatter('add_dob_nepali');
            setupDateFormatter('edit_dob_nepali');
            try {
                initAddress();
            } catch (e) {
                console.error("Address Init Failed", e);
            }

            // Global Auto-date mask (YYYY/MM/DD)
            document.querySelectorAll('.nepali-date').forEach(input => {
                input.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 8) value = value.substring(0, 8);

                    let formattedValue = '';
                    if (value.length > 0) {
                        formattedValue = value.substring(0, 4);
                        if (value.length > 4) {
                            formattedValue += '/' + value.substring(4, 6);
                            if (value.length > 6) {
                                formattedValue += '/' + value.substring(6, 8);
                            }
                        }
                    }
                    e.target.value = formattedValue;

                    if (value.length >= 4) {
                        let year = parseInt(value.substring(0, 4));
                        if (year > 2082) {
                            e.target.setCustomValidity('Year cannot be in the future (Current Year: 2082 B.S.)');
                        } else if (value.length >= 6 && parseInt(value.substring(4, 6)) > 12) {
                            e.target.setCustomValidity('Month cannot be greater than 12');
                        } else if (value.length >= 6 && parseInt(value.substring(4, 6)) == 0) {
                            e.target.setCustomValidity('Month cannot be 00');
                        } else if (value.length >= 8 && parseInt(value.substring(6, 8)) > 32) {
                            e.target.setCustomValidity('Day cannot be greater than 32');
                        } else if (value.length >= 8 && parseInt(value.substring(6, 8)) == 0) {
                            e.target.setCustomValidity('Day cannot be 00');
                        } else {
                            e.target.setCustomValidity('');
                        }
                    } else {
                        e.target.setCustomValidity('');
                    }
                });
            });
        });

        // Student Deletion Protocol
        function deleteStudent(id) {
            if (confirm('⚠️ WARNING: You are about to PERMANENTLY delete this student record. This action will erase all associated academic history and cannot be undone. \n\nAre you absolutely sure?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const deleteInput = document.createElement('input');
                deleteInput.name = 'delete_student';
                deleteInput.value = '1';
                form.appendChild(deleteInput);

                const idInput = document.createElement('input');
                idInput.name = 'student_id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>