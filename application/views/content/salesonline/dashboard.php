<style>
    table {
        width: 100% !important;
    }

    table td,
    table th {
        white-space: nowrap;
    }

    @media (max-width: 768px) {

        table td,
        table th {
            white-space: normal;
            word-break: break-word;
        }
    }

    .tcenter {
        text-align: center;
    }

    .dt-body-center {
        text-align: center;
    }

    .badge {
        font-size: 12px;
        padding: 5px 8px;
    }

    /* Fix custom switch agar tidak overflow */
    .custom-control.custom-switch {
        padding-left: 2.25rem;
        min-width: 50px;
    }

    .custom-control-label::before {
        left: -2.25rem;
    }

    .custom-control-label::after {
        left: calc(-2.25rem + 2px);
    }
</style>

<?php $this->load->view('partial/salesonline/navbar') ?>

<div class="content-wrapper" style="background-color: #F5F5F5;">

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

                        <!-- Shopee -->
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

                        <!-- Tokopedia -->
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

                        <!-- KiuShop -->
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- INFO CARDS -->
    <div class="row mb-3 mt-3 px-2">
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

                    <!-- Icon Shopee -->
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

                    <!-- Icon Tokopedia -->
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

    <!-- FILTER PRODUK FOKUS + ONLINE SHOP -->
    <div class="row mb-3 px-2">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-2">

                    <!-- Filter Produk Fokus -->
                    <div class="d-flex align-items-center flex-wrap mb-2">
                        <label class="mr-2 mb-1"><strong>Produk Fokus:</strong></label>
                        <div class="btn-group flex-wrap" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus active" data-fokus="">Semua</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="A">A</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="B">B</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="C">C</button>
                            <!-- <button type="button" class="btn btn-sm btn-outline-warning btn-filter-fokus" 
                                    data-fokus="kosong">Tanpa Fokus</button> -->
                        </div>
                    </div>

                    <hr class="my-2">

                    <!-- Filter Online Shop -->
                    <div class="d-flex align-items-center flex-wrap">
                        <label class="mr-2 mb-1"><strong>Online Shop:</strong></label>
                        <div class="btn-group flex-wrap" role="group">

                            <button type="button" class="btn btn-sm btn-outline-secondary btn-filter-online active" data-online="">Semua</button>

                            <!-- Shopee -->
                            <button type="button" class="btn btn-sm btn-filter-online" data-online="shopee" style="border:1px solid #ee4d2d; color:#ee4d2d; background:white;">
                                Shopee
                            </button>

                            <!-- Tokopedia -->
                            <button type="button" class="btn btn-sm btn-filter-online" data-online="tokopedia" style="border:1px solid #42b549; color:#42b549; background:white;">
                                Tokopedia
                            </button>

                            <!-- KiuShop -->
                            <button type="button" class="btn btn-sm btn-filter-online" data-online="kiushop" style="border:1px solid #6096B4; color:#6096B4; background:white;">
                                KiuShop
                            </button>

                            <!-- Belum ada platform -->
                            <button type="button" class="btn btn-sm btn-outline-danger btn-filter-online" data-online="kosong">
                                <i class="fa fa-times-circle"></i> Belum Ada
                            </button>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="content mr-1 ml-1" style="margin-top: -15px;">
        <div class="row">
            <div class="col-lg">
                <div class="table-responsive">
                    <div class="card">
                        <div class="card-body" style="background-color: white;">
                            <table id="tableSalesOnline" class="table table-bordered table-striped display mt-2" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">kode</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Produk Fokus</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Nama Barang</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Nama Suplier</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Kelompok</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Bahan Aktif</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Gambar</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Status Online</th>
                                        <th class="tcenter" style="background-color:#6096B4; color:white;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="dt-body-center"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="main-footer">
    <strong>Copyright &copy; 2022
        <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.
    </strong> All rights reserved.
</footer>
</div>