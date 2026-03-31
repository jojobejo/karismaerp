<!-- views/kmt/promo/index.php -->
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
                        <h1 class="m-0"><i class="fas fa-tag text-orange"></i> Promo Material / Peralatan <small class="text-muted">KMT CORN</small></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Promo</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <div class="mb-3">
                    <a href="<?= base_url('kmt/promo/tambah') ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Item
                    </a>

                    <a href="<?= base_url('kmt/promo/export?tahun='.$tahun.'&bulan='.$bulan.'&id_wilayah='.$id_wilayah) ?>" 
                    class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                </div>              

                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/promo'),
                    'show_bulan' => true,
                ]); ?>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-orange shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-tools"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Biaya Peralatan</span>
                                <span class="info-box-number">Rp <?= number_format($total_biaya,0,',','.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-warning">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-table mr-1"></i> Daftar Promo / Peralatan — <?= $tahun ?></h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblPromo" class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th>#</th><th>Tanggal</th><th>Wilayah</th><th>Nama Item</th>
                                        <th>Kategori</th>
                                        <th class="text-right">Qty</th><th>Satuan</th>
                                        <th class="text-right">Harga Satuan</th>
                                        <th class="text-right">Total</th>
                                        <th>Keterangan</th><th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td><?= $i+1 ?></td>
                                        <td><?= date('d/m/Y',strtotime($row['tanggal'])) ?></td>
                                        <td><span class="badge badge-secondary"><?= $row['nama_wilayah']??'-' ?></span></td>
                                        <td><?= htmlspecialchars($row['nama_item']) ?></td>
                                        <td><span class="badge badge-light"><?= htmlspecialchars($row['kategori']??'-') ?></span></td>
                                        <td class="text-right"><?= $row['qty'] ?></td>
                                        <td><?= htmlspecialchars($row['satuan']??'-') ?></td>
                                        <td class="text-right"><?= number_format($row['harga_satuan'],0,',','.') ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($row['total_biaya'],0,',','.') ?></td>
                                        <td class="small"><?= htmlspecialchars($row['keterangan']??'-') ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('kmt/promo/edit/'.$row['id']) ?>" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="<?= base_url('kmt/promo/hapus/'.$row['id']) ?>" class="btn btn-xs btn-danger btn-hapus"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="8" class="text-right">TOTAL:</td>
                                        <td class="text-right"><?= number_format($total_biaya,0,',','.') ?></td>
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
    $('#tblPromo').DataTable({responsive:true,pageLength:25,
        columnDefs:[{targets:[5,7,8],className:'dt-right'},{targets:[10],orderable:false}],
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
