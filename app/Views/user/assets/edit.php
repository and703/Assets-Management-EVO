<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Asset #<?= esc($asset['id']) ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?php echo url('/') ?>"><?php echo lang('App.home') ?></a></li>
          <li class="breadcrumb-item active">Edit Asset #<?= esc($asset['id']) ?></li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <?php if (session('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('assets/' . $asset['id']) ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">
        <div class="col-md-6">
            <label class="form-label">Asset Number</label>
            <input type="number" value="<?= esc($asset['asset']) ?>" class="form-control" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Asset Description</label>
            <input type="text" name="asset_description" value="<?= esc($asset['asset_description']) ?>" class="form-control" disabled>
        </div>
        </br>
        </br>
        </br>
        </br>
        <div class="col-md-6">
            <label class="form-label">Serial Number</label>
            <input type="text" name="sn" value="<?= old('sn', $asset['sn']) ?>" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Location *</label>
            <input type="text" name="location" value="<?= old('location', $asset['location']) ?>" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Barcode Karyawan</label>
            <input type="text" name="bar_kar" value="<?= old('bar_kar', $asset['bar_kar']) ?>" class="form-control">
        </div>
        </br>
        </br>
        </br>
        </br>
        <div class="col-1 d-flex justify-content-end">
            <a href="<?= base_url('') ?>" class="btn btn-link">Cancel</a>
            <button class="btn btn-primary ms-2">Update</button>
        </div>
    </form>
</section>
<!-- /.content -->
<?= $this->endSection() ?>
