<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <section class="content">

                <!-- Notifikasi Flash -->
                <?php if ($this->session->flashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-1"></i> <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <!-- Tombol Navigasi -->
                <div class="row">
                    <div class="col-auto">
                        <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3">
                            <i class="fas fa-minus-circle"></i> Data DO
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-secondary w-100 mb-3">
                            <i class="fas fa-plus-circle"></i> Data LPB
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="container-fluid">

                            <!-- Tombol Import & Download -->
                            <div class="row mb-2">
                                <div class="col-2">
                                    <button class="btn btn-success mb-3 btn-block" data-toggle="modal" data-target="#modalImportCSV">
                                        <i class="fas fa-file-csv"></i> Import CSV
                                    </button>
                                </div>
                                <div class="col-2">
                                    <a class="btn btn-secondary mb-3 btn-block" href="<?= base_url('data_lpb_zahir') ?>">
                                        <i class="fas fa-file-csv"></i> Data LPB
                                    </a>
                                </div>
                            </div>

                            <!-- Form Filter Tanggal -->
                            <form action="<?= base_url('get_lpb') ?>" method="post">
                                <div class="row mb-3">
                                    <div class="col-2">
                                        <input type="date" class="form-control" name="date1" id="name1">
                                    </div>
                                    <div class="col-2">
                                        <input type="date" class="form-control" name="date2" id="name2">
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-success btn-block">
                                            <i class="fas fa-search"></i> Tampil
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Tabel Data PO -->
                            <table class="table table-striped table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No PO</th>
                                        <th>Tanggal</th>
                                        <th>Nama Barang</th>
                                        <th>Kode Barang</th>
                                        <th class="text-right">Qty Order</th>
                                        <th class="text-right">Qty Masuk</th>
                                        <th class="text-center" style="width:80px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lpb)) : ?>
                                        <?php foreach ($lpb as $row) : ?>
                                            <?php
                                                $qty_order  = (int)($row['qty']       ?? 0);
                                                $qty_masuk  = (int)($row['qty_masuk'] ?? 0);
                                                $sisa       = $qty_order - $qty_masuk;
                                                // Warna baris: hijau jika lunas, kuning jika sebagian, default jika belum
                                                $row_class  = '';
                                                if ($sisa <= 0)          $row_class = 'table-success';
                                                elseif ($qty_masuk > 0)  $row_class = 'table-warning';
                                            ?>
                                            <tr class="<?= $row_class ?>">
                                                <td><?= htmlspecialchars($row['no_po']      ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['tgl_transaksi'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['nama_barang'] ?? '<i class="text-muted">-</i>') ?></td>
                                                <td><?= htmlspecialchars($row['kd_barang']      ?? '') ?></td>
                                                <td class="text-right"><?= number_format($qty_order) ?></td>
                                                <td class="text-right"><?= number_format($qty_masuk) ?></td>
                                                <td class="text-center">
                                                    <button
                                                        class="btn btn-sm btn-success btn-input-qty"
                                                        title="Input Penerimaan"
                                                        data-no-po="<?= htmlspecialchars($row['no_po']       ?? '') ?>"
                                                        data-kd-barang="<?= htmlspecialchars($row['kd_barang'] ?? '') ?>"
                                                        data-nama-barang="<?= htmlspecialchars($row['nama_barang'] ?? '') ?>"
                                                        data-qty-order="<?= number_format($qty_order) ?>"
                                                        data-toggle="modal"
                                                        data-target="#modalInputQty">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-danger">
                                                <i class="fas fa-inbox mr-1"></i> Tidak ada data pada periode ini
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        </div><!-- /.container-fluid -->
                    </div><!-- /.card-body -->
                </div><!-- /.card -->

            </section>
        </div>
    </div><!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>

    <aside class="control-sidebar control-sidebar-dark"></aside>
</div><!-- ./wrapper -->


<!-- ================================================================
     MODAL INPUT PENERIMAAN BARANG
================================================================ -->
<div class="modal fade" id="modalInputQty" tabindex="-1" role="dialog" aria-labelledby="modalInputQtyLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalInputQtyLabel">
                    <i class="fas fa-plus-circle mr-2"></i> Input Penerimaan Barang
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Form POST ke controller save_qty_diterima -->
                <form id="formInputQty" action="<?= base_url('ics/save_qty_diterima') ?>" method="post">

                    <!-- ---- Informasi Read Only ---- -->
                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label font-weight-bold">No PO</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext font-weight-bold text-primary"
                                   id="modal_no_po" name="no_po" readonly>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label font-weight-bold">Kode Barang</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext"
                                   id="modal_kd_barang" name="kd_barang" readonly>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label font-weight-bold">Nama Barang</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext text-muted"
                                   id="modal_nama_barang" readonly>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label class="col-sm-4 col-form-label font-weight-bold">Qty Order</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext"
                                   id="modal_qty_order" readonly>
                        </div>
                    </div>

                    <hr class="my-2">

                    <!-- ---- Input yang bisa diisi ---- -->
                    <div class="form-group row align-items-center">
                        <label for="input_qty_diterima" class="col-sm-4 col-form-label font-weight-bold">
                            Qty Diterima <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control border-success"
                                   id="input_qty_diterima" name="qty_masuk"
                                   min="1" placeholder="Masukkan qty diterima" required>
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label for="input_no_lot" class="col-sm-4 col-form-label font-weight-bold">
                            No Lot
                        </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control"
                                   id="input_no_lot" name="no_lot"
                                   placeholder="Masukkan no lot / batch">
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label for="input_exp_date" class="col-sm-4 col-form-label font-weight-bold">
                            Exp Date
                        </label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control"
                                   id="input_exp_date" name="exp_date">
                        </div>
                    </div>

                </form>
            </div><!-- /.modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="submit" form="formInputQty" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- ================================================================ -->


<script>
$(document).ready(function () {

    // Saat tombol plus diklik, isi data ke dalam modal
    $('.btn-input-qty').on('click', function () {
        $('#modal_no_po').val($(this).data('no-po'));
        $('#modal_kd_barang').val($(this).data('kd-barang'));
        $('#modal_nama_barang').val($(this).data('nama-barang'));
        $('#modal_qty_order').val($(this).data('qty-order'));

        // Reset field input setiap modal dibuka
        $('#input_qty_diterima').val('');
        $('#input_no_lot').val('');
        $('#input_exp_date').val('');
    });

    // Auto-dismiss alert setelah 4 detik
    setTimeout(function () {
        $('.alert').fadeOut('slow');
    }, 4000);

});
</script>

</body>