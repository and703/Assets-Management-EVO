<?php
if (!is_admin()) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    return;
}

$pdo        = getPDO();
$action     = $_GET['action'] ?? 'list';
$sheet_id   = isset($_GET['sheet_id']) ? (int)$_GET['sheet_id'] : 0;
$message    = null;
$errors     = [];

if ($action === 'save_sheet' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($name === '') {
        $errors[] = 'Sheet name is required.';
    }

    if (!$errors) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE check_sheets SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $desc, $id]);
            $message = 'Check sheet updated.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO check_sheets (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            $message = 'Check sheet created.';
        }
        $action = 'list';
    }
}

if ($action === 'delete_sheet') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM check_sheets WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Check sheet deleted.';
    }
    $action = 'list';
}

if ($action === 'save_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $sheet_id      = isset($_POST['sheet_id']) ? (int)$_POST['sheet_id'] : 0;
    $item_label    = trim($_POST['item_label'] ?? '');
    $has_photo     = isset($_POST['has_photo']) ? 1 : 0;
    $has_note      = isset($_POST['has_note']) ? 1 : 0;
    $input_type    = trim($_POST['input_type'] ?? 'text');

    if ($sheet_id <= 0) {
        $errors[] = 'Invalid sheet.';
    }
    if ($item_label === '') {
        $errors[] = 'Item label is required.';
    }

    if (!$errors) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE check_items SET item_label = ?, has_photo = ?, has_note = ?, input_type = ? WHERE id = ?");
            $stmt->execute([$item_label, $has_photo, $has_note, $input_type, $id]);
            $message = 'Item updated.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO check_items (check_sheet_id, item_label, has_photo, has_note, input_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sheet_id, $item_label, $has_photo, $has_note, $input_type]);
            $message = 'Item added.';
        }
        $action = 'manage_items';
        $_GET['sheet_id'] = $sheet_id;
    }
}

if ($action === 'delete_item') {
    $id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $sheet_id = isset($_GET['sheet_id']) ? (int)$_GET['sheet_id'] : 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM check_items WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Item deleted.';
    }
    $action = 'manage_items';
    $_GET['sheet_id'] = $sheet_id;
}

if ($action === 'edit_sheet') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $sheet = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM check_sheets WHERE id = ?");
        $stmt->execute([$id]);
        $sheet = $stmt->fetch();
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5><?php echo $sheet ? 'Edit Check Sheet' : 'Add Check Sheet'; ?></h5>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=save_sheet'); ?>">
                <input type="hidden" name="id" value="<?php echo $sheet ? (int)$sheet['id'] : 0; ?>">
                <div class="form-group">
                    <label>Sheet Name</label>
                    <input type="text" name="name" class="form-control"
                           value="<?php echo $sheet ? htmlspecialchars($sheet['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php
                        echo $sheet ? htmlspecialchars($sheet['description']) : '';
                    ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Save</button>
                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets'); ?>" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return;
}

if ($action === 'manage_items') {
    $sheet_id = isset($_GET['sheet_id']) ? (int)$_GET['sheet_id'] : 0;
    $stmt = $pdo->prepare("SELECT * FROM check_sheets WHERE id = ?");
    $stmt->execute([$sheet_id]);
    $sheet = $stmt->fetch();
    if (!$sheet) {
        echo '<div class="alert alert-danger">Check sheet not found.</div>';
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM check_items WHERE check_sheet_id = ? ORDER BY id");
    $stmt->execute([$sheet_id]);
    $items = $stmt->fetchAll();

    $editItem = null;
    if (isset($_GET['item_id'])) {
        $itemId = (int)$_GET['item_id'];
        foreach ($items as $it) {
            if ((int)$it['id'] === $itemId) {
                $editItem = $it;
                break;
            }
        }
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5>Manage Items for: <?php echo htmlspecialchars($sheet['name']); ?></h5>
            <div class="card-header-right">
                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets'); ?>" class="btn btn-sm btn-secondary">
                    &laquo; Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                </div>
            <?php endif; ?>

            <h6><?php echo $editItem ? 'Edit Item' : 'Add Item'; ?></h6>
            <form method="post" action="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=save_item'); ?>">
                <input type="hidden" name="id" value="<?php echo $editItem ? (int)$editItem['id'] : 0; ?>">
                <input type="hidden" name="sheet_id" value="<?php echo (int)$sheet_id; ?>">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Item Label</label>
                        <input type="text" name="item_label" class="form-control"
                               value="<?php echo $editItem ? htmlspecialchars($editItem['item_label']) : ''; ?>" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Input Type</label>
                        <select name="input_type" class="form-control">
                            <?php
                            $types = ['ok_notok' => 'OK / NOT OK', 'text' => 'Text', 'number' => 'Number'];
                            $selectedType = $editItem ? $editItem['input_type'] : 'ok_notok';
                            foreach ($types as $k => $label) {
                                echo '<option value="' . htmlspecialchars($k) . '"' .
                                     ($k === $selectedType ? ' selected' : '') . '>' .
                                     htmlspecialchars($label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Options</label><br>
                        <label class="mr-3">
                            <input type="checkbox" name="has_photo" value="1"
                                <?php echo ($editItem && $editItem['has_photo']) ? 'checked' : ''; ?>>
                            Require Photo
                        </label>
                        <label>
                            <input type="checkbox" name="has_note" value="1"
                                <?php echo ($editItem && $editItem['has_note']) ? 'checked' : ''; ?>>
                            Require Note
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Save Item</button>
            </form>

            <hr>

            <h6>Items</h6>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Label</th>
                        <th>Input Type</th>
                        <th>Photo?</th>
                        <th>Note?</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo (int)$it['id']; ?></td>
                            <td><?php echo htmlspecialchars($it['item_label']); ?></td>
                            <td><?php echo htmlspecialchars($it['input_type']); ?></td>
                            <td><?php echo $it['has_photo'] ? '<span class="badge badge-info">Yes</span>' : 'No'; ?></td>
                            <td><?php echo $it['has_note'] ? '<span class="badge badge-info">Yes</span>' : 'No'; ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=manage_items&sheet_id=' . (int)$sheet_id . '&item_id=' . (int)$it['id']); ?>"
                                   class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=delete_item&sheet_id=' . (int)$sheet_id . '&id=' . (int)$it['id']); ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this item?');">Del</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$items): ?>
                        <tr><td colspan="6" class="text-center">No items defined.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <?php
    return;
}

$stmt = $pdo->query("SELECT * FROM check_sheets ORDER BY id");
$sheets = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header">
        <h5>Check Sheets</h5>
        <div class="card-header-right">
            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=edit_sheet'); ?>" class="btn btn-sm btn-primary">
                <i class="ti-plus"></i> Add
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
                    <th>Name</th>
                    <th>Description</th>
                    <th style="width:180px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sheets as $sheet): ?>
                    <tr>
                        <td><?php echo (int)$sheet['id']; ?></td>
                        <td><?php echo htmlspecialchars($sheet['name']); ?></td>
                        <td><?php echo htmlspecialchars($sheet['description']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=manage_items&sheet_id=' . (int)$sheet['id']); ?>"
                               class="btn btn-sm btn-info">Items</a>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=edit_sheet&id=' . (int)$sheet['id']); ?>"
                               class="btn btn-sm btn-warning">Edit</a>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=check_sheets&action=delete_sheet&id=' . (int)$sheet['id']); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this sheet?');">Del</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$sheets): ?>
                    <tr><td colspan="4" class="text-center">No check sheets found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
