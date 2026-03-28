<?php
require_once 'language_manager.php';
if (isset($_SESSION['school_photo']) && !empty($_SESSION['school_photo'])): ?>
    <style>
        :root {
            --overlay-color: rgba(255, 255, 255, 0.92);
            --card-glass: rgba(255, 255, 255, 0.98);
        }

        html[data-theme="dark"] {
            --overlay-color: rgba(15, 23, 42, 0.88);
            --card-glass: rgba(30, 41, 59, 0.9);
        }

        body {
            background: linear-gradient(var(--overlay-color), var(--overlay-color)), url('<?php echo htmlspecialchars($_SESSION['school_photo']); ?>') !important;
            background-size: cover !important;
            background-position: center !important;
            background-attachment: fixed !important;
            background-repeat: no-repeat !important;
        }

        .main-content {
            background: transparent !important;
        }

        .stat-card,
        .table-container,
        .glass-panel,
        .glass-panel-v2 {
            background: var(--card-glass) !important;
            backdrop-filter: blur(8px);
            border: 1px solid var(--glass-border) !important;
        }
    </style>
<?php endif; ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

    :root {
        --nav-bg: rgba(255, 255, 255, 0.95);
        --nav-border: rgba(229, 231, 235, 0.5);
        --nav-height: 80px;
        --sidebar-width: 280px;
    }

    body {
        font-family: 'Outfit', sans-serif;
    }

    .top-navbar {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--glass-border);
        padding: 0 1.5rem;
        min-height: var(--nav-height);
        height: auto;
        /* Dynamic height for safety */
        padding: 0.5rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 2000;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        flex-wrap: wrap;
        /* Safety wrap */
        gap: 15px;
    }

    .theme-toggle-btn,
    .lang-toggle-btn {
        color: #4f46e5 !important;
        /* Force darker blue */
        font-weight: 800 !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        background: white !important;
        /* Force solid white background */
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 1rem;
        text-decoration: none;
    }

    .theme-toggle-btn:hover,
    .lang-toggle-btn:hover {
        background: #eef2ff;
        border-color: #4f46e5;
        color: #4f46e5;
        transform: translateY(-2px);
    }

    [data-theme="dark"] .theme-toggle-btn:hover {
        background: #334155;
        color: #fbbf24 !important;
        /* Golden sun */
    }

    [data-theme="dark"] .lang-toggle-btn {
        color: #818cf8 !important;
    }



    .top-nav-item span {
        color: #475569;
        /* Solid slate gray */
        font-weight: 700;
    }

    .top-nav-item i {
        color: #334155;
        opacity: 0.9;
    }

    .top-nav-item.active span {
        color: #4f46e5;
    }

    [data-theme="dark"] .top-nav-item.active span {
        color: #818cf8;
    }

    .nav-brand-container {
        display: flex;
        align-items: center;
        gap: 15px;
        /* Back to original more compact gap */
        text-decoration: none;
        margin-right: 30px;
        /* Reduced from 60px */
        flex-shrink: 0;
    }

    .school-logo-img {
        height: 60px;
        /* Slightly larger */
        width: 60px;
        object-fit: contain;
        border-radius: 14px;
        background: var(--body-bg);
        padding: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        /* Soft shadow for depth */
    }

    .brand-text-block {
        display: flex;
        flex-direction: column;
        line-height: 1.4;
        /* Slightly more relaxed */
    }

    .school-name-text {
        font-size: 1.25rem;
        /* Slightly reduced for flexibility */
        font-weight: 800;
        color: #1e293b;
        background: linear-gradient(135deg, #4f46e5, #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        max-width: 300px;
        /* Prevent over-growing */
        white-space: normal;
        /* Allow wrapping if needed but keep it in block */
    }

    .school-meta-text {
        font-size: 0.75rem;
        color: #475569;
        /* Darker gray for visibility */
        font-weight: 600;
        margin-top: 2px;
    }

    .desktop-links {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 1;
        justify-content: flex-end;
        min-width: 0;
        flex-shrink: 1;
    }

    .top-nav-item {
        display: flex;
        align-items: center;
        gap: 6px;
        /* Tiny gap */
        padding: 0.6rem 0.8rem;
        /* Even more compact */
        color: #334155;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        /* Smaller font for fit */
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        flex-shrink: 0;
        /* Keep individual items solid but the group can shrink */
        white-space: nowrap;
        /* Don't wrap text within items */
    }

    .top-nav-item i {
        font-size: 1.1rem;
        transition: transform 0.3s;
    }

    .top-nav-item:hover {
        background: var(--primary);
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.2);
    }

    .top-nav-item:hover span,
    .top-nav-item:hover i {
        color: white !important;
        opacity: 1;
    }

    .top-nav-item:hover i {
        transform: scale(1.2) rotate(-5deg);
    }

    .top-nav-item.active {
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
        border-bottom: 3px solid var(--primary);
        border-radius: 12px 12px 0 0;
    }

    .top-nav-item.active span,
    .top-nav-item.active i {
        color: var(--primary) !important;
        opacity: 1;
    }

    .logout-btn-nav {
        color: #ef4444 !important;
        border: 1px solid transparent;
    }

    .logout-btn-nav i {
        color: #ef4444 !important;
        opacity: 1 !important;
    }

    .logout-btn-nav:hover {
        background: rgba(239, 68, 68, 0.1) !important;
        border-color: rgba(239, 68, 68, 0.2);
    }

    /* Mobile Interaction */
    .menu-toggle {
        display: none;
        background: #f1f5f9;
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 12px;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #1e293b;
        transition: all 0.3s;
    }

    .menu-toggle:hover {
        background: #e2e8f0;
        transform: scale(1.05);
    }

    .mobile-nav-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 2999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s ease;
    }

    .mobile-sidebar {
        position: fixed;
        top: 0;
        right: -320px;
        width: 300px;
        height: 100%;
        background: white;
        z-index: 3000;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
        padding: 2rem 1.5rem;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        overflow-y: auto;
    }

    .mobile-sidebar.open {
        right: 0;
    }

    .mobile-nav-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2.5rem;
    }

    .mobile-link-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .mobile-nav-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 1rem;
        border-radius: 14px;
        color: #4b5563;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .mobile-nav-item i {
        width: 24px;
        text-align: center;
        font-size: 1.25rem;
    }

    .mobile-nav-item:hover,
    .mobile-nav-item.active {
        background: var(--body-bg);
        color: #4f46e5;
    }

    .close-nav {
        background: var(--body-bg);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        color: var(--text-main);
        font-size: 1.1rem;
        cursor: pointer;
    }

    @media (max-width: 1280px) {
        .desktop-links {
            display: none;
        }

        .menu-toggle {
            display: flex;
        }
    }

    @media (max-width: 640px) {
        .top-navbar {
            padding: 0 1rem;
        }

        .school-meta-text {
            display: none;
        }

        .school-logo-img {
            height: 45px;
            width: 45px;
        }
    }
