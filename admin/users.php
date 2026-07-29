<?php require_once("admin_guard.php"); ?>
<?php
// Delete a user (and their data)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $_SESSION['user_id']) {
        foreach (['income','expenses','savings','budgets','notifications','settings','login_logs'] as $table) {
            $d = $conn->prepare("DELETE FROM $table WHERE user_id=?");
            $d->bind_param("i", $id);
            $d->execute();
        }
        $d = $conn->prepare("DELETE FROM users WHERE id=?");
        $d->bind_param("i", $id);
        $d->execute();
    }
    header("Location: users.php");
    exit();
}

// Promote / demote admin role
if (isset($_GET['toggle_role'])) {
    $id = (int)$_GET['toggle_role'];
    $r = $conn->prepare("SELECT role FROM users WHERE id=?");
    $r->bind_param("i", $id); $r->execute();
    $current = $r->get_result()->fetch_assoc()['role'];
    $newRole = $current === 'admin' ? 'user' : 'admin';
    $u = $conn->prepare("UPDATE users SET role=? WHERE id=?");
    $u->bind_param("si", $newRole, $id);
    $u->execute();
    header("Location: users.php");
    exit();
}

$search = $_GET['search'] ?? '';
$sql = "SELECT id, full_name, email, phone, role, created_at FROM users WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Users | NeoFinance Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">🛡️ <span>Admin Panel</span></div>
    <ul class="sidebar-menu">
        <li><a href="index.php"><i class="fa-solid fa-gauge"></i> Overview</a></li>
        <li class="active"><a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
        <li class="sidebar-divider"></li>
        <li><a href="../dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to App</a></li>
        <li><a href="../authentication/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>
<div class="topbar">
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
    <div class="topbar-right"><span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span></div>
</div>

<div class="dashboard-content">
    <h2 class="fw-bold mb-4"><i class="fa-solid fa-users text-info"></i> Manage Users</h2>

    <form method="GET" class="mb-4">
        <input type="text" name="search" class="form-control" style="max-width:320px;" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
    </form>

    <div class="transaction-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if ($users->num_rows === 0): ?>
                    <tr><td colspan="6" class="text-center">No users found.</td></tr>
                <?php else: while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['phone']); ?></td>
                        <td><span class="badge <?php echo $u['role']==='admin'?'bg-info text-dark':'bg-secondary'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><?php echo date("d M Y", strtotime($u['created_at'])); ?></td>
                        <td>
                            <a href="?toggle_role=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-info">
                                <?php echo $u['role']==='admin' ? 'Revoke Admin' : 'Make Admin'; ?>
                            </a>
                            <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user and all their data?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>document.getElementById('sidebarToggle').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));</script>
</body>
</html>
