<!-- views/kmt/retur/index.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-undo text-danger"></i> Data Retur <small class="text-muted">KMT CORN</small></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <div class="mb-3 d-flex justify-content-between">
                    <div>
                        <a href="<?= base_url('kmt/retur/export')
                                . '?tahun=' . $tahun
                                . '&bulan=' . $bulan
                                . '&id_wilayah=' . $id_wilayah ?>"
                        class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    </div>

                </div>

                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/retur'),
                    'show_bulan' => true,
                ]); ?>

                <!-- Summary per wilayah -->
                <?php if (!empty($summary)): ?>
                <div class="row mb-3">
                    <?php foreach ($summary as $s): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-danger shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-undo"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= $s['nama_wilayah'] ?> (<?= $s['jumlah'] ?> transaksi)</span>
                                <span class="info-box-number">Rp <?= number_format($s['total_retur'],0,',','.') ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="card card-outline card-danger">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-table mr-1"></i> Daftar Retur — <?= $tahun ?></h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblRetur" class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th>#</th><th>Tgl Retur</th><th>Wilayah</th>
                                        <th>No Retur</th><th>Nama Toko</th><th>Produk</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Nilai Retur</th>
                                        <th>Keterangan</th><th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td><?= $i+1 ?></td>
                                        <td><?= date('d/m/Y',strtotime($row['tanggal_retur'])) ?></td>
                                        <td><span class="badge badge-secondary"><?= $row['nama_wilayah']??'-' ?></span></td>
                                        <td><?= htmlspecialchars($row['no_retur']??'-') ?></td>
                                        <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                                        <td><?= htmlspecialchars($row['produk']) ?></td>
                                        <td class="text-right"><?= number_format($row['quantity'],2,',','.') ?></td>
                                        <td class="text-right font-weight-bold text-danger">
                                            <?= number_format($row['nilai_retur'],0,',','.') ?>
                                        </td>
                                        <td class="small"><?= htmlspecialchars($row['keterangan']??'-') ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('kmt/retur/edit/'.$row['id']) ?>" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="<?= base_url('kmt/retur/hapus/'.$row['id']) ?>" class="btn btn-xs btn-danger btn-hapus"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="7" class="text-right">TOTAL NILAI RETUR:</td>
                                        <td class="text-right text-danger"><?= number_format($total_retur,0,',','.') ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.<div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<script>
$(function(){
    $('#tblRetur').DataTable({responsive:true,pageLength:25,
        columnDefs:[{targets:[6,7],className:'dt-right'},{targets:[9],orderable:false}],
        language:{url:'<?= base_url('assets/plugins/datatables/id.json') ?>'}
    });
    $(document).on('click','.btn-hapus',function(e){
        e.preventDefault();var url=$(this).attr('href');
        Swal.fire({title:'Hapus data ini?',icon:'warning',showCancelButton:true,
            confirmButtonColor:'#d33',confirmButtonText:'Ya, Hapus!',cancelButtonText:'Batal'
        }).then(r=>{if(r.isConfirmed) window.location.href=url;});
    });
});
</script>
