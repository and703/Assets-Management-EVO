<?= $this->extend('admin/layout/default') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>API Log #<?= esc($log['id']) ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?php echo url('/') ?>"><?php echo lang('App.home') ?></a></li>
          <li class="breadcrumb-item active">API Log #<?= esc($log['id']) ?></li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">

    <div class="card mb-4">
        <div class="card-header fw-bold">Request Snapshot</div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-sm-3 text-muted">Timestamp</div>
                <div class="col-sm-9"><?= esc($log['timestamp']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3 text-muted">Method</div>
                <div class="col-sm-9"><?= esc($log['method']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3 text-muted">URI</div>
                <div class="col-sm-9"><?= esc($log['uri']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3 text-muted">IP Address</div>
                <div class="col-sm-9"><?= esc($log['ip_address']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3 text-muted">User Agent</div>
                <div class="col-sm-9"><?= esc($log['user_agent']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3 text-muted">Headers</div>
                <div class="col-sm-9"><pre class="mb-0 small bg-light p-2 border rounded"><?= esc($log['headers']) ?></pre></div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-3 text-muted">Body (JSON)</div>
                <div class="col-sm-9"><pre class="mb-0 small bg-light p-2 border rounded"><?= esc($log['body']) ?></pre></div>
            </div>
        </div>
    </div>

    <h2 class="h4 mb-3">Tag Reads (<?= count($tags) ?>)</h2>
    <table id="tagsTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>TagID</th>
                <th>RSSI</th>
                <th>PC</th>
                <th>MemoryBank</th>
                <th>MemoryBankData</th>
                <th>Count</th>
                <th>Phase</th>
                <th>ChannelIdx</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $i => $t): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><code><?= esc($t['tagID']) ?></code></td>
                    <td><?= esc($t['RSSI']) ?></td>
                    <td><?= esc($t['PC']) ?></td>
                    <td><?= esc($t['memoryBank']) ?></td>
                    <td><small><?= esc($t['memoryBankData']) ?></small></td>
                    <td><?= esc($t['count']) ?></td>
                    <td><?= esc($t['phase']) ?></td>
                    <td><?= esc($t['channelIndex']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <a href="<?= site_url('logs') ?>" class="btn btn-secondary mt-3">
        <i class="fa fa-arrow-left"></i> Back to Logs
    </a>
</section>
<!-- /.content -->

<?= $this->endSection() ?>

<?php /**
 * ────────────────────────────────────────────────────────────────────────────
 * DataTables init (pushed to a section so layout can place scripts at bottom)
 * ────────────────────────────────────────────────────────────────────────────
 */
$this->section('js'); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        new DataTable('#tagsTable', {
            searching: false,
            ordering: false,
            paging: false,
            lengthChange: false,
            info: false,
        });
    });
</script>
<?php $this->endSection(); ?>
