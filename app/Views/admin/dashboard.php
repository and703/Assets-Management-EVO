<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
  <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?php echo lang('App.assets_list');?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#"><?php echo lang('App.home');?></a></li>
              <li class="breadcrumb-item active"><?php echo lang('App.assets_list');?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
           
          <section class="col-lg-12 connectedSortable">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?= base_url('assets/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> New Asset
                </a>
            </div>

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
            <table id="assetsTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Assets Number</th>
                        <th>Joint Assets Number</th>
                        <th>Capitalized On</th>
                        <th>Asset Class</th>
                        <th>Asset Class Description</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Barcode Karyawan</th>
                        <th>Serial Number</th>
                        <th>Last Scan</th>
                        <?php if ( hasPermissions('assets_edit') ): ?>
                            <th class="text-center">Action</th>
                        <?php endif ?>
                    </tr>
                </thead>
            </table>
          </section>
          <!-- /.Left col -->
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->


<?= $this->endSection() ?>
<?= $this->section('js') ?>

<!-- DataTables -->
    <script src="<?php echo assets_url('admin') ?>/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/js/jszip.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/js/pdfmake.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/js/vfs_fonts.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/extensions/buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/extensions/buttons/js/buttons.flash.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/extensions/buttons/js/jszip.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/extensions/buttons/js/vfs_fonts.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/extensions/buttons/js/buttons.colVis.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/data-table/js/dataTables.bootstrap4.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo assets_url('admin') ?>/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script>
$(function () {
    const table = $('#assetsTable').DataTable({
        ajax: {
            url: '<?= base_url('assets/data') ?>',
            dataSrc: ''
        },
        deferRender: true,
        scrollX: true,
        dom: 'Bfrtpi',
        buttons: [
            { extend: 'copy',   text: '<i class="far fa-copy"></i> Copy' },
            { extend: 'csv',    text: '<i class="fas fa-file-csv"></i> CSV' },
            { extend: 'excel',  text: '<i class="far fa-file-excel"></i> Excel' },
            { extend: 'pdf',    text: '<i class="far fa-file-pdf"></i> PDF' },
            { extend: 'print',  text: '<i class="fas fa-print"></i> Print' }
        ],
        columns: [
            { data: 'id' },
            { data: 'asset' },
            { data: 'joint_assets_number' },
            { data: 'capitalized_on' },
            { data: 'asset_class' },
            { data: 'asset_class_desc' },
            { data: 'asset_description' },
            { data: 'location' },
            { data: 'bar_kar' },
            { data: 'sn' },
            {
                data: 'last_scan',
                render: function (data, type, row) {
                    if (type !== 'display') return data;

                    // no scan yet → red button
                    if (!data) {
                        return '<button class="btn btn-sm btn-danger">Never</button>';
                    }

                    const scanDate = new Date(data.replace(' ', 'T'));
                    const now      = new Date();
                    const diffYears = (now - scanDate) / (1000 * 60 * 60 * 24 * 365);

                    if (diffYears <= 1) {
                        // ≤ 1 year old → green
                        return '<button class="btn btn-sm btn-success">' + data + '</button>';
                    }

                    // > 1 year → purple (Bootstrap doesn\'t have purple by default)
                    return '<button class="btn btn-sm" style="background-color:#6f42c1;color:#fff;">' + data + '</button>';
                }
            },
            
            <?php if ( hasPermissions('assets_edit') ): ?>
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        const id = row.id;
                        return `
                            <a href="<?= base_url('assets') ?>/${id}" class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i></a>
                            <a href="<?= base_url('assets') ?>/${id}/edit" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                            <?php if ( hasPermissions('company_settings') ): ?>
                            <a href="<?= base_url('assets') ?>/${id}/audits" class="btn btn-sm btn-default me-1"><i class="fas fa-list"></i></a>
                            <a href="<?= base_url('assets') ?>/${id}/delete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                            `;
                    }
                }
            <?php endif ?>
        ],
        order: [[2, 'desc']], // Sort by capitalized_on column DESC
    });

    // auto‑refresh every 5 s when tab is visible
    setInterval(() => {
        if (!document.hidden) table.ajax.reload(null, false);
    }, 2000);
});
</script>
<?=  $this->endSection() ?>