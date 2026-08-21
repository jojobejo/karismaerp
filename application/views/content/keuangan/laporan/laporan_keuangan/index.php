<?php
$menus = [
    [
        'title' => 'Laporan Jurnal Transaksi',
        'icon'  => 'fas fa-book',
        'url'   => base_url('laporan/keuangan/jurnal-transaksi'),
        'desc'  => 'Laporan rincian semua transaksi jurnal umum dan ayat jurnal.',
    ]
];
$back_url   = base_url('laporan');
$page_title = 'Laporan Keuangan';
$page_icon  = 'fas fa-chart-pie';
$page_color = '#1788b8';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';

