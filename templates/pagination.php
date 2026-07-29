<?php
if (!isset($pagination) || empty($pagination)) return;

$total = (int)($pagination['total'] ?? 0);
$per_page = (int)($pagination['per_page'] ?? 12);
$current_page = (int)($pagination['current_page'] ?? 1);
$total_pages = (int)($pagination['total_pages'] ?? 1);
$has_prev = $pagination['has_prev'] ?? ($current_page > 1);
$has_next = $pagination['has_next'] ?? ($current_page < $total_pages);
$prev_page = $pagination['prev_page'] ?? max(1, $current_page - 1);
$next_page = $pagination['next_page'] ?? min($total_pages, $current_page + 1);

$base_url = $_SERVER['REQUEST_URI'] ?? '';
$separator = strpos($base_url, '?') !== false ? '&' : '?';
$base_url = strtok($base_url, '?');

$build_url = function($page) use ($base_url, $separator) {
    $params = $_GET;
    $params['page'] = $page;
    return $base_url . '?' . http_build_query($params);
};

$start = (($current_page - 1) * $per_page) + 1;
$end = min($current_page * $per_page, $total);
?>

<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="text-muted small d-none d-sm-block">
            Showing <strong><?= $start ?></strong> to <strong><?= $end ?></strong> of <strong><?= number_format($total) ?></strong> results
        </div>

        <ul class="pagination mb-0 justify-content-center flex-wrap">
            <li class="page-item <?= !$has_prev ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $has_prev ? $build_url($prev_page) : '#' ?>" aria-label="Previous">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>

            <?php
            $range = 2;
            $start_page = max(1, $current_page - $range);
            $end_page = min($total_pages, $current_page + $range);

            if ($start_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $build_url(1) ?>">1</a>
                </li>
                <?php if ($start_page > 2): ?>
                    <li class="page-item disabled d-none d-sm-block">
                        <span class="page-link">&hellip;</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $build_url($i) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <li class="page-item disabled d-none d-sm-block">
                        <span class="page-link">&hellip;</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $build_url($total_pages) ?>"><?= $total_pages ?></a>
                </li>
            <?php endif; ?>

            <li class="page-item <?= !$has_next ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $has_next ? $build_url($next_page) : '#' ?>" aria-label="Next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.pagination .page-link {
    border: none;
    color: #555;
    border-radius: 8px;
    margin: 0 2px;
    padding: 8px 14px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}
.pagination .page-link:hover:not(.active) {
    background: #f0f0f0;
    color: #333;
}
.pagination .page-item.disabled .page-link {
    color: #ccc;
}
@media (max-width: 575.98px) {
    .pagination .page-link {
        padding: 6px 10px;
        font-size: 0.8rem;
    }
}
</style>
<?php endif; ?>
