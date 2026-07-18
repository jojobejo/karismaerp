<?php
$schemaReady = !empty($schema_ready);
$supportSchemaReady = !empty($support_schema_ready);
$summary = isset($summary) && is_array($summary) ? $summary : [];
$klasifikasiOptions = isset($klasifikasi_options) ? $klasifikasi_options : [];
$saldoNormalOptions = isset($saldo_normal_options) ? $saldo_normal_options : [];
$tipeKontrolOptions = isset($tipe_kontrol_options) ? $tipe_kontrol_options : [];
$supportCards = isset($support_cards) && is_array($support_cards) ? $support_cards : [];
?>
<style>
    .jurnal-page .content-header { padding: 6px .5rem 0; }
    .jurnal-page .page-title-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .jurnal-page .page-title-left { display: flex; align-items: center; gap: 10px; }
    .jurnal-page .page-home-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; background: #1788b8; color: #fff; }
    .jurnal-page .page-title { font-size: 30px; font-weight: 700; color: #34495e; margin: 0; }
    .jurnal-page .support-card-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .jurnal-page .report-card-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .jurnal-page .support-card-btn { width: 100%; min-height: 116px; text-align: left; border: 1px solid #d9e2ec; border-radius: 6px; background: #fff; padding: 14px; color: #1f2d3d; transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
    .jurnal-page .report-card-link { display: block; text-decoration: none; }
    .jurnal-page .report-card-link .support-card-btn { display: block; }
    .jurnal-page .support-card-btn:hover, .jurnal-page .support-card-btn:focus { border-color: #1788b8; box-shadow: 0 10px 22px rgba(23, 136, 184, .12); transform: translateY(-2px); outline: none; }
    .jurnal-page .support-card-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: #fff; background: #1788b8; margin-bottom: 10px; }
    .jurnal-page .support-card-title { display: block; font-weight: 800; font-size: 17px; }
    .jurnal-page .support-card-desc { display: block; color: #68778a; font-size: 13px; line-height: 1.35; margin-top: 4px; }
    .jurnal-page .list-panel, .jurnal-page .journal-panel { background: #fff; border: 1px solid #d9e2ec; border-radius: 4px; overflow: hidden; }
    .jurnal-page .panel-heading { background: #1788b8; color: #fff; padding: 13px 16px; font-weight: 700; display: flex; align-items: center; justify-content: space-between; }
    .jurnal-page .panel-heading-title-right { margin-left: auto; text-align: right; }
    .jurnal-page .list-toolbar { padding: 12px 14px; border-bottom: 1px solid #eef2f7; display: grid; grid-template-columns: 160px 1fr 42px; gap: 8px; align-items: center; }
    .jurnal-page .account-list { max-height: 690px; overflow-y: auto; padding: 10px; background: #f6f9fc; }
    .jurnal-page .account-item { border: 1px solid #d9e2ec; background: #fff; padding: 10px 12px; margin-bottom: 8px; cursor: pointer; border-left: 4px solid #b8c4d1; }
    .jurnal-page .account-item.active, .jurnal-page .account-item:hover { border-color: #1788b8; border-left-color: #1788b8; background: #edf8fc; }
    .jurnal-page .account-item-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .jurnal-page .account-code { font-weight: 800; color: #1788b8; }
    .jurnal-page .account-name { color: #1f2d3d; font-weight: 700; font-size: 16px; line-height: 1.25; }
    .jurnal-page .account-meta { color: #68778a; font-size: 13px; margin-top: 3px; }
    .jurnal-page .account-actions { display: inline-flex; gap: 5px; flex: 0 0 auto; }
    .jurnal-page .account-icon-btn { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .jurnal-page .journal-panel { min-height: 740px; }
    .jurnal-page .journal-account-head { padding: 14px 16px; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .jurnal-page .journal-account-title { font-size: 21px; font-weight: 800; color: #1f2d3d; line-height: 1.2; }
    .jurnal-page .journal-account-meta { color: #68778a; font-size: 13px; margin-top: 4px; }
    .jurnal-page .journal-table-wrap { padding: 12px 16px 16px; overflow-x: auto; }
    .jurnal-page .journal-table { width: 100%; min-width: 780px; }
    .jurnal-page .journal-table th { background: #1788b8; color: #fff; border-color: #1788b8; white-space: nowrap; }
    .jurnal-page .journal-table td { vertical-align: middle; }
    .jurnal-page .money-cell { text-align: right; white-space: nowrap; }
    .jurnal-page .detail-title { font-size: 22px; font-weight: 700; color: #34495e; margin-bottom: 18px; }
    .jurnal-page .form-grid { display: grid; grid-template-columns: 150px minmax(220px, 1fr) 150px minmax(220px, 1fr); gap: 13px 14px; align-items: center; }
    .jurnal-page .form-grid label { margin: 0; font-weight: 700; color: #3e4a59; }
    .jurnal-page .status-row { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
    .jurnal-page .action-bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 18px; padding-top: 16px; border-top: 1px solid #e6eaef; }
    .jurnal-page .btn-jurnal-primary { background: #1788b8; border-color: #1788b8; color: #fff; font-weight: 700; }
    .jurnal-page .empty-state { padding: 30px 12px; text-align: center; color: #68778a; }
    .jurnal-page .master-modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .jurnal-page .master-modal-grid .full { grid-column: 1 / -1; }
    .jurnal-page .master-row { border: 1px solid #e1e8f0; border-radius: 4px; padding: 8px 10px; margin-bottom: 8px; cursor: pointer; }
    .jurnal-page .master-row:hover, .jurnal-page .master-row.active { border-color: #1788b8; background: #edf8fc; }
    .jurnal-page .master-row-title { font-weight: 800; color: #1788b8; }
    .jurnal-page .master-row-meta { font-size: 13px; color: #68778a; }
    .jurnal-page .sales-journal-panel { background: #fff; border: 1px solid #d9e2ec; border-radius: 4px; overflow: hidden; margin-bottom: 14px; }
    .jurnal-page .sales-journal-toolbar { display: flex; gap: 10px; align-items: center; justify-content: space-between; padding: 12px 14px; background: #f8fafc; border-bottom: 1px solid #e6edf5; }
    .jurnal-page .sales-journal-search { width: min(360px, 100%); }
    .jurnal-page .zahir-table { width: 100%; margin: 0; }
    .jurnal-page .zahir-table th { background: #1788b8; color: #fff; border-color: #1788b8; white-space: nowrap; }
    .jurnal-page .zahir-table td { vertical-align: middle; background: #f1f3f5; border-top: 6px solid #fff; }
    .jurnal-page .zahir-table tr:hover td { background: #d8eef8; cursor: pointer; }
    .jurnal-page .zahir-table .money-cell { font-weight: 700; }
    .jurnal-page .zahir-detail-head { display: flex; gap: 22px; align-items: center; font-size: 18px; font-weight: 800; color: #020617; border-bottom: 1px solid #111827; padding-bottom: 8px; margin-bottom: 8px; }
    .jurnal-page .zahir-detail-table td { border: 0; padding: 7px 8px; background: #f1f3f5; }
    .jurnal-page .zahir-total-row { border-top: 1px solid #111827; margin-top: 8px; padding-top: 8px; font-weight: 800; }
    @media (max-width: 991.98px) {
        .jurnal-page .support-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .jurnal-page .report-card-grid { grid-template-columns: 1fr; }
        .jurnal-page .form-grid { grid-template-columns: 1fr; }
        .jurnal-page .journal-panel { margin-top: 14px; }
        .jurnal-page .master-modal-grid { grid-template-columns: 1fr; }
        .jurnal-page .list-toolbar { grid-template-columns: 1fr; }
        .jurnal-page .sales-journal-toolbar { align-items: stretch; flex-direction: column; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper jurnal-page">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header"><div class="container-fluid"></div></div>

            <section class="content">
                <div class="container-fluid">
                    <div class="page-title-row">
                        <div class="page-title-left">
                            <a href="<?= base_url('dashboard') ?>" class="page-home-btn" title="Dashboard"><i class="fas fa-home"></i></a>
                            <h1 class="page-title">Jurnal</h1>
                        </div>
                        <button type="button" class="btn btn-jurnal-primary" id="btnHeaderNewAccount" <?= !$schemaReady ? 'disabled' : '' ?>>
                            <i class="fas fa-plus mr-1"></i> Tambah Akun Jurnal
                        </button>
                    </div>

                    <?php if (!$schemaReady) : ?>
                        <div class="alert alert-warning">
                            <strong>Schema accounting belum tersedia.</strong>
                            Jalankan SQL migration `docs/database/accounting_jurnal_accounts_20260713.sql` sebelum memakai CRUD akun jurnal.
                        </div>
                    <?php endif; ?>

                    <?php if ($schemaReady && !$supportSchemaReady) : ?>
                        <div class="alert alert-info">
                            <strong>Master pendukung belum lengkap.</strong>
                            Jalankan SQL `docs/database/accounting_jurnal_master_options_20260713.sql` agar Saldo Normal dan Tipe Kontrol dapat dikelola via CRUD.
                        </div>
                    <?php endif; ?>



                    <div class="support-card-grid">
                        <?php foreach ($supportCards as $card) : ?>
                            <button type="button" class="support-card-btn btn-open-master" data-master="<?= html_escape($card['key']) ?>" <?= !$schemaReady ? 'disabled' : '' ?>>
                                <span class="support-card-icon"><i class="<?= html_escape($card['icon']) ?>"></i></span>
                                <span class="support-card-title"><?= html_escape($card['title']) ?></span>
                                <span class="support-card-desc"><?= html_escape($card['description']) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="report-card-grid">
                        <a href="<?= base_url('jurnal/neraca') ?>" class="report-card-link">
                            <span class="support-card-btn">
                                <span class="support-card-icon"><i class="fas fa-balance-scale"></i></span>
                                <span class="support-card-title">Neraca</span>
                                <span class="support-card-desc">Sajian aset, kewajiban, ekuitas, dan laba/rugi berjalan untuk audit posisi keuangan.</span>
                            </span>
                        </a>
                        <a href="<?= base_url('jurnal/laba-rugi') ?>" class="report-card-link">
                            <span class="support-card-btn">
                                <span class="support-card-icon"><i class="fas fa-chart-line"></i></span>
                                <span class="support-card-title">Laba Rugi</span>
                                <span class="support-card-desc">Sajian pendapatan, beban, laba kotor, laba operasional, dan laba bersih periode.</span>
                            </span>
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="list-panel">
                                <div class="panel-heading">
                                    <small id="accountCountLabel">0 data</small>
                                    <span class="panel-heading-title-right">Daftar Akun</span>
                                </div>
                                <div class="list-toolbar">
                                    <select class="form-control" id="accountKlasifikasiFilter" <?= !$schemaReady ? 'disabled' : '' ?>>
                                        <option value="">Semua Klasifikasi</option>
                                        <?php foreach ($klasifikasiOptions as $item) : ?>
                                            <option value="<?= (int)$item->id_klasifikasi ?>">
                                                <?= html_escape($item->nama_klasifikasi) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" class="form-control" id="accountSearch" placeholder="Search">
                                    <button type="button" class="btn btn-jurnal-primary account-icon-btn" id="btnListNewAccount" title="Tambah akun jurnal" <?= !$schemaReady ? 'disabled' : '' ?>>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="account-list" id="accountList">
                                    <div class="empty-state">Memuat data akun...</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="journal-panel">
                                <div class="panel-heading">
                                    <span>Form Jurnal</span>
                                    <span id="journalCountLabel">0 data</span>
                                </div>
                                <div class="journal-account-head">
                                    <div>
                                        <div class="journal-account-title" id="selectedAccountTitle">Pilih akun</div>
                                        <div class="journal-account-meta" id="selectedAccountMeta">Data jurnal akan menyesuaikan akun pada daftar.</div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnEditSelectedAccount" disabled>
                                        <i class="fas fa-edit mr-1"></i> Detail Akun
                                    </button>
                                </div>
                                <div class="journal-table-wrap">
                                    <table class="table table-striped table-bordered journal-table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>No Referensi</th>
                                                <th>Catatan</th>
                                                <th class="text-right">Debet</th>
                                                <th class="text-right">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="journalRows">
                                            <tr><td colspan="5" class="text-center text-muted">Pilih akun untuk melihat data jurnal.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>

        <div class="modal fade" id="modalJurnalMaster" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalJurnalMasterTitle">Master</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Data Master</strong>
                                    <button type="button" class="btn btn-sm btn-secondary" id="btnMasterNew">Baru</button>
                                </div>
                                <div id="masterRows"><div class="empty-state">Pilih master.</div></div>
                            </div>
                            <div class="col-lg-7">
                                <form id="formJurnalMaster">
                                    <input type="hidden" id="master_key" name="master_key">
                                    <input type="hidden" id="master_id" name="id">
                                    <div id="masterFormFields" class="master-modal-grid"></div>
                                    <div class="action-bar">
                                        <button type="button" class="btn btn-danger" id="btnMasterDelete" disabled>Hapus</button>
                                        <button type="submit" class="btn btn-jurnal-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalJurnalAccount" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalJurnalAccountTitle">Form Akun Jurnal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formJurnalAccount">
                            <input type="hidden" id="id_akun" name="id_akun">

                            <div class="form-grid">
                                <label for="kode_akun">Kode Akun :</label>
                                <input type="text" class="form-control" id="kode_akun" name="kode_akun" maxlength="30" <?= !$schemaReady ? 'disabled' : '' ?>>

                                <label for="nama_akun">Nama Akun :</label>
                                <input type="text" class="form-control" id="nama_akun" name="nama_akun" maxlength="150" <?= !$schemaReady ? 'disabled' : '' ?>>

                                <label for="id_klasifikasi">Klasifikasi :</label>
                                <select class="form-control" id="id_klasifikasi" name="id_klasifikasi" <?= !$schemaReady ? 'disabled' : '' ?>>
                                    <option value="">Pilih Klasifikasi</option>
                                    <?php foreach ($klasifikasiOptions as $item) : ?>
                                        <option value="<?= (int)$item->id_klasifikasi ?>" data-saldo="<?= html_escape($item->saldo_normal) ?>">
                                            <?= html_escape($item->kode_klasifikasi . ' - ' . $item->nama_klasifikasi) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="parent_id">Parent :</label>
                                <select class="form-control" id="parent_id" name="parent_id" <?= !$schemaReady ? 'disabled' : '' ?>>
                                    <option value="">Tanpa Parent</option>
                                </select>

                                <label for="saldo_normal">Saldo Normal :</label>
                                <select class="form-control" id="saldo_normal" name="saldo_normal" <?= !$schemaReady ? 'disabled' : '' ?>>
                                    <?php foreach ($saldoNormalOptions as $item) : ?>
                                        <option value="<?= html_escape($item->kode_saldo) ?>"><?= html_escape($item->nama_saldo . ' (' . $item->kode_saldo . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label for="tipe_akun">Tipe Akun :</label>
                                <select class="form-control" id="tipe_akun" name="tipe_akun" <?= !$schemaReady ? 'disabled' : '' ?>>
                                    <option value="HEADER">HEADER</option>
                                    <option value="POSTING">POSTING</option>
                                </select>

                                <label for="tipe_kontrol">Tipe Kontrol :</label>
                                <select class="form-control" id="tipe_kontrol" name="tipe_kontrol" <?= !$schemaReady ? 'disabled' : '' ?>>
                                    <?php foreach ($tipeKontrolOptions as $item) : ?>
                                        <option value="<?= html_escape($item->kode_tipe_kontrol) ?>"><?= html_escape($item->nama_tipe_kontrol . ' (' . $item->kode_tipe_kontrol . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <label>Status :</label>
                                <div class="status-row">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" checked <?= !$schemaReady ? 'disabled' : '' ?>>
                                        <label class="custom-control-label" for="is_active">Aktif</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="allow_manual_journal" <?= !$schemaReady ? 'disabled' : '' ?>>
                                        <label class="custom-control-label" for="allow_manual_journal">Boleh Jurnal Manual</label>
                                    </div>
                                </div>
                            </div>

                            <div class="action-bar">
                                <div>
                                    <button type="button" class="btn btn-secondary" id="btnNewAccount" <?= !$schemaReady ? 'disabled' : '' ?>>Baru</button>
                                    <button type="button" class="btn btn-danger" id="btnDeleteAccount" disabled>Hapus</button>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-warning mr-2" id="btnDeactivateAccount" disabled>Nonaktifkan</button>
                                    <button type="submit" class="btn btn-jurnal-primary" id="btnSaveAccount" <?= !$schemaReady ? 'disabled' : '' ?>>Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalSalesJournal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="zahir-detail-head">
                            <span id="salesJournalRef">SJ</span>
                            <span id="salesJournalDate">-</span>
                            <span id="salesJournalTitle">Penjualan</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm zahir-detail-table">
                                <tbody id="salesJournalDetailRows">
                                    <tr><td class="text-muted">Memuat detail jurnal...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row zahir-total-row">
                            <div class="col-sm-6">Total Debit = <span id="salesJournalDebit">Rp 0</span></div>
                            <div class="col-sm-6 text-sm-right">Total Kredit = <span id="salesJournalKredit">Rp 0</span></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">Diinput oleh : <span id="salesJournalUser">-</span></div>
                            <div>
                                <button type="button" class="btn btn-jurnal-primary mr-2" onclick="window.print()">Print</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
