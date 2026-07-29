<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Page Not Found';
http_response_code(404);
require __DIR__ . '/includes/header.php';
?>
<section class="section text-center" style="padding-top:140px;padding-bottom:140px;">
  <div class="container">
    <i class="bi bi-signpost-split" style="font-size:5rem;color:var(--accent);"></i>
    <h1 class="mt-4" style="font-size:5rem;">404</h1>
    <h4 class="mb-3">Looks like this trail doesn't exist</h4>
    <p class="text-muted mb-4">The page you're looking for may have been moved or doesn't exist. Let's get you back on the map.</p>
    <a href="/tourism-portal/index.php" class="btn-brand">Back to Home</a>
    <a href="/tourism-portal/destinations.php" class="btn-teal ms-2">Explore Destinations</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
