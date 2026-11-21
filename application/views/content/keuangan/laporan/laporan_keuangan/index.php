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
        'onclick' => "openPeriodeFilter('laba_rugi_standar'); return false;",
        'desc'    => 'Laporan laba rugi standar 1 kolom saldo periode berjalan.',
    ],
    [
        'title'   => 'Laba Rugi (Saldo & YTD)',
        'icon'    => 'fas fa-balance-scale',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('laba_rugi_ytd'); return false;",
        'desc'    => 'Laporan laba rugi komparasi Saldo periode dan Year to Date (YTD).',
    ],
    [
        'title'   => 'Laba Rugi - Perbandingan 2 Kolom',
        'icon'    => 'fas fa-columns',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('laba_rugi_perbandingan_2'); return false;",
        'desc'    => 'Laporan perbandingan laba rugi antara dua periode berbeda.',
    ],
    [
        'title'   => 'Laba Rugi - Anggaran vs Realisasi',
        'icon'    => 'fas fa-tasks',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('laba_rugi_anggaran'); return false;",
        'desc'    => 'Laporan perbandingan antara realisasi transaksi dan anggaran belanja.',
    ],
    [
        'title'   => 'Laba Rugi - Perbandingan 4 Kolom',
        'icon'    => 'fas fa-th',
        'url'     => 'javascript:void(0)',
        'onclick' => "openPeriodeFilter('laba_rugi_perbandingan_4'); return false;",
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
$menu_tabs = [
    [
        'id'    => 'tab-laporan-keuangan',
        'title' => 'Laporan Keuangan',
        'icon'  => 'fas fa-chart-pie',
        'menus' => $menus,
    ],
    [
        'id'    => 'tab-buku-besar',
        'title' => 'Buku Besar',
        'icon'  => 'fas fa-book-open',
        'menus' => [
            [
                'title' => 'Buku Besar - Standar',
                'icon'  => 'fas fa-book-open',
                'url'   => base_url('laporan/keuangan/buku-besar/buku-besar-standar'),
                'desc'  => 'Rincian transaksi buku besar per rekening dengan No. Dept & No. Proyek.',
            ],
            [
                'title' => 'Buku Besar - Mutasi',
                'icon'  => 'fas fa-exchange-alt',
                'url'   => base_url('laporan/keuangan/buku-besar/buku-besar-mutasi'),
                'desc'  => 'Mutasi debet, kredit, dan saldo berjalan per rekening buku besar.',
            ],
            [
                'title' => 'Buku Besar - Mata Uang Sebenarnya',
                'icon'  => 'fas fa-coins',
                'url'   => base_url('laporan/keuangan/buku-besar/buku-besar-mata-uang'),
                'desc'  => 'Buku besar standar dengan tampilan mata uang rekening (IDR).',
            ],
            [
                'title' => 'Buku Besar Mutasi - Mata Uang Sebenarnya',
                'icon'  => 'fas fa-money-bill-wave',
                'url'   => base_url('laporan/keuangan/buku-besar/buku-besar-mutasi-mata-uang'),
                'desc'  => 'Buku besar mutasi dan saldo berjalan dengan mata uang rekening (IDR).',
            ],
            [
                'title' => 'Daftar Jurnal - Semua Transaksi',
                'icon'  => 'fas fa-list-alt',
                'url'   => base_url('laporan/keuangan/buku-besar/daftar-jurnal'),
                'desc'  => 'Daftar seluruh jurnal transaksi aktif beserta rincian akun debet/kredit.',
            ],
            [
                'title' => 'Jurnal Umum',
                'icon'  => 'fas fa-pen-square',
                'url'   => base_url('laporan/keuangan/buku-besar/jurnal-umum'),
                'desc'  => 'Daftar transaksi General Journal / Jurnal Umum akuntansi.',
            ],
            [
                'title' => 'Daftar Rekening',
                'icon'  => 'fas fa-sitemap',
                'url'   => base_url('laporan/keuangan/buku-besar/daftar-rekening'),
                'desc'  => 'Daftar Chart of Accounts (COA) dikelompokkan berdasarkan klasifikasi.',
            ],
            [
                'title' => 'Jejak Audit - Rangkuman',
                'icon'  => 'fas fa-user-shield',
                'url'   => base_url('laporan/keuangan/buku-besar/jejak-audit-rangkuman'),
                'desc'  => 'Ringkasan riwayat input, edit, dan reversal transaksi jurnal oleh pengguna.',
            ],
            [
                'title' => 'Jejak Audit - Semua Transaksi',
                'icon'  => 'fas fa-history',
                'url'   => base_url('laporan/keuangan/buku-besar/jejak-audit-semua'),
                'desc'  => 'Jejak audit lengkap beserta rincian akun dan catatan pengguna.',
            ],
            [
                'title' => 'Rincian Jejak Audit - Semua Transaksi',
                'icon'  => 'fas fa-clipboard-list',
                'url'   => base_url('laporan/keuangan/buku-besar/jejak-audit-rincian'),
                'desc'  => 'Rincian detail transaksi dan histori input/perubahan jurnal per baris.',
            ],
        ],
    ],
    [
        'id'    => 'tab-kas-bank',
        'title' => 'Kas dan Bank',
        'icon'  => 'fas fa-university',
        'menus' => [
            [
                'title' => 'Kas Masuk',
                'icon'  => 'fas fa-arrow-circle-down',
                'url'   => base_url('keuangan/kas_masuk'),
                'desc'  => 'Daftar dan pencatatan transaksi penerimaan kas dan bank.',
            ],
            [
                'title' => 'Kas Keluar',
                'icon'  => 'fas fa-arrow-circle-up',
                'url'   => base_url('keuangan/kas_keluar'),
                'desc'  => 'Daftar dan pencatatan transaksi pengeluaran kas dan bank.',
            ],
            [
                'title' => 'Kas Harian / Kasir',
                'icon'  => 'fas fa-cash-register',
                'url'   => base_url('keuangan/kasir'),
                'desc'  => 'Pengelolaan transaksi kas harian kasir.',
            ],
            [
                'title' => 'Laporan Mutasi Kas',
                'icon'  => 'fas fa-exchange-alt',
                'url'   => base_url('keuangan/kasir/report_mutasi'),
                'desc'  => 'Laporan mutasi penerimaan dan pengeluaran kas harian.',
            ],
            [
                'title' => 'Jurnal Kas Keluar',
                'icon'  => 'fas fa-file-invoice-dollar',
                'url'   => base_url('laporan/keuangan/kas-bank/jurnal-kas-keluar'),
                'desc'  => 'Jurnal transaksi pengeluaran kas & bank (Write Checks) format Zahir.',
            ],
            [
                'title' => 'Jurnal Kas Masuk',
                'icon'  => 'fas fa-hand-holding-usd',
                'url'   => base_url('laporan/keuangan/kas-bank/jurnal-kas-masuk'),
                'desc'  => 'Jurnal transaksi penerimaan kas & bank (Cash Deposit) format Zahir.',
            ],
        ],
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
