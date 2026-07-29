<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (p.name LIKE :search OR p.sku LIKE :search2)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
}
if ($status_filter !== '') {
    $where .= " AND p.status = :status";
    $params[':status'] = intval($status_filter);
}
if ($category_filter !== '') {
    $where .= " AND p.category_id = :category_id";
    $params[':category_id'] = intval($category_filter);
}

$total = $db->query("SELECT COUNT(*) as count FROM products p WHERE $where", $params)->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE $where ORDER BY p.id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if (isset($_POST['bulk_action']) && isset($_POST['product_ids'])) {
    $action = $_POST['bulk_action'];
    $ids = array_map('intval', $_POST['product_ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    if ($action === 'delete') {
        foreach ($ids as $pid) {
            $images = $db->query("SELECT image FROM product_images WHERE product_id=?", [$pid])->fetchAll();
            foreach ($images as $img) {
                $path = __DIR__ . '/../assets/uploads/products/' . $img['image'];
                if (file_exists($path)) unlink($path);
            }
            $db->query("DELETE FROM product_images WHERE product_id=?", [$pid]);
            $db->query("DELETE FROM product_specifications WHERE product_id=?", [$pid]);
            $db->query("DELETE FROM cart WHERE product_id=?", [$pid]);
            $db->query("DELETE FROM wishlist_items WHERE product_id=?", [$pid]);
            $db->query("DELETE FROM products WHERE id=?", [$pid]);
        }
        flash('success', count($ids) . ' products deleted successfully.');
    } elseif ($action === 'feature') {
        $db->query("UPDATE products SET is_featured=1 WHERE id IN ($placeholders)", $ids);
        flash('success', 'Products marked as featured.');
    } elseif ($action === 'unfeature') {
        $db->query("UPDATE products SET is_featured=0 WHERE id IN ($placeholders)", $ids);
        flash('success', 'Products unmarked as featured.');
    }
    redirect('products.php');
}

$pageTitle = 'Products';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">All Products (<?= number_format($total) ?>)</h5>
    <a href="<?= BASE_URL ?>/admin/add_product.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Product</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:13px;">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:13px;">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:13px;">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-outline-secondary w-100"><i class="fas fa-times me-1"></i>Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <form method="POST" id="bulkForm">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Discount Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                        <tr><td colspan="12" class="text-center text-muted py-4">No products found</td></tr>
                        <?php endif; ?>
                        <?php foreach ($products as $product): ?>
                        <?php $primaryImage = $db->query("SELECT image FROM product_images WHERE product_id=? AND is_primary=1 LIMIT 1", [$product['id']])->fetch(); ?>
                        <tr>
                            <td><input type="checkbox" name="product_ids[]" value="<?= $product['id'] ?>" class="product-checkbox"></td>
                            <td class="fw-semibold">#<?= $product['id'] ?></td>
                            <td>
                                <?php if ($primaryImage): ?>
                                <img src="<?= BASE_URL ?>assets/uploads/products/<?= sanitize($primaryImage['image']) ?>" alt="" width="50" height="50" style="object-fit:cover;border-radius:8px;">
                                <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width:50px;height:50px;border-radius:8px;"><i class="fas fa-image text-muted"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold" style="max-width:200px;"><?= sanitize($product['name']) ?></td>
                            <td><?= sanitize($product['category_name'] ?? 'N/A') ?></td>
                            <td><?= formatPrice($product['price']) ?></td>
                            <td><?= $product['discount_price'] ? formatPrice($product['discount_price']) : '-' ?></td>
                            <td><span class="badge bg-<?= $product['quantity'] > 0 ? 'success' : 'danger' ?>"><?= $product['quantity'] ?></span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-<?= $product['status'] ? 'success' : 'danger' ?> btn-action" onclick="toggleStatus('products',<?= $product['id'] ?>,'status',this)">
                                    <?= $product['status'] ? 'Active' : 'Inactive' ?>
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-<?= $product['is_featured'] ? 'warning' : 'secondary' ?> btn-action" onclick="toggleStatus('products',<?= $product['id'] ?>,'is_featured',this)">
                                    <?= $product['is_featured'] ? 'Yes' : 'No' ?>
                                </button>
                            </td>
                            <td style="white-space:nowrap;"><?= date('M d, Y', strtotime($product['created_at'])) ?></td>
                            <td style="white-space:nowrap;">
                                <a href="<?= BASE_URL ?>/admin/edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteItem('<?= BASE_URL ?>/admin/delete_product.php',<?= $product['id'] ?>)"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div class="d-flex gap-2">
                    <select name="bulk_action" class="form-select form-select-sm" style="width:auto;">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Delete Selected</option>
                        <option value="feature">Mark as Featured</option>
                        <option value="unfeature">Remove Featured</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Apply bulk action?')">Apply</button>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category_id=<?= urlencode($category_filter) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </form>
    </div>
</div>

<?php
$extraScripts = '<script>
document.getElementById("selectAll").addEventListener("change", function(){
    document.querySelectorAll(".product-checkbox").forEach(cb => cb.checked = this.checked);
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
