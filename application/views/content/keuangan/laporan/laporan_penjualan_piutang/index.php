<?php
$menus = [
    [
        'title' => 'Uang Muka',
        'icon'  => 'fas fa-money-check-alt',
        'url'   => base_url('laporan/penjualan/uang-muka'),
        'desc'  => 'Penerimaan uang muka pelanggan dan pembayaran uang muka supplier.',
    ],
    [
        'title' => 'eFaktur Penjualan',
        'icon'  => 'fas fa-file-invoice',
        'url'   => base_url('laporan/penjualan/efaktur'),
        'desc'  => 'Daftar eFaktur pajak penjualan yang telah diterbitkan.',
    ],
    [
        'title' => 'Jurnal - Penjualan',
        'icon'  => 'fas fa-book',
        'url'   => base_url('laporan/penjualan/jurnal-penjualan'),
        'desc'  => 'Pencatatan jurnal akuntansi dari transaksi penjualan.',
    ],
    [
        'title' => 'Jurnal - Potongan Penjualan',
        'icon'  => 'fas fa-book-open',
        'url'   => base_url('laporan/penjualan/jurnal-potongan'),
        'desc'  => 'Pencatatan jurnal akuntansi dari potongan atau diskon penjualan.',
    ],
    [
        'title' => 'Penjualan - Rincian',
        'icon'  => 'fas fa-list-alt',
        'url'   => base_url('laporan/penjualan/rincian'),
        'desc'  => 'Laporan penjualan lengkap dengan detail per transaksi.',
    ],
    [
        'title' => 'Penjualan - Sederhana',
        'icon'  => 'fas fa-stream',
        'url'   => base_url('laporan/penjualan/sederhana'),
        'desc'  => 'Laporan penjualan format ringkas dan mudah dibaca.',
    ],
    [
        'title' => 'Penjualan Bernilai Negatif Rangkuman',
        'icon'  => 'fas fa-arrow-down',
        'url'   => base_url('laporan/penjualan/negatif-rangkuman'),
        'desc'  => 'Ringkasan penjualan yang menghasilkan nilai negatif.',
    ],
    [
        'title' => 'Penjualan Bernilai Negatif - Sederhana',
        'icon'  => 'fas fa-exclamation-circle',
        'url'   => base_url('laporan/penjualan/negatif-sederhana'),
        'desc'  => 'Format sederhana penjualan bernilai negatif.',
    ],
    [
        'title' => 'Penjualan Per Kelompok Pelanggan Per Faktur',
        'icon'  => 'fas fa-users',
        'url'   => base_url('laporan/penjualan/per-kelompok-pelanggan-faktur'),
        'desc'  => 'Penjualan dikelompokkan per segmen pelanggan, ditampilkan per faktur.',
    ],
    [
        'title' => 'Penjualan Per Kelompok Salesman',
        'icon'  => 'fas fa-user-tie',
        'url'   => base_url('laporan/penjualan/per-kelompok-salesman'),
        'desc'  => 'Laporan penjualan dikelompokkan berdasarkan tim atau grup salesman.',
    ],
    [
        'title' => 'Penjualan Per Mata Uang Rangkuman',
        'icon'  => 'fas fa-dollar-sign',
        'url'   => base_url('laporan/penjualan/per-mata-uang-rangkuman'),
        'desc'  => 'Ringkasan penjualan dipisah berdasarkan mata uang transaksi.',
    ],
    [
        'title' => 'Penjualan Per Mata Uang Sederhana',
        'icon'  => 'fas fa-coins',
        'url'   => base_url('laporan/penjualan/per-mata-uang-sederhana'),
        'desc'  => 'Format sederhana penjualan per mata uang.',
    ],
    [
        'title' => 'Penjualan Per Salesman Per Faktur',
        'icon'  => 'fas fa-file-signature',
        'url'   => base_url('laporan/penjualan/per-salesman-faktur'),
        'desc'  => 'Rincian penjualan per salesman ditampilkan per faktur.',
    ],
    [
        'title' => 'Retur Penjualan - Rangkuman',
        'icon'  => 'fas fa-undo-alt',
        'url'   => base_url('laporan/penjualan/retur-rangkuman'),
        'desc'  => 'Ringkasan nilai retur penjualan per periode.',
    ],
    [
        'title' => 'Retur Penjualan - Rincian',
        'icon'  => 'fas fa-undo',
        'url'   => base_url('laporan/penjualan/retur-rincian'),
        'desc'  => 'Detail transaksi retur penjualan per dokumen SPR.',
    ],
    [
        'title' => 'Retur Penjualan - Sederhana',
        'icon'  => 'fas fa-reply',
        'url'   => base_url('laporan/penjualan/retur-sederhana'),
        'desc'  => 'Format sederhana laporan retur penjualan.',
    ],
];

