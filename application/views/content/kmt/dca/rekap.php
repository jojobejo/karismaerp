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
                                <label class="mr-1 small">Tahun</label>
                                <select name="tahun" class="form-control form-control-sm">
                                    <?php for($y=date('Y');$y>=2022;$y--): ?>
                                    <option value="<?= $y ?>" <?= $tahun==$y?'selected':'' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-1 small">Bulan</label>
                                <select name="bulan" class="form-control form-control-sm">
                                    <option value="0" <?= $bulan===''?'selected':'' ?>>Semua</option>
                                    <?php foreach($nama_bulan as $nb=>$nm):if(!$nb)continue; ?>
                                    <option value="<?= $nb ?>" <?= $bulan==$nb?'selected':'' ?>><?= $nm ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if($lv!=3): ?>
                            <div class="form-group mr-2 mb-2">
                                <label class="mr-1 small">Wilayah</label>
                                <select name="id_wilayah" class="form-control form-control-sm">
                                    <option value="">Semua</option>
                                    <?php foreach($wilayah_list as $w): ?>
                                    <option value="<?= $w['id'] ?>" <?= $id_wilayah==$w['id']?'selected':'' ?>><?= htmlspecialchars($w['nama_wilayah']) ?></option>
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
                                    <?php foreach($abm_list as $ab): ?>
                                    <option value="<?= htmlspecialchars($ab['abm']) ?>" <?= $abm==$ab['abm']?'selected':'' ?>><?= htmlspecialchars($ab['abm']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm mb-2 mr-2"><i class="fas fa-filter mr-1"></i> Filter</button>
                            <a href="<?= base_url('kmt/dca/rekap') ?>" class="btn btn-outline-secondary btn-sm mb-2 mr-2"><i class="fas fa-undo mr-1"></i> Reset Filter</a>
                            <a href="<?= base_url('kmt/dca/export_rekap').'?tahun='.$tahun.'&bulan='.$bulan.'&id_wilayah='.$id_wilayah.'&abm='.urlencode($abm) ?>" class="btn btn-success btn-sm mb-2"><i class="fas fa-file-excel mr-1"></i> Export Excel</a>
                        </form>
                    </div>
                </div>

                <?php if(empty($grouped)): ?>
                <?php
                    $selected_wilayah='semua wilayah';
                    foreach($wilayah_list as $wilayah){
                        if((string)$wilayah['id']===(string)$id_wilayah){$selected_wilayah=$wilayah['nama_wilayah'];break;}
                    }
                    $bulan_label=$bulan?$nama_bulan[(int)$bulan]:'semua bulan';
                    $available_labels=[];
                    foreach($available_months as $available_month){
                        if(isset($nama_bulan[$available_month]))$available_labels[]=$nama_bulan[$available_month];
                    }
                    $all_months_url=base_url('kmt/dca/rekap').'?tahun='.$tahun.'&bulan=0&id_wilayah='.$id_wilayah.'&abm='.urlencode($abm);
                ?>
                <div class="alert alert-info">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="mr-3 mb-2">
                            <strong>Tidak ada data <?= htmlspecialchars($bulan_label.' '.$tahun) ?></strong> untuk wilayah <?= htmlspecialchars($selected_wilayah) ?>.
                            <?php if($available_labels): ?><div class="small mt-1">Data tersedia pada bulan: <?= htmlspecialchars(implode(', ',$available_labels)) ?>.</div><?php endif; ?>
                        </div>
                        <?php if($bulan&&$available_labels): ?><a href="<?= $all_months_url ?>" class="btn btn-sm btn-info mb-2"><i class="fas fa-calendar-alt mr-1"></i> Tampilkan Semua Bulan</a><?php endif; ?>
                    </div>
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
                    $periode=$bulan?$nama_bulan[(int)$bulan].' '.$tahun:'Tahun '.$tahun;
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
                                <tr><td class="identity"><?= htmlspecialchars($abm_nama) ?></td><td class="summary-label">No Bukti</td><td colspan="5"></td></tr>
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
                                <?php foreach([['MARKET DEVELOPMENT OFFICER (MDO) Total',null],[$abm_nama.' Total',$abm_data['um']],['Grand Total',$abm_data['um']]] as $i=>$total_row): ?>
                                <tr class="<?= $i===2?'grand-row':'total-row' ?>"><td><?= htmlspecialchars($total_row[0]) ?></td><td class="num"><?= $total_row[1]===null?'':number_format($total_row[1],0,',','.') ?></td><td class="num"><?= $format_number($total_bisi) ?></td><td class="num"><?= $format_number($total_q235) ?></td><td class="num muted-dash">-</td><td class="num"><?= $format_number($total_peserta) ?></td><td class="num"><?= $format_number($abm_total_biaya) ?></td></tr>
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
