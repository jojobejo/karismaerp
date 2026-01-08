<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
    }

    .select2-selection__rendered {
        line-height: 1.5;
    }
</style>



<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">

                    <div class="row">
                        <div class="col-4">

                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Gudang Aktif</label>
                                        <input type="text" id="gudang_aktif" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Nama Barang</label>
                                        <select name="select_barang" id="select_barang" class="form-control"></select>
                                    </div>

                                    <div class="form-group">
                                        <label>Expired Date</label>
                                        <select name="select_exp" id="select_exp" class="form-control"></select>
                                    </div>
                                    <div class="form-group">
                                        <label>Qty Gudang</label>
                                        <input type="number" name="qtygudang" id="qtygudang" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Qty Input</label>
                                        <input type="number" name="qtyinput" id="qtyinput" class="form-control" value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="id_bar">No Ref : </label>
                                            <input class="form-control col-4 ml-4" type="text" id="driver_i" name="driver_i" value="" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="id_bar">Tanggal : </label>
                                            <input class="form-control col-4 ml-4" type="date" id="driver_i" name="driver_i" value="" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label for="id_bar">Keterangan : </label>
                                            <input class="form-control col-4 ml-4" type="text" id="driver_i" name="driver_i" value="" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="row">
                                                    <label for="id_bar">Dari Gudang : </label>
                                                    <select name="fromgdg" id="fromgdg" class="form-control col-4 ml-4">
                                                        <?php foreach ($gudang as $gdg) : ?>
                                                            <option value="<?= $gdg->id_gudang ?>"><?= $gdg->nama_gudang ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="row">
                                                    <label for="id_bar">Ke Gudang : </label>
                                                    <select name="tujuangdg" id="tujuangdg" class="form-control col-4 ml-4">
                                                        <?php foreach ($gudang as $gdg) : ?>
                                                            <option value="<?= $gdg->id_gudang ?>"><?= $gdg->nama_gudang ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-auto mt-4">
                                        <table id="input_tmp_mutasi" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Nama Barang</th>
                                                    <th>Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <script>
        $(document).ready(function() {


            const dropdownParent = $('.content-wrapper');

            function initSelectBarang(id_gudang) {

                $('#select_barang').val(null).trigger('change');

                $('#select_barang').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: dropdownParent,
                    placeholder: 'Pilih Barang',
                    allowClear: true,
                    ajax: {
                        url: '<?= base_url("ics/ajax_barang_by_gudang") ?>',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term,
                                id_gudang: id_gudang
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        }
                    }
                });
            }

            $('#fromgdg').on('change', function() {
                let id_gudang = $(this).val();
                let nama_gudang = $('#fromgdg option:selected').text();

                $('#gudang_aktif').val(nama_gudang);

                $('#select_exp').val(null).trigger('change');

                if (id_gudang) {
                    initSelectBarang(id_gudang);
                }
            });

            $('#select_exp').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: modalParent,
                placeholder: 'Pilih Expired Date',
                allowClear: true
            });

            $('#select_barang').on('change', function() {
                let id_barang = $(this).val();

                $('#select_exp').val(null).trigger('change');

                if (id_barang) {
                    $('#select_exp').select2({
                        ajax: {
                            url: '<?= base_url("ics/ajax_expired_by_barang") ?>',
                            dataType: 'json',
                            delay: 250,
                            data: function() {
                                return {
                                    id_barang: id_barang
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data
                                };
                            }
                        }
                    });
                }
            });

        });
    </script>

    <script>
        $(document).ready(function() {

            const dropdownParent = $('.content-wrapper');

            function initSelectExp(id_gudang, nama_barang) {

                $('#select_exp').val(null).trigger('change');

                $('#select_exp').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: dropdownParent,
                    placeholder: 'Pilih Expired Date',
                    allowClear: true,
                    ajax: {
                        url: '<?= base_url("ics/ajax_exp_by_gudang_barang") ?>',
                        dataType: 'json',
                        delay: 250,
                        data: function() {
                            return {
                                id_gudang: id_gudang,
                                nama_barang: nama_barang
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        }
                    }
                });
            }

            $('#select_barang').on('change', function() {
                let nama_barang = $(this).val();
                let id_gudang = $('#fromgdg').val();

                if (nama_barang && id_gudang) {
                    initSelectExp(id_gudang, nama_barang);
                }
            });

        });
    </script>

    <script>
        $(document).ready(function() {

            $('#select_exp').on('change', function() {

                let expired_date = $(this).val();
                let nama_barang = $('#select_barang').val();
                let id_gudang = $('#fromgdg').val();

                $('#qtygudang').val('');

                if (!expired_date || !nama_barang || !id_gudang) return;

                $.ajax({
                    url: '<?= base_url("ics/ajax_get_qty_gudang") ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        id_gudang: id_gudang,
                        nama_barang: nama_barang,
                        expired_date: expired_date
                    },
                    success: function(res) {
                        $('#qtygudang').val(res.qty);
                    }
                });

            });

        });
    </script>