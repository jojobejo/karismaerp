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
    [
        'title'   => 'Laba Rugi (Standar)',
        'icon'    => 'fas fa-chart-line',
        'url'     => 'javascript:void(0)',
        'onclick' => "openZahirFilter('laba_rugi_standar'); return false;",
        'desc'    => 'Laporan laba rugi standar 1 kolom saldo periode berjalan.',
    ],
    [
        'title'   => 'Laba Rugi (Saldo & YTD)',
        'icon'    => 'fas fa-balance-scale',
        'url'     => 'javascript:void(0)',
        'onclick' => "openZahirFilter('laba_rugi_ytd'); return false;",
        'desc'    => 'Laporan laba rugi komparasi Saldo periode dan Year to Date (YTD).',
    ],
    [
        'title'   => 'Laba Rugi - Perbandingan 2 Kolom',
        'icon'    => 'fas fa-columns',
        'url'     => 'javascript:void(0)',
        'onclick' => "openZahirFilter('laba_rugi_perbandingan_2'); return false;",
        'desc'    => 'Laporan perbandingan laba rugi antara dua periode berbeda.',
    ],
    [
        'title'   => 'Laba Rugi - Anggaran vs Realisasi',
        'icon'    => 'fas fa-tasks',
        'url'     => 'javascript:void(0)',
        'onclick' => "openZahirFilter('laba_rugi_anggaran'); return false;",
        'desc'    => 'Laporan perbandingan antara realisasi transaksi dan anggaran belanja.',
    ],
    [
        'title'   => 'Laba Rugi - Perbandingan 4 Kolom',
        'icon'    => 'fas fa-th',
        'url'     => 'javascript:void(0)',
        'onclick' => "openZahirFilter('laba_rugi_perbandingan_4'); return false;",
        'desc'    => 'Laporan perbandingan komprehensif 4 kolom (Realisasi & Anggaran) 2 periode.',
    ],
];
$back_url    = base_url('laporan');
$page_title  = 'Laporan Keuangan';
$page_icon   = 'fas fa-chart-pie';
$page_color  = '#1788b8';
$extra_modal = APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_zahir.php';
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';


