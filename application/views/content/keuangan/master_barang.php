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
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Master Barang</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Master Barang</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>

            <!-- /.content-header -->
            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Master Barang</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" id="btn-add-master">
                                    <i class="fas fa-plus"></i> Tambah Barang
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="table-master-barang" class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Nama Suplier</th>
                                        <th>Satuan</th>
                                        <th>Bahan Aktif</th>
                                        <th>P</th>
                                        <th>L</th>
                                        <th>T</th>
                                        <th>Dimensi</th>
                                        <th>Berat</th>
                                        <th>Kubikasi</th>
                                        <th>QR Code</th>
                                        <th>Barcode</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2026 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <!-- Modal for Add/Edit -->
        <div class="modal fade" id="modal-master-barang" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah/Edit Master Barang</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form id="form-master-barang">
                        <div class="modal-body">
                            <input type="hidden" id="id" name="id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_barang">Nama Barang</label>
                                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kd_supplier">Kode Supplier</label>
                                        <input type="text" class="form-control" id="kd_supplier" name="kd_supplier" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="satuan">Satuan</label>
                                        <input type="text" class="form-control" id="satuan" name="satuan" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bahan_aktif">Bahan Aktif</label>
                                        <input type="text" class="form-control" id="bahan_aktif" name="bahan_aktif" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="qrcode">QR Code</label>
                                        <input type="text" class="form-control" id="qrcode" name="qrcode">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="barcode">Barcode</label>
                                        <input type="text" class="form-control" id="barcode" name="barcode">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="p">Panjang (P)</label>
                                        <input type="number" class="form-control" id="p" name="p" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="l">Lebar (L)</label>
                                        <input type="number" class="form-control" id="l" name="l" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="t">Tinggi (T)</label>
                                        <input type="number" class="form-control" id="t" name="t" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="berat">Berat (kg)</label>
                                        <input type="number" class="form-control" id="berat" name="berat" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kubikasi">Kubikasi (m³)</label>
                                        <input type="number" step="0.000001" class="form-control" id="kubikasi" name="kubikasi" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <!-- Bootstrap 4 -->
    <script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <!-- DataTables -->
    <script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo base_url('assets/js/adminlte.min.js') ?>"></script>

    <script>
    $(document).ready(function() {
        const table = $('#table-master-barang').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?php echo base_url('master_barang/list'); ?>',
                type: 'POST'
            },
            columns: [
                { data: null, orderable: false, searchable: false },
                { data: 'nama_barang' },
                { data: 'nama_suplier' },
                { data: 'satuan' },
                { data: 'bahan_aktif' },
                { data: 'p' },
                { data: 'l' },
                { data: 't' },
                { data: 'dimensi' },
                { data: 'berat' },
                { data: 'kubikasi' },
                { data: 'qrcode' },
                { data: 'barcode' },
                { data: 'aksi', orderable: false, searchable: false }
            ],
            order: [[1, 'asc']],
            pageLength: 50,
            language: {
                "emptyTable": "Tidak ada data yang tersedia",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(difilter dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "loadingRecords": "Memuat...",
                "processing": "Memproses...",
                "search": "Cari:",
                "zeroRecords": "Tidak ditemukan data yang sesuai",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });

        table.on('draw.dt', function() {
            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        });

        // Add button
        $('#btn-add-master').on('click', function() {
            $('#form-master-barang')[0].reset();
            $('#id').val('');
            $('#modal-master-barang .modal-title').text('Tambah Master Barang');
            $('#modal-master-barang').modal('show');
        });

        // Edit button
        $(document).on('click', '.btn-edit-master', function() {
            const id = $(this).data('id');
            $.ajax({
                url: '<?php echo base_url('master_barang/detail'); ?>',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#id').val(response.data.id);
                        $('#nama_barang').val(response.data.nama_barang);
                        $('#kd_supplier').val(response.data.kd_supplier);
                        $('#satuan').val(response.data.satuan);
                        $('#bahan_aktif').val(response.data.bahan_aktif);
                        $('#qrcode').val(response.data.qrcode);
                        $('#barcode').val(response.data.barcode);
                        $('#p').val(response.data.p);
                        $('#l').val(response.data.l);
                        $('#t').val(response.data.t);
                        $('#berat').val(response.data.berat);
                        $('#kubikasi').val(response.data.kubikasi);
                        $('#modal-master-barang .modal-title').text('Edit Master Barang');
                        $('#modal-master-barang').modal('show');
                    } else {
                        alert('Gagal memuat data: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat memuat data');
                }
            });
        });

        // Delete button
        $(document).on('click', '.btn-delete-master', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            if (confirm('Apakah Anda yakin ingin menghapus barang "' + nama + '"?')) {
                $.ajax({
                    url: '<?php echo base_url('master_barang/delete'); ?>',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            alert('Barang berhasil dihapus');
                            table.ajax.reload();
                        } else {
                            alert('Gagal menghapus barang: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menghapus barang');
                    }
                });
            }
        });

        // Form submit
        $('#form-master-barang').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const url = $('#id').val() ? '<?php echo base_url('master_barang/update'); ?>' : '<?php echo base_url('master_barang/store'); ?>';
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        alert('Data berhasil disimpan');
                        $('#modal-master-barang').modal('hide');
                        table.ajax.reload();
                    } else {
                        alert('Gagal menyimpan data: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menyimpan data');
                }
            });
        });
    });
    </script>
</body>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->