</style>

<nav class="top-navbar">
    <a href="dashboard.php" class="nav-brand-container">
        <?php if (isset($_SESSION['school_logo']) && !empty($_SESSION['school_logo'])): ?>
            <img src="<?php echo htmlspecialchars($_SESSION['school_logo']); ?>" alt="Logo" class="school-logo-img">
        <?php else: ?>
            <div class="school-logo-img" style="display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-school" style="font-size: 1.5rem; color: #4f46e5;"></i>
            </div>
        <?php endif; ?>

        <div class="brand-text-block">
            <span class="school-name-text">
                <?php
                if (isset($_SESSION['lang']) && $_SESSION['lang'] == 'ne' && isset($_SESSION['school_name_ne']) && !empty($_SESSION['school_name_ne'])) {
                    echo htmlspecialchars($_SESSION['school_name_ne']);
                } else {
                    echo htmlspecialchars($_SESSION['school_name']);
                }
                ?>
            </span>
            <span class="school-meta-text">
                <?php echo isset($_SESSION['school_address']) ? htmlspecialchars($_SESSION['school_address']) : ''; ?>
                <?php echo isset($_SESSION['estd_date']) ? ' | Estd: ' . htmlspecialchars($_SESSION['estd_date']) : ''; ?>
            </span>
        </div>
    </a>

    <!-- Desktop Navigation -->
    <div class="desktop-links">
        <a href="dashboard.php"
            class="top-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> <span><?php echo t('dashboard'); ?></span>
        </a>
        <?php if (hasFeature('students')): ?>
            <a href="students.php"
                class="top-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> <span><?php echo t('students'); ?></span>
            </a>
        <?php endif; ?>

        <?php if (hasFeature('teachers')): ?>
            <a href="teachers.php"
                class="top-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i> <span><?php echo t('teachers'); ?></span>
            </a>
        <?php endif; ?>

        <?php if (hasFeature('exams')): ?>
            <a href="exams.php"
                class="top-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['exams.php', 'exam_marks.php', 'exam_class_selector.php', 'mark_entry.php', 'mark_ledger.php']) ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> <span><?php echo t('exams'); ?></span>
            </a>
        <?php endif; ?>

        <?php if (hasFeature('billing')): ?>
            <a href="billing.php"
                class="top-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i> <span><?php echo t('billing'); ?></span>
            </a>
        <?php endif; ?>

        <?php if (hasFeature('attendance')): ?>
            <a href="attendance_entry.php"
                class="top-nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['attendance_entry.php']) ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> <span><?php echo t('attendance'); ?></span>
            </a>
            <a href="attendance_reports.php"
                class="top-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> <span>Reports</span>
            </a>
        <?php endif; ?>

        <?php if (hasFeature('id_cards')): ?>
            <a href="id_card_selector.php"
                class="top-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'id_card_selector.php' ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i> <span><?php echo t('id_cards'); ?></span>
            </a>
        <?php endif; ?>

        <!-- Theme Toggle -->
        <button onclick="toggleTheme()" class="theme-toggle-btn" title="Toggle Dark/Light Mode">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>

        <!-- Language Toggle -->
        <?php if ($_SESSION['lang'] == 'en'): ?>
            <a href="?lang=ne" class="lang-toggle-btn" title="Translate to Nepali">NE</a>
        <?php else: ?>
            <a href="?lang=en" class="lang-toggle-btn" title="Translate to English">EN</a>
        <?php endif; ?>

        <a href="logout.php" class="top-nav-item logout-btn-nav">
            <i class="fas fa-sign-out-alt"></i> <span><?php echo t('logout'); ?></span>
        </a>
    </div>

    <!-- Mobile Toggle Button -->
    <button class="menu-toggle" onclick="toggleMobileNav(true)">
        <i class="fas fa-bars"></i>
    </button>
