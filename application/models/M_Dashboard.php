<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Dashboard extends CI_Model
{
    public function current_user_context()
    {
        $lv = (int)$this->session->userdata('lv');
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $departemen = strtoupper(trim((string)($this->session->userdata('departemen') ?: $this->session->userdata('departement'))));
        $username = strtolower(trim((string)$this->session->userdata('username')));

        return array(
            'id' => $this->session->userdata('id'),
            'nama' => $this->session->userdata('nama') ?: $this->session->userdata('username'),
            'username' => $username,
            'departemen' => $departemen,
            'lv' => $lv,
            'jobdesk' => $jobdesk,
            'is_admin' => (bool)$this->session->userdata('is_admin_dashboard') || $username === 'admin' || $lv >= 99 || in_array($jobdesk, array('ADMIN', 'SUPER ADMIN', 'SUPERADMIN', 'ADMINICS'), true),
        );
    }

    public function module_sections($context = array())
    {
        $retur_pj_route = $this->get_retur_penjualan_route($context);
        $retur_pb_route = $this->get_retur_pembelian_route($context);
        $piutang_customer_route = $this->get_piutang_customer_route($context);
        $sections = array(
            'keuangan' => array(
                'label' => 'KEUANGAN',
                'icon' => 'fas fa-wallet',
                'description' => 'Kontrol stok harian, master barang, dan laporan keuangan operasional.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.'),
                    $this->menu('Daily Stock', 'keuangan', 'fas fa-chart-line', 'blue', 'Pantau dashboard daily stock dan ringkasan data keuangan.'),
                    $this->menu('Jurnal Pembelian', 'keuangan/pembelian', 'fas fa-shopping-cart', 'orange', 'Akses modul jurnal pembelian dan monitoring PO keuangan.'),
                    $this->menu('Pembayaran Supplier', 'keuangan/pembayaran-supplier', 'fas fa-money-check-alt', 'green', 'Bayar hutang supplier dari LPB, retur, invoice, dan jurnal hutang.'),
                    $this->menu('Jurnal Penjualan', 'keuangan/penjualan', 'fas fa-handshake', 'green', 'Akses modul jurnal penjualan dan jurnal pembayaran.'),
                    $this->menu('Retur Pembelian', $retur_pb_route, 'fas fa-undo-alt', 'red', 'Akses retur pembelian untuk verifikasi dampak stok serta jurnal.'),
                    $this->menu('Penyesuaian Barang', 'persediaan/penyesuaian_barang', 'fas fa-sliders-h', 'teal', 'Kelola penyesuaian persediaan barang gudang (penambahan/pengurangan stok).'),
                    $this->menu('Daily Stock Lot', 'daily_stock_lot', 'fas fa-layer-group', 'teal', 'Buka stok harian berbasis lot untuk rekonsiliasi persediaan.'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-boxes', 'slate', 'Kelola master barang yang dipakai modul keuangan dan stok.'),
                    $this->menu('Jurnal', 'jurnal', 'fas fa-book-open', 'purple', 'Kelola chart of accounts dan akun jurnal general ledger.'),
                    $this->menu('Transaksi Jurnal Umum', 'buku_besar/jurnal_umum', 'fas fa-book-open', 'lime', 'Kelola dan rekam transaksi jurnal umum.'),
                    $this->menu('Buku Besar', 'keuangan/buku_besar', 'fas fa-book', 'blue', 'Buka laporan buku besar / general ledger per akun.'),
                    $this->menu('Kas Keluar', 'keuangan/kas_keluar', 'fas fa-money-check-alt', 'teal', 'Kelola transaksi pengeluaran kas / bank dan posting jurnal umum.'),
                    $this->menu('Kas Masuk', 'keuangan/kas_masuk', 'fas fa-cash-register', 'green', 'Kelola transaksi penerimaan kas / bank dan posting jurnal umum.'),
                    $this->menu('Kasir', 'keuangan/kasir', 'fas fa-cash-register', 'brown', 'Kelola transaksi kas masuk & kas keluar kasir harian.'),
                    $this->menu('Piutang Customer', $piutang_customer_route, 'fas fa-cash-register', 'blue', 'Kelola piutang customer dan input pembayaran faktur.'),
                    $this->menu('Test Market', 'test-market', 'fas fa-flask', 'cyan', 'Pantau barang Test Market di kios, realisasi pembelian, dan sisa titipan.'),
                    $this->menu('Pecah Faktur', 'sales_order/pecah_faktur', 'fas fa-cut', 'orange', 'Kelola dan proses pemecahan Faktur Z menjadi faktur turunan.'),
                    $this->menu('Data Customers', 'data_customers', 'fas fa-address-book', 'slate', 'Kelola data pelanggan (Zahir style).'),
                    $this->menu('Retur Penjualan', $retur_pj_route, 'fas fa-undo-alt', 'orange', 'Kelola data dan input transaksi retur penjualan.'),
                    $this->menu('Semua Laporan', 'laporan', 'fas fa-chart-bar', 'cyan', 'Laporan keuangan, penjualan & piutang, pembelian & hutang, barang, dan lainnya.'),
                ),
            ),
            'hrd' => array(
                'label' => 'HRD',
                'icon' => 'fas fa-users',
                'description' => 'Akses pengelolaan user, struktur kerja, dan penilaian lingkungan.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.'),
                    $this->menu('Penilaian Lingkungan', 'penilaian_lingkungan', 'fas fa-clipboard-check', 'green', 'Masuk ke modul penilaian lingkungan kerja.'),
                    $this->menu('Admin Penilaian', 'hrd/penilaian_lingkungan/admin', 'fas fa-user-shield', 'purple', 'Panel admin untuk monitoring dan konfigurasi penilaian.'),
                    $this->menu('User Management', 'master/user-management', 'fas fa-user-cog', 'dark', 'Kelola akun, level akses, dan status user.'),
                    $this->menu('Schedule Direktur', 'schedule_direktur', 'fas fa-calendar-check', 'cyan', 'Kelola dan pantau jadwal direktur.'),
                ),
            ),
            'logistik' => array(
                'label' => 'LOGISTIK',
                'icon' => 'fas fa-warehouse',
                'description' => 'Pintu masuk operasional gudang, ICS, distribusi, dan stockopname.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.'),
                    $this->menu('Delivery Order', 'logistik', 'fas fa-truck', 'cyan', 'Kelola data Delivery Order, verifikasi SO loading, dan pengiriman barang.'),
                    $this->menu('Activity Warehouse', 'checker', 'fas fa-warehouse', 'orange', 'Akses aktivitas bongkar muat dan loading barang gudang / checker.'),
                    $this->menu('Data All Barang', 'ics/by_allbarang', 'fas fa-boxes', 'blue', 'Pantau seluruh stok barang ICS lintas gudang dalam satu pintu data.'),
                    $this->menu('Data By Expired Date', 'ics/by_expdate', 'fas fa-calendar-alt', 'orange', 'Lihat barang berdasarkan tanggal expired untuk prioritas kontrol stok.'),
                    $this->menu('Data DO', 'ics/icsdo', 'fas fa-truck-loading', 'slate', 'Masuk ke data Delivery Order dan arus keluar barang.'),
                    $this->menu('Master Gudang', 'ics/gudang', 'fas fa-warehouse', 'green', 'Kelola master gudang dan wilayah penyimpanan.'),
                    $this->menu('Data PO', 'ics/icspo', 'fas fa-file-invoice', 'red', 'Buka data Purchase Order untuk kontrol barang masuk.'),
                    $this->menu('Data LPB', 'ics/data_lpb', 'fas fa-clipboard-list', 'teal', 'Pantau progress penerimaan barang dari PO berdasarkan status belum, partial, dan done.'),
                    $this->menu('Input LPB Manual', 'ics/lpb_manual', 'fas fa-keyboard', 'green', 'Input penerimaan fisik LPB tanpa PO (draft, harga diisi oleh Purchasing).'),
                    $this->menu('Laporan LPB', 'ics/lpb_report', 'fas fa-chart-bar', 'cyan', 'Pantau LPB manual dan LPB hasil input Logistik.'),
                    $this->menu('Retur Pembelian', $retur_pb_route, 'fas fa-undo-alt', 'orange', 'Buka dashboard retur pembelian berbasis stok gudang.'),
                    $this->menu('Master Barang PIC', 'ics/barangpic', 'fas fa-user-check', 'lime', 'Atur daftar barang yang menjadi tanggung jawab PIC.'),
                    $this->menu('Barang Per Gudang', 'ics/barangpergudang', 'fas fa-dolly-flatbed', 'purple', 'Cek komposisi barang pada setiap gudang.'),
                    $this->menu('Mutasi Barang Gudang', 'ics/mutasi_barang', 'fas fa-exchange-alt', 'teal', 'Telusuri perpindahan barang antar gudang secara operasional.'),
                    $this->menu('Penyesuaian Barang', 'persediaan/penyesuaian_barang', 'fas fa-sliders-h', 'purple', 'Kelola penyesuaian persediaan barang gudang (penambahan/pengurangan stok).'),
                    $this->menu('Show Diffrent', 'ics/ics_diffrent', 'fas fa-eye', 'dark', 'Bandingkan selisih data stok ICS untuk tindakan koreksi.'),
                    $this->menu('List Faktur Terkirim / Belum', 'logistik/distibusi/list_faktur_status', 'fas fa-file-signature', 'brown', 'Monitor status faktur distribusi yang sudah atau belum terkirim.'),
                    $this->menu('Export Data Expired Date', 'export-stock', 'fas fa-file-export', 'cyan', 'Unduh data expired date untuk kebutuhan pelaporan dan audit.'),
                    $this->menu('Stockopname', 'admin/stockopname', 'fas fa-tasks', 'blue', 'Buka dashboard admin stockopname dan monitoring stok fisik.'),
                    $this->menu('Retur Penjualan', $retur_pj_route, 'fas fa-undo-alt', 'orange', 'Kelola data dan input transaksi retur penjualan.'),
                    $this->menu('Realisasi Paket Bundling', 'logistik/bundling', 'fas fa-boxes', 'blue', 'Monitoring request, mutasi bahan, dan perakitan (assembly) paket bundling.')
                ),
            ),
            'purchasing' => array(
                'label' => 'PURCHASING',
                'icon' => 'fas fa-shopping-cart',
                'description' => 'Akses pembelian, LPB, monitoring PO, dan paket bundling.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.'),
                    $this->menu('Request Paket Bundling', 'purchasing/bundling/request', 'fas fa-boxes', 'blue', 'Permintaan dan monitoring perakitan paket bundling ke Logistik.'),
                    $this->menu('Barang Konsinyasi', 'purchasing/konsinyasi', 'fas fa-handshake', 'purple', 'Penerimaan barang titipan supplier dan penyelesaian tagihan konsinyasi yang terjual.'),
                    $this->menu('Data PO', 'ics/icspo', 'fas fa-file-invoice', 'red', 'Buka data PO dan LPB yang berjalan.'),
                    $this->menu('Data LPB', 'ics/data_lpb', 'fas fa-clipboard-list', 'teal', 'Pantau data LPB dan progress penerimaan barang dari PO.'),
                    $this->menu('Input LPB Manual', 'ics/lpb_manual', 'fas fa-keyboard', 'green', 'Input LPB tanpa data PO dengan lot dan expired manual.'),
                    $this->menu('Laporan LPB', 'ics/lpb_report', 'fas fa-chart-bar', 'cyan', 'Pantau LPB manual Purchasing dan LPB hasil input Logistik.'),
                    $this->menu('Retur Pembelian', $retur_pb_route, 'fas fa-undo-alt', 'orange', 'Akses retur pembelian dari LPB final dan monitoring retur.'),
                    $this->menu('Pending PO', 'pendingpo', 'fas fa-hourglass-half', 'orange', 'Pantau PO yang masih membutuhkan tindak lanjut.'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-box', 'slate', 'Buka master barang untuk referensi pembelian.'),
                ),
            ),
            'it' => array(
                'label' => 'IT',
                'icon' => 'fas fa-laptop-code',
                'description' => 'Akses modul lintas departemen untuk support dan validasi teknis.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.'),
                    $this->menu('Log LPB Manual', 'ics/lpb_manual_log', 'fas fa-terminal', 'dark', 'Pantau log sistem khusus LPB Manual.'),
                    $this->menu('Retur Pembelian', 'ics/retur', 'fas fa-undo-alt', 'red', 'Buka dashboard retur pembelian untuk support workflow Logistik, Purchasing, dan Keuangan.'),
                ),
            ),
            'sales' => array(
                'label' => 'SALES',
                'icon' => 'fas fa-handshake',
                'description' => 'Akses penjualan, katalog, order, dan laporan sales.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.'),
                    $this->menu('Admin SC', 'sales_order/admin_sc', 'fas fa-file-invoice-dollar', 'purple', 'Kelola verifikasi SO, proses faktur, pecah faktur, dan rute pengiriman Admin SC.'),
                    $this->menu('Sales Order', 'sales_order', 'fas fa-file-signature', 'blue', 'Kelola dokumen sales order dan approval.'),
                    $this->menu('Sales Order Loby', 'sales_order_loby', 'fas fa-store', 'orange', 'Penjualan langsung / walk-in cash customer di Loby.'),
                    $this->menu('Pecah Faktur', 'sales_order/pecah_faktur', 'fas fa-cut', 'teal', 'Kelola dan proses pemecahan Faktur Z menjadi faktur turunan.'),
                    $this->menu('Faktur Penjualan', 'admin/transaksi', 'fas fa-file-invoice-dollar', 'cyan', 'Kelola, audit, edit kuantitas & harga faktur penjualan serta sinkronisasi jurnal.'),
                    $this->menu('Test Market', 'test-market', 'fas fa-flask', 'cyan', 'Pantau barang Test Market yang dititipkan, dibeli, dan masih tersisa di kios.'),
                    $this->menu('Katalog Sales', 'kiu_katalog', 'fas fa-store', 'green', 'Buka katalog penjualan untuk tim sales.'),
                    $this->menu('Sales Report', 'sales_report', 'fas fa-chart-bar', 'purple', 'Pantau laporan sales counter dan aktivitas penjualan.'),
                    $this->menu('Stok Online', 'stock', 'fas fa-box-open', 'teal', 'Cek stok online yang dipakai kanal sales.'),
                    $this->menu('Data Customers', 'data_customers', 'fas fa-address-book', 'slate', 'Kelola data pelanggan (Zahir style).'),
                    $this->menu('Retur Penjualan', $retur_pj_route, 'fas fa-undo-alt', 'orange', 'Kelola data dan input transaksi retur penjualan.'),
                    $this->menu('Pengajuan OD', 'sales/C_PengajuanOD', 'fas fa-calendar-alt', 'red', 'Kelola pengajuan overdue (OD) untuk tempo faktur.'),
                ),
            ),
        );

        if (!empty($context['is_admin'])) {
            $sections = array('admin' => $this->admin_section($sections)) + $sections;
        }

        $sections = $this->apply_access_rules($sections, $context);

        // Batasi tampilan dashboard ADMPNJ hanya section penjualan/sales
        $jobdesk = strtoupper(trim((string)($context['jobdesk'] ?? '')));
        if ($jobdesk === 'ADMPNJ' && isset($sections['sales'])) {
            $sections = array('sales' => $sections['sales']);
        }

        return $sections;
    }

    public function default_active_section(array $context, array $sections)
    {
        if (!empty($context['is_admin']) && isset($sections['admin'])) {
            return 'admin';
        }

        $jobdesk = $context['jobdesk'];
        $map = array(
            // Keuangan & Accounting
            'ADMINKEU' => 'keuangan',
            'ADMINKEUTC' => 'keuangan',
            'ADMIN JURNAL PAJAK' => 'keuangan',
            'ADMIN KEUANGAN' => 'keuangan',
            'ADMIN JURNAL' => 'keuangan',
            'MT ADMIN ACCOUNTING' => 'keuangan',
            'MT MANAGER ACCOUNTING & TAX' => 'keuangan',
            'MT MANAGER KEUANGAN' => 'keuangan',
            'TEAM COLLECTION' => 'keuangan',
            'MT TEAM COLLECTION' => 'keuangan',
            'COLLECTION' => 'keuangan',
            'KOLEKTOR' => 'keuangan',
            'KASIR' => 'keuangan',
            'DIREKTUR' => 'keuangan',
            'DIREKSI' => 'keuangan',
            'DIREKTUR UTAMA' => 'keuangan',
            'DIREKTURUTAMA' => 'keuangan',
            'MT DIREKTUR OPERASIONAL' => 'keuangan',
            'DIREKTUROP' => 'keuangan',

            // HRD & GA
            'ADMINGA' => 'hrd',
            'SUPERADMIN' => 'hrd',
            'HRD & GA' => 'hrd',
            'MT HRD & GA' => 'hrd',
            'MANAGER HRD & GA' => 'hrd',
            'GENERAL AFFAIR' => 'hrd',
            'ADMIN GA' => 'hrd',

            // Logistik & Gudang
            'LOGISTIK' => 'logistik',
            'LOGISTIC' => 'logistik',
            'ADMIN GUDANG' => 'logistik',
            'MT KADEP LOGISTIK' => 'logistik',
            'ADMIN LPB & GUDANG BENIH' => 'logistik',
            'MT ADMIN LOGISTIK' => 'logistik',
            'MT MANAGER WAREHOUSE' => 'logistik',
            'MT MANAGER DISTRIBUSI' => 'logistik',
            'ADMIN DISTRIBUSI & MANAGER WAREHOUSE' => 'logistik',
            'CHECKER' => 'logistik',
            'MT CHECKER' => 'logistik',
            'DRIVER' => 'logistik',
            'HELPER' => 'logistik',
            'ADMINICS' => 'logistik',
            'DISTRIBUSI' => 'logistik',
            'STOCKOPNAME' => 'logistik',
            'ADMRETUR' => 'logistik',
            'ADMLPB' => 'logistik',
            'ADMINLOGLPB' => 'logistik',

            // Purchasing
            'ADMINPURCHASING' => 'purchasing',
            'ADMIN PURCHASING' => 'purchasing',
            'MT KADEP PURCHASING' => 'purchasing',
            'ADMIN PO' => 'purchasing',
            'PURCHASING' => 'purchasing',

            // IT
            'IT' => 'it',
            'IT HARDWARE' => 'it',
            'IT SOFTWARE' => 'it',
            'MT IT SOFTWARE' => 'it',
            'ADMINIT' => 'it',
            'ADMIN IT' => 'it',
            'PROGRAMMER' => 'it',

            // Sales & Unit Bisnis
            'SALES' => 'sales',
            'SALESONLINE' => 'sales',
            'SALESCOUNTER' => 'sales',
            'SALES COUNTER' => 'sales',
            'MT SALES COUNTER' => 'sales',
            'SC' => 'sales',
            'ADMINSC' => 'sales',
            'ADMSC' => 'sales',
            'ADMIN SALES COUNTER' => 'sales',
            'MT ADMIN SALES COUNTER' => 'sales',
            'LOBY' => 'sales',
            'SALESLOBY' => 'sales',
            'ADMINLOBY' => 'sales',
            'MNGSC' => 'sales',
            'MANAGER SC' => 'sales',
            'MANAGERSC' => 'sales',
            'MANAGER SALES COUNTER' => 'sales',
            'MANAGER SALES EXECUTIVE' => 'sales',
            'MANAGERSE' => 'sales',
            'KADEPSC' => 'sales',
            'KADEPKS' => 'sales',
            'MT KADEP KEUANGAN & SALES' => 'sales',
            'MANAGERTC' => 'sales',
            'MNGTC' => 'sales',
            'KADEPUB' => 'sales',
            'KEPALA UNIT BISNIS' => 'sales',
            'MT ABM SEED JATIM TIMUR' => 'sales',
            'ADMPNJ' => 'sales',
            'ADMIN PRICELIST & RETUR PENJUALAN' => 'sales',
        );

        if (isset($map[$jobdesk]) && isset($sections[$map[$jobdesk]])) {
            return $map[$jobdesk];
        }

        $departemen = $context['departemen'] ?? '';
        if (strpos($departemen, 'LOGISTI') !== false && isset($sections['logistik'])) return 'logistik';
        if (strpos($departemen, 'PURCHAS') !== false && isset($sections['purchasing'])) return 'purchasing';
        if (strpos($departemen, 'IT') !== false && isset($sections['it'])) return 'it';
        if (strpos($departemen, 'HRD') !== false && isset($sections['hrd'])) return 'hrd';
        if ((strpos($departemen, 'SALES') !== false || strpos($departemen, 'BISNIS') !== false) && isset($sections['sales'])) return 'sales';
        if (strpos($departemen, 'KEUANGAN') !== false && isset($sections['keuangan'])) return 'keuangan';

        return key($sections);
    }

    private function apply_access_rules(array $sections, array $context)
    {
        $allowed_retur_pj = array(
            'SC', 'SALESCOUNTER', 'SALES COUNTER', 'MT SALES COUNTER',
            'ADMINSC', 'ADMSC', 'ADMIN SALES COUNTER', 'MT ADMIN SALES COUNTER',
            'MANAGERSC', 'MNGSC', 'MANAGER SC', 'MANAGER SALES COUNTER',
            'ADMRETUR', 'ADMPNJ', 'ADMSTOCK', 'ADMIN PRICELIST & RETUR PENJUALAN',
            'KADEPSC', 'KADEPKS', 'MT KADEP KEUANGAN & SALES',
            'ADMLPB', 'ADMINLOGLPB', 'ADMIN LPB & GUDANG BENIH',
            'LOGISTIC', 'LOGISTIK', 'MT KADEP LOGISTIK', 'ADMIN GUDANG',
            'COLLECTION', 'KOLEKTOR', 'TEAM COLLECTION', 'MT TEAM COLLECTION',
            'KASIR', 'ADMIN',
            'KADEPUB', 'KEPALA UNIT BISNIS', 'MT ABM SEED JATIM TIMUR',
            'MANAGERACC', 'MT MANAGER ACCOUNTING & TAX', 'MT MANAGER KEUANGAN', 'MANAGERTC', 'MNGTC',
            'MANAGERSE', 'MANAGER SALES EXECUTIVE',
            'DIREKTUROP', 'MT DIREKTUR OPERASIONAL',
            'DIREKTURUTAMA', 'DIREKSI', 'DIREKTUR', 'DIREKTUR UTAMA'
        );
        $has_retur_pj = in_array(strtoupper((string)($context['jobdesk'] ?? '')), $allowed_retur_pj, true)
            || in_array(strtolower((string)($context['username'] ?? '')), array('admlpb', 'yunika'), true)
            || !empty($context['is_admin']);

        $jobdesk = strtoupper((string)($context['jobdesk'] ?? ''));
        $username = strtolower((string)($context['username'] ?? ''));
        $allowed_admin_sc = array(
            'ADMINSC', 'ADMSC', 'ADMIN SALES COUNTER', 'MT ADMIN SALES COUNTER',
            'SC', 'SALESCOUNTER', 'SALES COUNTER', 'MT SALES COUNTER',
            'ADMIN', 'MNGSC', 'MANAGER SC', 'MANAGERSC', 'MANAGER SALES COUNTER',
            'KADEPSC', 'KADEPKS', 'MT KADEP KEUANGAN & SALES'
        );
        $has_admin_sc = in_array($jobdesk, $allowed_admin_sc, true)
            || in_array($username, array('admsc', 'nina', 'admin'), true)
            || !empty($context['is_admin']);
        $is_admin_sc_only = in_array($jobdesk, array('ADMINSC', 'ADMSC', 'ADMIN SALES COUNTER', 'MT ADMIN SALES COUNTER'), true)
            || in_array($username, array('admsc', 'nina'), true);

        foreach ($sections as $key => $section) {
            if (empty($section['menus']) || !is_array($section['menus'])) {
                continue;
            }

            $sections[$key]['menus'] = array_values(array_filter($section['menus'], function ($menu) use ($context, $has_retur_pj, $has_admin_sc, $is_admin_sc_only) {
                $route = (string)($menu['route'] ?? '');

                if ($route === 'sales_order/admin_sc') {
                    return $has_admin_sc;
                }

                if ($route === 'sales_order' && $is_admin_sc_only) {
                    return false;
                }

                if ($route === 'ics/retur' || strpos($route, 'ics/retur/pembelian/monitoring') === 0) {
                    return $this->has_retur_access($context);
                }

                if (strpos($route, 'retur_penjualan') === 0) {
                    return $has_retur_pj;
                }

                return true;
            }));

            if ($key === 'it' && empty($sections[$key]['menus'])) {
                unset($sections[$key]);
            }
        }

        return $sections;
    }

    private function get_retur_pembelian_route($context)
    {
        $jobdesk = strtoupper((string)($context['jobdesk'] ?? ''));
        $username = strtolower((string)($context['username'] ?? ''));
        $departemen = strtoupper((string)($context['departemen'] ?? ''));

        if (in_array($jobdesk, array('ADMLPB', 'ADMINLOGLPB', 'ADMIN LPB & GUDANG BENIH'), true)
            || in_array($username, array('admlpb', 'adminloglpb', 'yunika'), true)
            || (in_array($departemen, array('LOGISTIK', 'LOGISTIC'), true) && strpos($jobdesk, 'LPB') !== false)) {
            return 'ics/retur/pembelian/monitoring';
        }
        return 'ics/retur';
    }

    private function get_retur_penjualan_route($context)
    {
        $jobdesk = strtoupper((string)($context['jobdesk'] ?? ''));
        $username = strtolower((string)($context['username'] ?? ''));
        if (in_array($jobdesk, array('LOGISTIC', 'MT KADEP LOGISTIK'), true) || in_array($username, array('sprlog', 'kurniawan'), true)) {
            return 'retur_penjualan/logistik';
        } elseif (in_array($username, array('admlpb', 'yunika'), true) || in_array($jobdesk, array('ADMLPB', 'ADMINLOGLPB', 'ADMIN LPB & GUDANG BENIH'), true)) {
            return 'retur_penjualan/admlpb';
        } elseif (in_array($jobdesk, array(
            'ADMRETUR', 'COLLECTION', 'KOLEKTOR', 'TEAM COLLECTION', 'MT TEAM COLLECTION',
            'KASIR', 'MANAGERACC', 'MT MANAGER ACCOUNTING & TAX', 'MT MANAGER KEUANGAN',
            'MANAGERSE', 'MANAGER SALES EXECUTIVE',
            'DIREKTUROP', 'MT DIREKTUR OPERASIONAL',
            'DIREKTURUTAMA', 'DIREKSI', 'DIREKTUR UTAMA'
        ), true)) {
            return 'retur_penjualan/retur';
        }
        return 'retur_penjualan';
    }

    private function get_piutang_customer_route($context)
    {
        $jobdesk = strtoupper((string)($context['jobdesk'] ?? ''));
        if ($jobdesk === 'KASIR') {
            return 'keuangan/pembayaran/kasir';
        }
        if (in_array($jobdesk, array('COLLECTION', 'KOLEKTOR', 'TEAM COLLECTION', 'MT TEAM COLLECTION'), true)) {
            return 'keuangan/pembayaran/collection';
        }
        return 'keuangan/pembayaran';
    }

    private function has_retur_access(array $context)
    {
        if (!empty($context['is_admin'])) {
            return true;
        }

        $departemen = strtoupper(trim((string)($context['departemen'] ?? '')));
        $jobdesk = strtoupper(trim((string)($context['jobdesk'] ?? '')));

        foreach (array('LOGISTIK', 'LOGISTIC', 'PURCHASING', 'KEUANGAN', 'FINANCE', 'ACCOUNTING', 'IT', 'DIREKSI') as $allowed) {
            if ($departemen === $allowed || strpos($departemen, $allowed) !== false) {
                return true;
            }
        }

        return in_array($jobdesk, array(
            'LOGISTIK',
            'LOGISTIC',
            'ADMIN GUDANG',
            'MT KADEP LOGISTIK',
            'ADMIN LPB & GUDANG BENIH',
            'MT ADMIN LOGISTIK',
            'MT MANAGER WAREHOUSE',
            'MT MANAGER DISTRIBUSI',
            'ADMIN DISTRIBUSI & MANAGER WAREHOUSE',
            'ADMLPB',
            'ADMINLOGLPB',
            'ADMINICS',
            'ADMLOG',
            'ADMINPURCHASING',
            'ADMIN PURCHASING',
            'MT KADEP PURCHASING',
            'ADMIN PO',
            'PURCHASING',
            'ADMINKEU',
            'ADMINKEUTC',
            'ADMIN JURNAL PAJAK',
            'ADMIN KEUANGAN',
            'ADMIN JURNAL',
            'MT ADMIN ACCOUNTING',
            'MT MANAGER ACCOUNTING & TAX',
            'MT MANAGER KEUANGAN',
            'MT KADEP KEUANGAN & SALES',
            'KIUKEU',
            'KEUANGAN',
            'ACCOUNTING',
            'FINANCE',
            'IT',
            'IT HARDWARE',
            'IT SOFTWARE',
            'MT IT SOFTWARE',
            'ADMINIT',
            'ADMIN IT',
            'PROGRAMMER',
            'DEVELOPMENT',
            'DIREKSI',
            'DIREKTUR',
            'MT DIREKTUR OPERASIONAL'
        ), true);
    }

    private function admin_section(array $sections)
    {
        $menus = array();
        $seenRoutes = array();
        $adminMenus = array(
            $this->menu('Semua Transaksi', 'admin/transaksi', 'fas fa-exchange-alt', 'teal', 'Kelola, audit, unpost, edit, dan sinkronisasi seluruh transaksi & jurnal akuntansi.'),
            $this->menu('User Management', 'master/user-management', 'fas fa-users-cog', 'dark', 'Kelola akun, level akses, status user, dan reset password.'),
            $this->menu('Fasilitas Per User', 'master/user-facility', 'fas fa-user-lock', 'blue', 'Kelola fasilitas menu, aksi, data sensitif, cabang, gudang, dan status dokumen per user.'),
            $this->menu('Jobdesk', 'master/jobdesk', 'fas fa-briefcase', 'slate', 'Kelola master jobdesk yang dipakai rules akses aplikasi.'),
            $this->menu('Akses Level', 'master/akses-level', 'fas fa-key', 'orange', 'Atur level akses dan matrix permission user.'),
            $this->menu('Menu Aplikasi', 'master/menu', 'fas fa-bars', 'cyan', 'Kelola struktur menu dinamis aplikasi.'),
        );

        foreach ($adminMenus as $menu) {
            $this->append_unique_menu($menus, $seenRoutes, $menu);
        }

        foreach ($this->all_module_menus($sections) as $menu) {
            $this->append_unique_menu($menus, $seenRoutes, $menu);
        }

        foreach ($this->dynamic_app_menus($seenRoutes) as $menu) {
            $this->append_unique_menu($menus, $seenRoutes, $menu);
        }

        return array(
            'label' => 'ADMIN',
            'icon' => 'fas fa-user-shield',
            'description' => 'Panel admin berisi master akses dan seluruh modul aplikasi yang tersedia di dashboard.',
            'menus' => $menus,
        );
    }

    private function all_module_menus(array $sections)
    {
        $menus = array();
        $seenRoutes = array();

        foreach ($sections as $section) {
            if (empty($section['menus']) || !is_array($section['menus'])) {
                continue;
            }

            foreach ($section['menus'] as $menu) {
                if (empty($menu['route']) || isset($seenRoutes[$menu['route']])) {
                    continue;
                }

                $seenRoutes[$menu['route']] = true;
                $menus[] = $menu;
            }
        }

        return $menus;
    }

    private function dynamic_app_menus(array $seenRoutes)
    {
        if (!isset($this->db) || !$this->db->table_exists('tb_menu')) {
            return array();
        }

        $fields = $this->db->list_fields('tb_menu');
        $nameField = $this->first_available_field($fields, array('nama_menu', 'menu_name', 'title', 'label'));
        $urlField = $this->first_available_field($fields, array('url', 'url_menu', 'route'));
        $iconField = $this->first_available_field($fields, array('icon', 'icon_menu'));
        $orderField = $this->first_available_field($fields, array('urutan', 'sort_order', 'order_no', 'id_menu', 'id'));

        if (!$nameField || !$urlField) {
            return array();
        }

        if (in_array('status', $fields, true)) {
            $this->db->where('status', 1);
        }

        if ($orderField) {
            $this->db->order_by($orderField, 'ASC');
        }

        $rows = $this->db->get('tb_menu')->result_array();
        $menus = array();
        $tones = array('blue', 'orange', 'slate', 'green', 'red', 'lime', 'purple', 'teal', 'dark', 'brown', 'cyan');
        $toneIndex = 0;

        foreach ($rows as $row) {
            $route = trim((string)$row[$urlField]);
            $title = trim((string)$row[$nameField]);

            if ($route === '' || $route === '#' || $title === '' || isset($seenRoutes[$route])) {
                continue;
            }

            $menus[] = $this->menu(
                $title,
                $route,
                !empty($iconField) && !empty($row[$iconField]) ? $row[$iconField] : 'fas fa-th-large',
                $tones[$toneIndex % count($tones)],
                'Buka modul ' . $title . ' dari menu aplikasi.'
            );
            $seenRoutes[$route] = true;
            $toneIndex++;
        }

        return $menus;
    }

    private function first_available_field(array $fields, array $candidates)
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $fields, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function append_unique_menu(array &$menus, array &$seenRoutes, array $menu)
    {
        if (empty($menu['route']) || isset($seenRoutes[$menu['route']])) {
            return;
        }

        $seenRoutes[$menu['route']] = true;
        $menus[] = $menu;
    }

    private function menu($title, $route, $icon, $tone, $description)
    {
        return array(
            'title' => $title,
            'route' => $route,
            'icon' => $icon,
            'tone' => $tone,
            'description' => $description,
        );
    }
}
