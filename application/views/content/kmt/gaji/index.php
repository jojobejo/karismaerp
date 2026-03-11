<!-- views/kmt/gaji/index.php -->
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
                        <h1 class="m-0"><i class="fas fa-users text-purple"></i> Data Gaji <small class="text-muted">KMT CORN</small></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Gaji</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <div class="mb-3">
                    <a href="<?= base_url('kmt/gaji/tambah') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Karyawan
                    </a>
                </div>

                <!-- Filter (tanpa bulan) -->
                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/gaji'),
                    'show_bulan' => true,
                ]); ?>

                <!-- Summary per wilayah -->
                <?php if (!empty($summary)): ?>
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header"><h3 class="card-title">Rekap Total Gaji per Wilayah — <?= $tahun ?></h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th>Wilayah</th>
                                        <?php foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $bln): ?>
                                        <th class="text-right"><?= $bln ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-right">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($summary as $s):
                                        $total_w = 0;
                                    ?>
                                    <tr>
                                        <td class="font-weight-bold"><?= $s['nama_wilayah'] ?></td>
                                        <?php foreach ($bulan_cols as $col):
                                            $val = (float)($s[$col] ?? 0);
                                            $total_w += $val;
                                        ?>
                                        <td class="text-right small"><?= $val > 0 ? number_format($val,0,',','.') : '-' ?></td>
                                        <?php endforeach; ?>
                                        <td class="text-right font-weight-bold"><?= number_format($total_w,0,',','.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabel Detail -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-table mr-1"></i> Data Gaji Karyawan — <?= $tahun ?></h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblGaji" class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th>#</th><th>Wilayah</th><th>Nama</th><th>Posisi</th><th>Status</th>
                                        <th>Mulai</th><th>Resign</th>
                                        <?php foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $b): ?>
                                        <th class="text-right"><?= $b ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-right">Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td><?= $i+1 ?></td>
                                        <td><span class="badge badge-secondary"><?= $row['nama_wilayah']??'-' ?></span></td>
                                        <td class="font-weight-bold"><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['posisi']??'-') ?></td>
                                        <td>
                                            <span class="badge <?= $row['tgl_resign'] ? 'badge-danger':'badge-success' ?>">
                                                <?= $row['tgl_resign'] ? 'Resign':'Aktif' ?>
                                            </span>
                                        </td>
                                        <td><?= $row['tgl_mulai'] ? date('d/m/Y',strtotime($row['tgl_mulai'])):'-' ?></td>
                                        <td><?= $row['tgl_resign'] ? date('d/m/Y',strtotime($row['tgl_resign'])):'-' ?></td>
                                        <?php foreach ($bulan_cols as $col):
                                            $val=(float)($row[$col]??0); ?>
                                        <td class="text-right small"><?= $val>0 ? number_format($val,0,',','.'):'-' ?></td>
                                        <?php endforeach; ?>
                                        <td class="text-right font-weight-bold">
                                            <?= number_format($row['total_gaji'],0,',','.') ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('kmt/gaji/edit/'.$row['id']) ?>" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="<?= base_url('kmt/gaji/hapus/'.$row['id']) ?>" class="btn btn-xs btn-danger btn-hapus"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
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
    $('#tblGaji').DataTable({responsive:true,pageLength:25,scrollX:true,
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
