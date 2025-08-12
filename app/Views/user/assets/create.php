<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>
<div class="container my-4">
    <h1 class="h3 mb-4">Add Asset</h1>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('assets') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-4">
            <label class="form-label">Asset Number *</label>
            <input type="number" name="asset" value="<?= old('asset') ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Quantity *</label>
            <input type="number" name="quantity" value="<?= old('quantity', 1) ?>" min="1" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Asset Description *</label>
            <input type="text" name="asset_description" value="<?= old('asset_description') ?>" class="form-control" required>
        </div>
        <!-- optional fields -->
        <div class="col-md-4">
            <label class="form-label">Location</label>
            <input type="text" name="location" value="<?= old('location') ?>" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Barcode Karyawan</label>
            <input type="text" name="bar_kar" value="<?= old('bar_kar') ?>" class="form-control">
        </div>
        <div class="col-12 d-flex justify-content-end">
            <a href="<?= base_url('') ?>" class="btn btn-link">Cancel</a>
            <button class="btn btn-primary ms-2">Save Asset</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
