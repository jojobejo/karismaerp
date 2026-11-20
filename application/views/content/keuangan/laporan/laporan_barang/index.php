<?php
$menus = [
    [
        'title' => 'Kartu Stock Per Gudang',
        'icon'  => 'fas fa-warehouse',
        'url'   => base_url('laporan/barang/kartu-stock-gudang'),
        'desc'  => 'Laporan mutasi kartu stock barang masuk dan keluar per gudang (Moving Average).',
    ],
    [
        'title' => 'Kartu Stok Barang Konsinyasi',
        'icon'  => 'fas fa-handshake',
        'url'   => base_url('laporan/barang/kartu-stock-konsinyasi'),
        'desc'  => 'Laporan mutasi kartu stok barang konsinyasi dengan metode FIFO per batch/lot LPB.',
    ],
    [
        'title' => 'Kartu Stok Pajak',
        'icon'  => 'fas fa-file-invoice-dollar',
        'url'   => base_url('laporan/barang/kartu-stock-pajak'),
        'desc'  => 'Laporan mutasi kartu stok barang pajak (BKP/BKPS): penjualan reguler, test market lunas, dan faktur pecahan H.',
    ],
    [
        'title' => 'Kartu Stok A',
        'icon'  => 'fas fa-box-open',
        'url'   => base_url('laporan/barang/kartu-stock-a'),
        'desc'  => 'Laporan mutasi kartu stok barang dengan kode barang A, Z, X (selain kode Q dan C).',
    ],
    [
        'title' => 'Kartu Stok - Umum',
        'icon'  => 'fas fa-clipboard-check',
        'url'   => base_url('laporan/barang/kartu-stok-umum'),
        'desc'  => 'Mutasi kartu stok barang umum lengkap dengan saldo awal, masuk, keluar, harga pokok, dan nilai.',
    ],
    [
        'title' => 'Analisa Barang Per Gudang',
        'icon'  => 'fas fa-chart-bar',
        'url'   => base_url('laporan/barang/analisa-per-gudang'),
        'desc'  => 'Analisa stok tersedia, dipesan ke pemasok, dan pesanan pelanggan per gudang.',
    ],
    [
        'title' => 'Barang Dibeli',
        'icon'  => 'fas fa-cart-plus',
        'url'   => base_url('laporan/barang/barang-dibeli'),
        'desc'  => 'Rekapitulasi jumlah dan nilai pembelian barang per kelompok dagang.',
    ],
    [
        'title' => 'Barang Terjual',
        'icon'  => 'fas fa-shopping-bag',
        'url'   => base_url('laporan/barang/barang-terjual'),
        'desc'  => 'Rekapitulasi jumlah terjual dan nilai penjualan barang per kelompok dagang.',
    ],
    [
        'title' => 'Daftar Barang - Umum',
        'icon'  => 'fas fa-boxes',
        'url'   => base_url('laporan/barang/daftar-barang-umum'),
        'desc'  => 'Daftar master barang beserta stok tersedia, satuan, harga satuan, dan total nilai.',
    ],
    [
        'title' => 'Daftar Barang Per Gudang',
        'icon'  => 'fas fa-cubes',
        'url'   => base_url('laporan/barang/daftar-barang-per-gudang'),
        'desc'  => 'Daftar stok dan nilai persediaan barang yang dikelompokkan per gudang.',
    ],
    [
        'title' => 'Daftar Harga Jual Barang',
        'icon'  => 'fas fa-tags',
        'url'   => base_url('laporan/barang/daftar-harga-jual'),
        'desc'  => 'Daftar harga jual barang beserta stok tersedia dan satuan.',
    ],
    [
        'title' => 'Jurnal Persediaan',
        'icon'  => 'fas fa-book-open',
        'url'   => base_url('laporan/barang/jurnal-persediaan'),
        'desc'  => 'Rincian jurnal mutasi persediaan (penyesuaian stok, pindah gudang, dan promosi).',
    ],
    [
        'title' => 'Kartu Stok Per Pemasok Per Gudang',
        'icon'  => 'fas fa-dolly',
        'url'   => base_url('laporan/barang/kartu-stok-per-pemasok-per-gudang'),
        'desc'  => 'Kartu mutasi stok masuk, keluar, dan saldo akhir per gudang.',
    ],
    [
        'title' => 'Formulir Stok Opname',
        'icon'  => 'fas fa-tasks',
        'url'   => base_url('laporan/barang/formulir-stok-opname'),
        'desc'  => 'Formulir cetak stok opname per gudang dan kelompok barang.',
    ],
    [
        'title' => 'Transaksi Barang - Semua Transaksi',
        'icon'  => 'fas fa-exchange-alt',
        'url'   => base_url('laporan/barang/transaksi-semua'),
        'desc'  => 'Rincian seluruh transaksi mutasi barang masuk, keluar, penyesuaian, dan pindah gudang.',
    ],
    [
        'title' => 'Transaksi - Pemakaian / Penyesuaian Barang',
        'icon'  => 'fas fa-sliders-h',
        'url'   => base_url('laporan/barang/transaksi-pemakaian-penyesuaian'),
        'desc'  => 'Rincian transaksi pemakaian dan penyesuaian stok barang beserta nilai.',
    ],
    [
        'title' => 'Mutasi Barang Per Gudang',
        'icon'  => 'fas fa-warehouse',
        'url'   => base_url('laporan/barang/mutasi-per-gudang'),
        'desc'  => 'Rekap mutasi stok awal, masuk, keluar, dan stok akhir serta nilai per gudang.',
    ],
    [
        'title' => 'Mutasi Barang - Umum',
        'icon'  => 'fas fa-sync-alt',
        'url'   => base_url('laporan/barang/mutasi-umum'),
        'desc'  => 'Rekap mutasi stok awal, masuk, keluar, dan nilai persediaan per kelompok barang.',
    ],
    [
        'title' => 'Nomor Lot Barang - Mutasi',
        'icon'  => 'fas fa-barcode',
        'url'   => base_url('laporan/barang/lot-mutasi'),
        'desc'  => 'Mutasi stok masuk, keluar, dan saldo per nomor lot serta tanggal kadaluarsa.',
    ],
    [
        'title' => 'Nomor Lot Barang - Tersedia',
        'icon'  => 'fas fa-check-double',
        'url'   => base_url('laporan/barang/lot-tersedia'),
        'desc'  => 'Daftar nomor lot barang yang tersedia per gudang lengkap dengan tanggal kadaluarsa.',
    ],
];

