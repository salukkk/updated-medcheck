<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCheck - Doctor Appointment System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            min-height: 100vh;
        }

        /* =============== NAVBAR =============== */
        .navbar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #00e5ff, #00bfa6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
        }

        .logo-text h1 {
            font-size: 26px;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo-text p {
            font-size: 12px;
            color: #00e5ff;
            margin: 0;
            font-weight: 500;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 10px;
            border: none;
            font-family: "Inter", sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-login {
            background: transparent;
            color: #00e5ff;
            border: 2px solid #00e5ff;
        }

        .btn-login:hover {
            background: #00e5ff;
            color: #0f2027;
            transform: translateY(-2px);
        }

        .btn-signup {
            background: linear-gradient(135deg, #00e5ff, #00bfa6);
            color: #0f2027;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 229, 255, 0.3);
        }

        /* =============== HERO SECTION =============== */
        .hero {
            text-align: center;
            padding: 80px 40px;
        }

        .hero-content h2 {
            font-size: 56px;
            margin-bottom: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #00e5ff, #00bfa6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-content p {
            font-size: 18px;
            color: #ccc;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00e5ff, #00bfa6);
            color: #0f2027;
            padding: 14px 40px;
            font-size: 16px;
        }

        .btn-primary:hover {
            box-shadow: 0 15px 40px rgba(0, 229, 255, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(0, 229, 255, 0.5);
            color: #00e5ff;
            padding: 14px 40px;
            font-size: 16px;
        }

        .btn-secondary:hover {
            background: rgba(0, 229, 255, 0.1);
            border-color: #00e5ff;
        }

        /* =============== FEATURES SECTION =============== */
        .features {
            padding: 80px 40px;
            background: rgba(255, 255, 255, 0.02);
        }

        .features-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .features-title h3 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .features-title p {
            font-size: 16px;
            color: #bbb;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(0, 229, 255, 0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(0, 229, 255, 0.3);
            box-shadow: 0 20px 50px rgba(0, 229, 255, 0.1);
        }

        .feature-icon {
            font-size: 40px;
            margin-bottom: 15px;
            height: 50px;
            display: flex;
            align-items: center;
        }

        .feature-card h4 {
            font-size: 20px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .feature-card p {
            color: #bbb;
            font-size: 14px;
            line-height: 1.6;
        }

        /* =============== ROLES SECTION =============== */
        .roles {
            padding: 80px 40px;
        }

        .roles-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .roles-title h3 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .roles-title p {
            font-size: 16px;
            color: #bbb;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .role-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            padding: 35px;
            border-radius: 16px;
            border: 1px solid rgba(0, 229, 255, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(0, 229, 255, 0.3);
        }

        .role-emoji {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .role-card h4 {
            font-size: 24px;
            margin-bottom: 12px;
            font-weight: 700;
            color: #00e5ff;
        }

        .role-features {
            list-style: none;
            margin: 20px 0;
            text-align: left;
            font-size: 14px;
            color: #bbb;
            line-height: 1.8;
        }

        .role-features li::before {
            content: "✓ ";
            color: #00bfa6;
            font-weight: 600;
            margin-right: 8px;
        }

        /* =============== CTA SECTION =============== */
        .cta {
            background: rgba(0, 229, 255, 0.05);
            padding: 60px 40px;
            text-align: center;
            border-top: 1px solid rgba(0, 229, 255, 0.1);
            border-bottom: 1px solid rgba(0, 229, 255, 0.1);
        }

        .cta h3 {
            font-size: 36px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .cta p {
            font-size: 16px;
            color: #bbb;
            margin-bottom: 30px;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* =============== FOOTER =============== */
        .footer {
            background: rgba(0, 0, 0, 0.3);
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer p {
            color: #999;
            font-size: 14px;
            margin: 0;
        }

        /* =============== RESPONSIVE =============== */
        @media (max-width: 768px) {
            .navbar {
                flex-wrap: wrap;
                padding: 15px 20px;
            }

            .hero-content h2 {
                font-size: 36px;
            }

            .features-title h3,
            .roles-title h3,
            .cta h3 {
                font-size: 28px;
            }

            .hero {
                padding: 50px 20px;
            }

            .features,
            .roles,
            .cta {
                padding: 50px 20px;
            }

            .nav-buttons {
                width: 100%;
                justify-content: flex-end;
                gap: 8px;
            }

            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <!-- =============== NAVBAR =============== -->
    <nav class="navbar">
        <div class="logo-section">
            <div class="logo-icon">M</div>
            <div class="logo-text">
                <h1>MedCheck</h1>
                <p>Doctor Appointment System</p>
            </div>
        </div>
        <div class="nav-buttons">
            <a href="login.php" class="btn btn-login">Login</a>
            <a href="signup.php" class="btn btn-signup">Get Started</a>
        </div>
    </nav>

    <!-- =============== HERO SECTION =============== -->
    <section class="hero">
        <div class="hero-content">
            <h2>Your Health, Our Priority</h2>
            <p>Book doctor appointments online with ease. Say goodbye to long waiting queues and hello to convenient healthcare scheduling.</p>
            <div class="hero-buttons">
                <a href="signup.php" class="btn btn-primary">Book an Appointment</a>
                <a href="#features" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- =============== FEATURES SECTION =============== -->
    <section class="features" id="features">
        <div class="features-title">
            <h3>Why Choose MedCheck?</h3>
            <p>Experience seamless healthcare scheduling</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🏥</div>
                <h4>Verified Doctors</h4>
                <p>Access a network of licensed medical professionals across various specialties.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h4>Flexible Scheduling</h4>
                <p>View doctor schedules and book appointments that fit your timetable.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h4>Secure & Private</h4>
                <p>Your health information is encrypted and protected with industry-standard security.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h4>Instant Confirmation</h4>
                <p>Get real-time appointment confirmations and reminders.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h4>Mobile Friendly</h4>
                <p>Book appointments anytime, anywhere from your device.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🆓</div>
                <h4>Free Service</h4>
                <p>No hidden charges. Our doctor channeling service is completely free.</p>
            </div>
        </div>
    </section>

    <!-- =============== ROLES SECTION =============== -->
    <section class="roles">
        <div class="roles-title">
            <h3>For Everyone</h3>
            <p>MedCheck serves different roles in healthcare</p>
        </div>
        <div class="roles-grid">
            <div class="role-card">
                <div class="role-emoji">👨‍⚕️</div>
                <h4>Doctors</h4>
                <ul class="role-features">
                    <li>Manage appointments efficiently</li>
                    <li>View patient information</li>
                    <li>Control your schedule</li>
                    <li>Monitor appointment status</li>
                </ul>
            </div>
            <div class="role-card">
                <div class="role-emoji">🤒</div>
                <h4>Patients</h4>
                <ul class="role-features">
                    <li>Book appointments easily</li>
                    <li>View doctor profiles</li>
                    <li>Track appointment history</li>
                    <li>Access anytime, anywhere</li>
                </ul>
            </div>
            <div class="role-card">
                <div class="role-emoji">👨‍💼</div>
                <h4>Administrators</h4>
                <ul class="role-features">
                    <li>Manage doctors & specialties</li>
                    <li>Monitor all appointments</li>
                    <li>View system analytics</li>
                    <li>System administration</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- =============== CTA SECTION =============== -->
    <section class="cta">
        <h3>Ready to Get Started?</h3>
        <p>Join thousands of patients and doctors using MedCheck for better healthcare</p>
        <div class="cta-buttons">
            <a href="signup.php" class="btn btn-primary">Sign Up as Patient</a>
            <a href="doctor_signup.php" class="btn btn-secondary">Sign Up as Doctor</a>
            <a href="login.php" class="btn btn-secondary">Already a Member? Login</a>
        </div>
    </section>

    <!-- =============== FOOTER =============== -->
    <footer class="footer">
        <p>&copy; 2026 MedCheck. Your Health, Our Priority. | A Web Solution for Better Healthcare</p>
    </footer>

</body>
</html>
