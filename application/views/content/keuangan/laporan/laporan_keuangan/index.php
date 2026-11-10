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
    // ==========================================
    // MENU LAPORAN NERACA (ZAHIR ACCOUNTING)
    // ==========================================
    [
        'title'   => 'Neraca (Standar)',
        'icon'    => 'fas fa-file-invoice',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('neraca_standar'); return false;",
        'desc'    => 'Laporan neraca aktiva, kewajiban, dan modal standar satu periode.',
    ],
    [
        'title'   => 'Neraca Saldo',
        'icon'    => 'fas fa-table',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('neraca_saldo'); return false;",
        'desc'    => 'Laporan neraca saldo trial balance: saldo awal, transaksi bulan ini, dan saldo akhir.',
    ],
    [
        'title'   => 'Neraca - 12 Bulan',
        'icon'    => 'fas fa-calendar-alt',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('neraca_12_bulan'); return false;",
        'desc'    => 'Laporan komparasi neraca bulanan selama 12 bulan dalam satu tahun akuntansi.',
    ],
    [
        'title'   => 'Neraca - Perbandingan 2 Kolom',
        'icon'    => 'fas fa-pause',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('neraca_perbandingan_2'); return false;",
        'desc'    => 'Laporan perbandingan neraca antara dua periode akuntansi berbeda.',
    ],
    [
        'title'   => 'Neraca - Anggaran vs Realisasi',
        'icon'    => 'fas fa-chart-bar',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('neraca_anggaran'); return false;",
        'desc'    => 'Laporan komparasi antara realisasi posisi keuangan neraca terhadap anggaran.',
    ],
    [
        'title'   => 'Neraca - Perbandingan 4 kolom',
        'icon'    => 'fas fa-th-large',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('neraca_perbandingan_4'); return false;",
        'desc'    => 'Laporan perbandingan komprehensif 4 kolom realisasi dan anggaran 2 periode.',
    ],
];
$back_url    = base_url('laporan');
$page_title  = 'Laporan Keuangan';
$page_icon   = 'fas fa-chart-pie';
$page_color  = '#1788b8';
$extra_modal = APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_zahir.php';

// Include halaman menu
include APPPATH . 'views/content/keuangan/laporan/_sub_page.php';

// Include juga modal dialog periode akuntansi untuk laporan neraca
include APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_periode.php';
