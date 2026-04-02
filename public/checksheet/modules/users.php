<?php
if (!is_admin()) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    return;
}

$pdo     = getPDO();
$action  = $_GET['action'] ?? 'list';
$message = null;
$errors  = [];

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = trim($_POST['username'] ?? '');
    $role     = trim($_POST['role'] ?? 'inspector');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($role !== 'admin' && $role !== 'inspector') {
        $errors[] = 'Invalid role.';
    }

    if (!$errors) {
        if ($id > 0) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $role, $hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $role, $id]);
            }
            $message = 'User updated.';
        } else {
            if ($password === '') {
                $errors[] = 'Password is required for new user.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                try {
                    $stmt->execute([$username, $hash, $role]);
                    $message = 'User created.';
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        $errors[] = 'Username already exists.';
                    } else {
                        $errors[] = 'Database error.';
                    }
                }
            }
        }
        if ($message) {
            $action = 'list';
        }
    }
}

if ($action === 'delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $current = current_user();
        if ($current && (int)$current['id'] === $id) {
            $message = 'You cannot delete yourself.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'User deleted.';
        }
    }
    $action = 'list';
}

if ($action === 'edit') {
    $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $user = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5><?php echo $user ? 'Edit User' : 'Add User'; ?></h5>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=users&action=save'); ?>">
                <input type="hidden" name="id" value="<?php echo $user ? (int)$user['id'] : 0; ?>">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control"
                           value="<?php echo $user ? htmlspecialchars($user['username']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="inspector" <?php echo ($user && $user['role'] === 'inspector') ? 'selected' : ''; ?>>Inspector</option>
                        <option value="admin" <?php echo ($user && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password <?php echo $user ? '(leave blank to keep current)' : ''; ?></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary mt-3">Save</button>
                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=users'); ?>" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return;
}

$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id");
$users = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">
        <h5>Users</h5>
        <div class="card-header-right">
            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=users&action=edit'); ?>" class="btn btn-sm btn-primary">
                <i class="ti-plus"></i> Add User
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th style="width:120px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo (int)$u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=users&action=edit&id=' . (int)$u['id']); ?>"
                               class="btn btn-sm btn-warning">Edit</a>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=users&action=delete&id=' . (int)$u['id']); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this user?');">Del</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$users): ?>
                    <tr><td colspan="5" class="text-center">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
