<?php
$menus = [
    [
        'title' => 'TAGPD',
        'icon'  => 'fas fa-file-invoice-dollar',
        'url'   => base_url('laporan/lainnya/tagpd'),
        'desc'  => 'Daftar Tagihan Piutang (TAGPD): saldo awal, faktur berjalan, pelunasan, BG, dan saldo PD.',
    ],
];
$back_url   = base_url('laporan');
$page_title = 'Laporan Lainnya';
$page_icon  = 'fas fa-folder-open';
$page_color = '#2d6b6b';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';
