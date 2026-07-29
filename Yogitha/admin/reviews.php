<?php
// C:\xampp\htdocs\NewProject\admin\reviews.php
// Moderate User Reviews

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// 1. Process Review Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        if ($id > 0) {
            try {
                // Fetch destination_id associated with this review before deleting
                $stmt = $pdo->prepare("SELECT destination_id FROM reviews WHERE id = ?");
                $stmt->execute([$id]);
                $review = $stmt->fetch();

                if ($review) {
                    $dest_id = $review['destination_id'];

                    // Delete the review
                    $del = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                    $del->execute([$id]);

                    // Recalculate average rating of that destination
                    $updRating = $pdo->prepare("UPDATE destinations SET rating = (SELECT IFNULL(AVG(rating), 0) FROM reviews WHERE destination_id = ?) WHERE id = ?");
                    $updRating->execute([$dest_id, $dest_id]);

                    $_SESSION['admin_success'] = "Review deleted successfully.";
                } else {
                    $_SESSION['admin_error'] = "Review not found.";
                }
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Database error: " . $e->getMessage();
            }
        }
        header("Location: reviews.php");
        exit;
    }
}

// 2. Fetch all reviews
try {
    $reviews = $pdo->query("SELECT r.*, u.name AS user_name, u.email AS user_email, d.name AS destination_name 
                            FROM reviews r 
                            JOIN users u ON r.user_id = u.id 
                            JOIN destinations d ON r.destination_id = d.id 
                            ORDER BY r.created_at DESC")->fetchAll();
} catch (\PDOException $e) {
    die("Database fetch error: " . $e->getMessage());
}
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="bi-chat-left-text-fill text-warning me-2"></i>Review Moderation
    </h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>User Details</th>
                    <th>Destination</th>
                    <th>Rating</th>
                    <th>Review Content</th>
                    <th>Submitted On</th>
                    <th class="text-end" style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td><?php echo $rev['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($rev['user_name']); ?></strong><br>
                                <span class="text-muted small"><?php echo htmlspecialchars($rev['user_email']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($rev['destination_name']); ?></td>
                            <td>
                                <span class="text-warning text-nowrap">
                                    <?php for ($i=1; $i<=5; $i++): ?>
                                        <i class="bi-star-fill <?php echo $i <= $rev['rating'] ? '' : 'text-black-50'; ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="small fw-bold ms-1">(<?php echo $rev['rating']; ?>)</span>
                            </td>
                            <td>
                                <div class="text-muted small" style="max-width: 300px; white-space: normal;">
                                    <?php echo htmlspecialchars($rev['comment']); ?>
                                </div>
                            </td>
                            <td class="small text-muted"><?php echo date('M d, Y H:i', strtotime($rev['created_at'])); ?></td>
                            <td class="text-end">
                                <form action="reviews.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this review? This will update the destination average rating.');" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $rev['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No reviews found in the database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
