
<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><?php echo lang('App.assets_import') ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?php echo url('/') ?>"><?php echo lang('App.home') ?></a></li>
          <li class="breadcrumb-item active"><?php echo lang('App.assets_import') ?></li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">

    <h1 class="mb-4">Import Assets (Excel / CSV)</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('error')); ?>
                </div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('message')): ?>
                <div class="alert alert-success">
                    <?= esc(session()->getFlashdata('message')); ?>
                </div>
            <?php endif; ?>
            <form action="<?= base_url('/import'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="row mb-2">
                    <div class="col-md-3">
                        <input type="file" name="asset_file" accept=".xlsx,.csv" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-upload me-1"></i>Upload & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</section>
<!-- /.content -->
<?= $this->endSection(); ?>
