<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth; background: #030712;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Smart विद्यालय</title>
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
            <a href="about_us.php" style="color: white;">About</a>
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
            <h1 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem;">Empowering Education <br> <span
                    class="highlight"
                    style="background: linear-gradient(to right, #6366f1, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Through
                    Innovation</span></h1>
            <p style="font-size: 1.25rem; color: #94a3b8; max-width: 800px; margin: 0 auto; line-height: 1.8;">
                Smart विद्यालय is more than just software. We are a team of educators and technologists dedicated to
                transforming how schools operate, making administration seamless and learning accessible.
            </p>
        </div>
    </header>

    <!-- Mission & Vision -->
    <section class="section-padding" style="background: #f8fafc;">
        <div class="feature-grid" style="margin-top: 0; max-width: 1200px;">
            <div class="glass-card-light" data-aos="fade-up" style="min-height: auto; padding: 3rem;">
                <div class="icon-wrapper" style="background: #e0e7ff; color: #4f46e5;">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">Our Mission</h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">
                    To simplify school management by providing a comprehensive, user-friendly, and affordable digital
                    platform that connects schools, teachers, students, and parents.
                </p>
            </div>

            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="200"
                style="min-height: auto; padding: 3rem;">
                <div class="icon-wrapper" style="background: #fce7f3; color: #db2777;">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">Our Vision</h3>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.8;">
                    To be the leading education technology provider in Nepal, fostering a digital ecosystem where every
                    school has access to world-class management tools.
                </p>
            </div>
        </div>
    </section>

    <!-- Our Story -->
    <section class="section-padding" style="background: #fff; color: #1e293b;">
        <div
            style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
            <div data-aos="fade-right">
                <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1.5rem;">Driven by Passion,<br>Built for
                    Impact.</h2>
                <p style="font-size: 1.1rem; color: #64748b; line-height: 1.8; margin-bottom: 1.5rem;">
                    Founded in 2024, Smart विद्यालय started with a simple observation: schools were spending too much
                    time on paperwork and not enough time on students.
                </p>
                <p style="font-size: 1.1rem; color: #64748b; line-height: 1.8;">
                    Our founder, Raj Bhusal, envisioned a platform that would automate the mundane, illuminate the
                    important, and bring joy back to school administration. Today, we serve hundreds of institutions
                    across the region.
                </p>
            </div>
            <div style="position: relative;" data-aos="fade-left">
                <div class="glass-card-premium"
                    style="height: 400px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #4f46e5, #818cf8);">
                    <i class="fas fa-users" style="font-size: 5rem; color: white; opacity: 0.8;"></i>
                </div>
                <div class="glass-card"
                    style="position: absolute; bottom: -30px; left: -30px; background: white; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.1);">
                    <div style="font-size: 3rem; font-weight: 800; color: #4f46e5;">3+</div>
                    <div style="color: #64748b; font-weight: 600;">Years of Excellence</div>
                </div>
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