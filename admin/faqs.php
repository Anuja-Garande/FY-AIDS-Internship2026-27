<?php
// C:\xampp\htdocs\NewProject\admin\faqs.php
// Admin CRUD Management for FAQs

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// 1. Handle Actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'add_faq') {
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $display_order = intval($_POST['display_order']);

        try {
            $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, display_order) VALUES (?, ?, ?)");
            $stmt->execute([$question, $answer, $display_order]);
            $_SESSION['admin_success'] = "FAQ added successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to add FAQ: " . $e->getMessage();
        }
        header("Location: faqs.php");
        exit;
    }

    if ($action === 'delete_faq') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "FAQ deleted.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to delete FAQ: " . $e->getMessage();
        }
        header("Location: faqs.php");
        exit;
    }
}

// 2. Fetch FAQs
$faqs = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC")->fetchAll();
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-question-circle-fill text-primary me-2"></i>Manage Frequently Asked Questions</h4>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addFaqModal">
            <i class="bi-plus-lg me-1"></i>Add FAQ
        </button>
    </div>

    <?php if (!empty($faqs)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faqs as $faq): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo $faq['display_order']; ?></span></td>
                            <td><strong><?php echo htmlspecialchars($faq['question']); ?></strong></td>
                            <td class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($faq['answer']); ?>">
                                <?php echo htmlspecialchars($faq['answer']); ?>
                            </td>
                            <td>
                                <form action="faqs.php" method="POST" class="d-inline" onsubmit="return confirm('Delete FAQ?');">
                                    <input type="hidden" name="action" value="delete_faq">
                                    <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No FAQs added yet.</p>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addFaqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="faqs.php" method="POST">
                <input type="hidden" name="action" value="add_faq">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Frequently Asked Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-9">
                        <label class="form-label small fw-bold">Question</label>
                        <input type="text" name="question" class="form-control" required placeholder="e.g. How do I cancel my booking?">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="1" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Answer</label>
                        <textarea name="answer" rows="4" class="form-control" required placeholder="Detailed explanation answer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add FAQ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
