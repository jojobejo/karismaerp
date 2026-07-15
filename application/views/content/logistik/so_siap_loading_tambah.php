<!-- views/content/logistik/so_siap_loading_tambah.php -->
<style>
    #tabelTambahSoSiapLoading_wrapper {
        padding: 12px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0 text-dark" style="font-size:1.3rem;">
                            <i class="fas fa-plus-circle mr-2"></i>Tambah SO Siap Loading
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('logistik') ?>">Logistik</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('logistik/so_siap_loading') ?>">SO Siap Loading</a></li>
                            <li class="breadcrumb-item active">Tambah SO</li>
                        </ol>
                    </div>
                </div>

                <a href="<?= base_url('logistik/so_siap_loading') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if ($this->session->flashdata('msg')): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <?= $this->session->flashdata('msg') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>Sales Order Open / Partial
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($so_list) ?> SO</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-striped table-sm" id="tabelTambahSoSiapLoading">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width:40px">No</th>
                                    <th>No SO</th>
                                    <th>Tanggal SO</th>
                                    <th>Customer</th>
                                    <th>Rute</th>
                                    <th>Regional</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Item</th>
                                    <th class="text-right">Qty Order</th>
                                    <th class="text-right">Sudah Faktur</th>
                                    <th class="text-right">Outstanding</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($so_list)): ?>
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">
                                            Tidak ada SO Open/Partial yang masih memiliki outstanding.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($so_list as $idx => $so): ?>
                                        <tr>
                                            <td class="text-center rownum"><?= $idx + 1 ?></td>
                                            <td class="font-weight-bold">
                                                <a href="<?= base_url('sales_order/detail/' . $so->id_so) ?>">
                                                    <?= htmlspecialchars($so->no_so) ?>
                                                </a>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= !empty($so->tanggal_transaksi) ? date('d/m/Y', strtotime($so->tanggal_transaksi)) : '-' ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($so->customer_name ?: ($so->nama_customer ?? '-')) ?>
                                                <?php if (!empty($so->nama_kios)): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($so->nama_kios) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold"><?= htmlspecialchars($so->kd_rute) ?></span>
                                                <br><small class="text-muted"><?= htmlspecialchars($so->nama_rute) ?></small>
                                            </td>
                                            <td><?= !empty($so->regional) ? htmlspecialchars($so->regional) : '<span class="text-muted">-</span>' ?></td>
                                            <td class="text-center">
                                                <?php if (($so->status ?? '') === 'partial'): ?>
                                                    <span class="badge badge-warning px-2 py-1">Partial</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info px-2 py-1">Open</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= number_format((int)$so->jumlah_item) ?></td>
                                            <td class="text-right"><?= number_format((float)$so->total_qty_order, 2) ?></td>
                                            <td class="text-right"><?= number_format((float)$so->total_qty_faktur, 2) ?></td>
                                            <td class="text-right font-weight-bold text-warning">
                                                <?= number_format((float)$so->total_qty_outstanding, 2) ?>
                                            </td>
                                            <td class="text-center">
                                                <form method="post"
                                                      action="<?= base_url('logistik/so_siap_loading/tambah/' . $so->id_so) ?>"
                                                      onsubmit="return confirm('Tambahkan SO <?= htmlspecialchars($so->no_so, ENT_QUOTES, 'UTF-8') ?> ke Siap Loading untuk diverifikasi?');">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Tambah ke SO Siap Loading">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function () {
    var table = $('#tabelTambahSoSiapLoading').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: [0, 11] },
            { className: 'text-center', targets: [0, 6, 7, 11] }
        ],
        order: [[2, 'desc']],
        drawCallback: function () {
            var api = this.api();
            api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }
    });
});
</script>
