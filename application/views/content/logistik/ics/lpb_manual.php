<style>
    .lpb-manual-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: space-between;
        align-items: center;
    }

    .lpb-manual-table th,
    .lpb-manual-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .lpb-manual-table .select2-container {
        min-width: 280px;
    }

    .lpb-manual-row-action {
        width: 38px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <div class="lpb-manual-toolbar">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-clipboard-list mr-2"></i> Input LPB Manual
                                </h3>
                                <div>
                                    <a href="<?= base_url('ics/icspo') ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-left mr-1"></i> Data LPB
                                    </a>
                                    <a href="<?= base_url('ics/lpb_report?source=manual') ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-chart-bar mr-1"></i> Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                        <form id="lpbManualForm" autocomplete="off">
                            <div class="card-body">
                                <div class="alert d-none" id="lpbManualAlert"></div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Ref Manual</label>
                                            <input type="text" class="form-control" name="manual_ref_no" value="<?= htmlspecialchars($manual_ref ?? '') ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal LPB</label>
                                            <input type="date" class="form-control" name="tgl_lpb" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Jenis LPB</label>
                                            <select class="form-control" name="jenis_lpb" required>
                                                <?php foreach (($lpb_type_options ?? []) as $key => $option) : ?>
                                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($option['label'] ?? $key) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Gudang</label>
                                            <select class="form-control" name="gudang_id" required>
                                                <option value="">Pilih Gudang</option>
                                                <?php foreach (($list_gudang ?? []) as $gudang) : ?>
                                                    <option value="<?= htmlspecialchars($gudang['id_gudang']) ?>"><?= htmlspecialchars($gudang['nama_gudang']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No SJ</label>
                                            <input type="text" class="form-control" name="nosj" placeholder="-">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No Invoice</label>
                                            <input type="text" class="form-control" name="no_invoice" placeholder="-">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input type="text" class="form-control" name="keterangan" placeholder="Catatan input manual">
                                        </div>
                                    </div>
                                </div>

                                <div class="lpb-manual-toolbar mb-2">
                                    <strong>Detail Barang</strong>
                                    <button type="button" class="btn btn-success btn-sm" id="btnAddManualRow">
                                        <i class="fas fa-plus mr-1"></i> Tambah Barang
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover lpb-manual-table" id="lpbManualTable">
                                        <thead class="thead-dark text-center">
                                            <tr>
                                                <th>Barang</th>
                                                <th>Satuan</th>
                                                <th>Qty</th>
                                                <th>No Lot</th>
                                                <th>Expired</th>
                                                <th>Harga Satuan</th>
                                                <th class="lpb-manual-row-action">#</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-end" style="gap:8px;">
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary" id="btnSaveManualLpb">
                                    <i class="fas fa-save mr-1"></i> Simpan LPB Manual
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <script>
        $(function() {
            var rowIndex = 0;

            function showAlert(type, message) {
                $('#lpbManualAlert')
                    .removeClass('d-none alert-success alert-danger alert-warning')
                    .addClass('alert-' + type)
                    .text(message);
            }

            function initBarangSelect($select) {
                $select.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Cari kode/nama barang',
                    ajax: {
                        url: '<?= base_url('ics/lpb_manual/barang') ?>',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return { q: params.term || '' };
                        },
                        processResults: function(response) {
                            return { results: response.results || [] };
                        }
                    }
                }).on('select2:select', function(e) {
                    var data = e.params.data || {};
                    var $row = $(this).closest('tr');
                    $row.find('.manual-satuan').val(data.satuan || 'PCS');
                });
            }

            function addRow() {
                rowIndex++;
                var row = '' +
                    '<tr>' +
                    '<td><select class="form-control manual-barang" name="kd_barang[]" required></select></td>' +
                    '<td><input type="text" class="form-control manual-satuan" name="satuan[]" readonly></td>' +
                    '<td><input type="number" class="form-control" name="qty_diterima[]" min="0.001" step="0.001" required></td>' +
                    '<td><input type="text" class="form-control" name="no_lot[]" required></td>' +
                    '<td><input type="date" class="form-control" name="expired_date[]" required></td>' +
                    '<td><input type="number" class="form-control" name="harga_satuan[]" min="0" step="0.0001" value="0"></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-danger btn-sm btnRemoveManualRow" title="Hapus baris"><i class="fas fa-trash"></i></button></td>' +
                    '</tr>';
                var $row = $(row);
                $('#lpbManualTable tbody').append($row);
                initBarangSelect($row.find('.manual-barang'));
            }

            $('#btnAddManualRow').on('click', addRow);
            $('#lpbManualTable').on('click', '.btnRemoveManualRow', function() {
                if ($('#lpbManualTable tbody tr').length <= 1) {
                    showAlert('warning', 'Minimal harus ada 1 baris barang.');
                    return;
                }
                $(this).closest('tr').remove();
            });

            $('#lpbManualForm').on('submit', function(e) {
                e.preventDefault();
                var $button = $('#btnSaveManualLpb');
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/lpb_manual/store') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message || 'LPB Manual berhasil disimpan.');
                            setTimeout(function() {
                                window.location.href = response.redirect_url || '<?= base_url('ics/lpb_report?source=manual') ?>';
                            }, 800);
                            return;
                        }
                        showAlert('danger', response.message || 'LPB Manual gagal disimpan.');
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'LPB Manual gagal disimpan.';
                        showAlert('danger', message);
                    },
                    complete: function() {
                        $button.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan LPB Manual');
                    }
                });
            });

            addRow();
        });
    </script>
</body>
