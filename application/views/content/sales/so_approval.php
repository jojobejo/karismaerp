<!-- views/content/sales/so_approval.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-check-circle mr-2"></i> Approval Harga Nego
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active">Approval Nego</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH MESSAGES -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-1"></i>
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <!-- TOMBOL KEMBALI -->
                <div class="mb-3">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <!-- Setelah tombol Kembali -->
                <div class="alert alert-info mb-3">
                    <i class="fas fa-user-check mr-1"></i>
                    Menampilkan SO yang perlu diapprove oleh: <b><?= htmlspecialchars($approver_name ?? '-') ?></b>
                </div>

                <div class="card">
                    <div class="card-header bg-warning py-2">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i>
                            Daftar SO Menunggu Approval Harga Nego
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="tabelApproval">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No SO</th>
                                    <th>Customer</th>
                                    <th>Tanggal SO</th>
                                    <th class="text-right">Tonase (kg)</th>
                                    <th class="text-right">Kubikasi (m³)</th>
                                    <th>Diminta Oleh</th>
                                    <th>Tanggal Request</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-inbox mr-1"></i>
                                            Tidak ada request approval yang pending
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($list as $row): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('sales_order/detail/' . $row['id_so_detail'] ?? $row['id']) ?>">
                                                <?= htmlspecialchars($row['id_so']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                        <td class="text-right"><?= number_format($row['total_tonase'], 3) ?></td>
                                        <td class="text-right"><?= number_format($row['total_kubikasi'], 5) ?></td>
                                        <td><?= htmlspecialchars($row['req_by']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($row['req_at'])) ?></td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-success btn-approval"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-action="approved"
                                                    data-so="<?= htmlspecialchars($row['id_so']) ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger btn-approval"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-action="rejected"
                                                    data-so="<?= htmlspecialchars($row['id_so']) ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
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

<!-- MODAL KONFIRMASI APPROVAL -->
<div class="modal fade" id="modal-approval" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('sales_order/approve') ?>">
                <div class="modal-header py-2" id="modal-approval-header">
                    <h5 class="modal-title" id="modal-approval-title">Konfirmasi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id"     id="inp-approval-id">
                    <input type="hidden" name="status" id="inp-approval-status">

                    <p id="modal-approval-msg" class="mb-3"></p>

                    <div class="form-group mb-0">
                        <label>Catatan <small class="text-muted">(opsional)</small></label>
                        <textarea name="note" class="form-control" rows="3"
                                  placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn" id="btn-confirm-approval">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // DataTable
    $('#tabelApproval').DataTable({
        responsive:  true,
        autoWidth:   false,
        pageLength:  25,
        order:       [[6, 'desc']],
        columnDefs:  [{ orderable: false, targets: -1 }],
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data",
            emptyTable:  "Tidak ada request pending",
            paginate: {
                first: "Pertama", last: "Terakhir",
                next:  "Berikutnya", previous: "Sebelumnya"
            }
        }
    });

    // Tombol approve/reject → buka modal
    $(document).on('click', '.btn-approval', function () {
        const id     = $(this).data('id');
        const action = $(this).data('action');
        const so     = $(this).data('so');
        const isApprove = (action === 'approved');

        $('#inp-approval-id').val(id);
        $('#inp-approval-status').val(action);
        $('#modal-approval-title').text(isApprove ? 'Approve Harga Nego' : 'Reject Harga Nego');
        $('#modal-approval-msg').html(
            isApprove
                ? `Anda akan <b class="text-success">menyetujui</b> harga nego untuk SO <b>${so}</b>.`
                : `Anda akan <b class="text-danger">menolak</b> harga nego untuk SO <b>${so}</b>.`
        );
        $('#modal-approval-header').removeClass('bg-success bg-danger')
            .addClass(isApprove ? 'bg-success' : 'bg-danger')
            .find('.modal-title, button.close').css('color', '#fff');
        $('#btn-confirm-approval')
            .removeClass('btn-success btn-danger')
            .addClass(isApprove ? 'btn-success' : 'btn-danger')
            .find('i').attr('class', isApprove ? 'fas fa-check' : 'fas fa-times');
        $('#btn-confirm-approval').html(
            `<i class="fas fa-${isApprove ? 'check' : 'times'}"></i> ${isApprove ? 'Approve' : 'Reject'}`
        );

        $('#modal-approval').modal('show');
    });
});
</script>
</body>