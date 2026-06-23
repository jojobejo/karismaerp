<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper supervisor-opname-page">
        <section class="content">
            <div class="container-fluid py-3 pb-4">
                <style>
                    .supervisor-opname-page{background:#eef3f8}.sup-shell{max-width:520px;margin:0 auto}.sup-panel{background:#fff;border:1px solid #dce5ee;border-radius:8px;box-shadow:0 8px 22px rgba(15,23,42,.07)}.sup-panel-head{padding:14px 16px;border-bottom:1px solid #e6edf4}.sup-title{font-size:16px;font-weight:800;color:#172033;margin:0}.sup-muted{color:#64748b;font-size:12px}.sup-actions{display:grid;gap:10px}.sup-actions .btn{height:48px;font-weight:800}.sup-stock-card{display:none}.sup-stock-card label{font-size:12px;font-weight:800;color:#334155}.select2-container--bootstrap4 .select2-selection{border-radius:8px;min-height:38px}.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered{line-height:36px}
                </style>

                <div class="sup-shell">
                    <div class="sup-panel mb-3">
                        <div class="p-3">
                            <div class="sup-actions">
                                <button type="button" class="btn btn-warning" id="btnShowRequest"><i class="fas fa-clipboard-list"></i> Request Opname</button>
                                <a href="<?= base_url('supervisi-opname/tracking') ?>" class="btn btn-outline-primary"><i class="fas fa-map-marked-alt"></i> Tracking Inputer Wilayah</a>
                            </div>
                            <div class="sup-muted mt-3">Wilayah supervisi: <strong><?= htmlspecialchars((string)$wilayah, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        </div>
                    </div>

                    <form id="formSupervisorRequest" class="sup-stock-card" novalidate>
                        <div class="sup-panel">
                            <div class="sup-panel-head"><h1 class="sup-title">Kartu Stock — Request Opname</h1></div>
                            <div class="p-3">
                                <div class="form-group">
                                    <label>Nama Barang</label>
                                    <select class="form-control" id="requestBarang" name="request_kode_barang"></select>
                                </div>
                                <div class="form-group">
                                    <label>No Lot</label>
                                    <input type="text" class="form-control" id="requestLot" name="request_no_lot" placeholder="Input no lot" required>
                                </div>
                                <div class="form-group">
                                    <label>Expired Date</label>
                                    <input type="text" class="form-control" id="requestExpired" name="request_expired_date" placeholder="dd/mm/yyyy" inputmode="numeric" maxlength="10" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-paper-plane"></i> Kirim Request Opname</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    function toast(icon, title) {
        if (typeof Swal !== 'undefined') return Swal.fire({toast:true,position:'top-end',icon:icon,title:title,showConfirmButton:false,timer:2600});
        alert(title);
    }
    function formatDate(value) {
        var digits = String(value || '').replace(/\D/g, '').slice(0, 8);
        return digits.length <= 2 ? digits : (digits.length <= 4 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4));
    }
    $('#btnShowRequest').on('click', function () {
        $('#formSupervisorRequest').slideDown(180);
        $(this).prop('disabled', true).html('<i class="fas fa-check"></i> Form Request Dibuka');
    });
    $('#requestBarang').select2({
        theme:'bootstrap4', width:'100%', placeholder:'Cari nama barang', allowClear:true,
        ajax:{url:'<?= base_url('admin/stockopname/input/manual/barang') ?>',dataType:'json',delay:250,data:function(params){return {q:params.term || '',page:params.page || 1};},processResults:function(data){return data;}}
    });
    $('#requestExpired').on('input', function () { $(this).val(formatDate($(this).val())); });
    $('#formSupervisorRequest').on('submit', function (event) {
        event.preventDefault();
        var $button = $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim');
        $.ajax({url:'<?= base_url('admin/stockopname/input/request/save') ?>',type:'POST',dataType:'json',data:$(this).serialize()})
            .done(function(res) {
                if (!res.status) return toast('warning', res.message || 'Request belum dapat disimpan');
                toast('success', res.message || 'Request opname berhasil dikirim');
                $('#formSupervisorRequest')[0].reset(); $('#requestBarang').val(null).trigger('change');
            })
            .fail(function(xhr) { toast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Server tidak merespons'); })
            .always(function() { $button.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Request Opname'); });
    });
});
</script>
