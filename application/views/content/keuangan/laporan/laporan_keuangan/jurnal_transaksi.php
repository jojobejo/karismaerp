<?php
/**
 * Laporan Jurnal Transaksi
 * Tampilan laporan jurnal transaksi dengan filter tanggal.
 */
$back_url   = base_url('laporan/keuangan');
$page_title = 'Laporan Jurnal Transaksi';
$page_icon  = 'fas fa-book';
$page_color = '#1788b8';
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar'); ?>
    <?php $this->load->view('partial/main/sidebar'); ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><i class="<?= $page_icon ?> mr-2"></i> <?= $page_title ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <a href="<?= $back_url ?>" class="btn btn-secondary float-right"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Filter</h3>
                </div>
                <div class="box-body">
                    <form id="filterForm" class="form-inline">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group ml-2">
                            <label for="end_date">Tanggal Akhir:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                        <button type="button" class="btn btn-primary ml-2" id="btnSearch">Tampilkan</button>
                    </form>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <table id="tbl_jurnal" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nomor Jurnal</th>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    <?php $this->load->view('partial/main/footergdg.php'); ?>
</div>
<script>
$(document).ready(function(){
    var table = $('#tbl_jurnal').DataTable({
        ajax: {
            url: '<?= base_url("laporan/keuangan/jurnal-transaksi-data") ?>',
            type: 'POST',
            data: function(d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            },
            dataSrc: function(json){
                return json.success ? json.data : [];
            }
        },
        columns: [
            { data: 'tanggal' },
            { data: 'nomor_jurnal' },
            { data: 'kd_akun' },
            { data: 'nama_akun' },
            { data: 'debit', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'kredit', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'keterangan' }
        ]
    });
    $('#btnSearch').on('click', function(){ table.ajax.reload(); });
});
</script>
</body>
