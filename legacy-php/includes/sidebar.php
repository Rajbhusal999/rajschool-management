<?php if (isset($_SESSION['school_photo']) && !empty($_SESSION['school_photo'])): ?>
    <style>
        body {
            background: linear-gradient(rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.92)), url('<?php echo htmlspecialchars($_SESSION['school_photo']); ?>') !important;
            background-size: cover !important;
            background-position: center !important;
            background-attachment: fixed !important;
            background-repeat: no-repeat !important;
        }

        .main-content {
            background: transparent !important;
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(10px);
        }

        .stat-card,
        .table-container,
        .glass-panel {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(5px);
        }
    </style>
<?php endif; ?>
<div class="sidebar">
    <div
        style="margin-bottom: 2rem; padding: 0 1rem; font-size: 1.25rem; font-weight: 800; color: #4f46e5; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-school"></i>
        <?php echo htmlspecialchars($_SESSION['school_name']); ?>
    </div>

    <a href="dashboard.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i
            class="fas fa-th-large"></i> Dashboard</a>
    <a href="students.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>"><i
            class="fas fa-user-graduate"></i> Students</a>
    <a href="teachers.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : ''; ?>"><i
            class="fas fa-chalkboard-teacher"></i> Teachers</a>
    <a href="exams.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : ''; ?>"><i
            class="fas fa-file-alt"></i> Exams</a>
    <a href="billing.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : ''; ?>"><i
            class="fas fa-file-invoice-dollar"></i> Billing</a>
    <a href="#" class="nav-item"><i class="fas fa-calendar-check"></i> Attendance</a>
    <a href="#" class="nav-item"><i class="fas fa-id-card"></i> ID Card</a>

    <div style="margin-top: auto; border-top: 1px solid #eee; padding-top: 1rem;">
        <a href="logout.php" class="nav-item" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>