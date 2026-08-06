<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= isset($is_edit) ? 'Edit Pengajuan OD / Tempo' : 'Buat Pengajuan OD / Tempo' ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('error')) : ?>
                <div class="alert alert-danger">
                    <?= $this->session->flashdata('error'); ?>
                </div>
            <?php endif; ?>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><?= isset($is_edit) ? 'Form Edit Pengajuan OD' : 'Form Pengajuan OD' ?></h3>
                </div>
                
                <form action="<?= isset($is_edit) ? base_url('sales/C_PengajuanOD/update/'.$pengajuan['id']) : base_url('sales/C_PengajuanOD/store') ?>" method="post" enctype="multipart/form-data">
                    <div class="card-body">
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Pilih Faktur</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div id="faktur_inputs_container">
                                        <?php if (isset($is_edit) && !empty($pengajuan['fakturs'])) : ?>
                                            <?php foreach ($pengajuan['fakturs'] as $fk) : ?>
                                                <input type="hidden" name="id_faktur[]" value="<?= $fk['id_faktur'] ?>">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="id_customer" id="id_customer" value="<?= isset($is_edit) ? $pengajuan['id_customer'] : '' ?>" required>
                                    <input type="hidden" name="customer_name" id="customer_name" value="<?= isset($is_edit) ? htmlspecialchars((string)$pengajuan['customer_name']) : '' ?>" required>
                                    
                                    <input type="text" id="display_faktur" class="form-control" placeholder="Pilih Faktur..." readonly required value="<?= isset($is_edit) ? count($pengajuan['fakturs']).' Faktur Terpilih ('.htmlspecialchars((string)$pengajuan['customer_name']).')' : '' ?>">
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-info btn-flat" data-toggle="modal" data-target="#modal-faktur"><i class="fas fa-search"></i> Cari Faktur</button>
                                    </span>
                                </div>
                                <small class="text-muted">Anda bisa memilih lebih dari satu faktur asalkan dari Customer yang sama.</small>
                            </div>
                        </div>

                        <div id="detail_faktur_container" style="display:none;" class="mb-3">
                            <hr>
                            <h5>Informasi Faktur Terpilih</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" style="font-size: 13px;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Barang</th>
                                            <th class="text-center">Jumlah (Qty)</th>
                                            <th class="text-right">Harga</th>
                                            <th class="text-right">Subtotal</th>
                                            <th>Tgl Faktur</th>
                                            <th>No Faktur</th>
                                            <th>Customer</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_faktur_body">
                                    </tbody>
                                </table>
                            </div>
                            <hr>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Tanggal Jatuh Tempo Baru</label>
                            <div class="col-sm-9">
                                <input type="date" name="tanggal_jatuh_tempo_baru" id="tanggal_jatuh_tempo_baru" class="form-control" required value="<?= isset($is_edit) ? $pengajuan['target_tanggal_jatuh_tempo'] : '' ?>">
                                <small class="text-muted">Semua faktur yang dipilih akan diperpanjang hingga tanggal ini.</small>
                            </div>
                        </div>

                        <hr>
                        <h5>Informasi Catatan (Sesuai Form)</h5>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Catatan</label>
                            <div class="col-sm-9">
                                <textarea name="catatan" class="form-control" rows="5" placeholder="Isikan informasi catatan"><?= isset($is_edit) ? htmlspecialchars((string)$pengajuan['catatan']) : '' ?></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Upload Lampiran (SS WhatsApp)</label>
                            <div class="col-sm-9">
                                <input type="file" name="lampiran_sc" class="form-control-file" accept="image/*,.pdf">
                                <small class="text-muted">Maksimal 2MB (jpg/png/pdf).</small>
                                <?php if (isset($is_edit) && !empty($pengajuan['lampiran_sc'])) : ?>
                                    <p class="mt-1 mb-0"><a href="<?= base_url($pengajuan['lampiran_sc']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-file"></i> Lihat Lampiran Terupload Saat Ini</a></p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                    
                    <div class="card-footer text-right">
                        <a href="<?= base_url('sales/C_PengajuanOD') ?>" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary"><?= isset($is_edit) ? 'Simpan Perubahan' : 'Kirim Pengajuan' ?></button>
                    </div>
                </form>
            </div>
            
        </div>
    </section>
</div>

</div> <!-- ./wrapper -->

<?php 
$selected_fakturs = (isset($is_edit) && !empty($pengajuan['fakturs'])) ? array_column($pengajuan['fakturs'], 'id_faktur') : [];
?>

