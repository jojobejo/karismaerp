<?php
$menus = [
    [
        'title' => 'Kartu Stock Per Gudang',
        'icon'  => 'fas fa-warehouse',
        'url'   => base_url('laporan/barang/kartu-stock-gudang'),
        'desc'  => 'Laporan mutasi kartu stock barang masuk dan keluar per gudang.',
    ]
];
$back_url   = base_url('laporan');
$page_title = 'Laporan Barang';
$page_icon  = 'fas fa-boxes';
$page_color = '#5a3ea1';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';
