<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact Us';

$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if ($old['name'] === '') $errors[] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if ($old['message'] === '') $errors[] = 'Please enter your message.';

    if (empty($errors)) {
        $ins = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $ins->execute([$old['name'], $old['email'], $old['subject'], $old['message']]);
        setFlash('success', 'Thank you for reaching out! We will get back to you soon.');
        redirect('/tourism-portal/contact.php');
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1 data-aos="fade-up">Get In Touch</h1>
    <p class="breadcrumb-custom" data-aos="fade-up" data-aos-delay="100"><a href="/tourism-portal/index.php">Home</a> / Contact</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5">
        <span class="section-eyebrow" data-aos="fade-up">Reach Us</span>
        <h2 class="section-title" data-aos="fade-up">We'd Love to Hear From You</h2>
        <p class="text-muted" data-aos="fade-up">Have a question about a destination, package, or booking? Send us a message and our team will respond shortly.</p>

        <div class="d-flex gap-3 mb-3" data-aos="fade-up">
          <div class="feature-icon"><i class="bi bi-telephone-fill"></i></div>
          <div><h6 class="mb-0">Call Us</h6><p class="text-muted mb-0">+91-90000-00000</p></div>
        </div>
        <div class="d-flex gap-3 mb-3" data-aos="fade-up">
          <div class="feature-icon"><i class="bi bi-envelope-fill"></i></div>
          <div><h6 class="mb-0">Email Us</h6><p class="text-muted mb-0">hello@bharatyatra.in</p></div>
        </div>
        <div class="d-flex gap-3" data-aos="fade-up">
          <div class="feature-icon"><i class="bi bi-geo-alt-fill"></i></div>
          <div><h6 class="mb-0">Visit Us</h6><p class="text-muted mb-0">BharatYatra HQ, Pune, Maharashtra, India</p></div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card-tile p-4 p-md-5" data-aos="fade-up">
          <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>
          <form method="post">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" name="name" class="form-control" value="<?= h($old['name']) ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= h($old['email']) ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Subject</label>
              <input type="text" name="subject" class="form-control" value="<?= h($old['subject']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea name="message" class="form-control" rows="5" required><?= h($old['message']) ?></textarea>
            </div>
            <button type="submit" class="btn-brand">Send Message <i class="bi bi-send"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
