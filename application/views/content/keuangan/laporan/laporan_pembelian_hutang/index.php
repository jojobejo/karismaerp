<?php
$menus = [
    [
        'title' => 'Jurnal - Pembelian',
        'desc'  => 'Rincian jurnal transaksi pembelian beserta akun persediaan, PPN, dan hutang usaha.',
        'url'   => base_url('laporan/pembelian/jurnal-pembelian'),
        'icon'  => 'fas fa-book',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Jurnal - Diskon Pembelian',
        'desc'  => 'Rincian jurnal potongan/diskon pembelian berdasarkan periode transaksi.',
        'url'   => base_url('laporan/pembelian/jurnal-diskon'),
        'icon'  => 'fas fa-percent',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Penerimaan Barang (Harian)',
        'desc'  => 'Laporan harian penerimaan barang (LPB) lengkap dengan PO, surat jalan, LOT/EXP, qty, dan harga.',
        'url'   => base_url('laporan/pembelian/penerimaan-barang-harian'),
        'icon'  => 'fas fa-dolly-flatbed',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pembelian - Rincian',
        'desc'  => 'Rincian transaksi pembelian per faktur/LPB meliputi pemasok, item barang, pajak, dan saldo.',
        'url'   => base_url('laporan/pembelian/rincian'),
        'icon'  => 'fas fa-file-invoice',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pembelian - eFaktur',
        'desc'  => 'Daftar faktur pajak masukan pembelian beserta nomor seri faktur, DPP, PPN, dan status pelaporan.',
        'url'   => base_url('laporan/pembelian/efaktur'),
        'icon'  => 'fas fa-receipt',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pesanan Pembelian - Rincian',
        'desc'  => 'Rincian pesanan pembelian (PO) per pemasok beserta qty dipesan, diterima, harga satuan, dan pajak.',
        'url'   => base_url('laporan/pembelian/pesanan-rincian'),
        'icon'  => 'fas fa-clipboard-list',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pembelian - Sederhana',
        'desc'  => 'Ringkasan daftar transaksi pembelian per nomor referensi, nomor pesanan, pemasok, dan nilai IDR.',
        'url'   => base_url('laporan/pembelian/sederhana'),
        'icon'  => 'fas fa-list-alt',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pembelian Bernilai Negatif - Rangkuman',
        'desc'  => 'Rangkuman transaksi pembelian yang bernilai negatif atau penyesuaian kurang.',
        'url'   => base_url('laporan/pembelian/negatif-rangkuman'),
        'icon'  => 'fas fa-minus-circle',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pengiriman Pembelian - Rangkuman',
        'desc'  => 'Rangkuman penerimaan/pengiriman barang pembelian yang belum difakturkan.',
        'url'   => base_url('laporan/pembelian/pengiriman-rangkuman'),
        'icon'  => 'fas fa-shipping-fast',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Purchase Return',
        'desc'  => 'Daftar transaksi retur pembelian per faktur lengkap dengan PO, supplier, barang, qty, harga, dan VAT.',
        'url'   => base_url('laporan/pembelian/purchase-return'),
        'icon'  => 'fas fa-undo-alt',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Retur Pembelian - Rincian',
        'desc'  => 'Kartu rincian transaksi retur pembelian meliputi pemasok, gudang, item barang, pajak, dan saldo.',
        'url'   => base_url('laporan/pembelian/retur-rincian'),
        'icon'  => 'fas fa-file-invoice-dollar',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Retur Pembelian - Sederhana',
        'desc'  => 'Ringkasan daftar transaksi retur pembelian beserta nilai dan total dalam mata uang IDR.',
        'url'   => base_url('laporan/pembelian/retur-sederhana'),
        'icon'  => 'fas fa-list-ul',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pesanan Pembelian - Sederhana',
        'desc'  => 'Ringkasan daftar pesanan pembelian (PO) per pemasok dan nilai transaksi dalam IDR.',
        'url'   => base_url('laporan/pembelian/pesanan-sederhana'),
        'icon'  => 'fas fa-file-alt',
        'color' => '#b35a00',
    ],
    [
        'title' => 'Pesanan Pembelian - Status Pengiriman',
        'desc'  => 'Pemantauan status pengiriman PO: qty pesanan, telah tiba (LPB), dan sisa pesanan per item.',
        'url'   => base_url('laporan/pembelian/pesanan-status-pengiriman'),
        'icon'  => 'fas fa-tasks',
        'color' => '#b35a00',
    ],
];

$menu_tabs = [
    [
        'id'    => 'tab-pembelian',
        'title' => 'Pembelian',
        'icon'  => 'fas fa-shopping-cart',
        'menus' => $menus,
    ],
    [
        'id'    => 'tab-hutang-usaha',
        'title' => 'Hutang Usaha',
        'icon'  => 'fas fa-file-invoice-dollar',
        'menus' => [
            [
                'title' => 'Rincian Hutang - Mata Uang Dasar',
                'desc'  => 'Rincian umur hutang usaha per faktur dan per pemasok dalam mata uang dasar (IDR).',
                'url'   => base_url('laporan/hutang/rincian-mata-uang-dasar'),
                'icon'  => 'fas fa-file-invoice-dollar',
                'color' => '#b35a00',
            ],
            [
                'title' => 'Kartu Hutang - Mata Uang Dasar',
                'desc'  => 'Kartu mutasi debit, kredit, dan saldo hutang per faktur dan per pemasok.',
                'url'   => base_url('laporan/hutang/kartu-hutang-mata-uang-dasar'),
                'icon'  => 'fas fa-id-card',
                'color' => '#b35a00',
            ],
            [
                'title' => 'Mutasi Hutang - Mata Uang Dasar',
                'desc'  => 'Ringkasan saldo awal, mutasi debit/kredit, dan saldo akhir hutang per pemasok.',
                'url'   => base_url('laporan/hutang/mutasi-hutang-mata-uang-dasar'),
                'icon'  => 'fas fa-exchange-alt',
                'color' => '#b35a00',
            ],
            [
                'title' => 'Pembayaran Hutang - Mata Uang Dasar',
                'desc'  => 'Daftar transaksi pembayaran hutang ke pemasok beserta alokasi nomor faktur.',
                'url'   => base_url('laporan/hutang/pembayaran-hutang-mata-uang-dasar'),
                'icon'  => 'fas fa-money-check-alt',
                'color' => '#b35a00',
            ],
            [
                'title' => 'Rangkuman Hutang - Mata Uang Dasar',
                'desc'  => 'Rangkuman umur hutang usaha per pemasok (0-30, 30-60, 60-90, dan >90 hari).',
                'url'   => base_url('laporan/hutang/rangkuman-hutang-mata-uang-dasar'),
                'icon'  => 'fas fa-chart-pie',
                'color' => '#b35a00',
            ],
        ],
    ],
];

$back_url   = base_url('laporan');
$page_title = 'Laporan Pembelian & Hutang';
$page_icon  = 'fas fa-shopping-cart';
$page_color = '#b35a00';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';
