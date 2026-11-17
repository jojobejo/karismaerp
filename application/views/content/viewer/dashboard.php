<style>
    /* Wrapper biar tabel tidak meledak */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 6px;
    }

    /* Tabel lebih stabil */
    .table-responsive table {
        table-layout: auto;
        width: 100%;
    }

    /* Header bersih di mobile */
    thead th {
        background-color: #6096B4;
        color: white;
        padding: 8px;
        font-size: 13px;
        white-space: nowrap;
    }

    /* Row style */
    .table-striped>tbody>tr:nth-child(2n+1)>td {
        background-color: #FFFBF5;
    }

    /* Isi tabel */
    td,
    th {
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
        padding: 8px 6px;
    }

    /* Gambar produk fix */
    td img {
        max-width: 60px;
        height: auto;
        border-radius: 4px;
    }

    /* Tombol aksi friendly mobile */
    .table .btn {
        padding: 6px 8px;
        font-size: 12px;
        border-radius: 6px;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Mobile optimization */
    @media (max-width: 768px) {

        thead th {
            font-size: 11px;
            padding: 6px;
            white-space: normal;
            line-height: 1.2;
        }

        td,
        th {
            padding: 7px 4px;
            font-size: 12px;
        }

        td img {
            max-width: 45px;
        }

        .table .btn {
            padding: 4px 6px;
            font-size: 11px;
        }

        /* Biar tabel tidak terlalu panjang */
        th:nth-child(2),
        th:nth-child(3),
        th:nth-child(4),
        th:nth-child(5),
        th:nth-child(6) {
            white-space: normal;
        }
    }
</style>
<?php $this->load->view('partial/katalog/navbar') ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="background-color:#F5F5F5;">

    <div class="content-header">
        <div class="container"></div>
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
                <span class="info-box-icon d-flex align-items-center justify-content-center" 
                    style="background-color:#ee4d2d;">
                    
                    <!-- Icon Shopee -->
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                        <g stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                            <path d="m4 7l.867 12.143a2 2 0 0 0 2 1.857h10.276a2 2 0 0 0 2-1.857L20.01 7z"/>
                            <path d="M8.5 7c0-1.653 1.5-4 3.5-4s3.5 2.347 3.5 4"/>
                            <path d="M9.5 17c.413.462 1 1 2.5 1s2.5-.897 2.5-2s-1-1.5-2.5-2s-2-1.47-2-2c0-1.104 1-2 2-2s1.5 0 2.5 1"/>
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
                <span class="info-box-icon d-flex align-items-center justify-content-center" 
                    style="background-color:#42b549;">
                    
                    <!-- Icon Tokopedia -->
                    <svg xmlns="http://www.w3.org/2000/svg" 
                        width="40" height="40" 
                        viewBox="0 0 48 48">
                        
                        <g stroke="white" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="4">
                            <path d="M27.043 12.942c-3.43-2.897-16.85-2.247-16.85-2.247l-.473 32.65s17.855.134 23.353 0s9.341-4.508 9.4-7.878s0-24.18 0-24.18c-6.858-.829-11.942-.178-15.43 1.655"/>
                            <circle cx="19.531" cy="24.172" r="6.976"/>
                            <path d="M32.043 29.33a6.272 6.272 0 1 0-2.3-1.786"/>
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
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus active" 
                                    data-fokus="">Semua</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" 
                                    data-fokus="A">A</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" 
                                    data-fokus="B">B</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" 
                                    data-fokus="C">C</button>
                            <!-- <button type="button" class="btn btn-sm btn-outline-warning btn-filter-fokus" 
                                    data-fokus="kosong">Tanpa Fokus</button> -->
                        </div>
                    </div>

                    <hr class="my-2">

                    <!-- Filter Online Shop -->
                    <div class="d-flex align-items-center flex-wrap">
                        <label class="mr-2 mb-1"><strong>Online Shop:</strong></label>
                        <div class="btn-group flex-wrap" role="group">

                            <button type="button" class="btn btn-sm btn-outline-secondary btn-filter-online active" 
                                    data-online="">Semua</button>

                            <!-- Shopee -->
                            <button type="button" class="btn btn-sm btn-filter-online" 
                                    data-online="shopee"
                                    style="border:1px solid #ee4d2d; color:#ee4d2d; background:white;">
                                Shopee
                            </button>

                            <!-- Tokopedia -->
                            <button type="button" class="btn btn-sm btn-filter-online" 
                                    data-online="tokopedia"
                                    style="border:1px solid #42b549; color:#42b549; background:white;">
                                Tokopedia
                            </button>

                            <!-- KiuShop -->
                            <button type="button" class="btn btn-sm btn-filter-online" 
                                    data-online="kiushop"
                                    style="border:1px solid #6096B4; color:#6096B4; background:white;">
                                KiuShop
                            </button>

                            <!-- Belum ada platform -->
                            <button type="button" class="btn btn-sm btn-outline-danger btn-filter-online" 
                                    data-online="kosong">
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
                <div class="card shadow-sm" style="border-radius:10px;">
                    <div class="card-body" style="background-color:white; border-radius:10px;">

                        <div class="table-responsive">
                            <table id="table" class="table table-bordered table-striped mt-2">
                                <thead>
                                    <tr>
                                        <th hidden>Kode Barang</th>
                                        <th>Produk Fokus</th>
                                        <th>Nama Barang</th>
                                        <th>Nama Suplier</th>
                                        <th>Kelompok Bahan</th>
                                        <th>Bahan Aktif</th>
                                        <th>Gambar Produk</th>
                                        <th>Status Online</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="dt-body-center">
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /.content-wrapper -->

<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <!-- Default to the left -->
    <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong> All rights reserved.
</footer>
</div>
<!-- ./wrapper -->