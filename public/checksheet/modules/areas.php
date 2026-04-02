<?php
if (!is_admin()) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    return;
}

$pdo    = getPDO();
$action = $_GET['action'] ?? 'list';
$message = null;
$errors  = [];

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name     = trim($_POST['name'] ?? '');
    $sub_area = trim($_POST['sub_area'] ?? '');

    if ($name === '' || $sub_area === '') {
        $errors[] = 'Name and Sub Area are required.';
    }

    if (!$errors) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE areas SET name = ?, sub_area = ? WHERE id = ?");
            $stmt->execute([$name, $sub_area, $id]);
            $message = 'Area updated successfully.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO areas (name, sub_area) VALUES (?, ?)");
            $stmt->execute([$name, $sub_area]);
            $message = 'Area added successfully.';
        }
        $action = 'list';
    }
}

if ($action === 'delete') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM areas WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Area deleted.';
    }
    $action = 'list';
}

if ($action === 'edit') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $area = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM areas WHERE id = ?");
        $stmt->execute([$id]);
        $area = $stmt->fetch();
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5><?php echo $area ? 'Edit Area' : 'Add Area'; ?></h5>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=areas&action=save'); ?>">
                <input type="hidden" name="id" value="<?php echo $area ? (int)$area['id'] : 0; ?>">
                <div class="form-group">
                    <label>Area Name</label>
                    <input type="text" name="name" class="form-control"
                           value="<?php echo $area ? htmlspecialchars($area['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Sub Area</label>
                    <input type="text" name="sub_area" class="form-control"
                           value="<?php echo $area ? htmlspecialchars($area['sub_area']) : ''; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Save</button>
                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=areas'); ?>" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return;
}

$stmt = $pdo->query("SELECT * FROM areas ORDER BY name, sub_area");
$areas = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">
        <h5>Areas</h5>
        <div class="card-header-right">
            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=areas&action=edit'); ?>" class="btn btn-sm btn-primary">
                <i class="ti-plus"></i> Add Area
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Area</th>
                        <th>Sub Area</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($areas as $row): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['sub_area']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=areas&action=edit&id=' . (int)$row['id']); ?>"
                               class="btn btn-sm btn-warning">Edit</a>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=areas&action=delete&id=' . (int)$row['id']); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this area?');">Del</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$areas): ?>
                    <tr><td colspan="4" class="text-center">No areas found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
