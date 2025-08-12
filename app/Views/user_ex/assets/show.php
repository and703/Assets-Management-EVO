<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Asset #<?= esc($asset['id']) ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?php echo url('/') ?>"><?php echo lang('App.home') ?></a></li>
          <li class="breadcrumb-item active">Asset #<?= esc($asset['id']) ?></li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <a href="<?= base_url('') ?>" class="btn btn-sm btn-secondary mb-3"><i class="fa fa-arrow-left me-1"></i> Back</a>
    <table class="table table-bordered mt-3 w-auto">
        <tr><th class="bg-light">TagID</th><td><?= esc($asset['tagID']) ?></td></tr>
        <tr><th class="bg-light">Asset Number</th><td><?= esc($asset['asset']) ?></td></tr>
        <tr><th class="bg-light">Sub Number</th><td><?= esc($asset['subnumber']) ?></td></tr>
        <tr><th class="bg-light">Join Asset Number</th><td><?= esc($asset['joint_assets_number']) ?></td></tr>
        <tr><th class="bg-light">Capitalized On</th><td><?= date('Y-m-d H:i', strtotime($asset['capitalized_on']))?></td></tr>
        <tr><th class="bg-light">Asset Class</th><td><?= esc($asset['asset_class']) ?></td></tr>
        <tr><th class="bg-light">Asset Class Description</th><td><?= esc($asset['asset_class_desc']) ?></td></tr>
        <tr><th class="bg-light">Category</th><td><?= esc($asset['category']) ?></td></tr>
        <tr><th class="bg-light">Asset Description</th><td><?= esc($asset['asset_description']) ?></td></tr>
        <tr><th class="bg-light">Serial Number</th><td><?= esc($asset['sn']) ?></td></tr>
        <tr><th class="bg-light">UOM</th><td><?= esc($asset['uom']) ?></td></tr>
        <tr><th class="bg-light">PO</th><td><?= esc($asset['po']) ?></td></tr>
        <tr><th class="bg-light">Quantity</th><td><?= esc($asset['quantity']) ?></td></tr>
        <tr><th class="bg-light">perpcs_id</th><td><?= esc($asset['perpcs_id']) ?></td></tr>
        <tr><th class="bg-light">Location</th><td><?= esc($asset['location']) ?></td></tr>
        <tr><th class="bg-light">Created</th><td><?= date('Y-m-d H:i', strtotime($asset['created_at'])) ?></td></tr>
        <tr><th class="bg-light">Last Scan</th><td><?= $asset['last_scan'] ? date('Y-m-d H:i', strtotime($asset['last_scan'])) : '—' ?></td></tr>
    </table>
    <a href="<?= base_url('assets/' . $asset['id'] . '/edit') ?>" class="btn btn-primary"><i class="fa fa-edit me-1"></i>Edit</a>
    
    <?php if ( hasPermissions('company_settings') ): ?>
      <a href="<?= base_url('assets/' . $asset['id'] . '/audits') ?>" class="btn btn-default"><i class="fas fa-list"></i>Logs Audit</a>
      <a href="<?= base_url('assets/' . $asset['id'] . '/delete') ?>" class="btn btn-danger"><i class="fa fa-trash me-1"></i>Delete</a>
    <?php endif; ?>
</br>
</br>
</br>
</section>
<!-- /.content -->

<?= $this->endSection() ?>
