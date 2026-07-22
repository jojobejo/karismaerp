<?php
$menus = [
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
        'title' => 'Penjualan Rangkuman',
        'icon'  => 'fas fa-chart-bar',
        'url'   => base_url('laporan/penjualan/rangkuman'),
        'desc'  => 'Laporan ringkasan total penjualan per periode.',
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
        'title' => 'Penjualan Bernilai Negatif - Rincian',
        'icon'  => 'fas fa-minus-circle',
        'url'   => base_url('laporan/penjualan/negatif-rincian'),
        'desc'  => 'Detail transaksi penjualan bernilai negatif per faktur.',
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
        'title' => 'Penjualan Per Kelompok Pelanggan',
        'icon'  => 'fas fa-user-friends',
        'url'   => base_url('laporan/penjualan/per-kelompok-pelanggan'),
        'desc'  => 'Ringkasan penjualan berdasarkan kelompok atau segmen pelanggan.',
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
        'title' => 'Penjualan Per Pelanggan',
        'icon'  => 'fas fa-user',
        'url'   => base_url('laporan/penjualan/per-pelanggan'),
        'desc'  => 'Total penjualan dikelompokkan per pelanggan dalam satu periode.',
    ],
    [
        'title' => 'Penjualan Per Pengiriman Barang',
        'icon'  => 'fas fa-truck',
        'url'   => base_url('laporan/penjualan/per-pengiriman'),
        'desc'  => 'Laporan penjualan dikaitkan dengan data pengiriman barang (DO).',
    ],
    [
        'title' => 'Penjualan Per Salesman Per Faktur',
        'icon'  => 'fas fa-file-signature',
        'url'   => base_url('laporan/penjualan/per-salesman-faktur'),
        'desc'  => 'Rincian penjualan per salesman ditampilkan per faktur.',
    ],
    [
        'title' => 'Penjualan Per Salesman',
        'icon'  => 'fas fa-handshake',
        'url'   => base_url('laporan/penjualan/per-salesman'),
        'desc'  => 'Total penjualan dirangkum per salesman dalam periode tertentu.',
    ],
    [
        'title' => 'Pesanan Penjualan - Status',
        'icon'  => 'fas fa-clipboard-list',
        'url'   => base_url('laporan/penjualan/so-status'),
        'desc'  => 'Monitor status Sales Order: pending, diproses, terkirim, selesai.',
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
    [
        'title' => 'Uang Muka Pelanggan - Rincian',
        'icon'  => 'fas fa-money-bill-wave',
        'url'   => base_url('laporan/penjualan/uang-muka-rincian'),
        'desc'  => 'Detail penerimaan uang muka dari pelanggan per transaksi.',
    ],
    [
        'title' => 'Uang Muka Pelanggan',
        'icon'  => 'fas fa-piggy-bank',
        'url'   => base_url('laporan/penjualan/uang-muka'),
        'desc'  => 'Ringkasan uang muka pelanggan yang belum diselesaikan.',
    ],
];
$back_url   = base_url('laporan');
$page_title = 'Laporan Penjualan & Piutang';
$page_icon  = 'fas fa-file-invoice-dollar';
$page_color = '#1a8f4c';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';
