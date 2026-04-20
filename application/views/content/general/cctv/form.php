<?php defined('BASEPATH') OR exit('No direct script access allowed');
$edit = isset($kamera);
?>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?= $edit ? 'Edit Kamera' : 'Tambah Kamera' ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('cctv') ?>">Tracking CCTV</a></li>
                            <li class="breadcrumb-item active"><?= $edit ? 'Edit' : 'Tambah' ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-8">

                        <?= validation_errors('<div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>', '</div>') ?>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-<?= $edit ? 'pencil-alt' : 'plus' ?> mr-2"></i>
                                    <?= $edit ? 'Edit Data Kamera' : 'Tambah Kamera Baru' ?>
                                </h3>
                            </div>

                            <form method="post"
                                  action="<?= $edit ? site_url('cctv/update/'.$kamera->id) : site_url('cctv/simpan') ?>">

                                <div class="card-body">

                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tgl" class="form-control"
                                               value="<?= set_value('tgl', $edit ? $kamera->tgl : date('Y-m-d')) ?>"
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label>Lokasi <span class="text-danger">*</span></label>
                                        <input type="text" name="lokasi" class="form-control"
                                               placeholder="Contoh: Lobby Utama"
                                               value="<?= set_value('lokasi', $edit ? $kamera->lokasi : '') ?>"
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label>Nama Kamera <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_kamera" class="form-control"
                                               placeholder="Contoh: CAM-001"
                                               value="<?= set_value('nama_kamera', $edit ? $kamera->nama_kamera : '') ?>"
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label>IP Kamera <span class="text-danger">*</span></label>
                                        <input type="text" name="ip_kamera" class="form-control"
                                               placeholder="Contoh: 192.168.1.101"
                                               value="<?= set_value('ip_kamera', $edit ? $kamera->ip_kamera : '') ?>"
                                               required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status <span class="text-danger">*</span></label>
                                                <select name="status" class="form-control" required>
                                                    <option value="Online"
                                                        <?= set_select('status', 'Online', ($edit && $kamera->status === 'Online')) ?>>
                                                        Online
                                                    </option>
                                                    <option value="Offline"
                                                        <?= set_select('status', 'Offline', ($edit && $kamera->status === 'Offline')) ?>>
                                                        Offline
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status Rekaman <span class="text-danger">*</span></label>
                                                <select name="status_rekaman" class="form-control" required>
                                                    <option value="Terekam"
                                                        <?= set_select('status_rekaman', 'Terekam', ($edit && $kamera->status_rekaman === 'Terekam')) ?>>
                                                        Terekam
                                                    </option>
                                                    <option value="Tidak"
                                                        <?= set_select('status_rekaman', 'Tidak', ($edit && $kamera->status_rekaman === 'Tidak')) ?>>
                                                        Tidak
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Keterangan</label>
                                        <textarea name="keterangan" class="form-control" rows="3"
                                                  placeholder="Catatan tambahan..."><?= set_value('keterangan', $edit ? $kamera->keterangan : '') ?></textarea>
                                    </div>

                                </div><!-- /.card-body -->

                                <div class="card-footer">
                                    <a href="<?= site_url('cctv') ?>" class="btn btn-default">
                                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary float-right">
                                        <i class="fas fa-save mr-1"></i> Simpan
                                    </button>
                                </div>

                            </form>
                        </div><!-- /.card -->

                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div>
        </section>
    </div><!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div><!-- /.wrapper -->
</body>