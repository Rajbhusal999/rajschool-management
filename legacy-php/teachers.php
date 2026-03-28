<?php
require 'includes/auth_school.php';
restrictFeature('teachers');
require 'includes/db_connect.php';

$school_id = $_SESSION['user_id'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Demo Mode Restriction
if (isset($_SESSION['is_demo']) && $_SESSION['is_demo'] && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = "feature disabled in demo mode";
    $_POST = array(); // Clear POST data to prevent execution
}

// Auto-migration: Ensure password column exists
try {
    $conn->query("SELECT 1 FROM teachers LIMIT 1"); // Check if table exists
    $conn->exec("ALTER TABLE teachers ADD COLUMN teacher_password VARCHAR(255) DEFAULT NULL");
    $conn->exec("ALTER TABLE teachers ADD COLUMN citizenship_no VARCHAR(100) DEFAULT NULL");
} catch (Exception $e) {
    // Column likely exists
}
// Auto-migration: staff_role and bank info columns
try {
    $conn->exec("ALTER TABLE teachers ADD COLUMN staff_role VARCHAR(20) NOT NULL DEFAULT 'Teacher' AFTER full_name");
} catch (Exception $e) {
}
try {
    $conn->exec("ALTER TABLE teachers ADD COLUMN bank_name VARCHAR(100) DEFAULT NULL");
    $conn->exec("ALTER TABLE teachers ADD COLUMN account_number VARCHAR(100) DEFAULT NULL");
} catch (Exception $e) {
}

// Handle Add Teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $name = $_POST['full_name'];
    $staff_role = $_POST['staff_role'] ?? 'Teacher';
    $subject = $_POST['subject'] ?? null;
    $contact = $_POST['contact'];
    $type = $_POST['teacher_type'];
    $attendance_date = $_POST['attendance_date_nepali'];
    $address = $_POST['address'];
    $tah = $_POST['tah'] ?? null;
    $pan_no = $_POST['pan_no'];
    $blood_group = $_POST['blood_group'];
    $citizenship_no = $_POST['citizenship_no'];
    $password = $_POST['teacher_password'] ?? null;
    $bank_name = $_POST['bank_name'] ?? null;
    $account_number = $_POST['account_number'] ?? null;

    // Handle Photo Upload (Add)
    $photo_path = null;
    if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['teacher_photo']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['teacher_photo']['size'];

        if (in_array($file_ext, $allowed) && $file_size <= 2 * 1024 * 1024) {
            $new_filename = uniqid('teacher_', true) . '.' . $file_ext;
            $upload_dir = 'uploads/teacher_photos/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES['teacher_photo']['tmp_name'], $upload_dir . $new_filename)) {
                $photo_path = $upload_dir . $new_filename;
            }
        }
    }

    $sql = "INSERT INTO teachers (school_id, full_name, staff_role, subject, contact, teacher_type, attendance_date_nepali, address, tah, pan_no, blood_group, citizenship_no, teacher_photo, teacher_password, bank_name, account_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$school_id, $name, $staff_role, $subject, $contact, $type, $attendance_date, $address, $tah, $pan_no, $blood_group, $citizenship_no, $photo_path, $password, $bank_name, $account_number])) {
        header("Location: teachers.php?msg=" . urlencode("Teacher added successfully!"));
        exit();
    } else {
        $msg = "Error adding teacher.";
    }
}