<!-- Modal Pilih Faktur -->
<div class="modal fade" id="modal-faktur">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih Faktur Penjualan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-bordered table-striped" id="table-faktur">
                    <thead>
                        <tr>
                            <th class="text-center">Pilih</th>
                            <th>No Faktur</th>
                            <th>Customer</th>
                            <th>Tgl Faktur</th>
                            <th>Tempo Lama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($faktur_list as $f) : ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="chk-faktur" 
                                        value="<?= $f['id_faktur'] ?>"
                                        data-no="<?= $f['no_faktur'] ?>"
                                        data-idcust="<?= htmlspecialchars((string)$f['kd_customer']) ?>"
                                        data-nama="<?= htmlspecialchars((string)$f['nama_customer']) ?>"
                                        data-tglfaktur="<?= $f['tanggal_faktur'] ?>"
                                        <?= in_array($f['id_faktur'], $selected_fakturs) ? 'checked' : '' ?>
                                        style="width: 20px; height: 20px; cursor: pointer;">
                                </td>
                                <td><?= $f['no_faktur'] ?></td>
                                <td><?= htmlspecialchars((string)$f['nama_customer']) ?></td>
                                <td><?= date('d-M-Y', strtotime($f['tanggal_faktur'])) ?></td>
                                <td><?= $f['tempo'] ?> Hari</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-submit-faktur">Gunakan Faktur Terpilih</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#table-faktur').DataTable();
    }

    // Validate checkbox selection (must be same customer)
    $(document).on('change', '.chk-faktur', function() {
        if ($(this).is(':checked')) {
            var currentCustId = $(this).data('idcust');
            var otherChecked = $('.chk-faktur:checked').not(this);
            
            if (otherChecked.length > 0) {
                var firstCustId = otherChecked.first().data('idcust');
                if (firstCustId !== currentCustId) {
                    alert('Faktur yang dipilih harus dari Customer yang sama!');
                    $(this).prop('checked', false);
                }
            }
        }
    });

    $('#btn-submit-faktur').on('click', function() {
        var checkedBoxes = $('.chk-faktur:checked');
        if (checkedBoxes.length === 0) {
            alert('Silakan pilih minimal 1 faktur terlebih dahulu.');
            return;
        }

        var id_fakturs = [];
        var no_fakturs = [];
        var firstCustId = checkedBoxes.first().data('idcust');
        var firstCustName = checkedBoxes.first().data('nama');

        $('#faktur_inputs_container').empty();

        checkedBoxes.each(function() {
            var id = $(this).val();
            var no = $(this).data('no');
            id_fakturs.push(id);
            no_fakturs.push(no);
            
            // Add hidden inputs for each faktur
            $('#faktur_inputs_container').append('<input type="hidden" name="id_faktur[]" value="'+id+'">');
        });

        $('#id_customer').val(firstCustId);
        $('#customer_name').val(firstCustName);
        
        var displayText = id_fakturs.length + ' Faktur Terpilih (' + firstCustName + ')';
        $('#display_faktur').val(displayText);

        // Fetch detail barang
        $.ajax({
            url: "<?= base_url('sales/C_PengajuanOD/ajax_get_multi_faktur_detail') ?>",
            type: "POST",
            data: { id_fakturs: id_fakturs },
            dataType: "json",
            success: function(data) {
                var html = '';
                var total = 0;
                if(data && data.length > 0) {
                    // Pre-calculate rowspan for No Faktur
                    var faktur_counts = {};
                    $.each(data, function(i, item) {
                        if (!faktur_counts[item.no_faktur]) {
                            faktur_counts[item.no_faktur] = 0;
                        }
                        faktur_counts[item.no_faktur]++;
                    });

                    var printed_faktur = {};

                    $.each(data, function(i, item) {
                        var harga = parseFloat(item.hrg_satuan) || 0;
                        var subtotal = parseFloat(item.total_harga) || 0;
                        total += subtotal;
                        
                        // Parse JS date
                        var tglObj = new Date(item.tanggal_faktur);
                        var tglStr = ('0' + tglObj.getDate()).slice(-2) + '-' + 
                                     tglObj.toLocaleString('default', { month: 'short' }) + '-' + 
                                     tglObj.getFullYear().toString().slice(-2);
                                     
                        html += '<tr>';
                        html += '<td>'+(i+1)+'</td>';
                        html += '<td>'+item.nama_barang+'</td>';
                        html += '<td class="text-center">'+parseFloat(item.qty)+' '+(item.satuan||'')+'</td>';
                        html += '<td class="text-right">'+new Intl.NumberFormat('id-ID').format(harga)+'</td>';
                        html += '<td class="text-right">'+new Intl.NumberFormat('id-ID').format(subtotal)+'</td>';
                        
                        // Merge Tgl Faktur and No Faktur cells
                        if (!printed_faktur[item.no_faktur]) {
                            var rowspan = faktur_counts[item.no_faktur];
                            html += '<td rowspan="'+rowspan+'" style="vertical-align: middle;">'+tglStr+'</td>';
                            html += '<td rowspan="'+rowspan+'" style="vertical-align: middle;">'+item.no_faktur+'</td>';
                            printed_faktur[item.no_faktur] = true;
                        }

                        // Merge Customer cell for all rows
                        if (i === 0) {
                            html += '<td rowspan="'+data.length+'" style="vertical-align: middle;">'+item.customer_name+'</td>';
                        }
                        html += '</tr>';
                    });
                    html += '<tr class="bg-light font-weight-bold">';
                    html += '<td colspan="4" class="text-right">TOTAL</td>';
                    html += '<td class="text-right">'+new Intl.NumberFormat('id-ID').format(total)+'</td>';
                    html += '<td colspan="3"></td>';
                    html += '</tr>';
                    
                    $('#detail_faktur_body').html(html);
                    $('#detail_faktur_container').slideDown();
                } else {
                    $('#detail_faktur_body').html('<tr><td colspan="8" class="text-center">Detail barang tidak ditemukan.</td></tr>');
                    $('#detail_faktur_container').slideDown();
                }
            },
            error: function() {
                alert('Gagal mengambil detail faktur.');
            }
        });

        $('#modal-faktur').modal('hide');
    });

    $('#tanggal_jatuh_tempo_baru').on('change', function() {
        var id_fakturs = $('input[name="id_faktur[]"]').length;
        if (id_fakturs === 0) {
            alert('Silakan pilih Faktur terlebih dahulu!');
            $(this).val('');
            return;
        }
    });

    <?php if (isset($is_edit)) : ?>
        // If edit mode, trigger submit faktur to populate items automatically
        $('#btn-submit-faktur').trigger('click');
    <?php endif; ?>
});
</script>
