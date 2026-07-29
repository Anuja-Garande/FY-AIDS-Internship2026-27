<?php
if (!isset($product) || empty($product)) return;

$product_id = $product['id'] ?? 0;
$product_name = sanitize($product['name'] ?? 'Product');
$slug = htmlspecialchars($product['slug'] ?? '');
$brand_name = htmlspecialchars($product['brand_name'] ?? '');
$category_name = htmlspecialchars($product['category_name'] ?? '');
$price = (float)($product['price'] ?? 0);
$sale_price = (float)($product['sale_price'] ?? 0);
$avg_rating = (float)($product['avg_rating'] ?? 0);
$review_count = (int)($product['review_count'] ?? 0);
$stock = (int)($product['stock'] ?? 0);
$is_featured = (int)($product['is_featured'] ?? 0);
$is_new = isset($product['created_at']) && strtotime($product['created_at']) > strtotime('-7 days');
$is_flash_sale = (int)($product['is_flash_sale'] ?? 0);
$is_sale = $sale_price > 0 && $sale_price < $price;
$discount = $is_sale ? round((1 - $sale_price / $price) * 100) : 0;
$primary_image = htmlspecialchars(!empty($product['primary_image']) ? 'assets/uploads/products/' . $product['primary_image'] : 'assets/uploads/products/placeholder.jpg');
$img_src = str_starts_with($primary_image, 'http') ? $primary_image : BASE_URL . $primary_image;
$link = BASE_URL . 'product.php?slug=' . $slug;
?>

<div class="card product-card h-100 glass-card position-relative" data-product-id="<?= $product_id ?>">
    <div class="product-card-badges position-absolute top-0 start-0 p-2 z-2">
        <?php if ($is_featured): ?>
            <span class="badge bg-warning text-dark me-1"><i class="fas fa-star me-1"></i>Featured</span>
        <?php endif; ?>
        <?php if ($is_new): ?>
            <span class="badge bg-info me-1">New</span>
        <?php endif; ?>
        <?php if ($is_sale): ?>
            <span class="badge bg-danger">-<?= $discount ?>%</span>
        <?php endif; ?>
        <?php if ($is_flash_sale): ?>
            <span class="badge bg-warning text-dark"><i class="fas fa-bolt me-1"></i>Sale</span>
        <?php endif; ?>
    </div>

    <button class="btn btn-sm wishlist-btn position-absolute top-0 end-0 m-2 z-2 rounded-circle bg-white shadow-sm border-0" data-id="<?= $product_id ?>" title="Add to Wishlist" style="width:36px;height:36px;">
        <i class="far fa-heart text-danger"></i>
    </button>

    <a href="<?= $link ?>" class="text-decoration-none">
        <div class="product-card-image overflow-hidden" style="height:220px;">
            <img
                src="<?= $img_src ?>"
                alt="<?= $product_name ?>"
                class="card-img-top w-100 h-100"
                style="object-fit:cover; transition: transform 0.5s ease;"
                loading="lazy"
                onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/uploads/products/placeholder.jpg';"
            >
        </div>
    </a>

    <div class="card-body d-flex flex-column p-3">
        <?php if ($brand_name): ?>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.7rem; letter-spacing:0.5px;"><?= $brand_name ?></small>
        <?php endif; ?>

        <h6 class="card-title mt-1 mb-2 text-truncate-2" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
            <a href="<?= $link ?>" class="text-decoration-none text-dark fw-semibold"><?= $product_name ?></a>
        </h6>

        <div class="d-flex align-items-center mb-2">
            <div class="d-flex">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= round($avg_rating) ? 'text-warning' : 'text-muted' ?>" style="font-size:0.75rem;"></i>
                <?php endfor; ?>
            </div>
            <small class="text-muted ms-1">(<?= $review_count ?>)</small>
        </div>

        <div class="mt-auto">
            <div class="d-flex align-items-baseline flex-wrap gap-1">
                <span class="fw-bold text-primary fs-5"><?= formatPrice($is_sale ? $sale_price : $price) ?></span>
                <?php if ($is_sale): ?>
                    <del class="text-muted small"><?= formatPrice($price) ?></del>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button
                    class="btn btn-primary btn-sm flex-grow-1 add-to-cart-btn fw-semibold"
                    data-id="<?= $product_id ?>"
                    <?= $stock <= 0 ? 'disabled' : '' ?>
                >
                    <?php if ($stock <= 0): ?>
                        <i class="fas fa-ban me-1"></i>Out of Stock
                    <?php else: ?>
                        <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                    <?php endif; ?>
                </button>
                <button
                    class="btn btn-outline-secondary btn-sm quick-view-btn"
                    data-id="<?= $product_id ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#quickViewModal"
                    title="Quick View"
                    style="width:36px; height:36px; flex-shrink:0;"
                >
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 16px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
}
.product-card:hover .card-img-top {
    transform: scale(1.08);
}
.product-card .card-img-top {
    transition: transform 0.5s ease;
}
.product-card-badges .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
    font-weight: 600;
}
.wishlist-btn {
    transition: transform 0.2s ease;
    z-index: 5;
}
.wishlist-btn:hover {
    transform: scale(1.15);
}
.wishlist-btn.active i {
    font-weight: 900;
}
.quick-view-btn {
    transition: transform 0.2s ease;
}
.quick-view-btn:hover {
    transform: scale(1.1);
}
</style>
