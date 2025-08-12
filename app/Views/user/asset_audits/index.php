<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Asset #<?= esc($audits[0]['asset_id']) ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?php echo url('/') ?>"><?php echo lang('App.home') ?></a></li>
          <li class="breadcrumb-item active">Asset #<?= esc($audits[0]['asset_id']) ?></li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Audit Trail – Asset #<?= esc($audits[0]['asset_id']) ?></h1>
        <a href="<?= base_url('') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Asset
        </a>
    </div>
    <table id="auditTable" class="table table-bordered mt-3 w-auto">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Changed&nbsp;At</th>
                <th>Changes</th>
            </tr>
        </thead>
    </table>
</div>

</section>

<?= $this->endSection() ?>
<?= $this->section('js') ?>
<!-- CDN CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css">

<!-- CDN JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

<script>
$(function () {
    // DataTable with live AJAX source
    const table = $('#auditTable').DataTable({
        ajax: {
            url: '<?= base_url('assets/'.$audits[0]['asset_id'].'/audits/data') ?>',
            dataSrc: ''
        },
        columns: [
            { data: 'id', width:'6%' },
            { data: row => row.user_id ?? 'System' },
            { data: 'changed_at' },
            {
                data: 'changes', width:'100%',
                render: function (data, type, row) {
                    try {
                        const changes = JSON.parse(data);
                        let html = '';
                        for (const [key, value] of Object.entries(changes)) {
                            const oldVal = value.old ?? '';
                            const newVal = value.new ?? '';
                            const changed = oldVal !== newVal;
                            html += `
                                <div class="mb-1">
                                    <span class="badge bg-secondary">${key} : </span>
                                    ${changed
                                        ? `<span class="badge bg-danger">${oldVal || 'NULL'}</span> → <span class="badge bg-success">${newVal || 'NULL'}</span>`
                                        : `<span class="badge bg-info">${newVal || 'NULL'}</span>`
                                    }
                                </div>
                            `;
                        }
                        return html;
                    } catch (e) {
                        return '<span class="text-danger">Invalid JSON</span>';
                    }
                }
            }
        ],
        order: [[2,'desc']],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons:[ 'copy', 'csv', 'excel', 'pdf', 'print' ]
    });

    // Auto-refresh every 5 s when tab is visible
    setInterval(() => {
        if (!document.hidden) table.ajax.reload(null, false);
    }, 5000);

});
</script>
<?= $this->endSection() ?>
