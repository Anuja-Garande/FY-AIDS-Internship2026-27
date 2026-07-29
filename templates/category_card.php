<?php
if (!isset($category) || empty($category)) return;

$cat_name = sanitize($category['name'] ?? 'Category');
$cat_slug = htmlspecialchars($category['slug'] ?? '');
$cat_image = htmlspecialchars($category['image'] ?? '');
$product_count = (int)($category['product_count'] ?? 0);
$cat_id = (int)($category['id'] ?? 0);
$img_src = $cat_image ? BASE_URL . 'assets/uploads/categories/' . $cat_image : '';
$link = BASE_URL . 'products.php?category=' . ($cat_slug ?: $cat_id);
?>

<a href="<?= $link ?>" class="text-decoration-none">
    <div class="category-card card h-100 border-0 overflow-hidden" style="border-radius:16px;">
        <div class="position-relative" style="height:180px;">
            <?php if ($cat_image): ?>
                <img
                    src="<?= $img_src ?>"
                    alt="<?= $cat_name ?>"
                    class="w-100 h-100"
                    style="object-fit:cover; transition: transform 0.5s ease;"
                    loading="lazy"
                    onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-100 h-100 bg-light d-flex align-items-center justify-content-center\'><i class=\'fas fa-tag fa-3x text-primary opacity-25\'></i></div>';"
                >
            <?php else: ?>
                <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                    <i class="fas fa-tag fa-3x text-primary opacity-25"></i>
                </div>
            <?php endif; ?>

            <div class="position-absolute bottom-0 start-0 w-100" style="height:60%; background: linear-gradient(to top, rgba(0,0,0,0.65), transparent);"></div>

            <div class="position-absolute bottom-0 start-0 w-100 p-3">
                <h6 class="text-white fw-bold mb-1" style="font-size:1rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.3);">
                    <?= $cat_name ?>
                </h6>
                <?php if ($product_count > 0): ?>
                    <small class="text-white-50"><?= $product_count ?> Product<?= $product_count !== 1 ? 's' : '' ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</a>

<style>
.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.category-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
}
.category-card:hover img {
    transform: scale(1.08);
}
.category-card img {
    transition: transform 0.5s ease;
}
</style>
