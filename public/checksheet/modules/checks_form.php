<?php
$pdo         = getPDO();
$user        = current_user();
$area_id     = isset($_GET['area_id']) ? (int)$_GET['area_id'] : 0;
$sheet_id    = isset($_GET['check_sheet_id']) ? (int)$_GET['check_sheet_id'] : 0;
$errors      = [];
$successMsg  = null;

function get_grouped_areas(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM areas ORDER BY name, sub_area");
    $all = $stmt->fetchAll();
    $groups = [];
    foreach ($all as $row) {
        $groups[$row['name']][] = $row;
    }
    return $groups;
}

function save_photo_upload(string $field, int $headerId, int $itemId): ?string
{
    if (!isset($_FILES[$field])) {
        return null;
    }

    $file = $_FILES[$field];
    if (!isset($file['error'][$itemId]) || $file['error'][$itemId] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'][$itemId] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tmp_name = $file['tmp_name'][$itemId];
    $size     = $file['size'][$itemId];
    $name     = $file['name'][$itemId];

    if ($size > 5 * 1024 * 1024) {
        return null;
    }

    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    $mime  = $finfo ? finfo_file($finfo, $tmp_name) : null;
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($mime, $allowed, true)) {
        return null;
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        return null;
    }

    $year  = date('Y');
    $month = date('m');
    $baseDir = __DIR__ . '/../public/uploads/' . $year . '/' . $month;
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0777, true);
    }

    $filename = 'check_' . $headerId . '_' . $itemId . '_' . time() . '.' . $ext;
    $fullPath = $baseDir . '/' . $filename;
    if (!move_uploaded_file($tmp_name, $fullPath)) {
        return null;
    }

    $relative = 'uploads/' . $year . '/' . $month . '/' . $filename;
    return $relative;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_check'])) {
    $area_id  = isset($_POST['area_id']) ? (int)$_POST['area_id'] : 0;
    $sheet_id = isset($_POST['check_sheet_id']) ? (int)$_POST['check_sheet_id'] : 0;
    $remarks  = trim($_POST['remarks'] ?? '');

    if ($area_id <= 0 || $sheet_id <= 0) {
        $errors[] = 'Area and Check Sheet are required.';
    }

    $stmt = $pdo->prepare("SELECT * FROM check_sheets WHERE id = ?");
    $stmt->execute([$sheet_id]);
    $sheet = $stmt->fetch();

    if (!$sheet) {
        $errors[] = 'Invalid check sheet.';
    }

    $stmt = $pdo->prepare("SELECT * FROM check_items WHERE check_sheet_id = ? ORDER BY id");
    $stmt->execute([$sheet_id]);
    $items = $stmt->fetchAll();

    if (!$items) {
        $errors[] = 'No items defined for this check sheet.';
    }

    $value_option = $_POST['value_option'] ?? [];
    $value_text   = $_POST['value_text'] ?? [];

    if (!$errors) {
        foreach ($items as $item) {
            $itemId       = (int)$item['id'];
            $input_type   = $item['input_type'];
            $has_photo    = (int)$item['has_photo'] === 1;
            $has_note     = (int)$item['has_note'] === 1;
            $itemLabel    = $item['item_label'];

            $opt = $value_option[$itemId] ?? '';
            $txt = trim($value_text[$itemId] ?? '');

            if ($input_type === 'ok_notok') {
                if ($opt === '') {
                    $errors[] = "Please select OK/NOT OK for '{$itemLabel}'.";
                } elseif (strtoupper($opt) !== 'OK') {
                    if ($has_note && $txt === '') {
                        $errors[] = "Note is required for '{$itemLabel}' when NOT OK.";
                    }
                    if ($has_photo) {
                        $file = $_FILES['photo'] ?? null;
                        if (!$file || !isset($file['error'][$itemId]) || $file['error'][$itemId] === UPLOAD_ERR_NO_FILE) {
                            $errors[] = "Photo is required for '{$itemLabel}' when NOT OK.";
                        }
                    }
                }
            } else {
                if ($txt === '') {
                    $errors[] = "Value is required for '{$itemLabel}'.";
                }
            }
        }

        if (!$errors && isset($_FILES['photo'])) {
            $file = $_FILES['photo'];
            foreach ($items as $item) {
                $itemId = (int)$item['id'];
                if (!isset($file['error'][$itemId]) || $file['error'][$itemId] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($file['error'][$itemId] !== UPLOAD_ERR_OK) {
                    $errors[] = "Upload error for '{$item['item_label']}'.";
                    continue;
                }
                $tmp_name = $file['tmp_name'][$itemId];
                $size     = $file['size'][$itemId];
                if ($size > 5 * 1024 * 1024) {
                    $errors[] = "File for '{$item['item_label']}' exceeds 5MB.";
                    continue;
                }
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                $mime  = $finfo ? finfo_file($finfo, $tmp_name) : null;
                if ($finfo) finfo_close($finfo);
                $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
                if ($mime && !in_array($mime, $allowed, true)) {
                    $errors[] = "Invalid image type for '{$item['item_label']}'.";
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO checks_header (check_sheet_id, area_id, checked_by, checked_at, remarks) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$sheet_id, $area_id, $user['id'], $remarks]);
        $headerId = (int)$pdo->lastInsertId();

        foreach ($items as $item) {
            $itemId       = (int)$item['id'];
            $input_type   = $item['input_type'];
            $itemLabel    = $item['item_label'];

            $opt = $input_type === 'ok_notok' ? ($value_option[$itemId] ?? '') : null;
            $txt = trim($value_text[$itemId] ?? '');

            $photoPath = null;
            if (isset($_FILES['photo']) && isset($_FILES['photo']['error'][$itemId])
                && $_FILES['photo']['error'][$itemId] !== UPLOAD_ERR_NO_FILE) {
                $photoPath = save_photo_upload('photo', $headerId, $itemId);
            }

            $stmt = $pdo->prepare("INSERT INTO checks_detail (checks_header_id, check_item_id, value_text, value_option, photo_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$headerId, $itemId, $txt !== '' ? $txt : null, $opt, $photoPath]);
        }

        $successMsg = 'Check submitted successfully.';
        $remarks     = '';
        $value_text  = [];
        $value_option = [];
    }
}

if ($area_id <= 0) {
    $groups = get_grouped_areas($pdo);
    ?>
    <div class="card">
        <div class="card-header">
            <h5>Select Area</h5>
            <span class="text-muted">Choose area &amp; sub-area to start inspection.</span>
        </div>
        <div class="card-body">
            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($groups as $name => $rows): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card area-group-card">
                            <div class="card-header">
                                <h6 class="text-uppercase text-muted">
                                    <?php echo htmlspecialchars($name); ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($rows as $row): ?>
                                    <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_form&area_id=' . (int)$row['id']); ?>"
                                       class="btn btn-outline-primary btn-block area-btn">
                                        <?php echo htmlspecialchars($row['sub_area']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
    <?php
    return;
}

if ($sheet_id <= 0) {
    $stmt = $pdo->query("SELECT * FROM check_sheets ORDER BY id");
    $sheets = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM areas WHERE id = ?");
    $stmt->execute([$area_id]);
    $area = $stmt->fetch();
    if (!$area) {
        echo '<div class="alert alert-danger">Invalid area.</div>';
        return;
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5>Select Check Sheet</h5>
            <span class="text-muted">
                Area: <?php echo htmlspecialchars($area['name'] . ' / ' . $area['sub_area']); ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($sheets as $sheet): ?>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6><?php echo htmlspecialchars(ucfirst($sheet['name'])); ?></h6>
                                <p class="text-muted small"><?php echo htmlspecialchars($sheet['description']); ?></p>
                                <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_form&area_id=' . (int)$area_id . '&check_sheet_id=' . (int)$sheet['id']); ?>"
                                   class="btn btn-primary btn-sm">Use this sheet</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$sheets): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">No check sheets configured.</div>
                    </div>
                <?php endif; ?>
            </div>
            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_form'); ?>" class="btn btn-secondary mt-3">&laquo; Back to Area</a>
        </div>
    </div>
    <?php
    return;
}

$stmt = $pdo->prepare("SELECT * FROM areas WHERE id = ?");
$stmt->execute([$area_id]);
$area = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM check_sheets WHERE id = ?");
$stmt->execute([$sheet_id]);
$sheet = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM check_items WHERE check_sheet_id = ? ORDER BY id");
$stmt->execute([$sheet_id]);
$items = $stmt->fetchAll();

if (!$area || !$sheet || !$items) {
    echo '<div class="alert alert-danger">Invalid configuration (area/sheet/items).</div>';
    return;
}
?>
<div class="card">
    <div class="card-header">
        <h5>Check Entry</h5>
        <span class="text-muted">
            <?php echo htmlspecialchars($area['name'] . ' &raquo; ' . $area['sub_area'] . ' &raquo; ' . ucfirst($sheet['name'])); ?>
        </span>
    </div>
    <div class="card-body">
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
            </div>
        <?php endif; ?>
        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data"
              action="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_form&area_id=' . (int)$area_id . '&check_sheet_id=' . (int)$sheet_id); ?>">

            <input type="hidden" name="area_id" value="<?php echo (int)$area_id; ?>">
            <input type="hidden" name="check_sheet_id" value="<?php echo (int)$sheet_id; ?>">

            <?php foreach ($items as $item): ?>
                <?php
                $itemId     = (int)$item['id'];
                $input_type = $item['input_type'];
                $has_photo  = (int)$item['has_photo'] === 1;
                $has_note   = (int)$item['has_note'] === 1;
                $label      = $item['item_label'];

                $optVal = $_POST['value_option'][$itemId] ?? '';
                $txtVal = $_POST['value_text'][$itemId] ?? '';

                // For ok_notok items, note is only shown when NOT OK
                $showNote = ($input_type === 'ok_notok' && $has_note && $optVal === 'NOT OK');
                ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><?php echo htmlspecialchars($label); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <?php if ($input_type === 'ok_notok'): ?>
                                <div class="form-group col-md-3">
                                    <label>Status</label>
                                    <select
                                        id="status_<?php echo $itemId; ?>"
                                        name="value_option[<?php echo $itemId; ?>]"
                                        class="form-control js-status"
                                        data-item-id="<?php echo $itemId; ?>"
                                        required
                                    >
                                        <option value="">-- choose --</option>
                                        <option value="OK" <?php echo $optVal === 'OK' ? 'selected' : ''; ?>>OK</option>
                                        <option value="NOT OK" <?php echo $optVal === 'NOT OK' ? 'selected' : ''; ?>>NOT OK</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        When NOT OK, additional evidence is required.
                                    </small>
                                </div>
                            <?php endif; ?>

                            <?php if ($input_type === 'text' || $input_type === 'number'): ?>
                                <div class="form-group col-md-4">
                                    <label>Value</label>
                                    <input
                                        type="<?php echo $input_type === 'number' ? 'number' : 'text'; ?>"
                                        name="value_text[<?php echo $itemId; ?>]"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($txtVal); ?>"
                                        required
                                    >
                                </div>
                            <?php endif; ?>

                            <?php if ($has_note && $input_type === 'ok_notok'): ?>
                                <div class="form-group col-md-5 js-note-wrapper js-note-<?php echo $itemId; ?>"
                                    style="<?php echo $showNote ? '' : 'display:none;'; ?>">
                                    <label>Note</label>
                                    <textarea
                                        id="note_<?php echo $itemId; ?>"
                                        name="value_text[<?php echo $itemId; ?>]"
                                        rows="2"
                                        class="form-control"
                                        <?php echo $showNote ? 'required' : ''; ?>
                                    ><?php echo htmlspecialchars($txtVal); ?></textarea>
                                </div>
                            <?php elseif ($has_note && $input_type !== 'ok_notok'): ?>
                                <div class="form-group col-md-5">
                                    <label>Note</label>
                                    <textarea
                                        name="value_text[<?php echo $itemId; ?>]"
                                        rows="2"
                                        class="form-control"
                                    ><?php echo htmlspecialchars($txtVal); ?></textarea>
                                </div>
                            <?php endif; ?>

                            <?php if ($has_photo): ?>
                                <div class="form-group col-md-4">
                                    <label>Photo</label>
                                    <input
                                        type="file"
                                        id="photo_<?php echo $itemId; ?>"
                                        name="photo[<?php echo $itemId; ?>]"
                                        class="form-control-file js-photo"
                                        accept="image/*"
                                        data-item-id="<?php echo $itemId; ?>"
                                        data-require-when-not-ok="<?php echo ($input_type === 'ok_notok') ? '1' : '0'; ?>"
                                        <?php
                                        if ($input_type === 'ok_notok' && $optVal === 'NOT OK') {
                                            echo 'required';
                                        }
                                        ?>
                                    >
                                    <small class="form-text text-muted">
                                        Allowed: JPG/PNG, max 5MB. Camera-friendly input.
                                    </small>

                                    <!-- Preview thumbnail -->
                                    <div class="mt-2 js-photo-preview-wrapper d-none"
                                        data-item-id="<?php echo $itemId; ?>">
                                        <img src=""
                                            alt="Preview"
                                            class="img-thumbnail js-photo-preview-img"
                                            style="max-height: 120px;">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="form-group">
                <label>Overall Remarks (optional)</label>
                <textarea name="remarks" class="form-control" rows="3"><?php
                    echo isset($remarks) ? htmlspecialchars($remarks) : '';
                ?></textarea>
            </div>

            <button type="submit" name="submit_check" class="btn btn-success">
                <i class="ti-check"></i> Submit Check
            </button>
            <a href="<?php echo htmlspecialchars(BASE_URL . 'index.php?page=checks_form'); ?>" class="btn btn-secondary">
                &laquo; Change Area
            </a>
        </form>
        <script>
        (function () {
            function updateItemFields(itemId) {
                var statusEl = document.getElementById('status_' + itemId);
                if (!statusEl) return;

                var value = statusEl.value;
                var noteWrapper = document.querySelector('.js-note-' + itemId);
                var noteEl = document.getElementById('note_' + itemId);
                var photoEl = document.getElementById('photo_' + itemId);

                // NOTE visibility & required
                if (noteWrapper && noteEl) {
                    if (value === 'NOT OK') {
                        noteWrapper.style.display = '';
                        noteEl.required = true;
                    } else {
                        noteWrapper.style.display = 'none';
                        noteEl.required = false;
                        // optional: clear note when switching back to OK
                        // noteEl.value = '';
                    }
                }

                // PHOTO required only when NOT OK for ok_notok items
                if (photoEl) {
                    var requireWhenNotOk = photoEl.getAttribute('data-require-when-not-ok') === '1';
                    if (requireWhenNotOk && value === 'NOT OK') {
                        photoEl.required = true;
                    } else {
                        photoEl.required = false; // optional when OK or for non-ok_notok items
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Attach change handlers for all status selects
                var selects = document.querySelectorAll('.js-status');
                selects.forEach(function (el) {
                    var itemId = el.getAttribute('data-item-id');
                    if (!itemId) return;

                    el.addEventListener('change', function () {
                        updateItemFields(itemId);
                    });

                    // Initialise on load (useful when reloading after validation error)
                    updateItemFields(itemId);
                });
            });
        })();
        </script>
        <script>
        (function () {
            function updateItemFields(itemId) {
                var statusEl = document.getElementById('status_' + itemId);
                if (!statusEl) return;

                var value = statusEl.value;
                var noteWrapper = document.querySelector('.js-note-' + itemId);
                var noteEl = document.getElementById('note_' + itemId);
                var photoEl = document.getElementById('photo_' + itemId);

                // NOTE visibility & required
                if (noteWrapper && noteEl) {
                    if (value === 'NOT OK') {
                        noteWrapper.style.display = '';
                        noteEl.required = true;
                    } else {
                        noteWrapper.style.display = 'none';
                        noteEl.required = false;
                    }
                }

                // PHOTO required only when NOT OK for ok_notok items
                if (photoEl) {
                    var requireWhenNotOk = photoEl.getAttribute('data-require-when-not-ok') === '1';
                    if (requireWhenNotOk && value === 'NOT OK') {
                        photoEl.required = true;
                    } else {
                        photoEl.required = false; // optional when OK or non-ok_notok
                    }
                }
            }

            function handlePhotoPreview(inputEl) {
                var itemId = inputEl.getAttribute('data-item-id');
                var wrapper = document.querySelector(
                    '.js-photo-preview-wrapper[data-item-id="' + itemId + '"]'
                );
                if (!wrapper) return;
                var img = wrapper.querySelector('.js-photo-preview-img');
                if (!img) return;

                // No file selected -> hide preview
                if (!inputEl.files || !inputEl.files[0]) {
                    wrapper.classList.add('d-none');
                    img.src = '';
                    return;
                }

                var file = inputEl.files[0];
                if (!file.type || file.type.indexOf('image/') !== 0) {
                    // not an image
                    wrapper.classList.add('d-none');
                    img.src = '';
                    return;
                }

                var url = URL.createObjectURL(file);
                img.src = url;
                wrapper.classList.remove('d-none');

                img.onload = function () {
                    URL.revokeObjectURL(url);
                };
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Status change handlers (OK / NOT OK)
                var selects = document.querySelectorAll('.js-status');
                selects.forEach(function (el) {
                    var itemId = el.getAttribute('data-item-id');
                    if (!itemId) return;

                    el.addEventListener('change', function () {
                        updateItemFields(itemId);
                    });

                    // init state on load
                    updateItemFields(itemId);
                });

                // Photo preview handlers
                var photoInputs = document.querySelectorAll('.js-photo');
                photoInputs.forEach(function (input) {
                    input.addEventListener('change', function () {
                        handlePhotoPreview(this);
                    });

                    // if browser kept the file on reload, show preview
                    if (input.files && input.files[0]) {
                        handlePhotoPreview(input);
                    }
                });
            });
        })();
        </script>

    </div>
</div>