$menu_tabs = [
    [
        'id'    => 'tab-persediaan-barang',
        'title' => 'Persediaan Barang',
        'icon'  => 'fas fa-boxes',
        'menus' => $menus,
    ],
    [
        'id'    => 'tab-analisa-produk',
        'title' => 'Analisa Produk',
        'icon'  => 'fas fa-chart-line',
        'menus' => [
            [
                'title' => 'Penjualan Per Barang Per Pelanggan Per Faktur',
                'icon'  => 'fas fa-file-invoice-dollar',
                'url'   => base_url('laporan/barang/penjualan-barang-pelanggan-faktur'),
                'desc'  => 'Rincian penjualan per barang, salesman, pelanggan, dan nomor faktur.',
            ],
            [
                'title' => 'Analisa Barang - Umum',
                'icon'  => 'fas fa-chart-bar',
                'url'   => base_url('laporan/barang/analisa-umum'),
                'desc'  => 'Analisa stok tersedia, dipesan ke pemasok, dan pesanan pelanggan secara umum.',
            ],
            [
                'title' => 'Barang Paling Laris - (10 Teratas) Per Kelompok',
                'icon'  => 'fas fa-trophy',
                'url'   => base_url('laporan/barang/laris-top10'),
                'desc'  => 'Peringkat 10 produk terlaris pada setiap kelompok barang dagangan.',
            ],
            [
                'title' => 'Barang Paling Laris - Per Kelompok',
                'icon'  => 'fas fa-sort-amount-down',
                'url'   => base_url('laporan/barang/laris-per-kelompok'),
                'desc'  => 'Daftar seluruh produk terlaris berdasarkan jumlah terjual per kelompok barang.',
            ],
            [
                'title' => 'Barang Paling Laris (Keuntungan)',
                'icon'  => 'fas fa-percentage',
                'url'   => base_url('laporan/barang/laris-keuntungan'),
                'desc'  => 'Analisa produk paling menguntungkan berdasarkan nilai penjualan, HPP, laba, dan % selisih.',
            ],
            [
                'title' => 'Laba / Rugi Barang - Rincian',
                'icon'  => 'fas fa-balance-scale',
                'url'   => base_url('laporan/barang/laba-rugi-rincian'),
                'desc'  => 'Rincian laba/rugi per faktur penjualan untuk setiap barang dagangan.',
            ],
            [
                'title' => 'Laba / Rugi Barang - Total',
                'icon'  => 'fas fa-coins',
                'url'   => base_url('laporan/barang/laba-rugi-total'),
                'desc'  => 'Rekap total laba/rugi dan persentase margin per barang dagangan.',
            ],
            [
                'title' => 'Pembelian Per Barang Per Pemasok Per Faktur',
                'icon'  => 'fas fa-truck-loading',
                'url'   => base_url('laporan/barang/pembelian-barang-pemasok-faktur'),
                'desc'  => 'Rincian pembelian per barang, pemasok, dan faktur penerimaan.',
            ],
            [
                'title' => 'Pembelian Per Pemasok Per Barang Per Invoice',
                'icon'  => 'fas fa-file-invoice',
                'url'   => base_url('laporan/barang/pembelian-pemasok-barang-invoice'),
                'desc'  => 'Rincian pembelian dikelompokkan per pemasok, barang, dan invoice.',
            ],
            [
                'title' => 'Pembelian Per Pemasok Per Barang Total',
                'icon'  => 'fas fa-layer-group',
                'url'   => base_url('laporan/barang/pembelian-pemasok-barang-total'),
                'desc'  => 'Ringkasan total pembelian per pemasok dan per barang.',
            ],
            [
                'title' => 'Pembelian Per Barang Per Pemasok Total',
                'icon'  => 'fas fa-cubes',
                'url'   => base_url('laporan/barang/pembelian-barang-pemasok-total'),
                'desc'  => 'Ringkasan total pembelian per barang dan per pemasok.',
            ],
            [
                'title' => 'Perputaran Barang Paling Cepat Per Kelompok',
                'icon'  => 'fas fa-tachometer-alt',
                'url'   => base_url('laporan/barang/perputaran-cepat-per-kelompok'),
                'desc'  => 'Peringkat perputaran barang berdasarkan nilai penjualan per kelompok.',
            ],
            [
                'title' => 'Perputaran Barang Paling Cepat - Semua Kelompok',
                'icon'  => 'fas fa-bolt',
                'url'   => base_url('laporan/barang/perputaran-cepat-semua'),
                'desc'  => 'Peringkat perputaran barang seluruh kelompok berdasarkan nilai penjualan.',
            ],
            [
                'title' => 'Penjualan Per Pelanggan Per Barang Total',
                'icon'  => 'fas fa-user-tag',
                'url'   => base_url('laporan/barang/penjualan-pelanggan-barang-total'),
                'desc'  => 'Rekapitulasi total penjualan per pelanggan dan per barang.',
            ],
            [
                'title' => 'Penjualan Per Pelanggan Per Barang Per Faktur',
                'icon'  => 'fas fa-file-invoice-dollar',
                'url'   => base_url('laporan/barang/penjualan-pelanggan-barang-faktur'),
                'desc'  => 'Rincian faktur penjualan per pelanggan dan per barang.',
            ],
            [
                'title' => 'Penjualan Per Salesman Per Barang Total',
                'icon'  => 'fas fa-user-tie',
                'url'   => base_url('laporan/barang/penjualan-salesman-barang-total'),
                'desc'  => 'Rekapitulasi total penjualan per salesman dan per barang.',
            ],
            [
                'title' => 'Penjualan Per Barang Per Pelanggan Total',
                'icon'  => 'fas fa-shopping-basket',
                'url'   => base_url('laporan/barang/penjualan-barang-pelanggan-total'),
                'desc'  => 'Rekapitulasi total penjualan per barang, salesman, dan pelanggan.',
            ],
            [
                'title' => 'Penjualan Per Barang Per Salesman Total',
                'icon'  => 'fas fa-chart-bar',
                'url'   => base_url('laporan/barang/penjualan-barang-salesman-total'),
                'desc'  => 'Rekapitulasi total penjualan per barang dan per salesman.',
            ],
            [
                'title' => 'Penjualan Per Salesman Per Pelanggan Per Barang Per Faktur',
                'icon'  => 'fas fa-receipt',
                'url'   => base_url('laporan/barang/penjualan-salesman-pelanggan-barang-faktur'),
                'desc'  => 'Rincian faktur penjualan lengkap per salesman, pelanggan, dan barang.',
            ],
            [
                'title' => 'Penjualan Per Salesman Per Barang (Selain Retur)',
                'icon'  => 'fas fa-check-double',
                'url'   => base_url('laporan/barang/penjualan-salesman-barang-non-retur'),
                'desc'  => 'Rekapitulasi penjualan bersih per salesman dan barang di luar retur.',
            ],
            [
                'title' => 'Penjualan Per Salesman Per Pelanggan',
                'icon'  => 'fas fa-users',
                'url'   => base_url('laporan/barang/penjualan-salesman-pelanggan'),
                'desc'  => 'Ringkasan total nilai penjualan per salesman dan per pelanggan.',
            ],
        ],
    ],
];

$back_url   = base_url('laporan');
$page_title = 'Laporan Barang';
$page_icon  = 'fas fa-boxes';
$page_color = '#5a3ea1';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';