$menu_tabs = [
    [
        'id'    => 'tab-penjualan',
        'title' => 'Penjualan',
        'icon'  => 'fas fa-shopping-bag',
        'menus' => $menus,
    ],
    [
        'id'    => 'tab-piutang-usaha',
        'title' => 'Piutang Usaha',
        'icon'  => 'fas fa-hand-holding-usd',
        'menus' => [
            [
                'title' => 'Kartu Piutang Per Salesman',
                'icon'  => 'fas fa-user-tie',
                'url'   => base_url('laporan/piutang/kartu-piutang-salesman'),
                'desc'  => 'Rincian kartu piutang per faktur dan pembayaran dikelompokkan per salesman.',
            ],
            [
                'title' => 'Kartu Piutang Mata Uang Dasar',
                'icon'  => 'fas fa-address-card',
                'url'   => base_url('laporan/piutang/kartu-piutang-mata-uang-dasar'),
                'desc'  => 'Rincian mutasi faktur dan pembayaran piutang usaha per pelanggan.',
            ],
            [
                'title' => 'Kartu Piutang Per Salesman - Rangkuman',
                'icon'  => 'fas fa-list-ol',
                'url'   => base_url('laporan/piutang/kartu-piutang-salesman-rangkuman'),
                'desc'  => 'Ringkasan nilai penjualan, pembayaran, dan saldo piutang per salesman.',
            ],
            [
                'title' => 'Mutasi Piutang - Mata Uang Dasar',
                'icon'  => 'fas fa-exchange-alt',
                'url'   => base_url('laporan/piutang/mutasi-piutang'),
                'desc'  => 'Rekapitulasi saldo awal, debet, kredit, dan saldo akhir piutang per pelanggan.',
            ],
            [
                'title' => 'Pembayaran Pelanggan - Mata Uang Dasar',
                'icon'  => 'fas fa-money-check-alt',
                'url'   => base_url('laporan/piutang/pembayaran-pelanggan'),
                'desc'  => 'Daftar penerimaan pembayaran piutang per pelanggan dan nomor faktur.',
            ],
            [
                'title' => 'Piutang Usaha Per Mata Uang - Rangkuman',
                'icon'  => 'fas fa-hourglass-half',
                'url'   => base_url('laporan/piutang/piutang-per-mata-uang-rangkuman'),
                'desc'  => 'Analisa umur piutang usaha (0-30, 30-60, 60-90, >90 hari) per pelanggan.',
            ],
            [
                'title' => 'Piutang Usaha Per Pelanggan - Rincian',
                'icon'  => 'fas fa-file-invoice-dollar',
                'url'   => base_url('laporan/piutang/piutang-per-pelanggan-rincian'),
                'desc'  => 'Rincian umur piutang usaha per faktur untuk setiap pelanggan.',
            ],
            [
                'title' => 'Piutang Usaha Per Salesman - Rangkuman',
                'icon'  => 'fas fa-chart-pie',
                'url'   => base_url('laporan/piutang/piutang-per-salesman-rangkuman'),
                'desc'  => 'Ringkasan umur piutang usaha per pelanggan dikelompokkan per salesman.',
            ],
            [
                'title' => 'Piutang Usaha Per Salesman - Rincian',
                'icon'  => 'fas fa-tasks',
                'url'   => base_url('laporan/piutang/piutang-per-salesman-rincian'),
                'desc'  => 'Rincian umur piutang per faktur dikelompokkan per salesman dan pelanggan.',
            ],
            [
                'title' => 'Piutang Usaha Per Mata Uang - Rincian',
                'icon'  => 'fas fa-coins',
                'url'   => base_url('laporan/piutang/piutang-per-mata-uang-rincian'),
                'desc'  => 'Rincian umur piutang per faktur dikelompokkan berdasarkan mata uang.',
            ],
            [
                'title' => 'Surat Tagihan Pelanggan',
                'icon'  => 'fas fa-envelope-open-text',
                'url'   => base_url('laporan/piutang/surat-tagihan-pelanggan'),
                'desc'  => 'Cetak surat tagihan resmi daftar piutang belum lunas per pelanggan.',
            ],
        ],
    ],
];

$back_url   = base_url('laporan');
$page_title = 'Laporan Penjualan & Piutang';
$page_icon  = 'fas fa-file-invoice-dollar';
$page_color = '#1a8f4c';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';
