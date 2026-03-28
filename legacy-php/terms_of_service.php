<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth; background: #030712;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Smart विद्यालय</title>
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>

<body>

    <!-- Navigation -->
    <nav class="nav-landing animate-up"
        style="position: sticky; top: 0; background: rgba(3, 7, 18, 0.9); backdrop-filter: blur(10px);">
        <a href="index.php" class="nav-brand">
            <i class="fas fa-graduation-cap fa-lg text-primary"></i>
            <span>Smart विद्यालय</span>
        </a>
        <div class="nav-links">
            <a href="index.php#features">Features</a>
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

    <!-- Header Section -->
    <header class="section-padding"
        style="padding-top: 5rem; padding-bottom: 5rem; text-align: center; color: white; position: relative; overflow: hidden;">
        <div class="hero-bg-shape shape-3" style="top: 10%; right: 10%;"></div>
        <div class="hero-bg-shape shape-1" style="bottom: 10%; left: 10%;"></div>

        <div style="position: relative; z-index: 2;" data-aos="fade-up">
            <h1 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem;">Terms of <br> <span
                    class="highlight"
                    style="background: linear-gradient(to right, #6366f1, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Service</span>
            </h1>
            <p style="font-size: 1.25rem; color: #94a3b8; max-width: 800px; margin: 0 auto; line-height: 1.8;">
                Please read these terms carefully before using our services.
            </p>
        </div>
    </header>

    <!-- Terms Content -->
    <section class="section-padding" style="background: #f8fafc; padding-bottom: 5rem;">
        <div style="max-width: 800px; margin: 0 auto;">

            <div class="glass-card-light" data-aos="fade-up" style="background: white; padding: 3rem; color: #374151;">

                <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">1. Acceptance of
                    Terms</h3>
                <p style="line-height: 1.8; margin-bottom: 2rem;">
                    By accessing and using Smart विद्यालय, you agree to be bound by these Terms of Service and all
                    applicable laws and regulations. If you do not agree with any of these terms, you are prohibited
                    from using this site.
                </p>

                <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">2. Use License
                </h3>
                <p style="line-height: 1.8; margin-bottom: 2rem;">
                    Permission is granted to temporarily download one copy of the materials (information or software) on
                    Smart विद्यालय's website for personal, non-commercial transitory viewing only. This is the grant of
                    a license, not a transfer of title, and under this license, you may not:
                <ul style="list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem;">
                    <li>modify or copy the materials;</li>
                    <li>use the materials for any commercial purpose, or for any public display (commercial or
                        non-commercial);</li>
                    <li>attempt to decompile or reverse engineer any software contained on Smart विद्यालय's website;
                    </li>
                    <li>remove any copyright or other proprietary notations from the materials; or</li>
                    <li>transfer the materials to another person or "mirror" the materials on any other server.</li>
                </ul>
                </p>

                <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">3. Disclaimer</h3>
                <p style="line-height: 1.8; margin-bottom: 2rem;">
                    The materials on Smart विद्यालय's website are provided on an 'as is' basis. Smart विद्यालय makes no
                    warranties, expressed or implied, and hereby disclaims and negates all other warranties including,
                    without limitation, implied warranties or conditions of merchantability, fitness for a particular
                    purpose, or non-infringement of intellectual property or other violation of rights.
                </p>

                <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">4. Limitations
                </h3>
                <p style="line-height: 1.8; margin-bottom: 2rem;">
                    In no event shall Smart विद्यालय or its suppliers be liable for any damages (including, without
                    limitation, damages for loss of data or profit, or due to business interruption) arising out of the
                    use or inability to use the materials on Smart विद्यालय's website, even if Smart विद्यालय or a Smart
                    विद्यालय authorized representative has been notified orally or in writing of the possibility of such
                    damage.
                </p>

                <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">5. Governing Law
                </h3>
                <p style="line-height: 1.8; margin-bottom: 0;">
                    These terms and conditions are governed by and construed in accordance with the laws of Nepal and
                    you irrevocably submit to the exclusive jurisdiction of the courts in that State or location.
                </p>

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
                    <a href="index.php#features" style="transition: 0.3s; color: inherit;">Features</a>
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
    </script>
</body>

</html>