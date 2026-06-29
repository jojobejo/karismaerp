<?php
$formatHistoryDate = static function ($value) {
    $value = trim((string)$value);
    if ($value === '' || $value === '0000-00-00' || strpos($value, '0000-00-00') === 0) {
        return '-';
    }

    $timestamp = strtotime($value);
    return $timestamp === false ? '-' : date('d/m/Y', $timestamp);
};
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper so-history-page">
        <section class="content">
            <div class="container-fluid py-3 pb-4">
                <style>
                    .so-history-page{background:#eef3f8}.so-history-shell{max-width:520px;margin:0 auto}.so-panel{background:#fff;border:1px solid #dce5ee;border-radius:8px;box-shadow:0 8px 22px rgba(15,23,42,.07)}
                    .so-panel-header{padding:14px 16px;border-bottom:1px solid #e6edf4;display:flex;align-items:center;justify-content:space-between;gap:10px}.so-panel-title{font-size:16px;font-weight:800;color:#172033;margin:0}.so-muted{font-size:12px;color:#64748b}
                    .so-history-list{display:grid;gap:10px}.so-history-item{border:1px solid #dbe4ef;border-radius:8px;background:#f8fafc;padding:12px}.so-history-name{font-size:14px;font-weight:800;color:#111827;line-height:1.35}.so-history-meta{font-size:12px;color:#64748b;margin-top:4px}.so-history-qty{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:10px;width:100%}.so-qty-box{background:#fff;border:1px solid #e1e8f0;border-radius:8px;padding:8px;text-align:center;min-width:0}.so-qty-label{font-size:11px;color:#64748b;font-weight:800;text-transform:uppercase}.so-qty-value{font-size:17px;font-weight:900;color:#172033}.so-history-actions{display:flex;justify-content:flex-end;margin-top:10px}.so-delete-btn{font-weight:800}.so-icon-btn{height:44px;font-weight:800}.btn i{margin-right:6px}
                </style>

                <div class="so-history-shell">
                    <a href="<?= base_url('stockopname/input') ?>" class="btn btn-outline-secondary btn-block so-icon-btn mb-3">
                        <i class="fas fa-arrow-left"></i>Kembali Input
                    </a>

                    <div class="so-panel">
                        <div class="so-panel-header">
                            <h2 class="so-panel-title">Histori Input</h2>
                            <span class="so-muted"><?= count($histori ?? []) ?> data</span>
                        </div>
                        <div class="p-3">
                            <?php if (empty($histori)) : ?>
                                <div class="text-center text-muted py-4">
                                    Belum ada histori input untuk <?= htmlspecialchars($input_by ?: '-', ENT_QUOTES, 'UTF-8') ?>.
                                </div>
                            <?php else : ?>
                                <div class="so-history-list">
                                    <?php foreach ($histori as $row) : ?>
                                        <div class="so-history-item">
                                            <div class="so-history-name"><?= htmlspecialchars($row['nama_barang'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="so-history-meta">
                                                Exp: <?= $formatHistoryDate($row['expired_date'] ?? '') ?>
                                                | Input: <?= $formatHistoryDate($row['create_at'] ?? '') ?>
                                            </div>
                                            <div class="so-history-qty">
                                                <div class="so-qty-box">
                                                    <div class="so-qty-label">Box</div>
                                                    <div class="so-qty-value"><?= number_format((int)($row['qty_box'] ?? 0), 0, ',', '.') ?></div>
                                                </div>
                                                <div class="so-qty-box">
                                                    <div class="so-qty-label">Pcs</div>
                                                    <div class="so-qty-value"><?= number_format((int)($row['qty_pcs'] ?? 0), 0, ',', '.') ?></div>
                                                </div>
                                            </div>
                                            <div class="so-history-actions">
                                                <button type="button" class="btn btn-outline-danger btn-sm so-delete-btn" data-id="<?= (int)($row['id'] ?? 0) ?>">
                                                    <i class="fas fa-trash"></i>Hapus
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<script>
$(function () {
    $('.so-delete-btn').on('click', function () {
        var button = $(this);
        var id = parseInt(button.data('id'), 10);
        if (!id) return;

        var remove = function () {
            button.prop('disabled', true);
            $.ajax({
                url: '<?= base_url('stockopname/history-input/delete') ?>',
                type: 'POST',
                dataType: 'json',
                data: {id: id}
            }).done(function (response) {
                if (response && response.status) {
                    window.location.reload();
                    return;
                }
                button.prop('disabled', false);
                if (window.Swal) Swal.fire('Gagal', (response && response.message) || 'Gagal menghapus data opname.', 'error');
                else alert((response && response.message) || 'Gagal menghapus data opname.');
            }).fail(function () {
                button.prop('disabled', false);
                if (window.Swal) Swal.fire('Gagal', 'Server tidak merespons.', 'error');
                else alert('Server tidak merespons.');
            });
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Hapus data opname?',
                text: 'Data yang dihapus tidak dapat dikembalikan dari histori ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) remove();
            });
        } else if (window.confirm('Hapus data opname ini?')) {
            remove();
        }
    });
});
</script>
