<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <style>
        .rekap-sheet-card{border:1px solid #6f6f6f;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        .rekap-sheet-wrap{overflow-x:auto;background:#e9ecef;padding:14px}
        .rekap-sheet{min-width:850px;max-width:1080px;margin:0 auto;background:#fff;font-family:Arial,sans-serif;color:#000}
        .rekap-sheet table{width:100%;margin:0;table-layout:fixed;border-collapse:collapse;font-size:12px}
        .rekap-sheet th,.rekap-sheet td{border:1px solid #7f7f7f!important;padding:4px 6px;height:27px;vertical-align:middle;background:#fff}
        .rekap-sheet .report-title{height:34px;font-size:18px;font-weight:700;text-align:center}
        .rekap-sheet .report-subtitle{height:28px;font-size:12px;font-style:italic;font-weight:700;text-align:center}
        .rekap-sheet .identity{text-align:center;font-weight:700}
        .rekap-sheet .summary-label,.rekap-sheet .section-row td,.rekap-sheet .activity-row td,
        .rekap-sheet .subtotal-row td,.rekap-sheet .total-row td{font-weight:700}
        .rekap-sheet .summary-value{text-align:right;font-weight:700}
        .rekap-sheet .negative-accent{color:#f00}
        .rekap-sheet .spacer-row td{height:10px;padding:0}
        .rekap-sheet .column-header th{height:62px;text-align:center;font-weight:700;line-height:1.25}
        .rekap-sheet .detail-row td:first-child{padding-left:18px}
        .rekap-sheet .grand-row td{font-weight:700;border-top:2px solid #000!important}
        .rekap-sheet .num{text-align:right;white-space:nowrap}
        .rekap-sheet .muted-dash{color:#777}
        .rekap-sheet .no-bukti-input{width:100%;height:25px;border:0;background:#fff;padding:2px 5px;font:inherit;color:#000}
        .rekap-sheet .no-bukti-input:focus{outline:2px solid #17a2b8;outline-offset:-2px}
        @media(max-width:767.98px){.rekap-sheet-wrap{padding:6px}}
    </style>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-file-invoice text-info"></i> Rekapitulasi DCA <small class="text-muted">KMT CORN</small></h1>
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

                <div class="card card-outline card-info mb-3">
                    <div class="card-body py-2">
                        <form method="GET" action="<?= base_url('kmt/dca/rekap') ?>" class="form-inline flex-wrap" id="formFilter">
                            <div class="form-group mr-2 mb-2">
                                <label for="tglKasbon" class="mr-2 small">Tanggal Kasbon</label>
                                <select id="tglKasbon" name="tgl_kasbon" class="form-control form-control-sm" required <?= empty($tgl_kasbon_list)?'disabled':'' ?>>
                                    <?php if(empty($tgl_kasbon_list)): ?>
                                    <option value="">Belum ada tanggal kasbon</option>
                                    <?php else: ?>
                                    <?php foreach($tgl_kasbon_list as $tanggal):
                                        $tanggal_value=$tanggal['tgl_kasbon'];
                                        $tanggal_time=strtotime($tanggal_value);
                                        $tanggal_label=date('d',$tanggal_time).' '.$nama_bulan[(int)date('n',$tanggal_time)].' '.date('Y',$tanggal_time);
                                    ?>
                                    <option value="<?= htmlspecialchars($tanggal_value) ?>" <?= $tgl_kasbon===$tanggal_value?'selected':'' ?>><?= htmlspecialchars($tanggal_label) ?></option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm mb-2 mr-2" <?= empty($tgl_kasbon_list)?'disabled':'' ?>><i class="fas fa-filter mr-1"></i> Tampilkan</button>
                            <a href="<?= base_url('kmt/dca/rekap') ?>" class="btn btn-outline-secondary btn-sm mb-2 mr-2"><i class="fas fa-undo mr-1"></i> Reset Filter</a>
                            <button type="submit" form="formExport" class="btn btn-success btn-sm mb-2" <?= empty($grouped)?'disabled':'' ?>><i class="fas fa-file-excel mr-1"></i> Export Excel</button>
                        </form>
                        <div class="small text-muted">Isi No. Bukti pada lembar rekap di bawah sebelum melakukan export.</div>
                        <form method="POST" action="<?= base_url('kmt/dca/export_rekap') ?>" id="formExport">
                            <input type="hidden" name="tgl_kasbon" value="<?= htmlspecialchars($tgl_kasbon) ?>">
                        </form>
                    </div>
                </div>

                <?php if(empty($grouped)): ?>
                <div class="alert alert-info">
                    <strong>Tidak ada data</strong> dengan tanggal kasbon <?= htmlspecialchars(date('d-m-Y', strtotime($tgl_kasbon))) ?>.
                </div>
                <?php endif; ?>

                <?php
                $grand_um=0;$grand_biaya=0;
                $format_number=static function($value,$dash=true){return(float)$value!=0?number_format($value,0,',','.') :($dash?'-':'0');};
                foreach($grouped as $abm_nama=>$abm_data):
                    $abm_total_biaya=0;$total_bisi=0;$total_q235=0;$total_peserta=0;
                    foreach($abm_data['kegiatan'] as $kg){
                        $abm_total_biaya+=$kg['total_biaya'];$total_bisi+=$kg['total_bisi'];
                        $total_q235+=$kg['total_q235'];$total_peserta+=$kg['total_peserta'];
                    }
                    $grand_um+=$abm_data['um'];$grand_biaya+=$abm_total_biaya;$sisa=$abm_data['um']-$abm_total_biaya;
                    $time_periode=strtotime($tgl_kasbon);
                    $periode=$nama_bulan[(int)date('n',$time_periode)].' '.date('Y',$time_periode);
                    $uraian_um=$abm_data['um_label'];
                    $tanggal_list=$abm_data['tanggal_kasbon']?:$abm_data['tanggal_dca'];$tanggal_um='-';
                    if($tanggal_list){sort($tanggal_list);$time_um=strtotime($tanggal_list[0]);$tanggal_um=date('d',$time_um).' '.$nama_bulan[(int)date('n',$time_um)].' '.date('Y',$time_um);}
                ?>
                <div class="card rekap-sheet-card mb-4">
                    <div class="rekap-sheet-wrap"><div class="rekap-sheet">
                        <table aria-label="Rekapitulasi DCA <?= htmlspecialchars($abm_nama) ?>">
                            <colgroup><col style="width:37.43%"><col style="width:14.57%"><col style="width:11.71%"><col style="width:11.71%"><col style="width:9.14%"><col style="width:9.43%"><col style="width:12.29%"></colgroup>
                            <tbody>
                                <tr><td colspan="7" class="report-title">REKAPITULASI OPERASIONAL</td></tr>
                                <tr><td colspan="7" class="report-subtitle">DCA JAGUNG BISI 959, Q 235 (CLING)</td></tr>
                                <tr><td class="identity"><?= htmlspecialchars($abm_nama) ?></td><td class="summary-label">No Bukti</td><td colspan="5"><input type="text" name="no_bukti[]" form="formExport" class="no-bukti-input" placeholder="Wajib isi nomor bukti" aria-label="No Bukti <?= htmlspecialchars($abm_nama) ?>" maxlength="100" required></td></tr>
                                <tr><td class="identity">AREA BUSINESS MANAGER (ABM)</td><td class="summary-label">Total Biaya</td><td colspan="2"></td><td colspan="3" class="summary-value"><?= number_format($abm_total_biaya,0,',','.') ?></td></tr>
                                <tr><td class="identity"><?= htmlspecialchars($abm_data['wilayah']) ?></td><td class="summary-label">UM DCA</td><td colspan="2"><?= htmlspecialchars($tanggal_um) ?></td><td colspan="3" class="summary-value"><?= number_format($abm_data['um'],0,',','.') ?></td></tr>
                                <tr><td class="identity">Periode <?= htmlspecialchars($periode) ?></td><td class="summary-label">Sisa Dana</td><td colspan="2"></td><td colspan="3" class="summary-value negative-accent"><?= number_format($sisa,0,',','.') ?></td></tr>
                                <tr><td></td><td class="summary-label">Sisa Bulan Lalu</td><td colspan="2"></td><td colspan="3"></td></tr>
                                <tr class="spacer-row"><td colspan="7"></td></tr>
                                <tr class="column-header"><th>Nama Petugas &amp; Jenis Kegiatan</th><th>DCA Kas Bon</th><th>DS Bisi 959<br>(20 x 1Kg)</th><th>DS Q-235 CLING<br>(10 x 1Kg)</th><th>DS CP Produk</th><th>Jml Peserta</th><th>Tot Biaya</th></tr>
                                <tr class="section-row"><td><?= htmlspecialchars($abm_nama) ?></td><td colspan="6"></td></tr>
                                <tr class="section-row"><td>AREA BUSINESS MANAGER (ABM)</td><td colspan="6"></td></tr>
                                <tr class="section-row"><td><?= htmlspecialchars($uraian_um) ?></td><td colspan="6"></td></tr>
                                <tr class="section-row"><td><?= htmlspecialchars($abm_nama) ?></td><td class="num"><?= number_format($abm_data['um'],0,',','.') ?></td><td colspan="4"></td><td class="num">-</td></tr>
                                <tr class="section-row"><td><?= htmlspecialchars($uraian_um) ?> Total</td><td class="num"><?= number_format($abm_data['um'],0,',','.') ?></td><td colspan="4"></td><td class="num">-</td></tr>
                                <tr class="section-row"><td>AREA BUSINESS MANAGER (ABM) Total</td><td class="num"><?= number_format($abm_data['um'],0,',','.') ?></td><td colspan="4"></td><td class="num">-</td></tr>
                                <tr class="section-row"><td>MARKET DEVELOPMENT OFFICER (MDO)</td><td colspan="6"></td></tr>
                                <?php foreach($abm_data['kegiatan'] as $kg_nama=>$kg_data): ?>
                                <tr class="activity-row"><td><?= htmlspecialchars($kg_nama) ?></td><td colspan="6"></td></tr>
                                <?php foreach($kg_data['rows'] as $detail): ?>
                                <tr class="detail-row"><td><?= htmlspecialchars($detail['nama_mdo']) ?></td><td></td><td class="num"><?= $format_number($detail['qty_bisi']) ?></td><td class="num"><?= $format_number($detail['qty_q235']) ?></td><td class="num muted-dash">-</td><td class="num"><?= $format_number($detail['jml_peserta']) ?></td><td class="num"><?= $format_number($detail['real_biaya']) ?></td></tr>
                                <?php endforeach; ?>
                                <tr class="subtotal-row"><td><?= htmlspecialchars($kg_nama) ?> Total</td><td></td><td class="num"><?= $format_number($kg_data['total_bisi']) ?></td><td class="num"><?= $format_number($kg_data['total_q235']) ?></td><td class="num muted-dash">-</td><td class="num"><?= $format_number($kg_data['total_peserta']) ?></td><td class="num"><?= $format_number($kg_data['total_biaya']) ?></td></tr>
                                <?php endforeach; ?>
                                <?php $grand_total_biaya=min($abm_total_biaya,$abm_data['um']); ?>
                                <?php foreach([['MARKET DEVELOPMENT OFFICER (MDO) Total',null,$abm_total_biaya],[$abm_nama.' Total',$abm_data['um'],$abm_total_biaya],['Grand Total',$abm_data['um'],$grand_total_biaya]] as $i=>$total_row): ?>
                                <tr class="<?= $i===2?'grand-row':'total-row' ?>"><td><?= htmlspecialchars($total_row[0]) ?></td><td class="num"><?= $total_row[1]===null?'':number_format($total_row[1],0,',','.') ?></td><td class="num"><?= $format_number($total_bisi) ?></td><td class="num"><?= $format_number($total_q235) ?></td><td class="num muted-dash">-</td><td class="num"><?= $format_number($total_peserta) ?></td><td class="num"><?= $format_number($total_row[2]) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div></div>
                </div>
                <?php endforeach; ?>

                <?php if(!empty($grouped)): ?>
                <div class="card card-outline card-success mb-4"><div class="card-body py-2"><div class="row text-center">
                    <div class="col-md-4"><div class="info-box bg-info shadow-sm mb-0"><span class="info-box-icon"><i class="fas fa-money-bill"></i></span><div class="info-box-content"><span class="info-box-text">Total UM</span><span class="info-box-number">Rp <?= number_format($grand_um,0,',','.') ?></span></div></div></div>
                    <div class="col-md-4"><div class="info-box bg-warning shadow-sm mb-0"><span class="info-box-icon"><i class="fas fa-receipt"></i></span><div class="info-box-content"><span class="info-box-text">Total Biaya</span><span class="info-box-number">Rp <?= number_format($grand_biaya,0,',','.') ?></span></div></div></div>
                    <div class="col-md-4"><div class="info-box <?= ($grand_um-$grand_biaya)>=0?'bg-success':'bg-danger' ?> shadow-sm mb-0"><span class="info-box-icon"><i class="fas fa-balance-scale"></i></span><div class="info-box-content"><span class="info-box-text">Sisa Dana</span><span class="info-box-number">Rp <?= number_format($grand_um-$grand_biaya,0,',','.') ?></span></div></div></div>
                </div></div></div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <footer class="main-footer"><strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong> All rights reserved.<div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div></footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
