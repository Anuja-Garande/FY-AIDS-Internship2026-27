<?php
require_once 'config.php';

// Fetch departments
$departments = [];
$doctors = [];

try {
    if (isset($pdo)) {
        $deptStmt = $pdo->query("SELECT * FROM departments ORDER BY name ASC");
        $departments = $deptStmt->fetchAll();

        $docStmt = $pdo->query("
            SELECT d.*, u.name as doctor_name, u.email as doctor_email, u.phone as doctor_phone, dept.name as dept_name 
            FROM doctors d 
            JOIN users u ON d.user_id = u.id 
            JOIN departments dept ON d.department_id = dept.id
            ORDER BY d.id ASC
        ");
        $doctors = $docStmt->fetchAll();
    }
} catch (PDOException $e) {
    // Gracefully handle if DB setup not yet executed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApexCare Healthcare | Premium Hospital & Booking System</title>
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS for Interactive Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Liquid Glass Custom Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Liquid Animated Background Blobs -->
    <div class="liquid-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Liquid Glass Header Navigation -->
    <header class="glass-header">
        <a href="index.php" class="logo-brand">
            <div class="logo-icon">
                <i class="fa-solid fa-notes-medical"></i>
            </div>
            <span>Apex<span class="text-gradient">Care</span></span>
        </a>

        <nav>
            <ul class="nav-menu">
                <li><a href="#hero" class="nav-link active">Home</a></li>
                <li><a href="#departments" class="nav-link">Departments</a></li>
                <li><a href="#doctors" class="nav-link">Specialists</a></li>
                <li><a href="#location" class="nav-link">Location & Map</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
        </nav>

        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <?php if (isLoggedIn()): ?>
                <?php if (getUserRole() === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn-glass"><i class="fa-solid fa-gauge-high"></i> Admin Portal</a>
                <?php elseif (getUserRole() === 'doctor'): ?>
                    <a href="doctor_dashboard.php" class="btn-glass"><i class="fa-solid fa-user-md"></i> Doctor Portal</a>
                <?php else: ?>
                    <a href="patient_dashboard.php" class="btn-glass"><i class="fa-solid fa-calendar-check"></i> My Appointments</a>
                <?php endif; ?>
                <a href="logout.php" class="btn-glass btn-glass-secondary" style="padding: 0.85rem 1.2rem;" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
            <?php else: ?>
                <a href="login.php" class="btn-glass btn-glass-secondary"><i class="fa-solid fa-lock"></i> Sign In</a>
                <a href="register.php" class="btn-glass"><i class="fa-solid fa-user-plus"></i> Patient Register</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Wrapper -->
    <main class="main-wrapper">

        <!-- DB Connection Notice if Table/DB Missing -->
        <?php if (isset($db_connection_error)): ?>
            <div class="alert-glass alert-error" style="margin-top: 1rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.5rem;"></i>
                <div>
                    <strong>Database Connection Alert:</strong> Could not connect to MySQL database <code>hospital_db</code>.
                    <br><small>Please import <code>database.sql</code> using phpMyAdmin or MySQL console to start. Error: <?= htmlspecialchars($db_connection_error) ?></small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hero Section -->
        <section id="hero" class="hero-section">
            <div>
                <div class="hero-badge">
                    <i class="fa-solid fa-shield-halved"></i> JCI Accredited Super Specialty Healthcare
                </div>
                <h1 class="hero-title">
                    Healthcare <span class="text-gradient">Redefined</span> with Precision & Care.
                </h1>
                <p class="hero-desc">
                    Seamlessly schedule appointments with world-class specialists in seconds. ApexCare combines advanced medical technology, instant doctor availability, and liquid digital experience.
                </p>
                <div class="hero-actions">
                    <a href="book_appointment.php" class="btn-glass" style="padding: 1rem 2.2rem; font-size: 1.05rem;">
                        <i class="fa-solid fa-calendar-plus"></i> Book Appointment Now
                    </a>
                    <a href="#doctors" class="btn-glass btn-glass-secondary" style="padding: 1rem 2.2rem; font-size: 1.05rem;">
                        <i class="fa-solid fa-stethoscope"></i> View Medical Team
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-glass-frame">
                    <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=800&auto=format&fit=crop" alt="Modern Hospital" class="hero-img" onerror="this.src='https://images.unsplash.com/photo-1586773860418-d37222d8fce3?q=80&w=800&auto=format&fit=crop'">
                </div>
                
                <!-- Floating Glass Card Badge 1 -->
                <div class="floating-glass-card card-top-right">
                    <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(0, 230, 118, 0.2); color: #00e676; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem;">Instant Confirmation</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Real-time doctor slot lock</div>
                    </div>
                </div>

                <!-- Floating Glass Card Badge 2 -->
                <div class="floating-glass-card card-bottom-left">
                    <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(0, 242, 254, 0.2); color: var(--primary-cyan); display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem;">50+ Specialists</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Top rated medical experts</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Ribbon -->
        <section class="stats-ribbon glass-panel">
            <div class="stat-item">
                <div class="stat-number">18,500+</div>
                <div class="stat-label">Happy Patients Treated</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">99.4%</div>
                <div class="stat-label">Diagnostic Accuracy</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">15+</div>
                <div class="stat-label">Specialist Departments</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24 / 7</div>
                <div class="stat-label">Emergency Care & ICU</div>
            </div>
        </section>

        <!-- Departments Section -->
        <section id="departments" style="padding: 4rem 0;">
            <div class="section-header">
                <div class="section-subtitle">Excellence in Medicine</div>
                <h2 class="section-title">Specialized Clinical Care</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.75rem;">
                <?php foreach ($departments as $dept): ?>
                    <div class="glass-card">
                        <div style="width: 55px; height: 55px; border-radius: 16px; background: linear-gradient(135deg, rgba(0, 242, 254, 0.15), rgba(127, 0, 255, 0.15)); border: 1px solid rgba(0, 242, 254, 0.3); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: var(--primary-cyan); margin-bottom: 1.25rem;">
                            <i class="fa-solid <?= htmlspecialchars($dept['icon']) ?>"></i>
                        </div>
                        <h3 style="font-size: 1.3rem; margin-bottom: 0.6rem;"><?= htmlspecialchars($dept['name']) ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;"><?= htmlspecialchars($dept['description']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Doctors Showcase Section -->
        <section id="doctors" style="padding: 5rem 0;">
            <div class="section-header">
                <div class="section-subtitle">Meet Our Experts</div>
                <h2 class="section-title">Senior Consultant Doctors</h2>
            </div>

            <!-- Filter Chips -->
            <div class="dept-filter">
                <button class="filter-chip active" data-dept="all">All Specialties</button>
                <?php foreach ($departments as $dept): ?>
                    <button class="filter-chip" data-dept="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></button>
                <?php endforeach; ?>
            </div>

            <!-- Doctors Grid -->
            <div class="doctors-grid">
                <?php foreach ($doctors as $doc): ?>
                    <div class="doctor-card-wrapper" data-dept-id="<?= $doc['department_id'] ?>">
                        <div class="glass-card doctor-card">
                            <div class="doc-img-wrapper">
                                <img src="<?= htmlspecialchars($doc['image_url'] ?: 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?q=80&w=400&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($doc['doctor_name']) ?>" class="doc-img" onerror="this.src='https://images.unsplash.com/photo-1622253692010-333f2da6031d?q=80&w=400&auto=format&fit=crop'">
                                <div class="doc-badge"><?= htmlspecialchars($doc['experience_years']) ?>+ Yrs Exp</div>
                            </div>
                            
                            <h3 class="doc-name"><?= htmlspecialchars($doc['doctor_name']) ?></h3>
                            <div class="doc-spec"><?= htmlspecialchars($doc['dept_name']) ?> &bull; <?= htmlspecialchars($doc['specialization']) ?></div>
                            
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem; line-height: 1.5;">
                                <?= htmlspecialchars($doc['qualification']) ?>. Available: <?= htmlspecialchars($doc['availability_days']) ?>.
                            </p>

                            <div class="doc-meta">
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">Consultation Fee</div>
                                    <div class="doc-fee">₹<?= number_format($doc['consultation_fee'], 2) ?></div>
                                </div>
                                <a href="book_appointment.php?doctor_id=<?= $doc['id'] ?>" class="btn-glass" style="padding: 0.5rem 1.1rem; font-size: 0.85rem;">
                                    Book <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Location & Interactive Map Section -->
        <section id="location" style="padding: 3rem 0;">
            <div class="section-header">
                <div class="section-subtitle">Find Us Easily</div>
                <h2 class="section-title">Hospital Location & Facility Map</h2>
            </div>

            <div class="glass-panel map-section" style="padding: 2.5rem;">
                <div>
                    <h3 style="font-size: 1.7rem; margin-bottom: 1rem;">ApexCare Medical Center</h3>
                    <p style="color: var(--text-muted); line-height: 1.6;">
                        Located in the heart of the health district with state-of-the-art emergency rooms, diagnostic labs, and outpatient suites.
                    </p>

                    <ul class="location-info-list">
                        <li class="location-item">
                            <div class="location-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Address</strong>
                                <span style="color: var(--text-muted); font-size: 0.9rem;">742 FC Road, Shivajinagar, Pune, Maharashtra 411005, India</span>
                            </div>
                        </li>
                        <li class="location-item">
                            <div class="location-icon"><i class="fa-solid fa-phone-volume"></i></div>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Emergency Hotline</strong>
                                <span style="color: var(--primary-cyan); font-size: 0.95rem; font-weight: 700;">+91 20 5555 2739</span>
                            </div>
                        </li>
                        <li class="location-item">
                            <div class="location-icon"><i class="fa-solid fa-clock"></i></div>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Working Hours</strong>
                                <span style="color: var(--text-muted); font-size: 0.9rem;">OPD: Mon - Sat (8:00 AM - 8:00 PM) | Emergency 24/7</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Interactive Leaflet Map Container -->
                <div>
                    <div id="hospitalMap" style="height: 380px; width: 100%; border-radius: 20px; overflow: hidden; position: relative;"></div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section style="padding: 4rem 0 6rem 0;">
            <div class="section-header">
                <div class="section-subtitle">Patient Experiences</div>
                <h2 class="section-title">Trusted by Thousands</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div class="glass-card">
                    <div style="color: #ffab00; margin-bottom: 1rem; font-size: 0.9rem;">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p style="color: var(--text-muted); font-style: italic; line-height: 1.6; margin-bottom: 1.5rem;">
                        "Booking an appointment with Dr. Sarah took less than a minute. The liquid glass UI made navigation smooth, and the care at ApexCare was phenomenal!"
                    </p>
                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-purple); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff;">
                            EM
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.9rem;">Emily Martinez</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Cardiology Patient</div>
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <div style="color: #ffab00; margin-bottom: 1rem; font-size: 0.9rem;">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                    </div>
                    <p style="color: var(--text-muted); font-style: italic; line-height: 1.6; margin-bottom: 1.5rem;">
                        "The online portal allowed me to check available slots, consult fees, and view my upcoming checkups without any waiting queue."
                    </p>
                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-cyan); color: #070913; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            DK
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 0.9rem;">David K.</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Orthopedics Patient</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Glass Footer -->
    <footer class="glass-footer">
        <div style="max-width: 1300px; margin: 0 auto; padding: 0 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem; font-weight: 700;">
                <div class="logo-icon" style="width: 32px; height: 32px; font-size: 1rem;">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <span>ApexCare Healthcare System</span>
            </div>
            <div>
                &copy; <?= date('Y') ?> ApexCare Hospital Management System. Internship Project Demo.
            </div>
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Main JavaScript -->
    <script src="assets/js/main.js"></script>

    <!-- Direct Override Script for Map Initialization to Pune, India -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var container = L.DomUtil.get('hospitalMap');
            if(container != null){
                container._leaflet_id = null; // Reset existing Leaflet instance if initialized
            }

            // Pune Coordinates
            var puneLat = 18.5204;
            var puneLng = 73.8567;

            var map = L.map('hospitalMap').setView([puneLat, puneLng], 14);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            var customIcon = L.divIcon({
                className: 'custom-map-marker',
                html: `
                    <div style="width:36px; height:36px; background:linear-gradient(135deg,#00f2fe,#4facfe); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.1rem; box-shadow:0 0 20px rgba(0,242,254,0.8); border:2px solid #fff;">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                `,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            var marker = L.marker([puneLat, puneLng], { icon: customIcon }).addTo(map);

            marker.bindPopup(`
                <div style="padding: 0.25rem; font-family: system-ui, sans-serif;">
                    <strong style="color: #00f2fe; font-size: 0.95rem; display: block; margin-bottom: 0.25rem;">ApexCare Super Specialty Hospital</strong>
                    <span style="font-size: 0.82rem; color: #333; display: block; margin-bottom: 0.4rem;">742 FC Road, Shivajinagar, Pune, Maharashtra 411005</span>
                    <span style="font-size: 0.78rem; background: rgba(0,230,118,0.15); color: #00a850; padding: 2px 8px; border-radius: 12px; font-weight: 600; display: inline-block;">
                        <i class="fa-solid fa-circle" style="font-size: 0.5rem; vertical-align: middle;"></i> Emergency 24/7 Open
                    </span>
                </div>
            `).openPopup();
        });
    </script>
</body>
</html>