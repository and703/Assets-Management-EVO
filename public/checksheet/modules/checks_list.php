<?php
$pdo = getPDO();

$action  = $_GET['action'] ?? 'list';
$message = null;

$filter_area_id   = isset($_GET['filter_area_id']) ? (int)$_GET['filter_area_id'] : 0;
$filter_sheet_id  = isset($_GET['filter_sheet_id']) ? (int)$_GET['filter_sheet_id'] : 0;
$filter_date_from = $_GET['filter_date_from'] ?? '';
$filter_date_to   = $_GET['filter_date_to'] ?? '';

if ($action === 'view') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $stmt = $pdo->prepare(
        "SELECT ch.*, 
                a.name AS area_name, a.sub_area,
                cs.name AS sheet_name,
                u.username
         FROM checks_header ch
         JOIN areas a ON ch.area_id = a.id
         JOIN check_sheets cs ON ch.check_sheet_id = cs.id
         JOIN users u ON ch.checked_by = u.id
         WHERE ch.id = ?"
    );
    $stmt->execute([$id]);
    $header = $stmt->fetch();

    if (!$header) {
        echo '<div class="alert alert-danger">Record not found.</div>';
        return;
    }

    $stmt = $pdo->prepare(
        "SELECT cd.*, ci.item_label, ci.input_type
         FROM checks_detail cd
         JOIN check_items ci ON cd.check_item_id = ci.id
         WHERE cd.checks_header_id = ?
         ORDER BY ci.id"
    );
    $stmt->execute([$id]);
    $details = $stmt->fetchAll();
    ?>
    <div class="card">
        <div class="card-header">
            <h5>Check Detail</h5>
            <span class="text-muted">
                Area: <?php echo htmlspecialchars($header['area_name'] . ' / ' . $header['sub_area']); ?>
                &mdash;
                Sheet: <?php echo htmlspecialchars($header['sheet_name']); ?>
            </span>
            <div class="card-header-right">
                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_list'); ?>" class="btn btn-sm btn-secondary">
                    &laquo; Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Checked at:</strong> <?php echo htmlspecialchars($header['checked_at']); ?><br>
                <strong>Checked by:</strong> <?php echo htmlspecialchars($header['username']); ?><br>
                <?php if ($header['remarks']): ?>
                    <strong>Remarks:</strong> <?php echo nl2br(htmlspecialchars($header['remarks'])); ?>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Status</th>
                        <th>Note / Value</th>
                        <th>Photo</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    foreach ($details as $d):
                        $status = $d['value_option'] ?? '';
                        $note   = $d['value_text'] ?? '';
                        $badge  = '';
                        if ($status !== '') {
                            $statusUpper = strtoupper($status);
                            if ($statusUpper === 'OK') {
                                $badge = '<span class="badge badge-success">OK</span>';
                            } else {
                                $badge = '<span class="badge badge-danger">' . htmlspecialchars($statusUpper) . '</span>';
                            }
                        }
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($d['item_label']); ?></td>
                            <td><?php echo $badge ?: '-'; ?></td>
                            <td><?php echo $note ? nl2br(htmlspecialchars($note)) : '-'; ?></td>
                            <td>
                                <?php if ($d['photo_path']): ?>
                                    <a href="<?php echo htmlspecialchars(BASE_URL . $d['photo_path']); ?>" target="_blank">
                                        <img src="<?php echo htmlspecialchars(BASE_URL . $d['photo_path']); ?>"
                                             style="max-width:120px; max-height:120px;" class="img-thumbnail">
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$details): ?>
                        <tr><td colspan="5" class="text-center">No detail rows.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <?php
    return;
}

$areas = $pdo->query("SELECT id, name, sub_area FROM areas ORDER BY name, sub_area")->fetchAll();
$sheets = $pdo->query("SELECT id, name FROM check_sheets ORDER BY id")->fetchAll();

$sql = "SELECT ch.id, ch.checked_at, ch.check_sheet_id, ch.area_id,
               a.name AS area_name, a.sub_area,
               cs.name AS sheet_name,
               u.username
        FROM checks_header ch
        JOIN areas a ON ch.area_id = a.id
        JOIN check_sheets cs ON ch.check_sheet_id = cs.id
        JOIN users u ON ch.checked_by = u.id
        WHERE 1=1";

$params = [];

if ($filter_area_id > 0) {
    $sql .= " AND ch.area_id = ?";
    $params[] = $filter_area_id;
}

if ($filter_sheet_id > 0) {
    $sql .= " AND ch.check_sheet_id = ?";
    $params[] = $filter_sheet_id;
}

if ($filter_date_from !== '') {
    $sql .= " AND ch.checked_at >= ?";
    $params[] = $filter_date_from . ' 00:00:00';
}

if ($filter_date_to !== '') {
    $sql .= " AND ch.checked_at <= ?";
    $params[] = $filter_date_to . ' 23:59:59';
}

$sql .= " ORDER BY ch.checked_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$totalChecks  = count($rows);
$todayStr     = date('Y-m-d');
$stmtToday    = $pdo->prepare("SELECT COUNT(*) FROM checks_header WHERE DATE(checked_at) = ?");
$stmtToday->execute([$todayStr]);
$todayChecks  = (int)$stmtToday->fetchColumn();
?>
<div class="row">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6>Total Checks</h6>
                <h3><?php echo (int)$totalChecks; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h6>Today</h6>
                <h3><?php echo (int)$todayChecks; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Check Results</h5>
    </div>
    <div class="card-body">

        <form method="get" class="mb-3">
            <input type="hidden" name="page" value="checks_list">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Area</label>
                    <select name="filter_area_id" class="form-control">
                        <option value="0">All</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?php echo (int)$a['id']; ?>"
                                <?php echo $filter_area_id === (int)$a['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['name'] . ' / ' . $a['sub_area']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>Check Sheet</label>
                    <select name="filter_sheet_id" class="form-control">
                        <option value="0">All</option>
                        <?php foreach ($sheets as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>"
                                <?php echo $filter_sheet_id === (int)$s['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Date From</label>
                    <input type="date" name="filter_date_from" class="form-control"
                           value="<?php echo htmlspecialchars($filter_date_from); ?>">
                </div>
                <div class="form-group col-md-2">
                    <label>Date To</label>
                    <input type="date" name="filter_date_to" class="form-control"
                           value="<?php echo htmlspecialchars($filter_date_to); ?>">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-light">
                <tr>
                    <th>Date/Time</th>
                    <th>Area</th>
                    <th>Check Sheet</th>
                    <th>Checked By</th>
                    <th style="width:100px;">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['checked_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['area_name'] . ' / ' . $row['sub_area']); ?></td>
                        <td><?php echo htmlspecialchars($row['sheet_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_list&action=view&id=' . (int)$row['id']); ?>"
                               class="btn btn-sm btn-info">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="5" class="text-center">No results found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
