    </main>

    <section class="newsletter-section" style="background:var(--bg-secondary);padding:50px 0;border-top:1px solid var(--border-glass);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h4 class="fw-bold mb-3"><i class="fas fa-envelope me-2" style="color:var(--primary);"></i>Subscribe to Our Newsletter</h4>
                    <p class="text-muted mb-4">Get the latest deals, new arrivals, and AI-powered product recommendations straight to your inbox.</p>
                    <form class="d-flex justify-content-center gap-2" id="newsletter-form" style="max-width:500px;margin:0 auto;">
                        <input type="email" class="form-control" style="border-radius:50px;height:48px;" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-primary" style="border-radius:50px;white-space:nowrap;">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer" style="background:var(--bg-primary);border-top:1px solid var(--border-glass);padding:50px 0 30px;">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div style="margin-bottom:12px;">
                        <img src="<?= BASE_URL ?>assets/images/shopsphere logo.jpeg" alt="<?= SITE_NAME ?>" style="height:32px;width:auto;">
                        <div style="background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-weight:800;font-size:0.95rem;margin-top:4px;"><?= SITE_NAME ?></div>
                    </div>
                    <p class="text-muted small">Your AI-powered shopping destination. Discover smart product recommendations, unbeatable prices, and a seamless shopping experience.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="me-3 text-muted fs-5"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3 text-muted fs-5"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3 text-muted fs-5"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-3 text-muted fs-5"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>categories.php" class="text-muted text-decoration-none">Categories</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>deals.php" class="text-muted text-decoration-none">Deals & Offers</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>products.php?sort=newest" class="text-muted text-decoration-none">New Arrivals</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>index.php" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>orders.php" class="text-muted text-decoration-none">Track Order</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>index.php" class="text-muted text-decoration-none">Returns & Refunds</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>index.php" class="text-muted text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>index.php" class="text-muted text-decoration-none">Shipping Policy</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>index.php" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>123 Shopping Street, Mumbai, India</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>+91 98765 43210</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i><?= ADMIN_EMAIL ?></li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i>Mon - Sat: 9:00 AM - 9:00 PM</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted small mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <img src="https://cdn-icons-png.flaticon.com/512/196/196566.png" alt="Visa" height="24" class="me-2">
                    <img src="https://cdn-icons-png.flaticon.com/512/196/196561.png" alt="Mastercard" height="24" class="me-2">
                    <img src="https://cdn-icons-png.flaticon.com/512/888/888870.png" alt="UPI" height="24" class="me-2">
                    <img src="https://cdn-icons-png.flaticon.com/512/5968/5968144.png" alt="PayPal" height="24">
                </div>
            </div>
        </div>
    </footer>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <style>html,body{overscroll-behavior:none;}main{margin:0;padding:0;}</style>
</body>
</html>
