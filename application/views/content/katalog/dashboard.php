<style>
    .content-wrapper {
        background: linear-gradient(180deg, #f7f8fb 0%, #eef2f7 100%);
    }

    .katalog-page {
        padding: 0 8px 24px;
    }

    .stats-row .info-box {
        min-height: 92px;
        border: 0;
        border-radius: 16px;
        overflow: hidden;
    }

    .stats-row .info-box-icon {
        min-height: 92px;
    }

    .stats-row .info-box-content {
        padding: 14px 14px 14px 12px;
    }

    .stats-row .info-box-text {
        font-size: 12px;
        white-space: normal;
        line-height: 1.3;
    }

    .stats-row .info-box-number {
        font-size: 24px;
        line-height: 1.1;
    }

    .filter-card,
    .table-card,
    .mobile-card-shell {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 16px 36px rgba(34, 64, 98, 0.08);
    }

    .filter-card .card-body,
    .table-card .card-body,
    .mobile-card-shell .card-body {
        padding: 1rem;
        background: #fff;
    }

    .filter-section {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-label {
        min-width: 120px;
        margin: 0;
        font-size: 13px;
        color: #44576a;
        padding-top: 6px;
    }

    .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .filter-group .btn {
        border-radius: 999px;
        padding: 6px 14px;
        font-weight: 600;
        box-shadow: none;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 14px;
        border: 1px solid #dfe7ef;
        background: #fff;
    }

    .desktop-table {
        width: 100% !important;
        min-width: 980px;
    }

    .desktop-table td,
    .desktop-table th {
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
    }

    .tcenter,
    .dt-body-center {
        text-align: center;
    }

    .table-striped>tbody>tr:nth-child(2n+1)>td,
    .table-striped>tbody>tr:nth-child(2n+1)>th {
        background-color: #FFFBF5;
    }

    .desktop-action-group,
    .mobile-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .desktop-action-group {
        justify-content: center;
    }

    .desktop-action-group .btn,
    .mobile-action-group .btn {
        border-radius: 12px;
        min-width: 40px;
        box-shadow: 0 10px 22px rgba(96, 150, 180, 0.12);
    }

    .desktop-action-group .btn:hover,
    .mobile-action-group .btn:hover {
        box-shadow: 0 14px 28px rgba(96, 150, 180, 0.18);
    }

    .mobile-katalog {
        display: none;
    }

    .mobile-list-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 0.9rem;
    }

    .mobile-list-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #28435a;
    }

    .mobile-list-subtitle {
        margin: 0;
        font-size: 12px;
        color: #708496;
    }

    .mobile-card-list {
        display: grid;
        gap: 14px;
    }

    .katalog-mobile-card {
        border: 1px solid #dfe7ef;
        border-radius: 18px;
        padding: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .mobile-card-top {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .mobile-card-image {
        width: 78px;
        height: 78px;
        flex: 0 0 78px;
        border-radius: 16px;
        object-fit: cover;
        border: 1px solid #dbe5ee;
        background: #fff;
    }

    .mobile-card-main {
        min-width: 0;
        flex: 1 1 auto;
    }

    .mobile-focus-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        margin-bottom: 8px;
        font-size: 11px;
        font-weight: 700;
        color: #31526d;
        background: #e9f3fa;
    }

    .mobile-card-name {
        margin: 0 0 6px;
        font-size: 15px;
        line-height: 1.35;
        color: #22394f;
        word-break: break-word;
    }

    .mobile-card-code,
    .mobile-card-supplier {
        margin: 0;
        font-size: 12px;
        color: #6b8092;
        line-height: 1.45;
        word-break: break-word;
    }

    .mobile-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .mobile-meta-item {
        padding: 10px 12px;
        border-radius: 14px;
        background: #f4f8fb;
        border: 1px solid #e4edf5;
    }

    .mobile-meta-label {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #7b8e9f;
    }

    .mobile-meta-value {
        font-size: 13px;
        font-weight: 600;
        color: #28435a;
        word-break: break-word;
    }

    .mobile-status-group {
        margin-top: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .mobile-status-group .badge {
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    .mobile-action-group {
        margin-top: 14px;
    }

    .mobile-action-group .btn {
        flex: 1 1 calc(50% - 8px);
        padding: 10px 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .mobile-empty {
        display: none;
        padding: 18px 14px;
        border-radius: 16px;
        background: #f7fafc;
        border: 1px dashed #ccd9e5;
        color: #607587;
        text-align: center;
        font-size: 13px;
    }

    .modal-dialog {
        margin: 1rem;
    }

    @media (max-width: 991.98px) {
        .katalog-page {
            padding: 0 4px 18px;
        }

        .stats-row {
            margin-top: 0.75rem;
        }

        .stats-row .info-box {
            min-height: 84px;
        }

        .stats-row .info-box-icon {
            min-height: 84px;
            width: 64px;
        }

        .stats-row .info-box-icon svg {
            width: 30px;
            height: 30px;
        }

        .stats-row .info-box-number {
            font-size: 20px;
        }

        .filter-card .card-body,
        .table-card .card-body,
        .mobile-card-shell .card-body {
            padding: 0.85rem;
        }

        .filter-section {
            gap: 8px;
        }

        .filter-label {
            width: 100%;
            min-width: 0;
            padding-top: 0;
            margin-bottom: 2px;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group .btn {
            flex: 1 1 calc(50% - 8px);
            min-width: 0;
            padding: 8px 10px;
        }

        .desktop-katalog {
            display: none;
        }

        .mobile-katalog {
            display: block;
        }

        .mobile-meta-grid {
            grid-template-columns: 1fr;
        }

        .modal-footer {
            gap: 8px;
        }

        .modal-footer .btn {
            width: 100%;
        }
    }
</style>

<?php
$hakAkses = $this->session->userdata('hak_akses');

if (!function_exists('katalog_badge_html')) {
    function katalog_badge_html($aktif, $label)
    {
        if ($aktif) {
            return '<span class="badge badge-success"><i class="fa fa-check"></i> ' . $label . '</span>';
        }

        return '<span class="badge badge-secondary">' . $label . ' -</span>';
    }
}
?>

<?php $this->load->view('partial/katalog/navbar') ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row"></div>
        </div>
    </div>

    <?php $this->load->view('content/katalog/modal') ?>

    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="<?= base_url('katalog/editKat') ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id_bar" id="edit_id_bar">
                        <input type="hidden" name="kode_barang_isi" id="edit_kode_barang">
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" class="form-control" name="nama_barang_isi" id="edit_nama_barang">
                        </div>
                        <div class="form-group">
                            <label>Produk Fokus</label>
                            <input type="text" class="form-control" name="produk_fokus_isi" id="edit_produk_fokus">
                        </div>
                        <div class="form-group">
                            <label>Nama Supplier</label>
                            <input type="text" class="form-control" name="nama_suplier_isi" id="edit_nama_suplier">
                        </div>
                        <div class="form-group">
                            <label>Kategori / Bahan Aktif</label>
                            <input type="text" class="form-control" name="katagori_isi" id="edit_katagori">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHapus" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Hapus Barang</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus <strong id="hapus_nama_barang"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <a href="#" id="hapus_link" class="btn btn-danger">Ya, Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalOnlineShop" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#6096B4; color:white;">
                    <h5 class="modal-title">
                        <i class="fa fa-store"></i> Update Status Online Shop
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('katalog/updateOnlineShop') ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id_barang" id="online_id_barang">
                        <input type="hidden" name="kode_barang" id="online_kode_barang">

                        <div class="alert alert-info py-2 mb-3">
                            <i class="fa fa-box"></i>
                            Produk: <strong id="online_nama_barang"></strong>
                        </div>

                        <label class="mb-2"><strong>Platform yang sudah diupload:</strong></label>

                        <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background:#fff4f2; border:1px solid #ee4d2d;">
                            <div class="d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <g fill="none" stroke="#ff4800" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                        <path d="m4 7l.867 12.143a2 2 0 0 0 2 1.857h10.276a2 2 0 0 0 2-1.857L20.01 7zm4.5 0c0-1.653 1.5-4 3.5-4s3.5 2.347 3.5 4" />
                                        <path d="M9.5 17c.413.462 1 1 2.5 1s2.5-.897 2.5-2s-1-1.5-2.5-2s-2-1.47-2-2c0-1.104 1-2 2-2s1.5 0 2.5 1" />
                                    </g>
                                </svg>
                                <strong class="ml-2" style="color:#ee4d2d;">Shopee</strong>
                            </div>
                            <div class="custom-control custom-switch mb-0">
                                <input type="checkbox" class="custom-control-input" id="switch_shopee" name="shopee" value="1">
                                <label class="custom-control-label" for="switch_shopee"></label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background:#f0fff1; border:1px solid #42b549;">
                            <div class="d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 48 48" style="display:block;">
                                    <rect width="48" height="48" fill="none" />
                                    <g stroke="#42b549" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                        <path d="M27.043 12.942c-3.43-2.897-16.85-2.247-16.85-2.247l-.473 32.65s17.855.134 23.353 0s9.341-4.508 9.4-7.878s0-24.18 0-24.18c-6.858-.829-11.942-.178-15.43 1.655" />
                                        <circle cx="19.531" cy="24.172" r="6.976" />
                                        <path d="M32.043 29.33a6.272 6.272 0 1 0-2.3-1.786m-19.55-16.849l-4.494 3.252L5.5 39.369l4.22 3.977m23.975-32.251a7.796 7.796 0 0 0-15.318-.299" />
                                        <path d="M34.396 19.662a2.36 2.36 0 0 1-3.878 2.59a4.194 4.194 0 1 0 3.878-2.59m-13.872.345a2.424 2.424 0 0 1-4.251 2.211a4.31 4.31 0 1 0 4.25-2.21m3.838 11.41c0-2.817 2.031-3.962 4.721-3.962c2.395 0 3.755 3.252 3.755 3.252a18.2 18.2 0 0 1-7.45 1.449a9.9 9.9 0 0 0 5.321 2.542s-.827.62-3.665.62c-2.306.001-2.682-2.453-2.682-3.902" />
                                        <path d="M30.317 31.569a10.4 10.4 0 0 1-.258 3.008" />
                                    </g>
                                </svg>
                                <strong class="ml-2" style="color:#42b549;">Tokopedia</strong>
                            </div>
                            <div class="custom-control custom-switch mb-0">
                                <input type="checkbox" class="custom-control-input" id="switch_tokopedia" name="tokopedia" value="1">
                                <label class="custom-control-label" for="switch_tokopedia"></label>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background:#f0f6ff; border:1px solid #6096B4;">
                            <div class="d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <g fill="none" stroke="#6096B4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                                        <line x1="3" y1="6" x2="21" y2="6" />
                                        <path d="M16 10a4 4 0 0 1-8 0" />
                                    </g>
                                </svg>
                                <strong class="ml-2" style="color:#6096B4;">KiuShop</strong>
                            </div>
                            <div class="custom-control custom-switch mb-0">
                                <input type="checkbox" class="custom-control-input" id="switch_kiushop" name="kiushop" value="1">
                                <label class="custom-control-label" for="switch_kiushop"></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="katalog-page">
        <div class="row mb-3 mt-3 px-2 stats-row">
            <div class="col-6 col-md-3 mb-2">
                <div class="info-box shadow-sm mb-0">
                    <span class="info-box-icon bg-primary">
                        <i class="fa fa-boxes"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Produk</span>
                        <span class="info-box-number"><?= $statistik['total'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="info-box shadow-sm mb-0" style="border-left: 4px solid #ee4d2d;">
                    <span class="info-box-icon d-flex align-items-center justify-content-center" style="background-color:#ee4d2d;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                            <g stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                <path d="m4 7l.867 12.143a2 2 0 0 0 2 1.857h10.276a2 2 0 0 0 2-1.857L20.01 7z" />
                                <path d="M8.5 7c0-1.653 1.5-4 3.5-4s3.5 2.347 3.5 4" />
                                <path d="M9.5 17c.413.462 1 1 2.5 1s2.5-.897 2.5-2s-1-1.5-2.5-2s-2-1.47-2-2c0-1.104 1-2 2-2s1.5 0 2.5 1" />
                            </g>
                        </svg>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Shopee</span>
                        <span class="info-box-number"><?= $statistik['shopee'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="info-box shadow-sm mb-0" style="border-left: 4px solid #42b549;">
                    <span class="info-box-icon d-flex align-items-center justify-content-center" style="background-color:#42b549;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 48 48">
                            <g stroke="white" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="4">
                                <path d="M27.043 12.942c-3.43-2.897-16.85-2.247-16.85-2.247l-.473 32.65s17.855.134 23.353 0s9.341-4.508 9.4-7.878s0-24.18 0-24.18c-6.858-.829-11.942-.178-15.43 1.655" />
                                <circle cx="19.531" cy="24.172" r="6.976" />
                                <path d="M32.043 29.33a6.272 6.272 0 1 0-2.3-1.786" />
                            </g>
                        </svg>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tokopedia</span>
                        <span class="info-box-number"><?= $statistik['tokopedia'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <div class="info-box shadow-sm mb-0" style="border-left: 4px solid #6096B4;">
                    <span class="info-box-icon" style="background-color:#6096B4;">
                        <i class="fa fa-shopping-cart" style="color:white;"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">KiuShop</span>
                        <span class="info-box-number"><?= $statistik['kiushop'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3 px-2">
            <div class="col-12">
                <div class="card shadow-sm filter-card">
                    <div class="card-body py-2">
                        <div class="filter-section mb-2">
                            <label class="filter-label"><strong>Produk Fokus:</strong></label>
                            <div class="filter-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus active" data-fokus="">Semua</button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="A">A</button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="B">B</button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="C">C</button>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="filter-section">
                            <label class="filter-label"><strong>Online Shop:</strong></label>
                            <div class="filter-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-filter-online active" data-online="">Semua</button>
                                <button type="button" class="btn btn-sm btn-filter-online" data-online="shopee" style="border:1px solid #ee4d2d; color:#ee4d2d; background:white;">Shopee</button>
                                <button type="button" class="btn btn-sm btn-filter-online" data-online="tokopedia" style="border:1px solid #42b549; color:#42b549; background:white;">Tokopedia</button>
                                <button type="button" class="btn btn-sm btn-filter-online" data-online="kiushop" style="border:1px solid #6096B4; color:#6096B4; background:white;">KiuShop</button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-filter-online" data-online="kosong">
                                    <i class="fa fa-times-circle"></i> Belum Ada
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content px-2" style="margin-top:-10px;">
            <div class="row">
                <div class="col-lg">
                    <div class="desktop-katalog">
                        <div class="card table-card">
                            <div class="card-body">
                                <?php if ($hakAkses == '1') : ?>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahbarang">
                                            <i class="fas fa-plus"></i>&nbsp;Tambah Data
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped display mt-2 desktop-table">
                                        <thead>
                                            <tr>
                                                <th hidden>Kode Barang</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;" width="50">Produk Fokus</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;">Nama Barang</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;">Nama Suplier</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;">Kelompok Bahan</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;">Bahan Aktif</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;">Gambar Produk</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;">Online Shop</th>
                                                <th class="tcenter" style="background-color:#6096B4; color:white;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="dt-body-center"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mobile-katalog">
                        <div class="card mobile-card-shell">
                            <div class="card-body">
                                <div class="mobile-list-header">
                                    <div>
                                        <h3 class="mobile-list-title">Katalog Produk</h3>
                                        <p class="mobile-list-subtitle">Mode mobile memakai kartu agar aksi lebih mudah disentuh.</p>
                                    </div>
                                    <?php if ($hakAkses == '1') : ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahbarang">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div id="mobileEmptyState" class="mobile-empty">
                                    Tidak ada produk yang cocok dengan filter yang dipilih.
                                </div>

                                <div id="mobileCardList" class="mobile-card-list">
                                    <?php foreach ($barang as $item) : ?>
                                        <?php
                                        $gambarProduk = !empty($item->gbr_produk) && $item->gbr_produk !== '-' && file_exists(FCPATH . 'images/produk/' . $item->gbr_produk)
                                            ? base_url('images/produk/' . $item->gbr_produk)
                                            : base_url('images/Karisma.png');

                                        $fokusValue = trim((string) $item->produk_fokus);
                                        $kelompokValue = trim((string) $item->kelompok);
                                        $bahanAktifValue = trim((string) $item->bahan_aktif);
                                        $supplierValue = trim((string) $item->nama_suplier);

                                        $onlineActive = ((int) $item->shopee) || ((int) $item->tokopedia) || ((int) $item->kiushop);
                                        $onlineKey = !$onlineActive ? 'kosong' : trim(implode(' ', array_filter(array(
                                            ((int) $item->shopee) ? 'shopee' : '',
                                            ((int) $item->tokopedia) ? 'tokopedia' : '',
                                            ((int) $item->kiushop) ? 'kiushop' : ''
                                        ))));
                                        ?>
                                        <article
                                            class="katalog-mobile-card"
                                            data-fokus="<?= htmlspecialchars($fokusValue, ENT_QUOTES, 'UTF-8') ?>"
                                            data-online="<?= htmlspecialchars($onlineKey, ENT_QUOTES, 'UTF-8') ?>">
                                            <div class="mobile-card-top">
                                                <img src="<?= $gambarProduk ?>" alt="<?= htmlspecialchars($item->nama_barang, ENT_QUOTES, 'UTF-8') ?>" class="mobile-card-image">
                                                <div class="mobile-card-main">
                                                    <span class="mobile-focus-pill">Fokus <?= $fokusValue !== '' ? htmlspecialchars($fokusValue, ENT_QUOTES, 'UTF-8') : '-' ?></span>
                                                    <h4 class="mobile-card-name"><?= htmlspecialchars($item->nama_barang, ENT_QUOTES, 'UTF-8') ?></h4>
                                                    <p class="mobile-card-code">Kode: <?= htmlspecialchars($item->kode_barang, ENT_QUOTES, 'UTF-8') ?></p>
                                                    <p class="mobile-card-supplier">Supplier: <?= $supplierValue !== '' ? htmlspecialchars($supplierValue, ENT_QUOTES, 'UTF-8') : '-' ?></p>
                                                </div>
                                            </div>

                                            <div class="mobile-meta-grid">
                                                <div class="mobile-meta-item">
                                                    <span class="mobile-meta-label">Kelompok Bahan</span>
                                                    <div class="mobile-meta-value"><?= $kelompokValue !== '' ? htmlspecialchars($kelompokValue, ENT_QUOTES, 'UTF-8') : '-' ?></div>
                                                </div>
                                                <div class="mobile-meta-item">
                                                    <span class="mobile-meta-label">Bahan Aktif</span>
                                                    <div class="mobile-meta-value"><?= $bahanAktifValue !== '' ? htmlspecialchars($bahanAktifValue, ENT_QUOTES, 'UTF-8') : '-' ?></div>
                                                </div>
                                            </div>

                                            <div class="mobile-status-group">
                                                <?= katalog_badge_html((int) $item->shopee === 1, 'Shopee') ?>
                                                <?= katalog_badge_html((int) $item->tokopedia === 1, 'Tokopedia') ?>
                                                <?= katalog_badge_html((int) $item->kiushop === 1, 'KiuShop') ?>
                                            </div>

                                            <div class="mobile-action-group">
                                                <a href="<?= base_url('pricelist?id=' . $item->kode_barang) ?>" class="btn btn-primary btn-sm" target="_blank">
                                                    <i class="fa fa-eye"></i> Lihat
                                                </a>

                                                <?php if ($hakAkses == '1') : ?>
                                                    <button
                                                        class="btn btn-warning btn-sm btn-edit"
                                                        data-id="<?= $item->id_barang ?>"
                                                        data-kode="<?= htmlspecialchars($item->kode_barang, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-nama="<?= htmlspecialchars($item->nama_barang, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-fokus="<?= htmlspecialchars($item->produk_fokus, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-suplier="<?= htmlspecialchars($item->nama_suplier, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-katagori="<?= htmlspecialchars($item->bahan_aktif, ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="fa fa-pencil-alt"></i> Edit
                                                    </button>
                                                    <button
                                                        class="btn btn-danger btn-sm btn-hapus"
                                                        data-id="<?= $item->id_barang ?>"
                                                        data-nama="<?= htmlspecialchars($item->nama_barang, ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="fa fa-trash"></i> Hapus
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($hakAkses == '1' || $hakAkses == '4') : ?>
                                                    <button
                                                        class="btn btn-info btn-sm btn-online"
                                                        data-id="<?= $item->id_barang ?>"
                                                        data-kode="<?= htmlspecialchars($item->kode_barang, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-nama="<?= htmlspecialchars($item->nama_barang, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-shopee="<?= (int) $item->shopee ?>"
                                                        data-tokopedia="<?= (int) $item->tokopedia ?>"
                                                        data-kiushop="<?= (int) $item->kiushop ?>">
                                                        <i class="fa fa-store"></i> Online
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="main-footer">
    <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong> All rights reserved.
</footer>
</div>
