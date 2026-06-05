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
                    <div class="col-sm-7">
                        <h1 class="m-0"><i class="fas fa-money-check-alt mr-2"></i>Input Pembayaran</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran') ?>">Pembayaran</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($faktur['no_faktur']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="mb-3">
                    <a href="<?= base_url('keuangan/pembayaran/customer/' . rawurlencode($faktur['kd_customer'])) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Detail Customer
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i>Ringkasan Faktur</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="42%">No Faktur</td>
                                        <td><strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal Faktur</td>
                                        <td><?= !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Customer</td>
                                        <td><?= htmlspecialchars($faktur['nama_customer']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status Overdue</td>
                                        <td><span class="badge badge-<?= $faktur['status_overdue'] === 'Belum overdue' ? 'secondary' : 'danger' ?>"><?= htmlspecialchars($faktur['status_overdue']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Total Tagihan</td>
                                        <td>Rp <?= number_format((float)$faktur['total_tagihan'], 0, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Total Pembayaran</td>
                                        <td>Rp <?= number_format((float)$faktur['total_pembayaran'], 0, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Sisa Tagihan</td>
                                        <td><strong class="text-danger">Rp <?= number_format((float)$faktur['sisa_tagihan'], 0, ',', '.') ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-history mr-1"></i>Histori Pembayaran</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Metode</th>
                                            <th class="text-right">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($history)): ?>
                                            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada pembayaran.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($history as $row): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pembayaran'])) ?></td>
                                                    <td><?= htmlspecialchars($row['metode_pembayaran'] ?: '-') ?></td>
                                                    <td class="text-right">Rp <?= number_format((float)$row['jumlah_pembayaran'], 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i>Form Pembayaran</h3>
                            </div>
                            <form action="<?= base_url('keuangan/pembayaran/simpan/' . $faktur['id_faktur']) ?>" method="post">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Tanggal Pembayaran <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_pembayaran" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Jumlah Pembayaran <span class="text-danger">*</span></label>
                                        <input type="number" name="jumlah_pembayaran" class="form-control" min="1"
                                               max="<?= (float)$faktur['sisa_tagihan'] ?>" step="0.01"
                                               value="<?= (float)$faktur['sisa_tagihan'] ?>" required>
                                        <small class="text-muted">Maksimal Rp <?= number_format((float)$faktur['sisa_tagihan'], 0, ',', '.') ?></small>
                                    </div>
                                    <div class="form-group">
                                        <label>Metode Pembayaran</label>
                                        <select name="metode_pembayaran" class="form-control">
                                            <option value="transfer">Transfer</option>
                                            <option value="cash">Cash</option>
                                            <option value="giro">Giro</option>
                                            <option value="bg">BG</option>
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Keterangan</label>
                                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Nomor referensi / catatan pembayaran"></textarea>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save mr-1"></i>Simpan Pembayaran
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>
