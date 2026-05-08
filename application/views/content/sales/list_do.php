<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h3>List Delivery Order — Sales</h3>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
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
                                            <a href="<?= base_url('sales/detail_do/') . $i->kddo ?>"
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
    $('#tbListDoSales').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true
    });
});
</script>