<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
require_once 'includes/nepali_date_helper.php';

$current_ad_date = date('Y-m-d');
$current_bs_date = NepaliDateHelper::convertToNepali($current_ad_date);
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart विद्यालय - Simplicity & Power</title>
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .nav-links a {
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            padding: 10px 22px !important;
            background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
            border-radius: 14px !important;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4) !important;
            transition: all 0.3s ease !important;
            display: inline-block !important;
            text-shadow: none !important;
        }

        .nav-links a:hover {
            color: #ffffff !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.6) !important;
            background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
        }

        .live-date-clock {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 18px;
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-size: 0.85rem;
            color: #1f2937;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .live-date-clock:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.2);
        }

        @media (max-width: 1024px) {
            .live-date-clock {
                display: none !important;
            }
        }
    </style>
</head>

<body class="theme-landing-dark">

    <!-- Hero Section -->
    <header class="landing-hero">
        <!-- Animated Background Shapes -->
        <div class="hero-bg-shape shape-1"></div>
        <div class="hero-bg-shape shape-2"></div>
        <div class="hero-bg-shape shape-3"></div> <!-- Added 3rd shape for balance -->

        <!-- Navigation -->
        <nav class="nav-landing animate-up">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <a href="#" class="nav-brand">
                    <i class="fas fa-graduation-cap fa-lg text-primary"></i>
                    <span>Smart विद्यालय</span>
                </a>

                <div class="live-date-clock">
                    <i class="far fa-clock" style="color: #4f46e5; font-size: 1rem;"></i>
                    <span id="liveClockIndex"
                        style="font-weight: 700; color: #4f46e5; letter-spacing: 1px;">--:--:--</span>
                    <span style="color: #d1d5db;">|</span>
                    <i class="far fa-calendar-alt" style="color: #6b7280;"></i>
                    <span><?php echo date('l, d M Y'); ?></span>
                    <span style="color: #d1d5db;">|</span>
                    <span style="color: #10b981; font-weight: 700;">
                        <?php echo date('l'); ?>, <?php echo $current_bs_date; ?> B.S.
                    </span>
                </div>
            </div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="about_us.php">About</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary-gradient"
                        style="padding: 10px 25px; box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn btn-white" style="color: #4f46e5; padding: 10px 25px;">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="hero-container">
            <!-- Left Side: Text Content -->
            <div class="hero-text-side">
                <div class="hero-badge" data-aos="fade-right">
                    <span class="badge-dot"></span> #1 School Management Platform
                </div>

                <h1 class="hero-title" data-aos="fade-right" data-aos-delay="100">
                    Manage Your School <br>
                    <span class="highlight">with Zero Gravity</span>
                </h1>

                <p class="hero-subtitle" data-aos="fade-right" data-aos-delay="200">
                    Experience the next generation of school management.
                    Automated attendance, instant billing, and powerful analytics—all in one beautiful interface.
                </p>

                <div class="hero-buttons" data-aos="fade-right" data-aos-delay="300">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="btn btn-white">
                            <i class="fas fa-th-large"></i> Go to Dashboard
                        </a>
                    <?php else: ?>
                        <a href="start_trial.php" class="btn btn-primary-gradient">
                            <i class="fas fa-rocket"></i> Start Free Trial
                        </a>
                        <a href="login.php" class="btn btn-glass">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    <?php endif; ?>
                </div>

                <div class="hero-trust" data-aos="fade-right" data-aos-delay="400">
                    <span>Trusted by 500+ schools</span>
                    <div class="avatars">
                        <img src="https://i.pravatar.cc/100?img=1" alt="">
                        <img src="https://i.pravatar.cc/100?img=2" alt="">
                        <img src="https://i.pravatar.cc/100?img=3" alt="">
                        <img src="https://i.pravatar.cc/100?img=4" alt="">
                        <div class="avatar-more">+2k</div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Visuals/Stats -->
            <div class="hero-visual-side" data-aos="fade-left" data-aos-delay="200">
                <div class="glass-card-premium float-card card-1">
                    <div class="premium-icon-box" style="background: rgba(99, 102, 241, 0.2); color: #818cf8;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>50k+</h3>
                    <p>Students Managed</p>
                </div>

                <div class="glass-card-premium float-card card-2">
                    <div class="premium-icon-box" style="background: rgba(236, 72, 153, 0.2); color: #f472b6;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>$2M+</h3>
                    <p>Fees Processed</p>
                </div>

                <div class="glass-card-premium float-card card-main">
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                        <h4 style="margin: 0; font-size: 1.1rem;">Attendance Overview</h4>
                        <span
                            style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">Live</span>
                    </div>
                    <div
                        style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; margin-bottom: 1rem; overflow: hidden;">
                        <div style="width: 92%; height: 100%; background: linear-gradient(90deg, #6366f1, #ec4899);">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #cbd5e1;">
                        <span>Present: 92%</span>
                        <span>Absent: 8%</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="section-padding" style="background: var(--body-bg);">
        <div class="section-title" style="text-align: center; margin-bottom: 3rem;" data-aos="fade-up">
            <span
                style="color: #4f46e5; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem;">Powerful
                Modules</span>
            <h2>Everything You Need</h2>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 1rem auto;">
                From admissions to alumni, handle every aspect of your institution with our comprehensive suite of
                tools.
            </p>
        </div>

        <div class="feature-grid">
            <!-- Card 1 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="100">
                <div class="icon-wrapper">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: #1e293b;">Student 360°
                </h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">Comprehensive student profiles with
                    admission details, academic history, and contact information all in one secure place.</p>
            </div>

            <!-- Card 2 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="200">
                <div class="icon-wrapper"
                    style="background: linear-gradient(135deg, #ecfccb, #dcfce7); color: #16a34a;">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: #1e293b;">Expert Faculty
                </h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">Manage teacher profiles, subject
                    allocations, and departmental roles efficiently. Empower your staff with digital tools.</p>
            </div>

            <!-- Card 3 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="300">
                <div class="icon-wrapper"
                    style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4f46e5;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: #1e293b;">Exams & Results
                </h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">Create exams, generate professional
                    admit cards, and publish marksheets instantly. Automated ledger generation included.</p>
            </div>

            <!-- Card 4 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="400">
                <div class="icon-wrapper"
                    style="background: linear-gradient(135deg, #ffedd5, #fed7aa); color: #ea580c;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: #1e293b;">Billing System
                </h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">Track student fees, manage donations,
                    and generate digital receipts. Keep a transparent financial history for every user.</p>
            </div>

            <!-- Card 5 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="500">
                <div class="icon-wrapper"
                    style="background: linear-gradient(135deg, #fce7f3, #fbcfe8); color: #db2777;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: #1e293b;">Smart Attendance
                </h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">Digital daily attendance tracking with
                    automated email notifications to parents/guardians. Say goodbye to paper registers.</p>
            </div>

            <!-- Card 6 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="600">
                <div class="icon-wrapper"
                    style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #475569;">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: #1e293b;">Mobile Ready
                </h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">Fully responsive design that works
                    perfectly on all devices—smartphones, tablets, and desktops. Access your school anywhere.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: #0f172a; color: #94a3b8; padding: 5rem 2rem 2rem;">
        <div
            style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 4rem;">
            <div>
                <h3 style="color: white; font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem;">Smart विद्यालय
                </h3>
                <p style="line-height: 1.8;">Empowering educational institutions with cutting-edge technology. Join the
                    digital revolution today.</p>
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <a href="https://www.facebook.com/share/1AdMMjH2yP/" target="_blank" class="btn-glass"
                        style="width: 40px; height: 40px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i
                            class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/itsmeraaz66?igsh=M2cxempldWd1c2I0" target="_blank"
                        class="btn-glass"
                        style="width: 40px; height: 40px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i
                            class="fab fa-instagram"></i></a>
                    <a href="https://wa.me/9779861079061" target="_blank" class="btn-glass"
                        style="width: 40px; height: 40px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i
                            class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <div>
                <h4 style="color: white; font-size: 1.2rem; margin-bottom: 1.5rem;">Quick Links</h4>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="#" style="transition: 0.3s; color: inherit;">Features</a>
                    <a href="pricing.php" style="transition: 0.3s; color: inherit;">Pricing</a>
                    <a href="case_studies.php" style="transition: 0.3s; color: inherit;">Case Studies</a>
                    <a href="support.php" style="transition: 0.3s; color: inherit;">Support</a>
                </div>
            </div>

            <div>
                <h4 style="color: white; font-size: 1.2rem; margin-bottom: 1.5rem;">Contact Us</h4>
                <p style="margin-bottom: 1rem;">
                    <a href="https://www.google.com/maps?q=27.681912,84.442490" target="_blank"
                        style="color: inherit; text-decoration: none; display: flex; align-items: center;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px; color: #4f46e5;"></i> Bharatpur,
                        Nepal
                    </a>
                </p>
                <p style="margin-bottom: 1rem;"><a
                        href="https://mail.google.com/mail/?view=cm&fs=1&to=smartvidhyalaya9861@gmail.com"
                        target="_blank" style="color: inherit; display: flex; align-items: center;"><i
                            class="fas fa-envelope" style="margin-right: 10px; color: #4f46e5;"></i>
                        smartvidhyalaya9861@gmail.com</a></p>
                <p><a href="tel:+9779861079061"
                        style="color: inherit; text-decoration: none; display: flex; align-items: center;"><i
                            class="fas fa-phone" style="margin-right: 10px; color: #4f46e5;"></i> +977-9861079061</a>
                </p>
            </div>
        </div>

        <div
            style="text-align: center; margin-top: 4rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <p>&copy; 2026 Smart विद्यालय. Developed with <i class="fas fa-heart text-red-500"></i> by Raj Bhusal and
                Dibash Sharma.</p>
        </div>
    </footer>

    <!-- AOS Script -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 100
        });

        // Live Clock Script
        document.addEventListener('DOMContentLoaded', function () {
            const clockEl = document.getElementById('liveClockIndex');
            if (clockEl) {
                function updateIndexClock() {
                    const now = new Date();
                    let h = now.getHours();
                    let m = String(now.getMinutes()).padStart(2, '0');
                    let s = String(now.getSeconds()).padStart(2, '0');
                    let ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12;
                    h = h ? h : 12;
                    h = String(h).padStart(2, '0');
                    clockEl.textContent = `${h}:${m}:${s} ${ampm}`;
                }
                updateIndexClock(); // Initialize immediately
                setInterval(updateIndexClock, 1000);
            }
        });
    </script>
</body>

</html>