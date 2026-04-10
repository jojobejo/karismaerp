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
                        <h1 class="m-0">
                            <i class="fas fa-file-invoice text-info"></i> Rekapitulasi DCA
                            <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dca') ?>">DCA</a></li>
                            <li class="breadcrumb-item active">Rekapitulasi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <!-- Filter + Export -->
                <div class="card card-outline card-info mb-3">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('kmt/dca/rekap') ?>"
                              class="form-inline flex-wrap" id="formFilter">
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-1 small">Tahun</label>
                                <select name="tahun" class="form-control form-control-sm">
                                    <?php for ($y = date('Y'); $y >= 2022; $y--): ?>
                                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-1 small">Bulan</label>
                                <select name="bulan" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <?php foreach ($nama_bulan as $nb => $nm): if (!$nb) continue; ?>
                                    <option value="<?= $nb ?>" <?= $bulan == $nb ? 'selected' : '' ?>><?= $nm ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($lv != 3): ?>
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-1 small">Wilayah</label>
                                <select name="id_wilayah" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <?php foreach ($wilayah_list as $w): ?>
                                    <option value="<?= $w['id'] ?>" <?= $id_wilayah == $w['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($w['nama_wilayah']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="id_wilayah" value="<?= $id_wilayah ?>">
                            <?php endif; ?>
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-1 small">ABM</label>
                                <select name="abm" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <?php foreach ($abm_list as $ab): ?>
                                    <option value="<?= htmlspecialchars($ab['abm']) ?>"
                                        <?= $abm == $ab['abm'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ab['abm']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm mb-2 mr-2">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="<?= base_url('kmt/dca/export_rekap')
                                . '?tahun='.$tahun.'&bulan='.$bulan
                                . '&id_wilayah='.$id_wilayah.'&abm='.urlencode($abm) ?>"
                               class="btn btn-success btn-sm mb-2">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Rekapitulasi per ABM -->
                <?php if (empty($grouped)): ?>
                <div class="alert alert-info">Tidak ada data untuk filter yang dipilih.</div>
                <?php endif; ?>

                <?php
                $grand_um    = 0;
                $grand_biaya = 0;
                foreach ($grouped as $abm_nama => $abm_data):
                    $abm_total_biaya = 0;
                    foreach ($abm_data['mdo'] as $mdo_nm => $mdo_data) {
                        foreach ($mdo_data['kegiatan'] as $kg) $abm_total_biaya += $kg['total_biaya'];
                    }
                    $grand_um    += $abm_data['um'];
                    $grand_biaya += $abm_total_biaya;
                    $sisa         = $abm_data['um'] - $abm_total_biaya;
                ?>

                <div class="card card-outline card-info mb-4">
                    <!-- Card header ABM -->
                    <div class="card-header" style="background:#1f3864;">
                        <h3 class="card-title text-white">
                            <i class="fas fa-user-tie mr-1"></i>
                            <?= htmlspecialchars($abm_nama) ?> — ABM
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-warning mr-2">
                                UM: Rp <?= number_format($abm_data['um'], 0, ',', '.') ?>
                            </span>
                            <span class="badge badge-light mr-2">
                                Total Biaya: Rp <?= number_format($abm_total_biaya, 0, ',', '.') ?>
                            </span>
                            <span class="badge <?= $sisa >= 0 ? 'badge-success' : 'badge-danger' ?>">
                                Sisa: Rp <?= number_format($sisa, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">

                        <?php foreach ($abm_data['mdo'] as $mdo_nm => $mdo_data): ?>
                        <?php
                        $mdo_bisi = $mdo_peserta = $mdo_biaya = 0;
                        foreach ($mdo_data['kegiatan'] as $kg) {
                            $mdo_bisi    += $kg['total_bisi'];
                            $mdo_peserta += $kg['total_peserta'];
                            $mdo_biaya   += $kg['total_biaya'];
                        }
                        ?>

                        <!-- MDO sub-header -->
                        <div class="px-3 py-2" style="background:#2e75b6;">
                            <strong class="text-white">
                                <i class="fas fa-user mr-1"></i> MDO: <?= htmlspecialchars($mdo_nm) ?>
                            </strong>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
                                <thead style="background:#344d7e;color:#fff;">
                                    <tr>
                                        <th width="38%">Nama Petugas & Jenis Kegiatan</th>
                                        <th class="text-right" width="10%">DCA Kas Bon</th>
                                        <th class="text-right" width="10%">DS Bisi 959<br><small>(20x1Kg)</small></th>
                                        <th class="text-right" width="10%">DS Q-235<br><small>(10x1Kg)</small></th>
                                        <th class="text-right" width="8%">Jml Peserta</th>
                                        <th class="text-right" width="8%">Qty Terjual</th>
                                        <th class="text-right" width="12%">Total Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <!-- UM row -->
                                    <tr style="background:#344d7e;color:#DAEEF3;">
                                        <td><strong>UM <?= htmlspecialchars($mdo_nm) ?> (BFM,FM,FFD,ODP)</strong></td>
                                        <td class="text-right"><strong><?= number_format($abm_data['um'], 0, ',', '.') ?></strong></td>
                                        <td colspan="4"></td>
                                        <td class="text-right"><strong>0</strong></td>
                                    </tr>

                                    <?php foreach ($mdo_data['kegiatan'] as $kg_nm => $kg_data): ?>

                                    <!-- Kegiatan header -->
                                    <tr style="background:#4472C4;color:#fff;">
                                        <td colspan="7"><strong><?= htmlspecialchars($kg_nm) ?></strong></td>
                                    </tr>

                                    <!-- Detail rows -->
                                    <?php foreach ($kg_data['rows'] as $det): ?>
                                    <tr>
                                        <td class="pl-4"><?= htmlspecialchars($det['nama_mdo']) ?></td>
                                        <td></td>
                                        <td class="text-right"><?= $det['qty_bisi'] > 0 ? number_format($det['qty_bisi'], 0, ',', '.') : '-' ?></td>
                                        <td class="text-right"><?= $det['qty_q235'] > 0 ? number_format($det['qty_q235'], 0, ',', '.') : '-' ?></td>
                                        <td class="text-right"><?= $det['jml_peserta'] > 0 ? number_format($det['jml_peserta'], 0, ',', '.') : '-' ?></td>
                                        <td class="text-right"><?= ($det['qty_bisi'] + $det['qty_q235']) > 0 ? number_format($det['qty_bisi'] + $det['qty_q235'], 0, ',', '.') : '-' ?></td>
                                        <td class="text-right"><?= number_format($det['real_biaya'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>

                                    <!-- Subtotal kegiatan -->
                                    <tr style="background:#DAEEF3;font-weight:bold;">
                                        <td><?= htmlspecialchars($kg_nm) ?> <em>Total</em></td>
                                        <td></td>
                                        <td class="text-right"><?= number_format($kg_data['total_bisi'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($kg_data['total_q235'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($kg_data['total_peserta'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($kg_data['total_bisi'] + $kg_data['total_q235'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($kg_data['total_biaya'], 0, ',', '.') ?></td>
                                    </tr>

                                    <?php endforeach; ?>

                                    <!-- Subtotal MDO -->
                                    <tr style="background:#9DC3E6;font-weight:bold;">
                                        <td>MARKET DEVELOPMENT OFFICER (MDO) Total</td>
                                        <td></td>
                                        <td class="text-right"><?= number_format($mdo_bisi, 0, ',', '.') ?></td>
                                        <td></td>
                                        <td class="text-right"><?= number_format($mdo_peserta, 0, ',', '.') ?></td>
                                        <td></td>
                                        <td class="text-right"><?= number_format($mdo_biaya, 0, ',', '.') ?></td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                        <?php endforeach; ?>

                        <!-- Grand Total ABM -->
                        <table class="table table-sm mb-0" style="font-size:12px;">
                            <tfoot style="background:#1f3864;color:#FFC000;font-weight:bold;">
                                <tr>
                                    <td width="38%"><?= htmlspecialchars($abm_nama) ?> Total / Grand Total</td>
                                    <td class="text-right" width="10%"><?= number_format($abm_data['um'], 0, ',', '.') ?></td>
                                    <td width="10%"></td>
                                    <td width="10%"></td>
                                    <td width="8%"></td>
                                    <td width="8%"></td>
                                    <td class="text-right" width="12%"><?= number_format($abm_total_biaya, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>

                    </div><!-- /.card-body -->
                </div><!-- /.card -->

                <?php endforeach; ?>

                <!-- Grand Total Semua -->
                <?php if (!empty($grouped)): ?>
                <div class="card card-outline card-success mb-4">
                    <div class="card-body py-2">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="info-box bg-info shadow-sm mb-0">
                                    <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total UM</span>
                                        <span class="info-box-number">Rp <?= number_format($grand_um, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-warning shadow-sm mb-0">
                                    <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Biaya</span>
                                        <span class="info-box-number">Rp <?= number_format($grand_biaya, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box <?= ($grand_um - $grand_biaya) >= 0 ? 'bg-success' : 'bg-danger' ?> shadow-sm mb-0">
                                    <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sisa Dana</span>
                                        <span class="info-box-number">Rp <?= number_format($grand_um - $grand_biaya, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022
            <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.
        </strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>