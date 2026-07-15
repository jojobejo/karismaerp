<?php
$access = isset($master_barang_access) && is_array($master_barang_access) ? $master_barang_access : [];
$canFullEdit = !empty($access['can_full_edit']);
$canInfoLainEdit = !empty($access['can_info_lain_edit']);
$jobdesk = isset($access['jobdesk']) ? (string)$access['jobdesk'] : '';
$dashboardUrl = base_url('dashboard/');
?>
<style>
    .master-barang-page .content-header {
        padding: 6px .5rem 0;
    }

    .master-barang-page .page-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .master-barang-page .page-home-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        background: #1788b8;
        color: #fff;
    }

    .master-barang-page .page-home-btn:hover {
        color: #fff;
        background: #126f96;
    }

    .master-barang-page .page-title {
        font-size: 32px;
        font-weight: 700;
        color: #3f4d63;
        margin: 0;
    }

    .master-barang-page .master-list-panel {
        background: #fff;
        border: 1px solid #d9e2ec;
        border-radius: 4px;
        overflow: hidden;
    }

    .master-barang-page .panel-heading {
        background: #1788b8;
        color: #fff;
        padding: 14px 16px;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .master-barang-page .panel-heading small {
        color: #d8f0fb;
        font-size: 13px;
        font-weight: 600;
    }

    .master-barang-page .search-box {
        padding: 14px 16px 8px;
        border-bottom: 1px solid #eef2f7;
    }

    .master-barang-page .search-box label {
        font-weight: 700;
        margin-right: 8px;
    }

    .master-barang-page .search-box input {
        width: calc(100% - 78px);
        display: inline-block;
    }

    .master-barang-page .master-list {
        max-height: 730px;
        overflow-y: auto;
        padding: 10px;
        background: #f6f9fc;
    }

    .master-barang-page .master-list-item {
        border: 1px solid #d9e2ec;
        background: #fff;
        padding: 12px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all .15s ease-in-out;
        display: flex;
        gap: 10px;
    }

    .master-barang-page .master-list-item.active {
        background: #d9edf9;
        border-color: #3c9dd0;
    }

    .master-barang-page .master-list-item:hover {
        border-color: #3c9dd0;
    }

    .master-barang-page .master-list-thumb {
        width: 64px;
        height: 64px;
        border: 1px solid #d9e2ec;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 64px;
    }

    .master-barang-page .master-list-thumb img,
    .master-barang-page .gambar-preview img {
        max-width: 100%;
        max-height: 100%;
    }

    .master-barang-page .master-list-meta {
        min-width: 0;
    }

    .master-barang-page .master-list-code {
        color: #1381c4;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .master-barang-page .master-list-name {
        font-size: 21px;
        font-weight: 700;
        color: #1f2d3d;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .master-barang-page .master-list-supplier {
        color: #68778a;
        font-size: 14px;
    }

    .master-barang-page .master-detail-panel {
        background: #fff;
        border: 1px solid #d9e2ec;
        padding: 24px 28px 28px;
        min-height: 840px;
    }

    .master-barang-page .detail-title {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 22px;
        color: #34495e;
    }

    .master-barang-page .form-grid {
        display: grid;
        grid-template-columns: 140px minmax(220px, 1fr) 140px minmax(220px, 1fr);
        gap: 14px 16px;
        align-items: center;
    }

    .master-barang-page .form-grid label,
    .master-barang-page .info-lain-grid label {
        margin: 0;
        font-weight: 700;
        color: #3e4a59;
    }

    .master-barang-page .info-lain-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        max-width: 700px;
        padding-top: 8px;
    }

    .master-barang-page .info-lain-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 12px;
    }

    .master-barang-page .info-lain-grid {
        display: grid;
        grid-template-columns: 86px 120px 30px;
        gap: 14px 10px;
        align-items: center;
    }

    .master-barang-page .gambar-preview {
        width: 330px;
        height: 210px;
        border: 1px dashed #cbd5e0;
        background: #fff;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .master-barang-page .nav-tabs {
        border-bottom: none;
        margin-top: 24px;
    }

    .master-barang-page .nav-tabs .nav-link {
        border: none;
        border-radius: 0;
        color: #263238;
        font-weight: 700;
        padding: 10px 14px;
        margin-right: 4px;
    }

    .master-barang-page .nav-tabs .nav-link.active {
        background: #1788b8;
        color: #fff;
    }

    .master-barang-page .tab-pane {
        padding-top: 18px;
        min-height: 280px;
    }

    .master-barang-page .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }

    .master-barang-page .action-right .btn,
    .master-barang-page .action-left .btn {
        min-width: 120px;
        border-radius: 0;
        font-weight: 700;
    }

    .master-barang-page .btn-master-primary {
        background: #1788b8;
        border-color: #1788b8;
        color: #fff;
    }

    .master-barang-page .btn-master-secondary {
        background: #1788b8;
        border-color: #1788b8;
        color: #fff;
    }

    .master-barang-page .readonly-note {
        font-size: 13px;
        color: #d35400;
        margin-top: 6px;
    }

    .master-barang-page .empty-state {
        color: #6c757d;
        text-align: center;
        padding: 32px 16px;
    }

    .master-barang-page .status-barang-control {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        font-weight: 700;
        color: #3e4a59;
    }

    .master-barang-page .status-barang-control .custom-control {
        min-height: auto;
    }

    .master-barang-page .kode-akun-layout {
        display: grid;
        grid-template-columns: 128px minmax(480px, 1fr);
        gap: 8px;
        max-width: 860px;
        border-top: 1px solid #e6eaef;
    }

    .master-barang-page .kode-akun-side,
    .master-barang-page .kode-akun-main {
        border: 1px solid #e6eaef;
        border-top: none;
        background: #fff;
    }

    .master-barang-page .kode-akun-side {
        align-self: start;
    }

    .master-barang-page .kode-akun-box {
        border-bottom: 1px solid #e6eaef;
        padding: 6px;
    }

    .master-barang-page .kode-akun-box:last-child {
        border-bottom: none;
    }

    .master-barang-page .kode-akun-box-title,
    .master-barang-page .kode-akun-main-title {
        font-weight: 600;
        margin-bottom: 8px;
        color: #212529;
    }

    .master-barang-page .kode-akun-check {
        display: block;
        margin: 12px 0;
        font-weight: 400;
    }

    .master-barang-page .kode-akun-main {
        padding: 6px 10px 14px;
    }

    .master-barang-page .kode-akun-row {
        display: grid;
        grid-template-columns: 122px 136px minmax(220px, 1fr);
        gap: 10px;
        align-items: center;
        margin-bottom: 7px;
    }

    .master-barang-page .kode-akun-row label {
        margin: 0;
        font-weight: 400;
        text-align: right;
        color: #212529;
    }

    .master-barang-page .kode-akun-input-wrap {
        position: relative;
    }

    .master-barang-page .kode-akun-input-wrap input {
        padding-right: 32px;
        text-align: right;
        background: #e9ecef;
    }

    .master-barang-page .kode-akun-lookup {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border-radius: 2px;
        background: #6c757d;
        color: #fff;
        font-size: 10px;
        line-height: 18px;
        text-align: center;
        pointer-events: none;
    }

    .master-barang-page .kode-akun-desc {
        color: #212529;
        white-space: nowrap;
    }

    @media (max-width: 991.98px) {
        .master-barang-page .form-grid {
            grid-template-columns: 1fr;
        }

        .master-barang-page .info-lain-layout {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .master-barang-page .master-detail-panel {
            margin-top: 16px;
        }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper master-barang-page">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6"></div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="page-title-row">
                        <a href="<?= $dashboardUrl ?>" class="page-home-btn" title="Dashboard">
                            <i class="fas fa-home"></i>
                        </a>
                        <h1 class="page-title">Master Barang</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="master-list-panel">
                                <div class="panel-heading">
                                    <span>Daftar Barang</span>
                                    <small id="masterBarangCountLabel">0 data</small>
                                </div>
                                <div class="search-box">
                                    <label for="masterBarangSearch">Search:</label>
                                    <input type="text" id="masterBarangSearch" class="form-control" placeholder="Cari kode / nama / supplier">
                                </div>
                                <div class="master-list" id="masterBarangList">
                                    <div class="empty-state">Memuat data master barang...</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div class="master-detail-panel">
                                <div class="detail-title">Data Barang / Persediaan</div>

                                <form id="formMasterBarangModern">
                                    <input type="hidden" id="master_id" name="id">

                                    <div class="form-grid">
                                        <label for="kode_barang">Kode Barang :</label>
                                        <input type="text" class="form-control" id="kode_barang" name="kode_barang">

                                        <label for="satuan">Satuan Dasar :</label>
                                        <div class="d-flex align-items-center">
                                            <input type="text" class="form-control mr-4" id="satuan" name="satuan" style="max-width:180px;">
                                            <div class="custom-control custom-checkbox mr-4">
                                                <input type="checkbox" class="custom-control-input" id="is_lot" name="is_lot" value="T">
                                                <label class="custom-control-label" for="is_lot">Pakai Lot</label>
                                            </div>
                                        </div>

                                        <label for="nama_barang">Deskripsi :</label>
                                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" style="grid-column: span 3;">

                                        <label for="kelompok_barang">Kelompok Barang :</label>
                                        <input type="text" class="form-control" id="kelompok_barang" name="kelompok_barang">

                                        <label>Status Barang :</label>
                                        <div class="status-barang-control">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="status_active" value="T">
                                                <label class="custom-control-label" for="status_active">Aktif</label>
                                            </div>
                                            <span>,</span>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="F">
                                                <label class="custom-control-label" for="is_active">Tidak Aktif</label>
                                            </div>
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs" id="masterBarangTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="tab-informasi-link" data-toggle="tab" href="#tab-informasi" role="tab">Informasi Barang</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab-kode-akun-link" data-toggle="tab" href="#tab-kode-akun" role="tab">Kode Akun dan HPP</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab-info-lain-link" data-toggle="tab" href="#tab-info-lain" role="tab">Info Lain</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab-gambar-link" data-toggle="tab" href="#tab-gambar" role="tab">Gambar</a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="tab-informasi" role="tabpanel">
                                            <div class="form-grid">
                                                <label for="kategori_barang">Kategori Barang :</label>
                                                <input type="text" class="form-control" id="kategori_barang" name="kategori_barang">

                                                <label for="bahan_aktif">Bahan Aktif :</label>
                                                <input type="text" class="form-control" id="bahan_aktif" name="bahan_aktif">

                                                <label for="merk_barang">Merk Barang :</label>
                                                <input type="text" class="form-control" id="merk_barang" name="merk_barang">

                                                <label for="kd_suplier">Supplier Utama :</label>
                                                <select class="form-control" id="kd_suplier" name="kd_suplier">
                                                    <option value="">Pilih Supplier</option>
                                                    <?php foreach (($supplier_options ?? []) as $supplier) : ?>
                                                        <option value="<?= html_escape($supplier->kd_suplier) ?>"><?= html_escape($supplier->nama_suplier) ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <label for="stock_minimum">Stok Minimal :</label>
                                                <input type="number" class="form-control" id="stock_minimum" name="stock_minimum" min="0">

                                                <label for="produk_fokus">Produk Fokus :</label>
                                                <input type="text" class="form-control" id="produk_fokus" name="produk_fokus">
                                            </div>
                                            <?php if (!$canFullEdit && $canInfoLainEdit) : ?>
                                                <div class="readonly-note">Akses `<?= html_escape($jobdesk) ?>` hanya dapat mengubah tab Info Lain. Informasi Barang tampil baca-saja.</div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="tab-pane fade" id="tab-kode-akun" role="tabpanel">
                                            <div class="kode-akun-layout">
                                                <div class="kode-akun-side">
                                                    <div class="kode-akun-box">
                                                        <div class="kode-akun-box-title">Sifat</div>
                                                        <label class="kode-akun-check"><input type="checkbox" checked disabled> Disimpan</label>
                                                        <label class="kode-akun-check"><input type="checkbox" checked disabled> Dibeli</label>
                                                        <label class="kode-akun-check"><input type="checkbox" checked disabled> Dijual</label>
                                                    </div>
                                                    <div class="kode-akun-box">
                                                        <div class="kode-akun-box-title">Harga Pokok</div>
                                                        <label class="kode-akun-check"><input type="checkbox" checked disabled> Average</label>
                                                        <label class="kode-akun-check"><input type="checkbox" disabled> FIFO</label>
                                                        <label class="kode-akun-check"><input type="checkbox" disabled> LIFO</label>
                                                    </div>
                                                </div>

                                                <div class="kode-akun-main">
                                                    <div class="kode-akun-main-title">Kode Akun</div>
                                                    <?php
                                                    $kodeAkunRows = [
                                                        ['Harga Pokok', '51030', 'Harga Pokok Penjualan # 3'],
                                                        ['Penjualan', '41032', 'A Penjualan Barang Dagangan'],
                                                        ['Persediaan', '14030', 'Persediaan # 3'],
                                                        ['Pengiriman Beli', '51032', 'A Biaya Ongkos Angkut Pembelian'],
                                                        ['Pengiriman Jual', '64030', 'A Biaya Ongkos Angkut Penjualan'],
                                                        ['Retur Penjualan', '41034', 'A Retur Penjualan'],
                                                    ];
                                                    ?>
                                                    <?php foreach ($kodeAkunRows as $akunRow) : ?>
                                                        <div class="kode-akun-row">
                                                            <label><?= html_escape($akunRow[0]) ?> :</label>
                                                            <div class="kode-akun-input-wrap">
                                                                <input type="text" class="form-control form-control-sm" value="<?= html_escape($akunRow[1]) ?>" readonly>
                                                                <span class="kode-akun-lookup"><i class="fas fa-table"></i></span>
                                                            </div>
                                                            <div class="kode-akun-desc"><?= html_escape($akunRow[2]) ?></div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="tab-info-lain" role="tabpanel">
                                            <div class="info-lain-layout">
                                                <div>
                                                    <div class="info-lain-section-title">Dimensi</div>
                                                    <div class="info-lain-grid">
                                                        <label for="panjang">Panjang :</label>
                                                        <input type="number" class="form-control" id="panjang" name="panjang" min="0">
                                                        <span>m</span>

                                                        <label for="lebar">Lebar :</label>
                                                        <input type="number" class="form-control" id="lebar" name="lebar" min="0">
                                                        <span>m</span>

                                                        <label for="tinggi">Tinggi :</label>
                                                        <input type="number" class="form-control" id="tinggi" name="tinggi" min="0">
                                                        <span>m</span>

                                                        <label for="berat">Berat :</label>
                                                        <input type="number" step="0.01" class="form-control" id="berat" name="berat" min="0">
                                                        <span>kg</span>
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="info-lain-section-title">Isi dan Kemasan</div>
                                                    <div class="info-lain-grid" style="grid-template-columns: 86px 190px;">
                                                        <label for="isi">Isi :</label>
                                                        <input type="number" step="0.01" class="form-control" id="isi" name="isi" min="0">

                                                        <label for="kemasan">Kemasan :</label>
                                                        <input type="number" step="0.01" class="form-control" id="kemasan" name="kemasan" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="tab-gambar" role="tabpanel">
                                            <div class="gambar-preview">
                                                <img src="<?= base_url('assets/images/Karisma.png') ?>" alt="Preview Barang">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="action-bar">
                                        <div class="action-left">
                                            <?php if ($canFullEdit) : ?>
                                                <button type="button" class="btn btn-master-secondary" id="btnBaruMasterBarang">Baru</button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="action-right">
                                            <button type="button" class="btn btn-secondary mr-2" id="btnBatalMasterBarang">Batal</button>
                                            <?php if ($canInfoLainEdit) : ?>
                                                <button type="submit" class="btn btn-master-primary" id="btnSimpanMasterBarang">Rekam</button>
                                            <?php endif; ?>
                                        </div>
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
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
</body>
