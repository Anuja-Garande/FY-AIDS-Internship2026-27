<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$pageTitle = 'My Wishlist';
$uid = $_SESSION['user_id'];

if (isset($_GET['remove'])) {
    $wid = (int)$_GET['remove'];
    $del = $pdo->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?");
    $del->execute([$wid, $uid]);
    setFlash('success', 'Removed from wishlist.');
    redirect('/tourism-portal/wishlist.php');
}

$destWishlist = $pdo->prepare("SELECT w.wishlist_id, d.* FROM wishlist w
                                JOIN destinations d ON d.destination_id = w.item_id
                                WHERE w.user_id = ? AND w.item_type = 'destination'");
$destWishlist->execute([$uid]);
$destWishlist = $destWishlist->fetchAll();

$pkgWishlist = $pdo->prepare("SELECT w.wishlist_id, p.* FROM wishlist w
                               JOIN packages p ON p.package_id = w.item_id
                               WHERE w.user_id = ? AND w.item_type = 'package'");
$pkgWishlist->execute([$uid]);
$pkgWishlist = $pkgWishlist->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:50px;">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3">
        <div class="dash-sidebar" data-aos="fade-up">
          <a href="/tourism-portal/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
          <a href="/tourism-portal/my-bookings.php"><i class="bi bi-suitcase-lg"></i> My Bookings</a>
          <a href="/tourism-portal/wishlist.php" class="active"><i class="bi bi-heart"></i> My Wishlist</a>
          <a href="/tourism-portal/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
      </div>
      <div class="col-lg-9">
        <h3 class="mb-4" data-aos="fade-up">My Wishlist</h3>

        <?php if (empty($destWishlist) && empty($pkgWishlist)): ?>
          <div class="card-tile p-5 text-center" data-aos="fade-up">
            <i class="bi bi-heart" style="font-size:2.5rem;color:var(--text-muted);"></i>
            <h5 class="mt-3">Your wishlist is empty</h5>
            <p class="text-muted">Save destinations and packages you love to plan them later.</p>
            <a href="/tourism-portal/destinations.php" class="btn-brand mx-auto">Explore Destinations</a>
          </div>
        <?php endif; ?>

        <?php if ($destWishlist): ?>
        <h5 class="mb-3" data-aos="fade-up">Saved Destinations</h5>
        <div class="row g-4 mb-5">
          <?php foreach ($destWishlist as $d): ?>
          <div class="col-md-4" data-aos="fade-up">
            <div class="card-tile">
              <div class="tile-img-wrap">
                <img src="/tourism-portal/assets/images/destinations/<?= h($d['image']) ?>" alt="<?= h($d['name']) ?>"
                     onerror="this.src='https://source.unsplash.com/500x400/?<?= urlencode($d['name']) ?>,india'">
              </div>
              <div class="tile-body">
                <h5><?= h($d['name']) ?></h5>
                <div class="tile-footer">
                  <a href="/tourism-portal/destination-details.php?id=<?= $d['destination_id'] ?>" class="tile-link">View <i class="bi bi-arrow-right"></i></a>
                  <a href="?remove=<?= $d['wishlist_id'] ?>" class="text-danger small">Remove</a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($pkgWishlist): ?>
        <h5 class="mb-3" data-aos="fade-up">Saved Packages</h5>
        <div class="row g-4">
          <?php foreach ($pkgWishlist as $p): ?>
          <div class="col-md-4" data-aos="fade-up">
            <div class="card-tile">
              <div class="tile-img-wrap">
                <img src="/tourism-portal/assets/images/destinations/<?= h($p['image']) ?>" alt="<?= h($p['name']) ?>"
                     onerror="this.src='https://source.unsplash.com/500x400/?india,travel'">
              </div>
              <div class="tile-body">
                <h5><?= h($p['name']) ?></h5>
                <p class="tile-price mb-2">₹<?= number_format($p['price'],0) ?></p>
                <div class="tile-footer">
                  <a href="/tourism-portal/package-details.php?id=<?= $p['package_id'] ?>" class="tile-link">View <i class="bi bi-arrow-right"></i></a>
                  <a href="?remove=<?= $p['wishlist_id'] ?>" class="text-danger small">Remove</a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
