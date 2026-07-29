<?php
if (!isset($items) || empty($items)) return;
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb mb-0" style="background:transparent; padding:0; font-size:0.9rem;">
        <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>" class="text-decoration-none text-muted">
                <i class="fas fa-home me-1"></i>Home
            </a>
        </li>

        <?php
        $total = count($items);
        $max_visible_mobile = 1;
        $visible_items = $total > 2 ? array_slice($items, 0, $total - 1) : [];

        if (count($visible_items) > $max_visible_mobile):
        ?>
            <li class="breadcrumb-item d-none d-sm-inline-block">
                <a href="<?= BASE_URL . $visible_items[0]['url'] ?>" class="text-decoration-none text-muted">
                    <?= sanitize($visible_items[0]['label']) ?>
                </a>
            </li>

            <?php if (count($visible_items) > 1): ?>
                <li class="breadcrumb-item d-none d-sm-inline-block" aria-hidden="true">
                    <span class="text-muted">...</span>
                </li>
            <?php endif; ?>

            <li class="breadcrumb-item d-sm-none" aria-hidden="true">
                <span class="text-muted">...</span>
            </li>
        <?php
        elseif (count($visible_items) === 1):
        ?>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL . $visible_items[0]['url'] ?>" class="text-decoration-none text-muted">
                    <?= sanitize($visible_items[0]['label']) ?>
                </a>
            </li>
        <?php endif; ?>

        <?php
        $last = end($items);
        ?>
        <li class="breadcrumb-item active" aria-current="page" style="color:var(--text-primary,#333); font-weight:500;">
            <?= sanitize($last['label']) ?>
        </li>
    </ol>
</nav>

<style>
.breadcrumb-item + .breadcrumb-item::before {
    content: '/';
    color: #ccc;
    padding: 0 6px;
}
.breadcrumb-item a:hover {
    color: #667eea !important;
}
</style>
