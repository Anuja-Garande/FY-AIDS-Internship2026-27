<?php
// C:\xampp\htdocs\NewProject\includes\footer.php
?>
<!-- Footer Section -->
<footer class="footer-custom">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6">
                <h5 class="text-white"><i class="bi-airplane-fill text-warning me-2"></i>TravelPortal</h5>
                <p class="small text-white-50">Your ultimate companion to discover breathtaking places, plan custom itineraries, and share memorable experiences with our global travel community.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white-50 fs-5"><i class="bi-facebook"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi-twitter-x"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi-instagram"></i></a>
                    <a href="#" class="text-white-50 fs-5"><i class="bi-youtube"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="index.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Home</a></li>
                    <li><a href="destinations.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Destinations</a></li>
                    <li><a href="packages.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Tour Packages</a></li>
                    <li><a href="offers.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Special Offers</a></li>
                    <li><a href="hotels.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Hotels & Resorts</a></li>
                </ul>
            </div>

            <!-- More Services -->
            <div class="col-lg-3 col-md-6">
                <h5>Services & Help</h5>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="restaurants.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Top Restaurants</a></li>
                    <li><a href="guides.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Local Tour Guides</a></li>
                    <li><a href="faq.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Why Choose Us / FAQ</a></li>
                    <li><a href="contact.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>About & Contact</a></li>
                    <li><a href="admin/login.php" class="text-white-50"><i class="bi-chevron-right me-1 small"></i>Admin Area</a></li>
                </ul>
            </div>

            <!-- Newsletter Signup -->
            <div class="col-lg-3 col-md-6">
                <h5>Stay Updated</h5>
                <p class="small text-white-50">Subscribe to our monthly newsletter for curated travel guides and top flight deals.</p>
                <form id="newsletterForm" class="mt-3">
                    <div class="input-group">
                        <input type="email" class="form-control form-control-sm" placeholder="Your Email Address" required>
                        <button class="btn btn-warning btn-sm text-dark font-weight-bold" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="footer-bottom text-center text-white-50">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> TravelPortal. Built as an Internship Project. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
