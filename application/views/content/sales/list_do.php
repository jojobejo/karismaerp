<!-- views/content/sales/list_do.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">List Delivery Order — Sales</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">List DO Sales</li>
                        </ol>
                    </div>
                </div>
            </div>
            <?php
            // Hitung summary dari $listdo
            $total_do        = count($listdo);
            $total_pending   = 0;
            $total_siap      = 0;
            $total_blm_siap  = 0;

            foreach ($listdo as $i) {
                $confirm = $i->sales_confirm_status ?? 'pending';
                if ($confirm === 'siap')             $total_siap++;
                elseif ($confirm === 'belum_siap')   $total_blm_siap++;
                else                                 $total_pending++;
            }
            ?>

            <!-- SUMMARY CARDS -->
            <div class="row mb-3">
                <div class="col-6 col-sm-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-secondary elevation-1">
                            <i class="fas fa-truck"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total DO</span>
                            <span class="info-box-number"><?= $total_do ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning elevation-1">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Menunggu Konfirmasi</span>
                            <span class="info-box-number"><?= $total_pending ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success elevation-1">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Siap Loading</span>
                            <span class="info-box-number"><?= $total_siap ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-danger elevation-1">
                            <i class="fas fa-times-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Belum Siap Loading</span>
                            <span class="info-box-number"><?= $total_blm_siap ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ALERT jika ada yang belum dikonfirmasi -->
            <?php if ($total_pending > 0) : ?>
            <?php endif; ?>

            <?php if ($total_blm_siap > 0) : ?>
            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
                <i class="fas fa-times-circle mr-1"></i>
                Terdapat <strong><?= $total_blm_siap ?> DO</strong> yang ditandai <strong>Belum Siap Loading</strong> dan perlu ditinjau ulang.
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>
        </div>
        
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header py-2">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-list mr-1"></i> Daftar Delivery Order
                                </h3>
                            </div>
                            <div class="col-auto">
                                <select id="filterKonfirmasi" class="form-control form-control-sm" style="min-width:180px;">
                                    <option value="">— Semua Status —</option>
                                    <option value="Menunggu Konfirmasi">Menunggu Konfirmasi</option>
                                    <option value="Siap Loading">Siap Loading</option>
                                    <option value="Belum Siap Loading">Belum Siap Loading</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tbListDoSales" class="table table-bordered table-striped">
                            <thead style="background-color:#212529;color:white;">
                                <tr>
                                    <th>Kode DO</th>
                                    <th>Tgl. Buat</th>
                                    <th>Rute</th>
                                    <th>Total Faktur</th>
                                    <th>Total Barang</th>
                                    <th>Status Konfirmasi</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listdo as $i) :
                                    $confirm = $i->sales_confirm_status ?? 'pending';
                                    if ($confirm === 'pending' || $confirm === null) {
                                        $badge = '<span class="badge badge-warning">Menunggu Konfirmasi</span>';
                                    } elseif ($confirm === 'siap') {
                                        $badge = '<span class="badge badge-success">Siap Loading</span>';
                                    } else {
                                        $badge = '<span class="badge badge-danger">Belum Siap Loading</span>';
                                    }
                                ?>
                                    <tr>
                                        <td><?= $i->kddo ?></td>
                                        <td><?= $i->createat ?></td>
                                        <td><?= $i->rute ?></td>
                                        <td><?= $i->totalfaktur ?></td>
                                        <td><?= $i->totalbarang ?></td>
                                        <td><?= $badge ?></td>
                                        <td>
                                            <a href="<?= base_url('sales_order/detail_do/') . $i->kddo ?>"
                                               class="btn btn-sm btn-info btn-block">
                                                <i class="fas fa-eye"></i>
                                                <?php if ($confirm === 'pending') : ?>
                                                    <span class="badge badge-light ml-1">Konfirmasi</span>
                                                <?php endif; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
    </footer>
</div>

<script>
$(document).ready(function () {
    var table = $('#tbListDoSales').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'desc']],
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_–_END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            paginate: {
                next:     "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        columnDefs: [{ orderable: false, targets: -1 }]
    });

    // Filter by kolom Status Konfirmasi (kolom index 5)
    $('#filterKonfirmasi').on('change', function () {
        table.column(5).search($(this).val()).draw();
    });
});
</script>