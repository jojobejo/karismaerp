<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <?php foreach ($get_barang as $nm) : ?>
                                <h5 class="card-title">Detail Inputer Barang - <b><?= $nm->nm_barang ?></b></h5>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="<?= base_url('') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
                                </div>
                                <!-- SELECT OPTION EXPIRED DATE -->
                                <div class="col-auto">
                                    <select name="exp_date_kd" id="exp_date_kd" class="form-control">
                                        <?php foreach ($exp_date as $e) : ?>
                                            <option value="<?= $e->exp_date ?>"><?= $e->exp_date ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-md btn-secondary">
                                        <i class="fas fa-date"></i> FEFO
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-md btn-primary" id="btndo">
                                        <i class="fas fa-search-minus"></i> DO
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-md btn-primary" id="btnpo">
                                        <i class="fas fa-search-plus"></i> PO
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-md btn-success" data-toggle="modal" data-target="#modalAddOpname">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>

                            <div class="col-auto">
                                <div class="form-group" style="position: relative;background: #fff;">
                                    <h5 class="card-title mt-2 mb-2"><b>Compare By Expired Date</b> - </h5>
                                    <table id="tb_exp_form" class="table table-bordered table-striped" style="border: 1px solid #000000; border-collapse: collapse; width: 100%; text-align: center;">
                                        <thead>
                                            <tr>
                                                <th style="border: 1px solid #000000;">Expired</th>
                                                <th style="border: 1px solid #000000;">Qty</th>
                                                <th style="border: 1px solid #000000;">DO</th>
                                                <th style="border: 1px solid #000000;">PO</th>
                                                <th style="border: 1px solid #000000;">Qty All</th>
                                                <th style="border: 1px solid #000000;">ICS</th>
                                                <th style="border: 1px solid #000000;">Selisih</th>
                                                <th style="border: 1px solid #000000;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_exp_data">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-auto">

                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3" id="card_do" style="display: none;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title">Data DO</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm" id="ics_do_byexp">
                                <thead class="bg-warning text-white text-center">
                                    <tr>
                                        <th>Kode Faktur</th>
                                        <th>Tgl Transaksi</th>
                                        <th>Nama Barang</th>
                                        <th>Expired Date</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </section>
            </div>
        </div>

        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <!-- Modal Add Opname -->
        <div class="modal fade" id="modalAddOpname" tabindex="-1" role="dialog" aria-labelledby="modalAddOpnameLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="<?= base_url('ics/sv_opname') ?>" method="post">
                    <div class="modal-content">
                        <div class="modal-header bg-success">
                            <h5 class="modal-title" id="modalAddOpnameLabel"><i class="fas fa-box"></i> Input Data Opname</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php foreach ($detail_stok as $dstock) : ?>
                            <div class="modal-body">
                                <!-- Nama Barang -->
                                <div class="form-group">
                                    <label for="nama_barang">Nama Barang</label>
                                    <input type="text" name="nama_barang" class="form-control" value="<?= $dstock->nama_barang ?>" readonly required>
                                    <input type="text" name="dimensi" class="form-control" value="<?= $dstock->dimensi ?>" hidden readonly>
                                    <input type="text" name="id" class="form-control" value="<?= $dstock->id ?>" readonly hidden>
                                    <input type="hidden" name="action" id="action_id" value="formdetail">
                                </div>
                                <!-- Expired Date -->
                                <div class="form-group">
                                    <label for="exp_date">Expired Date</label>
                                    <input type="text" name="exp_date" class="form-control" value="<?= $dstock->exp_date ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="exp_date">Keterangan</label>
                                    <textarea class="form-control" name="keterangan_isi" id="modal_keterangan" required placeholder="Tambahkan keterangan inputer"></textarea>
                                </div>
                                <!-- Qty Box -->
                                <div class="form-group">
                                    <label for="qty_box">Qty Box</label>
                                    <input type="number" name="qty_box" class="form-control" placeholder="0">
                                </div>
                                <!-- Qty Pcs -->
                                <div class="form-group">
                                    <label for="qty_pcs">Qty Pcs</label>
                                    <input type="number" name="qty_pcs" class="form-control" placeholder="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>


        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
    </div>

    <script>
        $(document).ready(function() {
            $('#btndo').click(function() {
                $('#card_do').slideToggle();
                $('#card_po').slideUp();
            });
            $('#btnpo').click(function() {
                $('#card_po').slideToggle();
                $('#card_do').slideUp();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#exp_date_kd').change(function() {
                var selectedDate = $(this).val();
                var nama_barang = "<?= $get_barang[0]->nm_barang ?>"; // asumsikan hanya satu barang ditampilkan

                $.ajax({
                    url: "<?= base_url('ics/get_detail_by_exp_date') ?>",
                    type: "POST",
                    data: {
                        exp_date: selectedDate,
                        nama_barang: nama_barang
                    },
                    dataType: "json",
                    success: function(response) {
                        let tbody = '';
                        if (response.length > 0) {
                            response.forEach(function(row) {
                                let status = row.status == 1 ?
                                    '<span class="badge badge-success">KLOP</span>' :
                                    '<span class="badge badge-danger">SELISIH</span>';
                                tbody += `
                        <tr>
                            <td>${row.expired}</td>
                            <td>${row.qty}</td>
                            <td>${row.do}</td>
                            <td>${row.po}</td>
                            <td>${row.qty_all}</td>
                            <td>${row.ics}</td>
                            <td>${row.selisih}</td>
                            <td>${status}</td>
                        </tr>
                    `;
                            });
                        } else {
                            tbody = '<tr><td colspan="8">Tidak ada data</td></tr>';
                        }
                        $('#tbody_exp_data').html(tbody);
                    },
                    error: function() {
                        alert('Gagal mengambil data.');
                    }
                });
            });

            // Trigger change saat pertama kali halaman dimuat
            $('#exp_date_kd').trigger('change');
        });
    </script>