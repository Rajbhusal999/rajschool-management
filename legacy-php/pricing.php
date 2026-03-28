<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth; background: #030712;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - Smart विद्यालय</title>
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
            <h1 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem;">Simple, Transparent <br> <span
                    class="highlight"
                    style="background: linear-gradient(to right, #6366f1, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Pricing</span>
            </h1>
            <p style="font-size: 1.25rem; color: #94a3b8; max-width: 800px; margin: 0 auto; line-height: 1.8;">
                Choose the plan that fits your school's needs. No hidden fees, cancel anytime.
            </p>
        </div>
    </header>

    <!-- Pricing Section -->
    <section class="section-padding" style="background: #f8fafc;">
        <div
            style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2.5rem;">

            <!-- Basic Plan -->
            <div class="glass-card-light" data-aos="fade-up"
                style="background: white; border: 1px solid #e2e8f0; padding: 3rem; position: relative;">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">1 Year Access</h3>
                <div style="font-size: 3rem; font-weight: 800; color: #1e293b; margin-bottom: 2rem;">
                    Rs. 5,000<span style="font-size: 1rem; color: #64748b; font-weight: 400;">/year</span>
                </div>
                <p style="color: #64748b; margin-bottom: 2rem; line-height: 1.6;">Perfect for schools getting
                    started.</p>
                <ul style="list-style: none; padding: 0; margin-bottom: 2.5rem; color: #475569;">
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Student Management</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Teacher Management</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Exams & Results</li>
                </ul>
                <a href="register.php" class="btn btn-white"
                    style="width: 100%; text-align: center; border: 1px solid #e2e8f0;">Get Started</a>
            </div>

            <!-- Standard Plan -->
            <div class="glass-card-premium" data-aos="fade-up" data-aos-delay="100"
                style="background: linear-gradient(135deg, #4f46e5, #4338ca); padding: 3rem; position: relative; transform: scale(1.05); z-index: 2; overflow: visible; backdrop-filter: none;">
                <div
                    style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #ec4899; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1); white-space: nowrap; z-index: 10;">
                    MOST POPULAR</div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 1rem;">2 Years Access</h3>
                <div style="font-size: 3rem; font-weight: 800; color: white; margin-bottom: 2rem;">
                    Rs. 8,000<span style="font-size: 1rem; color: rgba(255,255,255,0.7); font-weight: 400;">/2
                        years</span>
                </div>
                <p style="color: rgba(255,255,255,0.8); margin-bottom: 2rem; line-height: 1.6;">Our best value for
                    growing
                    schools.</p>
                <ul style="list-style: none; padding: 0; margin-bottom: 2.5rem; color: white;">
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check"></i> Student & Teacher Management</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check"></i> Exams & Marksheets</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check"></i> Billing & Accounts</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check"></i> ID Card Generation</li>
                </ul>
                <a href="register.php" class="btn btn-white"
                    style="width: 100%; text-align: center; color: #4f46e5;">Get Started</a>
            </div>

            <!-- Premium Plan -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="200"
                style="background: white; border: 1px solid #e2e8f0; padding: 3rem; position: relative;">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">5 Years Access
                </h3>
                <div style="font-size: 3rem; font-weight: 800; color: #1e293b; margin-bottom: 2rem;">
                    Rs. 20,000<span style="font-size: 1rem; color: #64748b; font-weight: 400;">/5 years</span>
                </div>
                <p style="color: #64748b; margin-bottom: 2rem; line-height: 1.6;">Long-term solution with full dedicated
                    support.</p>
                <ul style="list-style: none; padding: 0; margin-bottom: 2.5rem; color: #475569;">
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Student Management</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Teacher Management</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Exams & Results</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Billing System</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> Smart Attendance</li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;"><i
                            class="fas fa-check text-primary"></i> ID Card Generation</li>
                </ul>
                <a href="register.php" class="btn btn-white"
                    style="width: 100%; text-align: center; border: 1px solid #e2e8f0;">Contact Sales</a>
            </div>

        </div>

        <!-- SMS Note -->
        <div style="max-width: 800px; margin: 3rem auto 0; text-align: center; padding: 2rem; background: #eff6ff; border-radius: 20px; border: 1px dashed #3b82f6;"
            data-aos="fade-up">
            <p style="color: #1e40af; font-weight: 500; font-size: 1.1rem; margin: 0;">
                <i class="fas fa-info-circle" style="margin-right: 10px;"></i>
                Note: For attendance tracking, the cost of SMS is to be borne by the school itself, and we will assist
                in its integration.
            </p>
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