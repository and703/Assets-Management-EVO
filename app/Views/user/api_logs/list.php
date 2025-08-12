<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content'); ?>


<!-- Content Header (Page header) -->
  <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?php echo lang('App.assets_api_logs');?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#"><?php echo lang('App.home');?></a></li>
              <li class="breadcrumb-item active"><?php echo lang('App.assets_api_logs');?></li>
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

            <table id="logsTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Time</th>
                    <th>IP</th>
                    <th>URI</th>
                    <th>Method</th>
                    <th>User-Agent</th>
                    <th>Tags</th>
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

<?= $this->endSection(); ?>

<?= $this->section('js') ?>
<script>
$(function(){
    const table = $('#logsTable').DataTable({
        processing: false,
        ajax: {
            url: "<?= base_url('logs/data') ?>",
            dataSrc: ''
        },
        responsive: true,
        /* default sort ↓↓↓ */
        order: [[0, 'desc']],          // ID (col-0) descending
        columns: [
            { data: 'id' },
            { data: 'timestamp' },
            { data: 'ip_address' },
            { data: 'uri' },
            { data: 'method' },
            { data: 'user_agent' },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    const base = "<?= base_url('logs') ?>";
                    return `
                        <a href="${base}/${row.id}" class="btn btn-sm btn-outline-secondary">view</a>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy',  className: 'btn btn-secondary' },
            { extend: 'csv',   className: 'btn btn-secondary' },
            { extend: 'excel', className: 'btn btn-success'  },
            { extend: 'pdf',   className: 'btn btn-danger'   },
            { extend: 'print', className: 'btn btn-primary'  },
            'colvis'
        ]
    });

    // Auto‑refresh every 5 seconds without resetting pagination
    setInterval(() => table.ajax.reload(null, false), 2000);
});
</script>
<?= $this->endSection() ?>
