<?php
$menus = [
    [
        'title' => 'Laporan Jurnal Transaksi',
        'icon'  => 'fas fa-book',
        'url'   => base_url('laporan/keuangan/jurnal-transaksi'),
        'desc'  => 'Laporan rincian semua transaksi jurnal umum dan ayat jurnal.',
    ],
    [
        'title'   => 'Arus Kas - Rangkuman',
        'icon'    => 'fas fa-file-invoice-dollar',
        'url'     => 'javascript:void(0)',
        'onclick' => "openZahirFilter('rangkuman'); return false;",
        'desc'    => 'Laporan ringkasan arus kas aktivitas operasi, investasi, dan pendanaan.',
    ],
    [
        'title'   => 'Arus Kas - Rincian',
        'icon'    => 'fas fa-list-alt',
        'url'     => 'javascript:void(0)',
        'onclick' => "openZahirFilter('rincian'); return false;",
        'desc'    => 'Laporan rincian per akun atas arus kas masuk dan keluar.',
    ],
];
$back_url   = base_url('laporan');
$page_title = 'Laporan Keuangan';
$page_icon  = 'fas fa-chart-pie';
$page_color = '#1788b8';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';
include APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_zahir.php';


