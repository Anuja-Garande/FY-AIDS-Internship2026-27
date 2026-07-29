<?php
if (!isset($review) || empty($review)) return;

$user_name = sanitize($review['user_name'] ?? 'Customer');
$user_avatar = htmlspecialchars($review['avatar'] ?? '');
$rating = (int)($review['rating'] ?? 0);
$comment = sanitize($review['comment'] ?? '');
$created_at = $review['created_at'] ?? '';
$admin_reply = $review['admin_reply'] ?? '';
$initials = strtoupper(substr($user_name, 0, 1));
?>

<div class="review-card h-100">
    <div class="d-flex align-items-center mb-3">
        <?php if ($user_avatar): ?>
            <img
                src="<?= BASE_URL ?>assets/uploads/users/<?= $user_avatar ?>"
                alt="<?= $user_name ?>"
                class="rounded-circle me-3"
                style="width:48px;height:48px;object-fit:cover;"
                loading="lazy"
                onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';"
            >
            <div class="rounded-circle bg-primary text-white d-none align-items-center justify-content-center me-3" style="width:48px;height:48px;font-size:1.1rem;font-weight:700;">
                <?= $initials ?>
            </div>
        <?php else: ?>
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;font-size:1.1rem;font-weight:700;">
                <?= $initials ?>
            </div>
        <?php endif; ?>

        <div class="flex-grow-1">
            <h6 class="mb-0 fw-semibold text-dark"><?= $user_name ?></h6>
            <?php if ($created_at): ?>
                <small class="text-muted"><?= timeAgo($created_at) ?></small>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-3">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="fas fa-star <?= $i <= $rating ? 'text-warning' : 'text-muted' ?>" style="font-size:0.9rem;"></i>
        <?php endfor; ?>
    </div>

    <?php if ($comment): ?>
        <p class="text-muted mb-0" style="font-size:0.95rem; line-height:1.6;"><?= nl2br($comment) ?></p>
    <?php endif; ?>

    <?php if ($admin_reply): ?>
        <div class="bg-light rounded-3 p-3 mt-3" style="border-left:3px solid #667eea;">
            <small class="fw-semibold text-primary d-block mb-1">
                <i class="fas fa-reply me-1"></i>Admin Reply
            </small>
            <p class="mb-0 small text-muted" style="line-height:1.5;"><?= nl2br(sanitize($admin_reply)) ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.review-card {
    background: var(--bg-card,white);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.review-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}
</style>