// Handle Edit Teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_teacher'])) {
    $id = $_POST['teacher_id'];
    $name = $_POST['full_name'];
    $staff_role = $_POST['staff_role'] ?? 'Teacher';
    $subject = $_POST['subject'] ?? null;
    $contact = $_POST['contact'];
    $type = $_POST['teacher_type'];
    $attendance_date = $_POST['attendance_date_nepali'];
    $address = $_POST['address'];
    $tah = $_POST['tah'] ?? null;
    $pan_no = $_POST['pan_no'];
    $blood_group = $_POST['blood_group'];
    $citizenship_no = $_POST['citizenship_no'];
    $password = $_POST['teacher_password'] ?? null;
    $bank_name = $_POST['bank_name'] ?? null;
    $account_number = $_POST['account_number'] ?? null;

    // Handle Photo Upload (Edit)
    $photo_sql_part = "";
    $photo_param = [];
    if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['teacher_photo']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['teacher_photo']['size'];

        if (in_array($file_ext, $allowed) && $file_size <= 2 * 1024 * 1024) {
            $new_filename = uniqid('teacher_', true) . '.' . $file_ext;
            $upload_dir = 'uploads/teacher_photos/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES['teacher_photo']['tmp_name'], $upload_dir . $new_filename)) {
                $photo_sql_part = ", teacher_photo = ?";
                $photo_param[] = $upload_dir . $new_filename;
            }
        }
    }

    $sql = "UPDATE teachers SET full_name = ?, staff_role = ?, subject = ?, contact = ?, teacher_type = ?, 
            attendance_date_nepali = ?, address = ?, tah = ?, pan_no = ?, blood_group = ?, citizenship_no = ?, teacher_password = ?, bank_name = ?, account_number = ? $photo_sql_part
            WHERE id = ? AND school_id = ?";
    $stmt = $conn->prepare($sql);
    $params = array_merge([$name, $staff_role, $subject, $contact, $type, $attendance_date, $address, $tah, $pan_no, $blood_group, $citizenship_no, $password, $bank_name, $account_number], $photo_param, [$id, $school_id]);
    if ($stmt->execute($params)) {
        header("Location: teachers.php?msg=" . urlencode("Teacher updated successfully!"));
        exit();
    } else {
        $msg = "Error updating teacher.";
    }
}

// Handle Delete Teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_teacher'])) {
    $id = $_POST['teacher_id'];
    $sql = "DELETE FROM teachers WHERE id = ? AND school_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$id, $school_id])) {
        header("Location: teachers.php?msg=" . urlencode("Teacher deleted successfully!"));
        exit();
    } else {
        $msg = "Error deleting teacher.";
    }
}

// Fetch Teachers with Search Filter
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';
$params = [$school_id];
$filter_sql = "";

