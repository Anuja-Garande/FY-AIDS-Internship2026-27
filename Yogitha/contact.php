<?php
// C:\xampp\htdocs\NewProject\contact.php
// Contact Form & About Us Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$name = $email = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Server-side validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);

            $_SESSION['success'] = "Thank you for contacting us! We will get back to you shortly.";
            // Clear inputs on success
            $name = $email = $message = '';
        } catch (\PDOException $e) {
            $errors[] = "Failed to send message: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <!-- About Us Section -->
    <section class="row align-items-center mb-5 pb-5 border-bottom">
        <div class="col-lg-6">
            <h5 class="text-uppercase text-primary small fw-bold mb-2">Who We Are</h5>
            <h2 class="fw-bold mb-4">About TravelPortal</h2>
            <p class="text-muted leading-relaxed mb-3">
                TravelPortal is a leading tourist guide and custom itinerary planner designed to make travel inspiration easy and accessible. Whether you want to escape to the cool snowy heights of the Swiss Alps, relax on the golden shores of Bali, or dive deep into the cultural heritage of Angkor Wat, we provide the ultimate portal for travelers.
            </p>
            <p class="text-muted leading-relaxed mb-4">
                Our platform lets you discover global hot spots, read reviews written by real tourists, add destinations to your wishlist, and construct detailed travel itineraries. We are committed to building the most reliable digital guide for your next journey.
            </p>
            <div class="row g-3">
                <div class="col-6">
                    <h5 class="fw-bold text-dark mb-1"><i class="bi-check2-circle text-success me-2"></i>15+ Countries</h5>
                    <p class="text-muted small">Curated guides for global destinations.</p>
                </div>
                <div class="col-6">
                    <h5 class="fw-bold text-dark mb-1"><i class="bi-check2-circle text-success me-2"></i>24/7 Planning</h5>
                    <p class="text-muted small">Access and manage itineraries anytime.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mt-4 mt-lg-0">
            <!-- Hero Image for About Section -->
            <img src="assets/images/hero_bg.jpg" class="w-100 rounded-4 shadow" style="height: 360px; object-fit: cover;" alt="About Us">
        </div>
    </section>

    <!-- Contact Form & Details Section -->
    <section class="row">
        <!-- Contact Details -->
        <div class="col-lg-5 mb-5 mb-lg-0">
            <h5 class="text-uppercase text-primary small fw-bold mb-2">Get In Touch</h5>
            <h2 class="fw-bold mb-4">Contact Our Team</h2>
            <p class="text-muted mb-4">Have questions about destinations, need partnership opportunities, or face troubleshooting issues? Write to us!</p>
            
            <div class="d-flex flex-column gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white fs-4 px-3 py-2 rounded-3"><i class="bi-geo-alt-fill"></i></div>
                    <div>
                        <div class="fw-bold text-dark">Office Location</div>
                        <span class="text-muted small">102 Travel Towers, Connaught Place, New Delhi, India</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success text-white fs-4 px-3 py-2 rounded-3"><i class="bi-envelope-fill"></i></div>
                    <div>
                        <div class="fw-bold text-dark">Support Email</div>
                        <span class="text-muted small">support@travelportal.com</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning text-dark fs-4 px-3 py-2 rounded-3"><i class="bi-telephone-fill"></i></div>
                    <div>
                        <div class="fw-bold text-dark">Phone Helpline</div>
                        <span class="text-muted small">+91 98765 43210 (Mon-Sat, 9AM - 6PM)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                <h4 class="fw-bold mb-3">Send a Message</h4>
                
                <!-- Display Server Validation Errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-bold text-muted">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($name); ?>" required>
                            <div class="invalid-feedback">Please enter your name.</div>
                        </div>
                        
                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="e.g. user@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        
                        <!-- Message -->
                        <div class="col-12">
                            <label for="message" class="form-label small fw-bold text-muted">Your Message</label>
                            <textarea name="message" id="message" rows="5" class="form-control" placeholder="Write details about your query (minimum 10 characters)..." required minlength="10"><?php echo htmlspecialchars($message); ?></textarea>
                            <div class="invalid-feedback">Please enter your message (at least 10 characters).</div>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">
                                Send Message <i class="bi-send ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
