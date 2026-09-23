<!-- application/views/content/sales/customer_acak_list.php -->
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark">
                        <i class="fas fa-users text-primary mr-2"></i> Master Customer Acak (Pecah Faktur)
                    </h1>
                    <small class="text-muted">Master kontak person penerima turunan per kios untuk keperluan pemecahan Faktur Z</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('sales_order/pecah_faktur') ?>" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Pecah Faktur
                    </a>
                    <a href="<?= base_url('sales_order/sync_customer_acak') ?>" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyinkronkan data kontak person dari file cust acak.xlsx?');">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Excel
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Card Filter & Pencarian -->
            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-body p-3">
                    <form method="get" action="<?= base_url('sales_order/customer_acak') ?>" class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Filter Kios / Toko Induk</label>
                            <select name="nama_toko" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                <option value="">-- Semua Kios / Toko (<?= count($unique_tokos) ?> Toko) --</option>
                                <?php foreach ($unique_tokos as $ut): ?>
                                    <option value="<?= htmlspecialchars($ut['nama_toko']) ?>" <?= $selected_toko === $ut['nama_toko'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ut['nama_toko']) ?> (<?= (int)$ut['total_kontak'] ?> kontak)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Pencarian Kontak / Kode / Kota</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" class="form-control" placeholder="Cari kode (misal KARU01AC), kontak, kota..." value="<?= htmlspecialchars($search) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <?php if (!empty($search) || !empty($selected_toko)): ?>
                                        <a href="<?= base_url('sales_order/customer_acak') ?>" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-right mt-2 mt-md-4">
                            <span class="badge badge-info p-2 font-weight-bold" style="font-size: 13px;">
                                Total: <?= number_format(count($customers_acak)) ?> Kontak Person
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data Customer Acak -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped mb-0 text-nowrap" id="tableCustomerAcak">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th width="120">Kode Acak</th>
                                    <th width="200">Kios / Toko Induk</th>
                                    <th>Kontak Person (Penerima)</th>
                                    <th>Alamat Lengkap</th>
                                    <th width="120">Kota</th>
                                    <th width="140">NIK</th>
                                    <th width="130">NPWP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers_acak)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle mr-1"></i> Tidak ada data kontak person customer acak ditemukan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($customers_acak as $ca): ?>
                                        <tr>
                                            <td class="text-center align-middle text-muted"><?= $no++ ?></td>
                                            <td class="align-middle">
                                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-family: monospace; font-size: 12px;">
                                                    <?= htmlspecialchars($ca['kd_customer']) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <strong class="text-dark"><?= htmlspecialchars($ca['nama_toko']) ?></strong>
                                                <?php if (!empty($ca['kd_customer_induk'])): ?>
                                                    <br><small class="text-primary font-weight-bold"><i class="fas fa-link mr-1"></i>Induk: <?= htmlspecialchars($ca['kd_customer_induk']) ?></small>
                                                <?php else: ?>
                                                    <br><small class="text-muted"><i class="fas fa-unlink mr-1"></i>Non-terpetakan</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle font-weight-bold text-dark">
                                                <?= htmlspecialchars($ca['kontak_person']) ?>
                                            </td>
                                            <td class="align-middle" style="white-space: normal; max-width: 250px;">
                                                <small class="text-muted"><?= htmlspecialchars($ca['alamat'] ?: '-') ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-light border"><?= htmlspecialchars($ca['kota'] ?: '-') ?></span>
                                            </td>
                                            <td class="align-middle">
                                                <small class="text-secondary"><?= htmlspecialchars($ca['nik'] ?: '-') ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <small class="text-secondary"><?= htmlspecialchars($ca['npwp'] ?: '-') ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div> <!-- /.wrapper -->

<script>
$(document).ready(function() {
    if ($('.select2').length && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#tableCustomerAcak')) {
        $('#tableCustomerAcak').DataTable({
            "pageLength": 25,
            "language": {
                "search": "Cari di tabel:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Tidak ada data yang cocok",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Sebelum"
                }
            },
            "order": [[0, "asc"]]
        });
    }
});
</script>
