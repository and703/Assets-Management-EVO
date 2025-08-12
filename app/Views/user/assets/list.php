<?php
/*****************************************************************************************
 *  assets/index.php – live DataTable with Export buttons + conditional last_scan badge  *
 *  Requirements (CDN): Bootstrap 5, jQuery 3.7, DataTables 1.13, Buttons, JSZip, pdfmake *
 *  Route dependencies:                                                                   *
 *      GET /assets/data   → JSON feed from AssetController::data()                       *
 *      GET /assets/create → new asset form                                              *
 *****************************************************************************************/
?>
<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Assets</h1>
        <a href="<?= base_url('assets/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Asset
        </a>
    </div>

    <table id="assetsTable" class="table table-striped table-bordered nowrap" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Assets Number</th>
                <th>Capitalized On</th>
                <th>Asset Class</th>
                <th>Asset Class Description</th>
                <th>Description</th>
                <th>Location</th>
                <th>Barcode Karyawan</th>
                <th>Serial Number</th>
                <th>Last Scan</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
    </table>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    const table = $('#assetsTable').DataTable({
        ajax: {
            url: '<?= base_url('assets/data') ?>',
            dataSrc: ''
        },
        deferRender: true,
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copy',   text: '<i class="far fa-copy"></i> Copy' },
            { extend: 'csv',    text: '<i class="fas fa-file-csv"></i> CSV' },
            { extend: 'excel',  text: '<i class="far fa-file-excel"></i> Excel' },
            { extend: 'pdf',    text: '<i class="far fa-file-pdf"></i> PDF' },
            { extend: 'print',  text: '<i class="fas fa-print"></i> Print' },
            { extend: 'colvis', text: '<i class="fas fa-columns"></i> Columns' }
        ],
        columns: [
            { data: 'id' },
            { data: 'asset' },
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
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    const id = row.id;
                    return `
                        <a href="<?= base_url('assets') ?>/${id}" class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i></a>
                        <a href="<?= base_url('assets') ?>/${id}/edit" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                        <a href="<?= base_url('assets') ?>/${id}/audits" class="btn btn-sm btn-info me-1"><i class="fas fa-list-check"></i></a>
                        `;
                }
            }
        ]
    });

    // auto‑refresh every 5 s when tab is visible
    setInterval(() => {
        if (!document.hidden) table.ajax.reload(null, false);
    }, 2000);
});
</script>
<?= $this->endSection() ?>