if (!empty($search_query)) {
    $filter_sql = " AND (full_name LIKE ? OR contact LIKE ? OR subject LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$sql = "SELECT * FROM teachers WHERE school_id = ? $filter_sql ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$teachers_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - Smart विद्यालय</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            background: #f9fafb;
            padding: 2rem;
            min-height: calc(100vh - 65px);
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

        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }

            .sidebar {
                display: none;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div class="header-responsive" style="margin-bottom: 2.5rem;">
            <div class="title-block">
                <h1 style="font-size: 2.25rem; font-weight: 800; color: #0f172a; margin: 0;">Faculty Directory</h1>
                <p style="color: #64748b; margin-top: 4px;">Oversee academic staff, departmental roles, and profile
                    credentials.</p>
            </div>

            <div class="action-stack" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                <!-- Filter Section -->
                <form method="GET" class="filter-form-custom" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div class="search-input-group" style="position: relative;">
                        <i class="fas fa-search"
                            style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" name="search_query" placeholder="Search faculty name..."
                            value="<?php echo htmlspecialchars($search_query); ?>"
                            style="padding: 12px 15px 12px 45px; border-radius: 14px; border: 1px solid #e2e8f0; width: 250px; font-weight: 500;">
                    </div>

                    <button type="submit" class="btn btn-primary-gradient" style="padding: 12px 20px;">
                        <i class="fas fa-search"></i> Find
                    </button>

                    <?php if (!empty($search_query)): ?>
                        <a href="teachers.php" class="btn"
                            style="background:#f1f5f9; color:#475569; padding: 12px 20px;">Reset</a>
                    <?php endif; ?>
                </form>

                <div style="display: flex; gap: 10px; margin-left: auto;">
                    <button onclick="document.getElementById('addModal').classList.add('show')"
                        class="btn btn-primary-gradient">
                        <i class="fas fa-user-plus"></i> Add Teacher/Staff
                    </button>
                </div>
            </div>
        </div>

        <?php if ($msg): ?>
            <div id="successMessage"
                style="background: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 16px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; border: 1px solid #10b981;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div class="responsive-table-container">
            <table>
                <thead>
                    <tr style="background: #f8fafc;">
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9; width: 80px;">
                            INFO</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            NAME & SPECIALIZATION</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            STAFF TIER</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            CONTACT</th>
                        <th
                            style="padding: 1.25rem; font-weight: 700; color: #475569; border-bottom: 2px solid #f1f5f9;">
                            ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers_list as $row): ?>
                        <tr>
                            <td style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <?php if (!empty($row['teacher_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['teacher_photo']); ?>" alt="Photo"
                                        style="width: 50px; height: 50px; border-radius: 14px; object-fit: cover; border: 2px solid #eef2ff; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                                <?php else: ?>
                                    <div
                                        style="width: 50px; height: 50px; border-radius: 14px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.2rem; border: 2px solid #eef2ff;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 800; color: #1e293b; font-size: 1.05rem;">
                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;margin-top:3px;">
                                    <span
                                        style="font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:6px;<?= ($row['staff_role'] ?? 'Teacher') === 'Staff' ? 'background:#fef3c7;color:#92400e;' : 'background:#ede9fe;color:#5b21b6;' ?>"><?= htmlspecialchars($row['staff_role'] ?? 'Teacher') ?></span>
                                    <span style="font-size: 0.85rem; color: #4f46e5; font-weight: 600;">Sub:
                                        <?php echo htmlspecialchars($row['subject']); ?></span>
                                </div>
                            </td>
                            <td style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; color: #475569; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($row['tah']); ?>
                                </div>
                                <span
                                    style="font-size: 0.75rem; background: #f1f5f9; color: #64748b; padding: 2px 8px; border-radius: 6px; font-weight: 700;">
                                    <?php echo htmlspecialchars($row['teacher_type']); ?>
                                </span>
                            </td>
                            <td
                                style="padding: 1.25rem; border-bottom: 1px solid #f1f5f9; font-weight: 500; color: #1e293b;">
                                <i class="fas fa-phone-alt"
                                    style="font-size: 0.8rem; color: #10b981; margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($row['contact']); ?>
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
                                        onclick='deleteTeacher(<?php echo $row['id']; ?>)'>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <h2 style="margin-bottom: 1rem;">Add New Teacher/Staff</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_teacher" value="1">
                <input type="hidden" name="staff_role" id="addStaffRole" value="Teacher">
                <!-- Role Toggle -->
                <div
                    style="display:flex;gap:0;margin-bottom:1.5rem;border:2px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <button type="button" id="addRoleTeacher" onclick="setAddRole('Teacher')"
                        style="flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;background:#2563eb !important;color:white !important;transition:background 0.2s,color 0.2s;"><i
                            class="fas fa-chalkboard-teacher"></i> Teacher</button>
                    <button type="button" id="addRoleStaff" onclick="setAddRole('Staff')"
                        style="flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;background:#e2e8f0 !important;color:#475569 !important;transition:background 0.2s,color 0.2s;"><i
                            class="fas fa-user-tie"></i> Staff</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Full Name *</label>
                        <input type="text" name="full_name" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Teacher Photo</label>
                        <input type="file" name="teacher_photo" class="form-control" accept=".jpg, .jpeg, .png"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Type *</label>
                        <select name="teacher_type" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                            <option value="Permanent">Permanent</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Internal source">Internal source</option>
                            <option value="School source">School source</option>
                            <option value="अनुदान">अनुदान (Grant)</option>
                        </select>
                    </div>
                    <div class="form-group teacher-only-field">
                        <label style="display:block; margin-bottom: 5px;">Tah (Level) *</label>
                        <select name="tah" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                            <option value="प्रा.वि">प्रा.वि (Primary)</option>
                            <option value="नि.मा.वि">नि.मा.वि (Lower Secondary)</option>
                            <option value="मा.वि">मा.वि (Secondary)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Attendance Date (BS) *</label>
                        <input type="text" name="attendance_date_nepali" id="add_attendance_date_nepali"
                            class="form-control nepali-date" placeholder="YYYY/MM/DD" maxlength="10"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                    </div>
                    <div class="form-group teacher-only-field">
                        <label style="display:block; margin-bottom: 5px;">Subject *</label>
                        <input type="text" name="subject" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Phone Number *</label>
                        <input type="text" name="contact" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" pattern="(97|98)[0-9]{8}"
                            maxlength="10" title="Phone number must be 10 digits starting with 97 or 98"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">PAN Number</label>
                        <input type="text" name="pan_no" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" pattern="[0-9]{9}"
                            title="PAN number must be 9 digits"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Citizenship Number</label>
                        <input type="text" name="citizenship_no" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" pattern="[0-9/\-]+"
                            title="Please enter a valid citizenship number (numbers, hyphen, slash allowed)">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Account Number</label>
                        <input type="text" name="account_number" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Blood Group</label>
                        <select name="blood_group" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="form-group teacher-only-field">
                        <label style="display:block; margin-bottom: 5px;">Password *</label>
                        <input type="password" name="teacher_password" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required
                            placeholder="Set login password"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" minlength="8"
                            title="Password must be at least 8 characters and include: uppercase letter, lowercase letter, number, and special character (@$!%*?&)">
                        <small style="color: #6b7280; font-size: 11px;">Min 8 chars: upper, lower, num, special
                            (@$!%*?&)</small>
                    </div>
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom: 5px;">Address</label>
                    <textarea name="address" class="form-control"
                        style="background:#f9fafb; color:black; border:1px solid #ddd; height: 80px;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('addModal').classList.remove('show')"
                        class="btn" style="flex:1; background:#e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Teacher/Staff</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <h2 style="margin-bottom: 1.5rem;">Teacher/Staff Details</h2>
            <div id="viewModalBody" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Content injected by JS -->
            </div>
            <div style="margin-top: 2rem; text-align: right;">
                <button onclick="document.getElementById('viewModal').classList.remove('show')" class="btn"
                    style="background:#e5e7eb;">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <h2 style="margin-bottom: 1rem;">Edit Teacher/Staff</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_teacher" value="1">
                <input type="hidden" name="teacher_id" id="edit_teacher_id">
                <input type="hidden" name="staff_role" id="editStaffRole" value="Teacher">
                <!-- Role Toggle -->
                <div
                    style="display:flex;gap:0;margin-bottom:1.5rem;border:2px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <button type="button" id="editRoleTeacher" onclick="setEditRole('Teacher')"
                        style="flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;background:#2563eb !important;color:white !important;transition:background 0.2s,color 0.2s;"><i
                            class="fas fa-chalkboard-teacher"></i> Teacher</button>
                    <button type="button" id="editRoleStaff" onclick="setEditRole('Staff')"
                        style="flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;background:#e2e8f0 !important;color:#475569 !important;transition:background 0.2s,color 0.2s;"><i
                            class="fas fa-user-tie"></i> Staff</button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Full Name *</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Update Photo</label>
                        <input type="file" name="teacher_photo" class="form-control" accept=".jpg, .jpeg, .png"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Type *</label>
                        <select name="teacher_type" id="edit_teacher_type" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                            <option value="Permanent">Permanent</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Internal source">Internal source</option>
                            <option value="School source">School source</option>
                            <option value="अनुदान">अनुदान (Grant)</option>
                        </select>
                    </div>
                    <div class="form-group teacher-only-field">
                        <label style="display:block; margin-bottom: 5px;">Tah (Level) *</label>
                        <select name="tah" id="edit_tah" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                            <option value="प्रा.वि">प्रा.वि (Primary)</option>
                            <option value="नि.मा.वि">नि.मा.वि (Lower Secondary)</option>
                            <option value="मा.वि">मा.वि (Secondary)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Attendance Date (BS) *</label>
                        <input type="text" name="attendance_date_nepali" id="edit_attendance_date_nepali"
                            class="form-control nepali-date" placeholder="YYYY/MM/DD" maxlength="10"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                    </div>
                    <div class="form-group teacher-only-field">
                        <label style="display:block; margin-bottom: 5px;">Subject *</label>
                        <input type="text" name="subject" id="edit_subject" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Phone Number *</label>
                        <input type="text" name="contact" id="edit_contact" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" pattern="(97|98)[0-9]{8}"
                            maxlength="10" title="Phone number must be 10 digits starting with 97 or 98"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">PAN Number</label>
                        <input type="text" name="pan_no" id="edit_pan_no" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" pattern="[0-9]{9}"
                            title="PAN number must be 9 digits"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Citizenship Number</label>
                        <input type="text" name="citizenship_no" id="edit_citizenship_no" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;" pattern="[0-9/\-]+"
                            title="Please enter a valid citizenship number (numbers, hyphen, slash allowed)">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Bank Name</label>
                        <input type="text" name="bank_name" id="edit_bank_name" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Account Number</label>
                        <input type="text" name="account_number" id="edit_account_number" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                    </div>
                    <div class="form-group">
                        <label style="display:block; margin-bottom: 5px;">Blood Group</label>
                        <select name="blood_group" id="edit_blood_group" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="form-group teacher-only-field">
                        <label style="display:block; margin-bottom: 5px;">Password</label>
                        <input type="password" name="teacher_password" id="edit_teacher_password" class="form-control"
                            style="background:#f9fafb; color:black; border:1px solid #ddd;"
                            placeholder="Update password (leave empty to keep current)"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" minlength="8"
                            title="Password must be at least 8 characters and include: uppercase letter, lowercase letter, number, and special character (@$!%*?&)">
                        <small style="color: #6b7280; font-size: 11px;">Min 8 chars: upper, lower, num, special
                            (@$!%*?&)</small>
                    </div>
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom: 5px;">Address</label>
                    <textarea name="address" id="edit_address" class="form-control"
                        style="background:#f9fafb; color:black; border:1px solid #ddd; height: 80px;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="document.getElementById('editModal').classList.remove('show')"
                        class="btn" style="flex:1; background:#e5e7eb;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Update Teacher/Staff</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openViewModal(data) {
            const body = document.getElementById('viewModalBody');
            const photoHtml = data.teacher_photo
                ? `<div style="grid-column: span 2; text-align: center; margin-bottom: 1rem;">
                    <img src="${data.teacher_photo}" alt="Teacher Photo" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover; border: 3px solid #f3f4f6; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                   </div>`
                : `<div style="grid-column: span 2; text-align: center; margin-bottom: 1rem;">
                    <div style="width: 120px; height: 120px; border-radius: 12px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 3rem; margin: 0 auto; border: 3px solid #e5e7eb;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                   </div>`;

            body.innerHTML = `
                ${photoHtml}
                <div><strong>Full Name:</strong><br>${data.full_name}</div>
                <div><strong>Subject:</strong><br>${data.subject}</div>
                <div><strong>Contact:</strong><br>${data.contact}</div>
                <div><strong>Type:</strong><br>${data.teacher_type || '-'}</div>
                <div><strong>Level (Tah):</strong><br>${data.tah || '-'}</div>
                <div><strong>Attendance Date:</strong><br>${data.attendance_date_nepali || '-'}</div>
                <div><strong>PAN No:</strong><br>${data.pan_no || '-'}</div>
                <div><strong>Citizenship No:</strong><br>${data.citizenship_no || '-'}</div>
                <div><strong>Bank Name:</strong><br>${data.bank_name || '-'}</div>
                <div><strong>Account Number:</strong><br>${data.account_number || '-'}</div>
                <div><strong>Blood Group:</strong><br>${data.blood_group || '-'}</div>
                <div><strong>Password:</strong><br>${data.teacher_password || 'Not Set'}</div>
                <div style="grid-column: span 2;"><strong>Address:</strong><br>${data.address || '-'}</div>
            `;
            document.getElementById('viewModal').classList.add('show');
        }

        function openEditModal(data) {
            document.getElementById('edit_teacher_id').value = data.id;
            document.getElementById('edit_full_name').value = data.full_name;
            // Set role toggle
            setEditRole(data.staff_role || 'Teacher');
            document.getElementById('edit_subject').value = data.subject;
            document.getElementById('edit_contact').value = data.contact;
            document.getElementById('edit_teacher_type').value = data.teacher_type || 'Permanent';
            document.getElementById('edit_tah').value = data.tah || 'प्रा.वि';
            document.getElementById('edit_attendance_date_nepali').value = data.attendance_date_nepali || '';
            document.getElementById('edit_pan_no').value = data.pan_no || '';
            document.getElementById('edit_citizenship_no').value = data.citizenship_no || '';
            document.getElementById('edit_bank_name').value = data.bank_name || '';
            document.getElementById('edit_account_number').value = data.account_number || '';
            document.getElementById('edit_blood_group').value = data.blood_group || '';
            document.getElementById('edit_teacher_password').value = data.teacher_password || '';
            document.getElementById('edit_address').value = data.address || '';

            document.getElementById('editModal').classList.add('show');
        }

        // Role toggle helpers
        function toggleTeacherFields(modalId, isTeacher) {
            document.querySelectorAll('#' + modalId + ' .teacher-only-field').forEach(el => {
                el.style.display = isTeacher ? 'block' : 'none';
                const inputs = el.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (isTeacher) {
                        input.removeAttribute('disabled');
                    } else {
                        input.setAttribute('disabled', 'disabled');
                        input.value = ''; // clear value when hiding
                    }
                });
            });
        }

        function setAddRole(role) {
            document.getElementById('addStaffRole').value = role;
            const isTeacher = role === 'Teacher';
            document.getElementById('addRoleTeacher').setAttribute('style', `flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;transition:background 0.2s,color 0.2s;background:${isTeacher ? '#2563eb' : '#e2e8f0'} !important;color:${isTeacher ? 'white' : '#475569'} !important;`);
            document.getElementById('addRoleStaff').setAttribute('style', `flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;transition:background 0.2s,color 0.2s;background:${isTeacher ? '#e2e8f0' : '#2563eb'} !important;color:${isTeacher ? '#475569' : 'white'} !important;`);
            toggleTeacherFields('addModal', isTeacher);
        }
        function setEditRole(role) {
            document.getElementById('editStaffRole').value = role;
            const isTeacher = role === 'Teacher';
            document.getElementById('editRoleTeacher').setAttribute('style', `flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;transition:background 0.2s,color 0.2s;background:${isTeacher ? '#2563eb' : '#e2e8f0'} !important;color:${isTeacher ? 'white' : '#475569'} !important;`);
            document.getElementById('editRoleStaff').setAttribute('style', `flex:1;padding:11px;font-weight:700;font-size:0.95rem;border:none;cursor:pointer;transition:background 0.2s,color 0.2s;background:${isTeacher ? '#e2e8f0' : '#2563eb'} !important;color:${isTeacher ? '#475569' : 'white'} !important;`);
            toggleTeacherFields('editModal', isTeacher);
        }

        // Auto-date mask (YYYY/MM/DD)
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

        // Auto-fade status message
        const msg = document.getElementById('statusMessage');
        if (msg) {
            setTimeout(() => {
                msg.style.opacity = '0';
                setTimeout(() => {
                    msg.remove();
                }, 500);
            }, 3000);
        }

        // Faculty Deletion Protocol
        function deleteTeacher(id) {
            if (confirm('⚠️ WARNING: You are about to PERMANENTLY delete this faculty record. This action will erase all associated employment history and cannot be undone. \n\nAre you absolutely sure?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const deleteInput = document.createElement('input');
                deleteInput.name = 'delete_teacher';
                deleteInput.value = '1';
                form.appendChild(deleteInput);

                const idInput = document.createElement('input');
                idInput.name = 'teacher_id';
                idInput.value = id;
                form.appendChild(idInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

</body>

</html>