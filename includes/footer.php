  <!-- ===== Footer ===== -->
  <footer class="site-footer">
    <div class="footer-wave">
      <svg viewBox="0 0 1200 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,30 C300,80 900,-20 1200,30 L1200,00 L0,0 Z"></path>
      </svg>
    </div>
    <div class="container py-5">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <h5 class="footer-brand"><i class="bi bi-compass-fill"></i> BharatYatra</h5>
          <p class="footer-text">Your companion for discovering India's states, hidden gems, hotels, restaurants and unforgettable tour packages — all in one place.</p>
          <div class="social-icons">
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
            <a href="#"><i class="bi bi-twitter-x"></i></a>
            <a href="#"><i class="bi bi-youtube"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading">Explore</h6>
          <ul class="footer-links">
            <li><a href="/tourism-portal/destinations.php">Destinations</a></li>
            <li><a href="/tourism-portal/packages.php">Tour Packages</a></li>
            <li><a href="/tourism-portal/hotels.php">Hotels</a></li>
            <li><a href="/tourism-portal/restaurants.php">Restaurants</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading">Company</h6>
          <ul class="footer-links">
            <li><a href="/tourism-portal/about.php">About Us</a></li>
            <li><a href="/tourism-portal/contact.php">Contact</a></li>
            <li><a href="/tourism-portal/login.php">Login</a></li>
            <li><a href="/tourism-portal/register.php">Sign Up</a></li>
          </ul>
        </div>
        <div class="col-lg-4 col-md-6">
          <h6 class="footer-heading">Plan Your Trip</h6>
          <p class="footer-text mb-2">Get travel inspiration and offers straight to your inbox.</p>
          <form class="newsletter-form" onsubmit="return false;">
            <input type="email" placeholder="Enter your email" required>
            <button type="submit"><i class="bi bi-send-fill"></i></button>
          </form>
        </div>
      </div>
      <hr class="footer-divider">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-center">
        <p class="mb-0 small">&copy; <?= date('Y') ?> BharatYatra — Tourism &amp; Destination Portal. A B.Tech AI &amp; DS Project.</p>
        <p class="mb-0 small">Made with <i class="bi bi-heart-fill text-danger"></i> for Incredible India</p>
      </div>
    </div>
  </footer>

  <!-- Back to top -->
  <button id="backToTop" title="Back to top"><i class="bi bi-arrow-up"></i></button>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
  <script src="/tourism-portal/assets/js/script.js"></script>
</body>
</html>