</nav>

<!-- Mobile Sidebar Overlay -->
<div class="mobile-nav-overlay" id="mobileOverlay" onclick="toggleMobileNav(false)"></div>

<!-- Mobile Sidebar Content -->
<aside class="mobile-sidebar" id="mobileSidebar">
    <div class="sidebar-header">
        <span style="font-weight: 800; color: #4f46e5; font-size: 1.2rem;">Navigation</span>
        <button class="close-nav" onclick="toggleMobileNav(false)">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="mobile-link-list">
        <a href="dashboard.php"
            class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> <?php echo t('dashboard'); ?>
        </a>
        <?php if (hasFeature('students')): ?>
            <a href="students.php"
                class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> <?php echo t('students'); ?>
            </a>
        <?php endif; ?>

        <?php if (hasFeature('teachers')): ?>
            <a href="teachers.php"
                class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i> <?php echo t('teachers'); ?>
            </a>
        <?php endif; ?>

        <?php if (hasFeature('exams')): ?>
            <a href="exams.php"
                class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> Exams Hub
            </a>
        <?php endif; ?>

        <?php if (hasFeature('billing')): ?>
            <a href="billing.php"
                class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'billing.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i> Billing & Finance
            </a>
        <?php endif; ?>

        <?php if (hasFeature('attendance')): ?>
            <a href="attendance_entry.php"
                class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance_entry.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
            <a href="attendance_reports.php"
                class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-table"></i> Att. Reports
            </a>
        <?php endif; ?>

        <?php if (hasFeature('id_cards')): ?>
            <a href="id_card_selector.php"
                class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'id_card_selector.php' ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i> ID Card Generator
            </a>
        <?php endif; ?>
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1rem 0;">
        <a href="logout.php" class="mobile-nav-item logout-btn-nav">
            <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
        </a>
    </div>
</aside>

<script>
    // Theme Management
    const themeIcon = document.getElementById('themeIcon');

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        if (theme === 'dark') {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        } else {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }

    function toggleTheme() {
        const currentTheme = localStorage.getItem('theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
    }

    // Initialize theme on load
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);

    function toggleMobileNav(show) {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('mobileOverlay');

        if (show) {
            sidebar.classList.add('open');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent scroll
        } else {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // New logic: Listen for logout event in other tabs (Instant sync)
    window.addEventListener('storage', (event) => {
        if (event.key === 'logout_event') {
            window.location.reload();
        }
    });

    // Heartbeat check (Every 5 seconds) to ensure session is still alive on server
    setInterval(function () {
        if (document.visibilityState === 'visible') { // Only check if tab is active to save resources
            fetch('includes/session_check.php')
                .then(response => response.json())
                .then(data => {
                    if (data.authenticated === false) {
                        window.location.reload();
                    }
                })
                .catch(err => console.error('Session check failed:', err));
        }
    }, 5000);

</script>