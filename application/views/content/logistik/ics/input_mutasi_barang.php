<style>
    .mutasi-page {
        min-height: calc(100vh - 56px);
        background: #f4f6f8;
        color: #0f1720;
        border-top: 3px solid #177fae;
        padding: 18px 22px 16px;
    }

    .mutasi-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
    }

    .mutasi-back-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #157fad;
        color: #fff;
        border-radius: 4px;
        text-decoration: none;
    }

    .mutasi-back-icon:hover,
    .mutasi-back-icon:focus {
        color: #fff;
        background: #0f6e98;
        text-decoration: none;
    }

    .mutasi-title {
        font-size: 24px;
        font-weight: 500;
        margin: 0;
    }

    .mutasi-header-grid {
        display: grid;
        grid-template-columns: 92px minmax(160px, 450px);
        gap: 6px 24px;
        align-items: center;
        max-width: 760px;
    }

    .mutasi-header-grid label {
        font-size: 16px;
        font-weight: 400;
        margin: 0;
    }

    .mutasi-field,
    .mutasi-select {
        height: 28px;
        min-height: 28px;
        border: 0;
        border-radius: 0;
        background: #e2e2e2;
        padding: 3px 8px;
        font-size: 14px;
    }

    .mutasi-date {
        max-width: 150px;
        color: #ff0000;
    }

    .mutasi-gudang-row {
        display: grid;
        grid-template-columns: minmax(150px, 1fr) 104px minmax(150px, 1fr) auto;
        gap: 16px;
        align-items: center;
        max-width: 590px;
    }

    .mutasi-table-wrap {
        margin-top: 10px;
        border-top: 1px solid #fff;
    }

    .mutasi-grid-table,
    .mutasi-modal-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        table-layout: fixed;
    }

    .mutasi-grid-table thead th,
    .mutasi-modal-table thead th {
        background: #157fad;
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        height: 40px;
        padding: 8px;
        border: 0;
    }

    .mutasi-grid-table tbody td,
    .mutasi-modal-table tbody td {
        background: #e5e5e5;
        height: 38px;
        padding: 7px 8px;
        border: 0;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mutasi-grid-table tbody tr.is-selected td,
    .mutasi-modal-table tbody tr.is-selected td {
        background: #6dbbe0;
        color: #fff;
    }

    .mutasi-grid-table tbody tr[data-id],
    .mutasi-modal-table tbody tr[data-row] {
        cursor: pointer;
    }

    .mutasi-grid-table .col-kode {
        width: 14%;
    }

    .mutasi-grid-table .col-nama {
        width: 28%;
    }

    .mutasi-grid-table .col-jumlah {
        width: 10%;
        text-align: right;
    }

    .mutasi-grid-table .col-satuan {
        width: 10%;
    }

    .mutasi-grid-table .col-lot {
        width: 14%;
    }

    .mutasi-grid-table .col-expired {
        width: 12%;
    }

    .mutasi-grid-table .col-gudang {
        width: 8%;
    }

    .mutasi-grid-table .col-action {
        width: 4%;
        text-align: center;
    }

    .mutasi-bottom-bar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        margin-top: 4px;
    }

    .mutasi-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .mutasi-btn {
        min-width: 96px;
        height: 27px;
        line-height: 15px;
        border-radius: 0;
        border: 0;
        background: #087bb0;
        color: #fff;
        font-size: 14px;
        padding: 6px 14px;
    }

    .mutasi-btn:hover,
    .mutasi-btn:focus {
        color: #fff;
        background: #066c9b;
    }

    .mutasi-btn-dark {
        background: #075983;
    }

    .mutasi-btn:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    .mutasi-check {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 16px;
        margin-right: 8px;
    }

    .mutasi-check input {
        width: 19px;
        height: 19px;
    }

    .mutasi-modal .modal-dialog {
        max-width: calc(100vw - 48px);
    }

    .mutasi-modal .modal-content {
        border-radius: 0;
        border: 0;
        background: #f6f7f8;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .25);
    }

    .mutasi-modal .modal-header,
    .mutasi-modal .modal-footer {
        border: 0;
    }

    .mutasi-modal .modal-title {
        font-size: 25px;
        font-weight: 500;
    }

    .mutasi-search {
        position: relative;
        width: 290px;
    }

    .mutasi-search input {
        width: 100%;
        height: 31px;
        border: 0;
        border-radius: 0;
        background: #e4e4e4;
        padding: 4px 34px 4px 8px;
    }

    .mutasi-search i {
        position: absolute;
        right: 10px;
        top: 9px;
        color: #bcc3c9;
    }

    .modal-footer-between {
        display: flex;
        justify-content: space-between;
        width: 100%;
        align-items: center;
        gap: 12px;
    }

    .modal-action-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .lot-summary {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 18px;
        align-items: center;
        padding: 0 10px 8px;
        font-size: 14px;
    }

    .qty-lot-input {
        width: 120px;
        height: 28px;
        border: 0;
        border-radius: 0;
        background: #fff;
        text-align: right;
        padding: 3px 8px;
    }

    .cell-editable {
        cursor: pointer;
    }

    .cell-editable:hover {
        background: #d4ecf7 !important;
    }

    .inline-qty-input,
    .inline-satuan-select {
        width: 100%;
        height: 28px;
        border: 1px solid #7ab8d5;
        border-radius: 0;
        background: #fff;
        padding: 3px 6px;
        font-size: 14px;
    }

    .inline-qty-input {
        text-align: right;
    }

    .mutasi-pagination {
        min-width: 210px;
        text-align: center;
        color: #59636e;
        font-size: 13px;
    }

    .mutasi-icon-btn {
        width: 28px;
        height: 28px;
        border: 0;
        border-radius: 0;
        background: #087bb0;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .mutasi-icon-btn:hover,
    .mutasi-icon-btn:focus {
        color: #fff;
        background: #066c9b;
    }

    .input-modal-dialog {
        max-width: 720px;
    }

    .input-modal-content {
        border: 0;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 16px 34px rgba(15, 23, 32, .28);
    }

    .input-modal-header {
        background: #157fad;
        color: #fff;
        border: 0;
        padding: 16px 20px;
    }

    .input-modal-header .modal-title {
        font-size: 22px;
        font-weight: 600;
    }

    .input-modal-subtitle {
        display: block;
        font-size: 13px;
        opacity: .9;
        margin-top: 2px;
    }

    .item-preview {
        background: #eef5f8;
        border-left: 4px solid #087bb0;
        padding: 12px 14px;
        margin-bottom: 14px;
    }

    .item-preview strong {
        display: block;
        font-size: 15px;
        color: #0f1720;
    }

    .item-preview span {
        color: #52606d;
        font-size: 13px;
    }

    .field-panel {
        background: #f7f9fb;
        border: 1px solid #d8e0e7;
        padding: 14px;
    }

    .input-grid {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 10px 14px;
        align-items: center;
    }

    .input-line {
        width: 100%;
        height: 32px;
        border: 0;
        border-radius: 0;
        background: #e4e4e4;
        padding: 5px 8px;
    }

    .qty-stock-panel {
        background: #0f1720;
        color: #fff;
        padding: 11px 14px;
        min-height: 58px;
    }

    .qty-stock-panel label {
        display: block;
        color: #a9d5e8;
        font-size: 12px;
        margin: 0;
    }

    .qty-stock-panel input {
        background: transparent;
        color: #fff;
        border: 0;
        padding: 0;
        height: auto;
        font-size: 24px;
        font-weight: 600;
    }

    .input-modal-footer {
        border: 0;
        background: #f4f6f8;
        padding: 14px 20px 18px;
    }

    @media (max-width: 768px) {
        .mutasi-page {
            padding: 14px 12px;
        }

        .mutasi-header-grid,
        .mutasi-gudang-row {
            display: block;
            max-width: none;
        }

        .mutasi-header-grid label {
            display: block;
            margin-top: 8px;
        }

        .mutasi-gudang-row .mutasi-select {
            width: 100%;
        }

        .mutasi-gudang-row .mutasi-btn {
            width: 100%;
            margin-top: 8px;
        }

        .mutasi-bottom-bar,
        .modal-footer-between,
        .lot-summary {
            display: block;
        }

        .mutasi-actions,
        .modal-action-group {
            margin-top: 8px;
            justify-content: flex-start;
        }

        .mutasi-search {
            width: 100%;
            margin-top: 8px;
        }

        .input-grid {
            display: block;
        }

        .input-grid label {
            display: block;
            margin-top: 10px;
        }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <section class="content mutasi-page">
                <div class="mutasi-title-row">
                    <a class="mutasi-back-icon" href="<?= base_url('ics/mutasi_barang') ?>" title="Kembali ke mutasi barang" aria-label="Kembali ke mutasi barang">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="mutasi-title">Pemindahan Barang Antar Gudang</h1>
                </div>

                <div class="mutasi-header-grid">
                    <label for="nofresnsi">Ref. :</label>
                    <input class="mutasi-field" type="text" id="nofresnsi" name="nofresnsi" value="<?= html_escape($ref_mutasi) ?>" readonly>

                    <label for="tgl_transaksi">Tanggal :</label>
                    <input class="mutasi-field mutasi-date" type="date" id="tgl_transaksi" name="tgl_transaksi" value="<?= html_escape($tanggal) ?>">

                    <label for="keterangan_mutasi">Keterangan :</label>
                    <input class="mutasi-field" type="text" id="keterangan_mutasi" name="keterangan_mutasi" value="Pindah Gudang">

                    <label>Dari Gudang :</label>
                    <div class="mutasi-gudang-row">
                        <select name="fromgdg" id="fromgdg" class="mutasi-select">
                            <?php foreach ($gudang as $gdg) : ?>
                                <option value="<?= (int) $gdg->id_gudang ?>"><?= html_escape($gdg->nama_gudang) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="tujuangdg" class="mb-0">Ke Gudang :</label>
                        <select name="tujuangdg" id="tujuangdg" class="mutasi-select">
                            <?php foreach ($gudang as $gdg) : ?>
                                <option value="<?= (int) $gdg->id_gudang ?>"><?= html_escape($gdg->nama_gudang) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="mutasi-btn" id="btnListBarangMutasi">List Barang</button>
                    </div>
                </div>

                <div class="mutasi-table-wrap">
                    <table class="mutasi-grid-table" id="input_tmp_mutasi">
                        <thead>
                            <tr>
                                <th class="col-kode">Kode</th>
                                <th class="col-nama">Nama Barang</th>
                                <th class="col-jumlah">Jumlah</th>
                                <th class="col-satuan">Satuan</th>
                                <th class="col-lot">No Lot</th>
                                <th class="col-expired">Expired Date</th>
                                <th class="col-gudang">Ke Gudang</th>
                                <th class="col-action">#</th>
                            </tr>
                        </thead>
                        <tbody id="mutasiRows"></tbody>
                    </table>
                </div>

                <div class="mutasi-bottom-bar">
                    <div>
                        <div>Perhatian : Jumlah Barang harus POSITIF untuk transfer antar gudang!!</div>
                        <button type="button" class="mutasi-btn mt-2" id="btnHapusBaris">Hapus Baris</button>
                    </div>
                    <div class="mutasi-actions">
                        <span id="barisInfo">Baris : 0</span>
                        <label class="mutasi-check mb-0"><input type="checkbox" id="cetakMutasi"> Cetak</label>
                        <button type="button" class="mutasi-btn" id="btnBatalMutasi">Batal</button>
                        <button type="button" class="mutasi-btn" id="btnRekamDraft">Rekam Draft</button>
                        <button type="button" class="mutasi-btn" id="rekammutasi">Rekam</button>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal fade mutasi-modal" id="modalBarangMutasi" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header align-items-start">
                        <h5 class="modal-title">Data Persediaan</h5>
                        <div class="ml-auto mutasi-search">
                            <input type="search" id="searchBarangMutasi" autocomplete="off">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    <div class="modal-body pt-0">
                        <table class="mutasi-modal-table" id="tableBarangMutasi">
                            <thead>
                                <tr>
                                    <th style="width: 32%;">Kode</th>
                                    <th>Deskripsi</th>
                                    <th style="width: 18%; text-align: right;">Tersedia</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-between">
                            <div class="modal-action-group">
                                <button type="button" class="mutasi-btn" id="btnBarangHapus">Hapus</button>
                            </div>
                            <span class="mutasi-pagination" id="barangPageInfo">Halaman 1 / 1</span>
                            <div class="modal-action-group">
                                <button type="button" class="mutasi-btn" id="btnBarangBaru">Baru</button>
                                <button type="button" class="mutasi-btn mutasi-btn-dark" disabled>Edit</button>
                                <button type="button" class="mutasi-btn" id="btnBarangPrev">Update</button>
                                <button type="button" class="mutasi-btn" data-dismiss="modal">Batal</button>
                                <button type="button" class="mutasi-btn" id="btnTambahBarangTmp">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade mutasi-modal" id="modalInputLotMutasi" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Lot</h5>
                    </div>
                    <div class="modal-body pt-0">
                        <input type="hidden" id="inputLotTmpId">
                        <table class="mutasi-modal-table" id="tableInputLotMutasi">
                            <thead>
                                <tr>
                                    <th>Lot ID</th>
                                    <th style="width: 32%;">Jumlah</th>
                                    <th style="width: 25%;">Satuan</th>
                                </tr>
                            </thead>
                            <tbody id="inputLotRows"></tbody>
                        </table>
                        <div class="lot-summary">
                            <span id="lotDiperlukan">Diperlukan : 0 Pcs</span>
                            <span>Total :</span>
                            <span id="lotTotal">0 Pcs</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-between">
                            <button type="button" class="mutasi-btn" id="btnHapusInputLot">Hapus</button>
                            <div class="modal-action-group">
                                <button type="button" class="mutasi-btn" data-dismiss="modal">Batal</button>
                                <button type="button" class="mutasi-btn" id="btnRekamInputLot">Rekam</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade mutasi-modal" id="modalLotBarangMutasi" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header align-items-start">
                        <h5 class="modal-title">Data Lot Barang</h5>
                        <div class="ml-auto mutasi-search">
                            <input type="search" id="searchLotBarang" autocomplete="off">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    <div class="modal-body pt-0">
                        <table class="mutasi-modal-table" id="tableLotBarangMutasi">
                            <thead>
                                <tr>
                                    <th>Lot ID</th>
                                    <th style="width: 32%;">Tgl. Exp.</th>
                                    <th style="width: 22%; text-align: right;">Tersedia</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-between">
                            <div class="modal-action-group">
                                <button type="button" class="mutasi-btn" disabled>Alias</button>
                                <button type="button" class="mutasi-btn" id="btnClearLotChoice">Hapus</button>
                            </div>
                            <span class="mutasi-pagination" id="lotPageInfo">Halaman 1 / 1</span>
                            <div class="modal-action-group">
                                <button type="button" class="mutasi-btn" id="btnLotPrev">Update</button>
                                <button type="button" class="mutasi-btn" data-dismiss="modal">Batal</button>
                                <button type="button" class="mutasi-btn" id="btnPilihLotBarang">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditBarangMutasi" tabindex="-1">
            <div class="modal-dialog input-modal-dialog modal-dialog-centered">
                <div class="modal-content input-modal-content">
                    <div class="modal-header input-modal-header">
                        <div>
                            <h5 class="modal-title mb-0">Edit Barang Mutasi</h5>
                            <span class="input-modal-subtitle">Update jumlah, no lot, dan expired date untuk list mutasi.</span>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editTmpId">
                        <input type="hidden" id="editKodeBarang">
                        <input type="hidden" id="editKodeBarangSystem">
                        <input type="hidden" id="editNamaBarang">
                        <input type="hidden" id="editSatuanId">
                        <div class="item-preview">
                            <strong id="editNamaBarangLabel">-</strong>
                            <span id="editKodeBarangLabel">-</span>
                        </div>
                        <div class="field-panel">
                            <div class="input-grid">
                                <label for="editQtyDiminta">Jumlah</label>
                                <input type="number" class="input-line" id="editQtyDiminta" min="1" step="1" value="1">

                                <label for="editNoLotSelect">No Lot</label>
                                <select id="editNoLotSelect" style="width:100%;"></select>

                                <label for="editExpiredSelect">Expired Date</label>
                                <select id="editExpiredSelect" style="width:100%;"></select>
                            </div>
                            <div class="qty-stock-panel mt-3">
                                <label for="editQtyStock">Qty Stock Tersedia</label>
                                <input type="text" id="editQtyStock" readonly value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer input-modal-footer">
                        <button type="button" class="mutasi-btn" data-dismiss="modal">Batal</button>
                        <button type="button" class="mutasi-btn" id="btnUpdateEditBarangMutasi">Update</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <script>
        $(function() {
            const defaultGudang = '<?= (int) $gudang_aktif ?>';
            const defaultTujuan = '<?= (int) ($gudang_tujuan_aktif ?? 0) ?>';
            const urls = {
                listTmp: '<?= base_url("ics/ajax_list_tmp_mutasi") ?>',
                listBarang: '<?= base_url("ics/ajax_list_barang_mutasi_gudang") ?>',
                addTmp: '<?= base_url("ics/ajax_add_tmp_mutasi") ?>',
                listLot: '<?= base_url("ics/ajax_lot_tmp_mutasi") ?>',
                updateTmp: '<?= base_url("ics/ajax_update_tmp_mutasi") ?>',
                updateField: '<?= base_url("ics/ajax_update_tmp_mutasi_field") ?>',
                lotSelect: '<?= base_url("ics/ajax_mutasi_lot_select2") ?>',
                expSelect: '<?= base_url("ics/ajax_mutasi_exp_select2") ?>',
                lotQty: '<?= base_url("ics/ajax_mutasi_lot_qty") ?>',
                deleteTmp: '<?= base_url("ics/ajax_delete_tmp_mutasi") ?>',
                rekam: '<?= base_url("ics/ajax_rekam_mutasi") ?>',
                listBarangPage: '<?= base_url("ics/mutasi_barang/list_barang") ?>'
            };
            const satuanOptions = <?= json_encode($satuan_options ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

            let draftRows = [];
            let selectedMainId = null;
            let selectedBarang = null;
            let selectedLot = null;
            let inputLotItem = null;
            let inputLotChoice = null;
            let editItem = null;
            let editLastQtyAlertAt = 0;
            let barangPage = 1;
            let barangTotalPages = 1;
            let lotPage = 1;
            let lotTotalPages = 1;
            let searchBarangTimer = null;
            let searchLotTimer = null;
            const mainMinRows = 5;
            const barangPerPage = 10;
            const lotPerPage = 10;

            function esc(value) {
                return $('<div>').text(value == null ? '' : value).html();
            }

            function qtyNumber(value) {
                return Number(value || 0);
            }

            function formatQty(value) {
                const number = qtyNumber(value);
                return Number.isInteger(number) ? number : number.toFixed(2);
            }

            function notify(icon, title, text) {
                if (window.Swal) {
                    Swal.fire({
                        icon: icon || 'info',
                        title: title || '',
                        text: text || '',
                        timer: icon === 'success' ? 1800 : undefined,
                        showConfirmButton: icon !== 'success'
                    });
                    return;
                }
                alert([title, text].filter(Boolean).join('\n'));
            }

            function toast(icon, title) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon || 'success',
                        title: title || '',
                        timer: 2100,
                        showConfirmButton: false
                    });
                    return;
                }
                alert(title || '');
            }

            function confirmAction(options, callback) {
                if (window.Swal) {
                    Swal.fire({
                        title: options.title || 'Konfirmasi',
                        text: options.text || '',
                        icon: options.icon || 'warning',
                        showCancelButton: true,
                        confirmButtonText: options.confirmText || 'Ya',
                        cancelButtonText: options.cancelText || 'Batal',
                        confirmButtonColor: options.confirmColor || '#087bb0'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            callback();
                        }
                    });
                    return;
                }

                if (confirm(options.title || 'Konfirmasi')) {
                    callback();
                }
            }

            function selectedGudangName() {
                return $('#tujuangdg option:selected').text() || '-';
            }

            function syncGudangTujuan() {
                const asal = $('#fromgdg').val();
                $('#tujuangdg option').prop('disabled', false).show();
                $('#tujuangdg option[value="' + asal + '"]').prop('disabled', true).hide();
                if ($('#tujuangdg').val() === asal) {
                    $('#tujuangdg').val($('#tujuangdg option:not(:disabled)').first().val() || '');
                }
            }

            function lockGudangAsal() {
                $('#fromgdg').prop('disabled', draftRows.length > 0);
            }

            function renderPlaceholderRows(startAt) {
                let html = '';
                const totalRows = Math.max(mainMinRows, startAt + 1);
                for (let i = startAt; i < totalRows; i++) {
                    html += '<tr data-empty="1"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
                }
                return html;
            }

            function renderMainRows(rows) {
                draftRows = rows || [];
                let html = '';

                if (draftRows.length && draftRows[0].gudang_asal) {
                    $('#fromgdg').val(draftRows[0].gudang_asal);
                    syncGudangTujuan();
                }

                draftRows.forEach(function(row) {
                    const isSelected = selectedMainId && Number(selectedMainId) === Number(row.id);
                    html += `
                        <tr data-id="${esc(row.id)}" class="${isSelected ? 'is-selected' : ''}">
                            <td>${esc(row.kd_barang || row.kode_barang_system || '')}</td>
                            <td title="${esc(row.nama_barang || '')}">${esc(row.nama_barang || '')}</td>
                            <td class="text-right cell-editable js-edit-qty" data-id="${esc(row.id)}">${esc(formatQty(row.qty))}</td>
                            <td class="cell-editable js-edit-satuan" data-id="${esc(row.id)}" data-satuan-id="${esc(row.satuan_id || '')}">${esc(row.satuan_nama || 'Pcs')}</td>
                            <td>${esc(row.no_lot || '')}</td>
                            <td>${esc(row.exp_date || '')}</td>
                            <td>${esc(selectedGudangName())}</td>
                            <td class="text-center">
                                <button type="button" class="mutasi-icon-btn btn-edit-mutasi" data-id="${esc(row.id)}" title="Edit barang mutasi">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>`;
                });

                html += renderPlaceholderRows(draftRows.length);
                $('#mutasiRows').html(html);
                $('#barisInfo').text('Baris : ' + draftRows.length);
                lockGudangAsal();
            }

            function loadTmpMutasi(callback) {
                $.getJSON(urls.listTmp, function(data) {
                    renderMainRows(data || []);
                    if (typeof callback === 'function') {
                        callback(data || []);
                    }
                }).fail(function() {
                    notify('error', 'Gagal memuat draft', 'Data temporary mutasi tidak dapat dimuat.');
                });
            }

            function findDraft(id) {
                for (let i = 0; i < draftRows.length; i++) {
                    if (Number(draftRows[i].id) === Number(id)) {
                        return draftRows[i];
                    }
                }
                return null;
            }

            function satuanOptionHtml(selectedId) {
                let html = '';
                satuanOptions.forEach(function(row) {
                    const id = String(row.id_satuan || '');
                    const selected = String(selectedId || '') === id ? ' selected' : '';
                    html += '<option value="' + esc(id) + '"' + selected + '>' + esc(row.nm_satuan || '') + '</option>';
                });
                return html;
            }

            function saveDraftField(id, field, value, successMessage) {
                return $.post(urls.updateField, {
                    id: id,
                    field: field,
                    value: value,
                    id_gudang: $('#fromgdg').val()
                }, function(res) {
                    if (!res.status) {
                        notify('warning', 'Data belum tersimpan', res.msg || 'Update tidak valid');
                        renderMainRows(draftRows);
                        return;
                    }

                    toast('success', successMessage || res.msg || 'Data diperbarui');
                    loadTmpMutasi();
                }, 'json').fail(function() {
                    notify('error', 'Gagal menyimpan data', 'Server tidak merespons.');
                    renderMainRows(draftRows);
                });
            }

            function startQtyEdit(cell) {
                const id = cell.data('id');
                const row = findDraft(id);
                if (!row || cell.find('input').length) {
                    return;
                }

                const input = $('<input type="number" class="inline-qty-input" min="1" step="1">').val(formatQty(row.qty));
                let committed = false;

                function commit() {
                    if (committed) {
                        return;
                    }
                    committed = true;
                    const qty = qtyNumber(input.val());
                    if (qty <= 0) {
                        notify('warning', 'Jumlah tidak valid', 'Jumlah harus lebih dari 0.');
                        renderMainRows(draftRows);
                        return;
                    }
                    saveDraftField(id, 'qty', qty, 'Jumlah diperbarui');
                }

                input.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        commit();
                    }
                    if (e.key === 'Escape') {
                        committed = true;
                        renderMainRows(draftRows);
                    }
                });
                input.on('blur', commit);

                cell.empty().append(input);
                input.trigger('focus').select();
            }

            function startSatuanEdit(cell) {
                const id = cell.data('id');
                const row = findDraft(id);
                if (!row || cell.find('select').length) {
                    return;
                }

                if (!satuanOptions.length) {
                    notify('warning', 'Data satuan kosong', 'Master tbpo_satuan belum memiliki data.');
                    return;
                }

                const select = $('<select class="inline-satuan-select"></select>').html(satuanOptionHtml(row.satuan_id));
                let committed = false;

                cell.empty().append(select);
                select.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownAutoWidth: true
                });

                select.on('select2:select', function() {
                    committed = true;
                    saveDraftField(id, 'satuan_id', $(this).val(), 'Satuan diperbarui');
                });

                select.on('select2:close', function() {
                    setTimeout(function() {
                        if (!committed) {
                            renderMainRows(draftRows);
                        }
                    }, 120);
                });

                select.select2('open');
            }

            function setSelect2Value(selector, value, text) {
                const select = $(selector);
                select.empty();
                if (value) {
                    const option = new Option(text || value, value, true, true);
                    select.append(option);
                }
                select.trigger('change');
            }

            function editItemParams() {
                return {
                    id_gudang: $('#fromgdg').val(),
                    kode_barang_system: $('#editKodeBarangSystem').val(),
                    nama_barang: $('#editNamaBarang').val()
                };
            }

            function resetEditLotFields() {
                setSelect2Value('#editNoLotSelect', '', '');
                setSelect2Value('#editExpiredSelect', '', '');
                $('#editQtyStock').val('0');
            }

            function loadEditQtyStock(showToast) {
                const noLot = $('#editNoLotSelect').val();
                const expDate = $('#editExpiredSelect').val();
                $('#editQtyStock').val('0');
                if (!noLot || !expDate) {
                    return;
                }

                $.getJSON(urls.lotQty, Object.assign(editItemParams(), {
                    no_lot: noLot,
                    exp_date: expDate
                }), function(res) {
                    const stock = qtyNumber(res.qty_gudang || 0);
                    $('#editQtyStock').val(formatQty(stock));
                    if (showToast) {
                        toast(stock > 0 ? 'success' : 'warning', 'Qty stock: ' + formatQty(stock));
                    }
                }).fail(function() {
                    notify('error', 'Gagal memuat qty stock', 'Server tidak merespons.');
                });
            }

            function openEditBarangModal(row) {
                editItem = row;
                selectedMainId = row.id;
                renderMainRows(draftRows);

                $('#editTmpId').val(row.id);
                $('#editKodeBarang').val(row.kd_barang || '');
                $('#editKodeBarangSystem').val(row.kode_barang_system || '');
                $('#editNamaBarang').val(row.nama_barang || '');
                $('#editSatuanId').val(row.satuan_id || 2);
                $('#editNamaBarangLabel').text(row.nama_barang || '-');
                $('#editKodeBarangLabel').text(row.kd_barang || row.kode_barang_system || '-');
                $('#editQtyDiminta').val(formatQty(row.qty || 1));
                resetEditLotFields();
                if (row.no_lot) {
                    setSelect2Value('#editNoLotSelect', row.no_lot, row.no_lot);
                }
                if (row.exp_date) {
                    setSelect2Value('#editExpiredSelect', row.exp_date, row.exp_date);
                    loadEditQtyStock(false);
                }
                $('#modalEditBarangMutasi').modal('show');
                toast('info', 'Edit barang mutasi');
            }

            function renderBarangRows(rows) {
                let html = '';
                if (!rows.length) {
                    html = '<tr><td colspan="3" class="text-center">Data tidak ditemukan</td></tr>';
                } else {
                    rows.forEach(function(row) {
                        html += `
                            <tr data-row="barang"
                                data-kode="${esc(row.kd_barang || '')}"
                                data-kode-system="${esc(row.kode_barang_system || '')}"
                                data-nama="${esc(row.nama_barang || '')}"
                                data-satuan="${esc(row.satuan_id || 2)}"
                                data-satuan-nama="${esc(row.satuan_nama || 'Pcs')}"
                                data-qty="${esc(row.qty || 0)}">
                                <td>${esc(row.kd_barang || row.kode_barang_system || '')}</td>
                                <td>${esc(row.nama_barang || '')}</td>
                                <td class="text-right">${esc(formatQty(row.qty))}</td>
                            </tr>`;
                    });
                }
                $('#tableBarangMutasi tbody').html(html);
            }

            function loadBarangModal(page) {
                barangPage = Math.max(1, page || 1);
                selectedBarang = null;
                $('#tableBarangMutasi tbody').html('<tr><td colspan="3" class="text-center">Memuat data...</td></tr>');

                $.getJSON(urls.listBarang, {
                    id_gudang: $('#fromgdg').val(),
                    term: $('#searchBarangMutasi').val(),
                    page: barangPage,
                    per_page: barangPerPage
                }, function(res) {
                    const data = res.data || [];
                    const pagination = res.pagination || {};
                    barangPage = Number(pagination.page || barangPage);
                    barangTotalPages = Number(pagination.total_pages || 1);
                    renderBarangRows(data);
                    $('#barangPageInfo').text('Halaman ' + barangPage + ' / ' + barangTotalPages + ' (' + Number(pagination.total_rows || data.length) + ' barang)');
                    $('#btnBarangPrev').prop('disabled', false);
                }).fail(function() {
                    notify('error', 'Gagal memuat barang', 'Data persediaan tidak dapat dimuat.');
                });
            }

            function openBarangModal() {
                if (!$('#fromgdg').val()) {
                    notify('warning', 'Dari Gudang wajib dipilih');
                    return;
                }
                $('#searchBarangMutasi').val('');
                barangPage = 1;
                $('#modalBarangMutasi').modal('show');
                loadBarangModal(1);
            }

            function openBarangPage() {
                if (!$('#fromgdg').val()) {
                    notify('warning', 'Dari Gudang wajib dipilih');
                    return;
                }

                if ($('#fromgdg').val() === $('#tujuangdg').val()) {
                    notify('warning', 'Gudang tidak valid', 'Dari Gudang dan Ke Gudang tidak boleh sama.');
                    return;
                }

                window.location.href = urls.listBarangPage +
                    '?fromgdg=' + encodeURIComponent($('#fromgdg').val()) +
                    '&tujuangdg=' + encodeURIComponent($('#tujuangdg').val());
            }

            function addSelectedBarang() {
                if (!selectedBarang) {
                    notify('warning', 'Pilih barang terlebih dahulu');
                    return;
                }

                if (qtyNumber(selectedBarang.qty) <= 0) {
                    notify('warning', 'Stock barang kosong');
                    return;
                }

                $.post(urls.addTmp, {
                    kode_barang: selectedBarang.kode,
                    kode_barang_system: selectedBarang.kodeSystem,
                    nama_barang: selectedBarang.nama,
                    exp_date: '',
                    no_lot: '',
                    qty: 1,
                    satuan_id: selectedBarang.satuan || 2,
                    gudang_asal: $('#fromgdg').val()
                }, function(res) {
                    if (!res.status) {
                        notify('error', 'Gagal menambah barang', res.msg || 'Data tidak valid');
                        return;
                    }

                    $('#modalBarangMutasi').modal('hide');
                    selectedMainId = res.id;
                    toast('success', 'Barang masuk draft');
                    loadTmpMutasi(function(rows) {
                        const row = findDraft(res.id) || rows[rows.length - 1];
                        if (row) {
                            openInputLotModal(row);
                        }
                    });
                }, 'json').fail(function() {
                    notify('error', 'Gagal menambah barang', 'Server tidak merespons.');
                });
            }

            function renderInputLotRows() {
                if (!inputLotItem) {
                    return;
                }

                const satuan = inputLotItem.satuan_nama || 'Pcs';
                const qty = qtyNumber((inputLotChoice && inputLotChoice.qty) || inputLotItem.qty || 1);
                let html = '';

                if (inputLotChoice && inputLotChoice.no_lot) {
                    html += `
                        <tr data-row="input-lot">
                            <td>${esc(inputLotChoice.no_lot)} / ${esc(inputLotChoice.exp_date || '')}</td>
                            <td><input type="number" class="qty-lot-input" id="qtyInputLot" min="1" max="${esc(inputLotChoice.qty_gudang || qty)}" value="${esc(qty)}"></td>
                            <td>${esc(satuan)}</td>
                        </tr>`;
                } else {
                    html += '<tr data-row="input-lot"><td></td><td></td><td></td></tr>';
                }

                for (let i = 0; i < 3; i++) {
                    html += '<tr data-row="input-lot"><td></td><td></td><td></td></tr>';
                }

                $('#inputLotRows').html(html);
                updateLotTotal();
            }

            function updateLotTotal() {
                if (!inputLotItem) {
                    return;
                }
                const satuan = inputLotItem.satuan_nama || 'Pcs';
                const qty = qtyNumber($('#qtyInputLot').val() || (inputLotChoice && inputLotChoice.qty) || inputLotItem.qty || 0);
                $('#lotDiperlukan').text('Diperlukan : ' + formatQty(qty) + ' ' + satuan);
                $('#lotTotal').text(formatQty(qty) + ' ' + satuan);
            }

            function openInputLotModal(row) {
                inputLotItem = row;
                inputLotChoice = null;
                selectedLot = null;
                selectedMainId = row.id;
                renderMainRows(draftRows);

                if (row.no_lot && row.exp_date) {
                    inputLotChoice = {
                        no_lot: row.no_lot,
                        exp_date: row.exp_date,
                        qty: row.qty,
                        qty_gudang: row.qty
                    };
                }

                $('#inputLotTmpId').val(row.id);
                renderInputLotRows();
                $('#modalInputLotMutasi').modal('show');
            }

            function renderLotBarangRows(rows) {
                let html = '';
                if (!rows.length) {
                    html = '<tr><td colspan="3" class="text-center">Data lot tidak ditemukan</td></tr>';
                } else {
                    rows.forEach(function(row) {
                        html += `
                            <tr data-row="lot"
                                data-lot="${esc(row.no_lot || '-')}"
                                data-exp="${esc(row.exp_date || '')}"
                                data-qty="${esc(row.qty_gudang || 0)}">
                                <td>${esc(row.no_lot || '-')}</td>
                                <td>${esc(row.exp_date || '')}</td>
                                <td class="text-right">${esc(formatQty(row.qty_gudang))}</td>
                            </tr>`;
                    });
                }
                $('#tableLotBarangMutasi tbody').html(html);
            }

            function loadLotBarangModal(page) {
                if (!inputLotItem) {
                    notify('warning', 'Pilih barang terlebih dahulu');
                    return;
                }

                lotPage = Math.max(1, page || 1);
                selectedLot = null;
                $('#tableLotBarangMutasi tbody').html('<tr><td colspan="3" class="text-center">Memuat data...</td></tr>');

                $.getJSON(urls.listLot, {
                    id: inputLotItem.id,
                    id_gudang: $('#fromgdg').val(),
                    term: $('#searchLotBarang').val(),
                    page: lotPage,
                    per_page: lotPerPage
                }, function(res) {
                    if (!res.status) {
                        notify('error', 'Gagal memuat lot', res.msg || 'Data tidak valid');
                        return;
                    }

                    const data = res.data || [];
                    const pagination = res.pagination || {};
                    lotPage = Number(pagination.page || lotPage);
                    lotTotalPages = Number(pagination.total_pages || 1);
                    renderLotBarangRows(data);
                    $('#lotPageInfo').text('Halaman ' + lotPage + ' / ' + lotTotalPages + ' (' + Number(pagination.total_rows || data.length) + ' lot)');
                    $('#btnLotPrev').prop('disabled', false);
                }).fail(function() {
                    notify('error', 'Gagal memuat lot', 'Server tidak merespons.');
                });
            }

            function openLotBarangModal() {
                if (!inputLotItem) {
                    notify('warning', 'Pilih barang terlebih dahulu');
                    return;
                }

                $('#searchLotBarang').val('');
                lotPage = 1;
                $('#modalLotBarangMutasi').modal('show');
                loadLotBarangModal(1);
            }

            function saveInputLotToDatabase(options) {
                options = options || {};
                if (!inputLotItem || !inputLotChoice) {
                    notify('warning', 'Pilih lot terlebih dahulu');
                    return;
                }

                const qty = qtyNumber($('#qtyInputLot').val() || inputLotChoice.qty || inputLotItem.qty || 0);
                const maxQty = qtyNumber(inputLotChoice.qty_gudang);

                if (qty <= 0) {
                    notify('warning', 'Qty tidak valid', 'Jumlah barang harus positif.');
                    return;
                }

                if (qty > maxQty) {
                    notify('warning', 'Qty tidak sesuai database', 'Jumlah yang diminta melebihi stok lot tersedia.');
                    return;
                }

                $.post(urls.updateTmp, {
                    id: inputLotItem.id,
                    id_gudang: $('#fromgdg').val(),
                    no_lot: inputLotChoice.no_lot,
                    exp_date: inputLotChoice.exp_date,
                    qty: qty,
                    satuan_id: inputLotItem.satuan_id || 2
                }, function(res) {
                    if (!res.status) {
                        notify('error', 'Gagal merekam lot', res.msg || 'Data tidak valid');
                        return;
                    }

                    inputLotChoice.qty = qty;
                    renderInputLotRows();
                    $('#modalLotBarangMutasi').modal('hide');
                    if (options.hideInputLot) {
                        $('#modalInputLotMutasi').modal('hide');
                    }
                    toast('success', options.message || 'Lot barang tersimpan');
                    loadTmpMutasi(function(rows) {
                        inputLotItem = findDraft(inputLotItem.id) || inputLotItem;
                    });
                }, 'json').fail(function() {
                    notify('error', 'Gagal merekam lot', 'Server tidak merespons.');
                });
            }

            function chooseSelectedLot() {
                if (!selectedLot) {
                    notify('warning', 'Pilih lot terlebih dahulu');
                    return;
                }

                const currentQty = qtyNumber(inputLotItem.qty || 1);
                const available = qtyNumber(selectedLot.qty_gudang);
                if (currentQty > available) {
                    notify('warning', 'Qty tidak sesuai database', 'Jumlah yang diminta melebihi stok lot tersedia.');
                    return;
                }

                inputLotChoice = {
                    no_lot: selectedLot.no_lot,
                    exp_date: selectedLot.exp_date,
                    qty_gudang: available,
                    qty: currentQty
                };
                renderInputLotRows();
                saveInputLotToDatabase({
                    message: 'Lot tersimpan ke input lot'
                });
            }

            function deleteTmpRow(id, afterDelete) {
                $.post(urls.deleteTmp, {
                    id: id
                }, function() {
                    if (Number(selectedMainId) === Number(id)) {
                        selectedMainId = null;
                    }
                    loadTmpMutasi(afterDelete);
                }).fail(function() {
                    notify('error', 'Gagal menghapus data', 'Server tidak merespons.');
                });
            }

            $('#fromgdg').val(defaultGudang);
            if (defaultTujuan !== '0' && defaultTujuan !== $('#fromgdg').val()) {
                $('#tujuangdg').val(defaultTujuan);
            }
            syncGudangTujuan();
            loadTmpMutasi();

            $('#editNoLotSelect').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalEditBarangMutasi'),
                placeholder: 'Cari no lot',
                allowClear: true,
                ajax: {
                    url: urls.lotSelect,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return Object.assign(editItemParams(), {
                            term: params.term || ''
                        });
                    }
                }
            });

            $('#editExpiredSelect').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalEditBarangMutasi'),
                placeholder: 'Pilih expired date',
                allowClear: true,
                ajax: {
                    url: urls.expSelect,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return Object.assign(editItemParams(), {
                            no_lot: $('#editNoLotSelect').val() || '',
                            term: params.term || ''
                        });
                    }
                }
            });

            $('#editNoLotSelect').on('change', function() {
                setSelect2Value('#editExpiredSelect', '', '');
                $('#editQtyStock').val('0');
            });

            $('#editNoLotSelect').on('select2:select', function(e) {
                toast('info', 'No Lot dipilih: ' + (e.params.data.text || e.params.data.id));
            });

            $('#editExpiredSelect').on('select2:select', function(e) {
                toast('info', 'Expired Date dipilih: ' + (e.params.data.text || e.params.data.id));
            });

            $('#editExpiredSelect').on('change', function() {
                loadEditQtyStock(true);
            });

            $('#editQtyDiminta').on('input', function() {
                const qty = qtyNumber($(this).val());
                const stock = qtyNumber($('#editQtyStock').val());
                if (stock > 0 && qty > stock && Date.now() - editLastQtyAlertAt > 1200) {
                    editLastQtyAlertAt = Date.now();
                    notify('warning', 'Stock tidak cukup', 'Qty yang diminta melebihi qty stock tersedia.');
                }
            });

            $('#fromgdg, #tujuangdg').on('change', function() {
                syncGudangTujuan();
                renderMainRows(draftRows);
            });

            $('#btnListBarangMutasi').on('click', openBarangPage);

            $('#mutasiRows').on('click', '.js-edit-qty', function(e) {
                e.stopPropagation();
                startQtyEdit($(this));
            });

            $('#mutasiRows').on('click', '.js-edit-satuan', function(e) {
                e.stopPropagation();
                startSatuanEdit($(this));
            });

            $('#mutasiRows').on('click', '.btn-edit-mutasi', function(e) {
                e.stopPropagation();
                const row = findDraft($(this).data('id'));
                if (!row) {
                    notify('warning', 'Data tidak ditemukan', 'Baris mutasi tidak dapat diedit.');
                    return;
                }
                openEditBarangModal(row);
            });

            $('#mutasiRows').on('click', 'tr[data-id]', function() {
                selectedMainId = $(this).data('id');
                renderMainRows(draftRows);
                toast('info', 'Baris dipilih untuk hapus');
            });

            $('#btnUpdateEditBarangMutasi').on('click', function() {
                if (!editItem) {
                    notify('warning', 'Pilih barang terlebih dahulu');
                    return;
                }

                const qty = qtyNumber($('#editQtyDiminta').val());
                const stock = qtyNumber($('#editQtyStock').val());
                if (qty <= 0) {
                    notify('warning', 'Qty tidak valid', 'Jumlah barang harus lebih dari 0.');
                    return;
                }
                if (!$('#editNoLotSelect').val() || !$('#editExpiredSelect').val()) {
                    notify('warning', 'Lot belum lengkap', 'No Lot dan Expired Date wajib dipilih.');
                    return;
                }
                if (qty > stock) {
                    notify('warning', 'Stock tidak cukup', 'Qty yang diminta melebihi qty stock tersedia.');
                    return;
                }

                confirmAction({
                    title: 'Update barang mutasi?',
                    text: 'Perubahan jumlah, lot, dan expired date akan disimpan ke draft.',
                    icon: 'question',
                    confirmText: 'Update'
                }, function() {
                    $.post(urls.updateTmp, {
                        id: $('#editTmpId').val(),
                        id_gudang: $('#fromgdg').val(),
                        no_lot: $('#editNoLotSelect').val(),
                        exp_date: $('#editExpiredSelect').val(),
                        qty: qty,
                        satuan_id: $('#editSatuanId').val() || 2
                    }, function(res) {
                        if (!res.status) {
                            notify('error', 'Gagal update barang', res.msg || 'Data tidak valid');
                            return;
                        }

                        $('#modalEditBarangMutasi').modal('hide');
                        toast('success', 'Barang mutasi diperbarui');
                        loadTmpMutasi();
                    }, 'json').fail(function() {
                        notify('error', 'Gagal update barang', 'Server tidak merespons.');
                    });
                });
            });

            $('#btnHapusBaris').on('click', function() {
                if (!selectedMainId) {
                    notify('warning', 'Pilih baris terlebih dahulu');
                    return;
                }

                confirmAction({
                    title: 'Hapus baris mutasi?',
                    text: 'Barang terpilih akan dihapus dari draft.',
                    confirmText: 'Hapus',
                    confirmColor: '#d33'
                }, function() {
                    deleteTmpRow(selectedMainId, function() {
                        toast('success', 'Baris dihapus');
                    });
                });
            });

            $('#btnBatalMutasi').on('click', function() {
                confirmAction({
                    title: 'Batalkan input mutasi?',
                    text: 'Layar akan kembali ke dashboard mutasi.',
                    confirmText: 'Batal Input'
                }, function() {
                    window.location.href = '<?= base_url("ics/mutasi_barang") ?>';
                });
            });

            $('#btnRekamDraft').on('click', function() {
                loadTmpMutasi(function() {
                    toast('success', 'Draft tersimpan otomatis');
                });
            });

            $('#searchBarangMutasi').on('input', function() {
                clearTimeout(searchBarangTimer);
                searchBarangTimer = setTimeout(function() {
                    loadBarangModal(1);
                }, 250);
            });

            $('#btnBarangBaru').on('click', function() {
                loadBarangModal(1);
            });

            $('#btnBarangPrev').on('click', function() {
                loadBarangModal(barangPage);
            });

            $('#btnBarangHapus').on('click', function() {
                selectedBarang = null;
                $('#tableBarangMutasi tbody tr').removeClass('is-selected');
            });

            $('#tableBarangMutasi').on('click', 'tr[data-row="barang"]', function() {
                $('#tableBarangMutasi tbody tr').removeClass('is-selected');
                $(this).addClass('is-selected');
                selectedBarang = {
                    kode: $(this).data('kode'),
                    kodeSystem: $(this).data('kode-system'),
                    nama: $(this).data('nama'),
                    satuan: $(this).data('satuan'),
                    satuanNama: $(this).data('satuan-nama'),
                    qty: $(this).data('qty')
                };
            });

            $('#tableBarangMutasi').on('dblclick', 'tr[data-row="barang"]', function() {
                $(this).trigger('click');
                addSelectedBarang();
            });

            $('#btnTambahBarangTmp').on('click', addSelectedBarang);

            $('#inputLotRows').on('click', 'tr[data-row="input-lot"]', function(e) {
                if ($(e.target).is('input')) {
                    return;
                }
                openLotBarangModal();
            });

            $('#inputLotRows').on('input', '#qtyInputLot', updateLotTotal);

            $('#btnHapusInputLot').on('click', function() {
                if (!inputLotItem) {
                    return;
                }

                confirmAction({
                    title: 'Hapus baris mutasi?',
                    text: 'Barang ini akan dihapus dari draft.',
                    confirmText: 'Hapus',
                    confirmColor: '#d33'
                }, function() {
                    deleteTmpRow(inputLotItem.id, function() {
                        $('#modalInputLotMutasi').modal('hide');
                        toast('success', 'Baris dihapus');
                    });
                });
            });

            $('#searchLotBarang').on('input', function() {
                clearTimeout(searchLotTimer);
                searchLotTimer = setTimeout(function() {
                    loadLotBarangModal(1);
                }, 250);
            });

            $('#btnLotPrev').on('click', function() {
                loadLotBarangModal(lotPage);
            });

            $('#btnClearLotChoice').on('click', function() {
                selectedLot = null;
                $('#tableLotBarangMutasi tbody tr').removeClass('is-selected');
            });

            $('#tableLotBarangMutasi').on('click', 'tr[data-row="lot"]', function() {
                $('#tableLotBarangMutasi tbody tr').removeClass('is-selected');
                $(this).addClass('is-selected');
                selectedLot = {
                    no_lot: $(this).data('lot'),
                    exp_date: $(this).data('exp'),
                    qty_gudang: $(this).data('qty')
                };
                chooseSelectedLot();
            });

            $('#btnPilihLotBarang').on('click', chooseSelectedLot);

            $('#btnRekamInputLot').on('click', function() {
                saveInputLotToDatabase({
                    hideInputLot: true,
                    message: 'Lot barang tersimpan'
                });
            });

            $('#rekammutasi').on('click', function() {
                if (!draftRows.length) {
                    notify('warning', 'Data mutasi kosong');
                    return;
                }

                if ($('#fromgdg').val() === $('#tujuangdg').val()) {
                    notify('warning', 'Gudang tidak valid', 'Dari Gudang dan Ke Gudang tidak boleh sama.');
                    return;
                }

                $.ajax({
                    url: urls.rekam,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        nofresnsi: $('#nofresnsi').val(),
                        tgl_transaksi: $('#tgl_transaksi').val(),
                        keterangan_mutasi: $('#keterangan_mutasi').val(),
                        fromgdg: $('#fromgdg').val(),
                        tujuangdg: $('#tujuangdg').val()
                    },
                    success: function(res) {
                        if (!res.status) {
                            notify('warning', 'Mutasi belum bisa direkam', res.msg || 'Data tidak valid');
                            return;
                        }

                        if (res.new_ref) {
                            $('#nofresnsi').val(res.new_ref);
                        }
                        selectedMainId = null;
                        toast('success', (res.msg || 'Mutasi berhasil direkam') + ' - ' + res.noreff);
                        loadTmpMutasi();
                    },
                    error: function() {
                        notify('error', 'Terjadi kesalahan sistem');
                    }
                });
            });
        });
    </script>
