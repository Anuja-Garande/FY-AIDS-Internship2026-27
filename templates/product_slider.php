<?php
if (!isset($title)) $title = 'Products';
if (!isset($products)) $products = [];
if (!isset($view_all_link)) $view_all_link = 'products.php';
if (!isset($view_all_text)) $view_all_text = 'View All';

if (empty($products)) return;

$slider_id = 'slider_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($title)) . '_' . mt_rand(1000, 9999);
?>

<section class="py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold section-title mb-0"><?= sanitize($title) ?></h2>
            <a href="<?= BASE_URL . $view_all_link ?>" class="btn btn-outline-primary btn-sm">
                <?= sanitize($view_all_text) ?> <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="position-relative">
            <button
                class="slider-nav slider-nav-prev btn btn-white shadow-sm position-absolute rounded-circle d-none d-md-flex"
                data-target="<?= $slider_id ?>"
                style="width:44px;height:44px;top:50%;left:-16px;transform:translateY(-50%);z-index:3;border:1px solid rgba(0,0,0,0.08);"
                aria-label="Previous"
            >
                <i class="fas fa-chevron-left text-dark"></i>
            </button>

            <button
                class="slider-nav slider-nav-next btn btn-white shadow-sm position-absolute rounded-circle d-none d-md-flex"
                data-target="<?= $slider_id ?>"
                style="width:44px;height:44px;top:50%;right:-16px;transform:translateY(-50%);z-index:3;border:1px solid rgba(0,0,0,0.08);"
                aria-label="Next"
            >
                <i class="fas fa-chevron-right text-dark"></i>
            </button>

            <div
                id="<?= $slider_id ?>"
                class="product-slider d-flex gap-3 pb-2"
                style="
                    overflow-x: auto;
                    overflow-y: visible;
                    scroll-snap-type: x mandatory;
                    scroll-behavior: smooth;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: thin;
                    scrollbar-color: #667eea #f0f0f0;
                    padding: 16px 4px 20px;
                    perspective: 900px;
                "
            >
                <?php foreach ($products as $product): ?>
                <div
                    class="product-slider-item flex-shrink-0"
                    style="
                        scroll-snap-align: start;
                        min-width: 280px;
                        width: calc(25% - 12px);
                        transform-style: preserve-3d;
                        perspective: 900px;
                    "
                >
                    <?php
                    $p = $product;
                    include __DIR__ . '/product_card.php';
                    ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<style>
.product-slider::-webkit-scrollbar {
    height: 6px;
}
.product-slider::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 3px;
}
.product-slider::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 3px;
}
.product-slider {
    scrollbar-width: thin;
    scrollbar-color: #667eea #f0f0f0;
}
.slider-nav {
    transition: all 0.2s ease;
    opacity: 0.85;
}
.slider-nav:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.05) !important;
}
@media (max-width: 991.98px) {
    .product-slider-item {
        width: calc(33.333% - 12px) !important;
        min-width: 240px !important;
    }
}
@media (max-width: 767.98px) {
    .product-slider-item {
        width: calc(50% - 10px) !important;
        min-width: 200px !important;
    }
}
@media (max-width: 575.98px) {
    .product-slider-item {
        width: 75vw !important;
        min-width: 200px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.slider-nav').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.dataset.target;
            var slider = document.getElementById(targetId);
            if (!slider) return;
            var scrollAmount = slider.clientWidth * 0.75;
            if (this.classList.contains('slider-nav-prev')) {
                slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        });
    });
});
</script>
