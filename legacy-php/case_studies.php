<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth; background: #030712;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Studies - Smart विद्यालय</title>
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
            <h1 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1.5rem;">Our Success <br> <span
                    class="highlight"
                    style="background: linear-gradient(to right, #6366f1, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Stories</span>
            </h1>
            <p style="font-size: 1.25rem; color: #94a3b8; max-width: 800px; margin: 0 auto; line-height: 1.8;">
                See how Smart विद्यालय is transforming education across the nation.
            </p>
        </div>
    </header>

    <!-- Case Studies Section -->
    <section class="section-padding" style="background: #f8fafc;">
        <div
            style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 3rem;">

            <!-- Case Study 1 -->
            <div class="glass-card-light" data-aos="fade-up"
                style="background: white; border: 1px solid #e2e8f0; padding: 0; overflow: hidden; min-height: auto;">
                <div
                    style="height: 200px; background: linear-gradient(135deg, #e0e7ff, #c7d2fe); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-school" style="font-size: 4rem; color: #4f46e5; opacity: 0.5;"></i>
                </div>
                <div style="padding: 2rem;">
                    <span
                        style="background: #e0e7ff; color: #4f46e5; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">K-12
                        School</span>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 1rem 0;">Himalayan Academy
                    </h3>
                    <p style="color: #64748b; line-height: 1.6; margin-bottom: 1.5rem;">
                        Before Smart विद्यालय, Himalayan Academy struggled with attendance tracking and fee collection.
                        Manual ledgers led to errors and delays.
                    </p>
                    <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem; margin-top: auto;">
                        <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Impact:
                        </h4>
                        <ul style="list-style: none; padding: 0; color: #475569;">
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-primary"
                                    style="margin-right: 8px;"></i> 95% faster fee processing</li>
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-primary"
                                    style="margin-right: 8px;"></i> 100% accurate attendance records</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Case Study 2 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="100"
                style="background: white; border: 1px solid #e2e8f0; padding: 0; overflow: hidden; min-height: auto;">
                <div
                    style="height: 200px; background: linear-gradient(135deg, #fce7f3, #fbcfe8); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-university" style="font-size: 4rem; color: #db2777; opacity: 0.5;"></i>
                </div>
                <div style="padding: 2rem;">
                    <span
                        style="background: #fce7f3; color: #db2777; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Higher
                        Secondary</span>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 1rem 0;">Kathmandu Model
                        College</h3>
                    <p style="color: #64748b; line-height: 1.6; margin-bottom: 1.5rem;">
                        Managing exams for 2,000+ students was a nightmare. Generating results took weeks. They needed a
                        scalable solution.
                    </p>
                    <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem; margin-top: auto;">
                        <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Impact:
                        </h4>
                        <ul style="list-style: none; padding: 0; color: #475569;">
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-primary"
                                    style="margin-right: 8px;"></i> Results published in 24 hours</li>
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-primary"
                                    style="margin-right: 8px;"></i> Automated admit card generation</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Case Study 3 -->
            <div class="glass-card-light" data-aos="fade-up" data-aos-delay="200"
                style="background: white; border: 1px solid #e2e8f0; padding: 0; overflow: hidden; min-height: auto;">
                <div
                    style="height: 200px; background: linear-gradient(135deg, #dcfce7, #bbf7d0); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-child" style="font-size: 4rem; color: #16a34a; opacity: 0.5;"></i>
                </div>
                <div style="padding: 2rem;">
                    <span
                        style="background: #dcfce7; color: #16a34a; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Primary
                        School</span>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 1rem 0;">Sunshine Kids
                        School</h3>
                    <p style="color: #64748b; line-height: 1.6; margin-bottom: 1.5rem;">
                        Parent communication was non-existent. Parents were unaware of their child's progress or daily
                        activities.
                    </p>
                    <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem; margin-top: auto;">
                        <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Impact:
                        </h4>
                        <ul style="list-style: none; padding: 0; color: #475569;">
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-primary"
                                    style="margin-right: 8px;"></i> 80% increase in parent engagement</li>
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-primary"
                                    style="margin-right: 8px;"></i> Real-time notifications via SMS</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Call to Action -->
    <section class="section-padding" style="background: #fff; text-align: center;">
        <div style="max-width: 800px; margin: 0 auto;" data-aos="zoom-in">
            <h2 style="font-size: 2.5rem; font-weight: 800; color: #1e293b; margin-bottom: 1.5rem;">Ready to Write Your
                Success Story?</h2>
            <p style="color: #64748b; font-size: 1.2rem; margin-bottom: 2.5rem;">Join hundreds of other schools
                transforming their administration today.</p>
            <a href="register.php" class="btn btn-primary-gradient"
                style="padding: 15px 40px; font-size: 1.1rem; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);">Get
                Started Now</a>
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