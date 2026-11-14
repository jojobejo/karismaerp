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

    /**
     * Cek apakah user yang login adalah Administrator sejati untuk mengelola hak akses
     */
    public function is_strict_admin($context = null)
    {
        if (!is_array($context)) {
            $context = $this->current_user_context();
        }
        $lv = (int)($context['lv'] ?? 0);
        $jobdesk = strtoupper(trim((string)($context['jobdesk'] ?? '')));
        $username = strtolower(trim((string)($context['username'] ?? '')));

        return (bool)$this->session->userdata('is_admin_dashboard')
            || $username === 'admin'
            || $lv >= 99
            || in_array($jobdesk, array('ADMIN', 'SUPER ADMIN', 'SUPERADMIN'), true);
    }

    /**
     * Definisi mentah seluruh section dan menu dashboard (single source of truth)
     */
    public function base_module_sections($context = array())
    {
        $retur_pj_route = $this->get_retur_penjualan_route($context);
        $retur_pb_route = $this->get_retur_pembelian_route($context);
        $piutang_customer_route = $this->get_piutang_customer_route($context);

        return array(
            'keuangan_sales' => array(
                'label' => 'KEUANGAN & SALES',
                'icon' => 'fas fa-wallet',
                'description' => 'Kontrol stok harian, keuangan operasional, penjualan, katalog, dan laporan sales.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.', 'kas_bon'),
                    $this->menu('Daily Stock', 'keuangan', 'fas fa-chart-line', 'blue', 'Pantau dashboard daily stock dan ringkasan data keuangan.', 'daily_stock'),
                    $this->menu('Jurnal Pembelian', 'keuangan/pembelian', 'fas fa-shopping-cart', 'orange', 'Akses modul jurnal pembelian dan monitoring PO keuangan.', 'jurnal_pembelian'),
                    $this->menu('Pembayaran Supplier', 'keuangan/pembayaran-supplier', 'fas fa-money-check-alt', 'green', 'Bayar hutang supplier dari LPB, retur, invoice, dan jurnal hutang.', 'pembayaran_supplier'),
                    $this->menu('Jurnal Penjualan', 'keuangan/penjualan', 'fas fa-handshake', 'green', 'Akses modul jurnal penjualan dan jurnal pembayaran.', 'jurnal_penjualan'),
                    $this->menu('Retur Pembelian', $retur_pb_route, 'fas fa-undo-alt', 'red', 'Akses retur pembelian untuk verifikasi dampak stok serta jurnal.', 'retur_pembelian'),
                    $this->menu('Penyesuaian Barang', 'persediaan/penyesuaian_barang', 'fas fa-sliders-h', 'teal', 'Kelola penyesuaian persediaan barang gudang (penambahan/pengurangan stok).', 'penyesuaian_barang'),
                    $this->menu('Daily Stock Lot', 'daily_stock_lot', 'fas fa-layer-group', 'teal', 'Buka stok harian berbasis lot untuk rekonsiliasi persediaan.', 'daily_stock_lot'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-boxes', 'slate', 'Kelola master barang yang dipakai modul keuangan dan stok.', 'master_barang'),
                    $this->menu('Jurnal', 'jurnal', 'fas fa-book-open', 'purple', 'Kelola chart of accounts dan akun jurnal general ledger.', 'jurnal'),
                    $this->menu('Transaksi Jurnal Umum', 'buku_besar/jurnal_umum', 'fas fa-book-open', 'lime', 'Kelola dan rekam transaksi jurnal umum.', 'jurnal_umum'),
                    $this->menu('Buku Besar', 'keuangan/buku_besar', 'fas fa-book', 'blue', 'Buka laporan buku besar / general ledger per akun.', 'buku_besar'),
                    $this->menu('Kas Keluar', 'keuangan/kas_keluar', 'fas fa-money-check-alt', 'teal', 'Kelola transaksi pengeluaran kas / bank dan posting jurnal umum.', 'kas_keluar'),
                    $this->menu('Kas Masuk', 'keuangan/kas_masuk', 'fas fa-cash-register', 'green', 'Kelola transaksi penerimaan kas / bank dan posting jurnal umum.', 'kas_masuk'),
                    $this->menu('Kasir', 'keuangan/kasir', 'fas fa-cash-register', 'brown', 'Kelola transaksi kas masuk & kas keluar kasir harian.', 'kasir'),
                    $this->menu('Piutang Customer', $piutang_customer_route, 'fas fa-cash-register', 'blue', 'Kelola piutang customer dan input pembayaran faktur.', 'piutang_customer'),
                    $this->menu('Test Market', 'test-market', 'fas fa-flask', 'cyan', 'Pantau barang Test Market di kios, realisasi pembelian, dan sisa titipan.', 'test_market'),
                    $this->menu('Pecah Faktur', 'sales_order/pecah_faktur', 'fas fa-cut', 'orange', 'Kelola dan proses pemecahan Faktur Z menjadi faktur turunan.', 'pecah_faktur'),
                    $this->menu('Data Customers', 'data_customers', 'fas fa-address-book', 'slate', 'Kelola data pelanggan (Zahir style).', 'data_customers'),
                    $this->menu('Retur Penjualan', $retur_pj_route, 'fas fa-undo-alt', 'orange', 'Kelola data dan input transaksi retur penjualan.', 'retur_penjualan'),
                    $this->menu('Semua Laporan', 'laporan', 'fas fa-chart-bar', 'cyan', 'Laporan keuangan, penjualan & piutang, pembelian & hutang, barang, dan lainnya.', 'semua_laporan'),
                    $this->menu('Admin SC', 'sales_order/admin_sc', 'fas fa-file-invoice-dollar', 'purple', 'Kelola verifikasi SO, proses faktur, pecah faktur, dan rute pengiriman Admin SC.', 'admin_sc'),
                    $this->menu('Sales Order', 'sales_order', 'fas fa-file-signature', 'blue', 'Kelola dokumen sales order dan approval.', 'sales_order'),
                    $this->menu('Sales Order Loby', 'sales_order_loby', 'fas fa-store', 'orange', 'Penjualan langsung / walk-in cash customer di Loby.', 'sales_order_loby'),
                    $this->menu('Faktur Penjualan', 'admin/transaksi', 'fas fa-file-invoice-dollar', 'cyan', 'Kelola, audit, edit kuantitas & harga faktur penjualan serta sinkronisasi jurnal.', 'faktur_penjualan'),
                    $this->menu('Katalog Sales', 'kiu_katalog', 'fas fa-store', 'green', 'Buka katalog penjualan untuk tim sales.', 'katalog_sales'),
                    $this->menu('Sales Report', 'sales_report', 'fas fa-chart-bar', 'purple', 'Pantau laporan sales counter dan aktivitas penjualan.', 'sales_report'),
                    $this->menu('Stok Online', 'stock', 'fas fa-box-open', 'teal', 'Cek stok online yang dipakai kanal sales.', 'stok_online'),
                    $this->menu('Pengajuan OD', 'sales/C_PengajuanOD', 'fas fa-calendar-alt', 'red', 'Kelola pengajuan overdue (OD) untuk tempo faktur.', 'pengajuan_od'),
                ),
            ),
            'hrd' => array(
                'label' => 'HRD',
                'icon' => 'fas fa-users',
                'description' => 'Akses pengelolaan user, struktur kerja, dan penilaian lingkungan.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.', 'kas_bon'),
                    $this->menu('Penilaian Lingkungan', 'penilaian_lingkungan', 'fas fa-clipboard-check', 'green', 'Masuk ke modul penilaian lingkungan kerja.', 'penilaian_lingkungan'),
                    $this->menu('Admin Penilaian', 'hrd/penilaian_lingkungan/admin', 'fas fa-user-shield', 'purple', 'Panel admin untuk monitoring dan konfigurasi penilaian.', 'admin_penilaian'),
                    $this->menu('User Management', 'master/user-management', 'fas fa-user-cog', 'dark', 'Kelola akun, level akses, dan status user.', 'user_management'),
                    $this->menu('Schedule Direktur', 'schedule_direktur', 'fas fa-calendar-check', 'cyan', 'Kelola dan pantau jadwal direktur.', 'schedule_direktur'),
                ),
            ),
            'logistik' => array(
                'label' => 'LOGISTIK',
                'icon' => 'fas fa-warehouse',
                'description' => 'Pintu masuk operasional gudang, ICS, distribusi, dan stockopname.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.', 'kas_bon'),
                    $this->menu('Delivery Order', 'logistik', 'fas fa-truck', 'cyan', 'Kelola data Delivery Order, verifikasi SO loading, dan pengiriman barang.', 'delivery_order'),
                    $this->menu('Activity Warehouse', 'checker', 'fas fa-warehouse', 'orange', 'Akses aktivitas bongkar muat dan loading barang gudang / checker.', 'activity_warehouse'),
                    $this->menu('Data All Barang', 'ics/by_allbarang', 'fas fa-boxes', 'blue', 'Pantau seluruh stok barang ICS lintas gudang dalam satu pintu data.', 'data_all_barang'),
                    $this->menu('Data By Expired Date', 'ics/by_expdate', 'fas fa-calendar-alt', 'orange', 'Lihat barang berdasarkan tanggal expired untuk prioritas kontrol stok.', 'data_by_expired'),
                    $this->menu('Data DO', 'ics/icsdo', 'fas fa-truck-loading', 'slate', 'Masuk ke data Delivery Order dan arus keluar barang.', 'data_do'),
                    $this->menu('Master Gudang', 'ics/gudang', 'fas fa-warehouse', 'green', 'Kelola master gudang dan wilayah penyimpanan.', 'master_gudang'),
                    $this->menu('Data PO', 'ics/icspo', 'fas fa-file-invoice', 'red', 'Buka data Purchase Order untuk kontrol barang masuk.', 'data_po'),
                    $this->menu('Data LPB', 'ics/data_lpb', 'fas fa-clipboard-list', 'teal', 'Pantau progress penerimaan barang dari PO berdasarkan status belum, partial, dan done.', 'data_lpb'),
                    $this->menu('Input LPB Manual', 'ics/lpb_manual', 'fas fa-keyboard', 'green', 'Input penerimaan fisik LPB tanpa PO (draft, harga diisi oleh Purchasing).', 'input_lpb_manual'),
                    $this->menu('Laporan LPB', 'ics/lpb_report', 'fas fa-chart-bar', 'cyan', 'Pantau LPB manual dan LPB hasil input Logistik.', 'laporan_lpb'),
                    $this->menu('Retur Pembelian', $retur_pb_route, 'fas fa-undo-alt', 'orange', 'Buka dashboard retur pembelian berbasis stok gudang.', 'retur_pembelian'),
                    $this->menu('Master Barang PIC', 'ics/barangpic', 'fas fa-user-check', 'lime', 'Atur daftar barang yang menjadi tanggung jawab PIC.', 'master_barang_pic'),
                    $this->menu('Barang Per Gudang', 'ics/barangpergudang', 'fas fa-dolly-flatbed', 'purple', 'Cek komposisi barang pada setiap gudang.', 'barang_per_gudang'),
                    $this->menu('Mutasi Barang Gudang', 'ics/mutasi_barang', 'fas fa-exchange-alt', 'teal', 'Telusuri perpindahan barang antar gudang secara operasional.', 'mutasi_barang'),
                    $this->menu('Penyesuaian Barang', 'persediaan/penyesuaian_barang', 'fas fa-sliders-h', 'purple', 'Kelola penyesuaian persediaan barang gudang (penambahan/pengurangan stok).', 'penyesuaian_barang'),
                    $this->menu('Show Diffrent', 'ics/ics_diffrent', 'fas fa-eye', 'dark', 'Bandingkan selisih data stok ICS untuk tindakan koreksi.', 'show_diffrent'),
                    $this->menu('List Faktur Terkirim / Belum', 'logistik/distibusi/list_faktur_status', 'fas fa-file-signature', 'brown', 'Monitor status faktur distribusi yang sudah atau belum terkirim.', 'list_faktur_status'),
                    $this->menu('Export Data Expired Date', 'export-stock', 'fas fa-file-export', 'cyan', 'Unduh data expired date untuk kebutuhan pelaporan dan audit.', 'export_expired'),
                    $this->menu('Stockopname', 'admin/stockopname', 'fas fa-tasks', 'blue', 'Buka dashboard admin stockopname dan monitoring stok fisik.', 'stockopname'),
                    $this->menu('Retur Penjualan', $retur_pj_route, 'fas fa-undo-alt', 'orange', 'Kelola data dan input transaksi retur penjualan.', 'retur_penjualan'),
                    $this->menu('Realisasi Paket Bundling', 'logistik/bundling', 'fas fa-boxes', 'blue', 'Monitoring request, mutasi bahan, dan perakitan (assembly) paket bundling.', 'realisasi_bundling')
                ),
            ),
            'purchasing' => array(
                'label' => 'PURCHASING',
                'icon' => 'fas fa-shopping-cart',
                'description' => 'Akses pembelian, LPB, monitoring PO, dan paket bundling.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.', 'kas_bon'),
                    $this->menu('Request Paket Bundling', 'purchasing/bundling/request', 'fas fa-boxes', 'blue', 'Permintaan dan monitoring perakitan paket bundling ke Logistik.', 'request_bundling'),
                    $this->menu('Barang Konsinyasi', 'purchasing/konsinyasi', 'fas fa-handshake', 'purple', 'Penerimaan barang titipan supplier dan penyelesaian tagihan konsinyasi yang terjual.', 'barang_konsinyasi'),
                    $this->menu('Data PO', 'ics/icspo', 'fas fa-file-invoice', 'red', 'Buka data PO dan LPB yang berjalan.', 'data_po'),
                    $this->menu('Data LPB', 'ics/data_lpb', 'fas fa-clipboard-list', 'teal', 'Pantau data LPB dan progress penerimaan barang dari PO.', 'data_lpb'),
                    $this->menu('Input LPB Manual', 'ics/lpb_manual', 'fas fa-keyboard', 'green', 'Input LPB tanpa data PO dengan lot dan expired manual.', 'input_lpb_manual'),
                    $this->menu('Laporan LPB', 'ics/lpb_report', 'fas fa-chart-bar', 'cyan', 'Pantau LPB manual Purchasing dan LPB hasil input Logistik.', 'laporan_lpb'),
                    $this->menu('Retur Pembelian', $retur_pb_route, 'fas fa-undo-alt', 'orange', 'Akses retur pembelian dari LPB final dan monitoring retur.', 'retur_pembelian'),
                    $this->menu('Pending PO', 'pendingpo', 'fas fa-hourglass-half', 'orange', 'Pantau PO yang masih membutuhkan tindak lanjut.', 'pending_po'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-box', 'slate', 'Buka master barang untuk referensi pembelian.', 'master_barang'),
                ),
            ),
            'it' => array(
                'label' => 'IT',
                'icon' => 'fas fa-laptop-code',
                'description' => 'Akses modul lintas departemen untuk support dan validasi teknis.',
                'menus' => array(
                    $this->menu('Kas Bon', 'C_Kasbon', 'fas fa-money-bill-wave', 'lime', 'Form pengajuan dan riwayat Kas Bon.', 'kas_bon'),
                    $this->menu('Log LPB Manual', 'ics/lpb_manual_log', 'fas fa-terminal', 'dark', 'Pantau log sistem khusus LPB Manual.', 'log_lpb_manual'),
                    $this->menu('Retur Pembelian', 'ics/retur', 'fas fa-undo-alt', 'red', 'Buka dashboard retur pembelian untuk support workflow Logistik, Purchasing, dan Keuangan.', 'retur_pembelian'),
                ),
            ),
        );
    }

    public function module_sections($context = array())
    {
        $rawSections = $this->base_module_sections($context);
        // Terapkan pengaturan pengelompokan menu per departemen dari database
        $sections = $this->apply_section_menu_config($rawSections);

        if (!empty($context['is_admin'])) {
            $sections = array('admin' => $this->admin_section($rawSections)) + $sections;
        }

        $sections = $this->apply_access_rules($sections, $context);

        // Untuk user non-admin, tampilkan hanya tab departemen milik user yang login
        if (empty($context['is_admin'])) {
            $jobdesk = strtoupper(trim((string)($context['jobdesk'] ?? '')));
            if ($jobdesk === 'ADMPNJ' && isset($sections['keuangan_sales'])) {
                return array('keuangan_sales' => $sections['keuangan_sales']);
            }

            $userSectionKey = $this->resolve_user_section_key($context, $sections);
            if ($userSectionKey !== null && isset($sections[$userSectionKey])) {
                return array($userSectionKey => $sections[$userSectionKey]);
            }
        }

        return $sections;
    }

    /**
     * Terapkan konfigurasi menu per departemen dari tabel tb_dashboard_section_menus
     */
    public function apply_section_menu_config(array $sections)
    {
        $db = $this->db;
        if (!$db || !$db->table_exists('tb_dashboard_section_menus')) {
            return $sections;
        }

        $rows = $db
            ->order_by('section_key', 'ASC')
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get('tb_dashboard_section_menus')
            ->result_array();

        if (empty($rows)) {
            return $sections;
        }

        // Bangun katalog semua menu yang tersedia (bawaan + menu dinamis)
        $sectionMenuMap = array();
        $globalCatalog = array();
        $seenRoutes = array();

        foreach ($sections as $secKey => $sec) {
            $sectionMenuMap[$secKey] = array();
            foreach (($sec['menus'] ?? array()) as $menu) {
                $mKey = (string)($menu['menu_key'] ?? '');
                if ($mKey === '') {
                    continue;
                }
                $sectionMenuMap[$secKey][$mKey] = $menu;
                if (!isset($globalCatalog[$mKey])) {
                    $globalCatalog[$mKey] = $menu;
                }
                if (!empty($menu['route'])) {
                    $seenRoutes[$menu['route']] = true;
                }
            }
        }

        foreach ($this->dynamic_app_menus($seenRoutes) as $dynMenu) {
            $mKey = (string)($dynMenu['menu_key'] ?? '');
            if ($mKey !== '' && !isset($globalCatalog[$mKey])) {
                $globalCatalog[$mKey] = $dynMenu;
            }
        }

        // Kelompokkan baris konfigurasi berdasarkan section_key
        $configuredBySection = array();
        foreach ($rows as $row) {
            $secKey = (string)($row['section_key'] ?? '');
            if ($secKey !== '') {
                $configuredBySection[$secKey][] = $row;
            }
        }

        foreach ($sections as $secKey => $sec) {
            if (!isset($configuredBySection[$secKey])) {
                continue;
            }

            $newMenus = array();
            $addedKeys = array();
            foreach ($configuredBySection[$secKey] as $row) {
                if ((int)($row['is_visible'] ?? 0) !== 1) {
                    continue;
                }
                $mKey = (string)($row['menu_key'] ?? '');
                if ($mKey === '' || isset($addedKeys[$mKey])) {
                    continue;
                }

                // Prioritaskan definisi menu dari section yang bersangkutan jika ada
                $menuItem = $sectionMenuMap[$secKey][$mKey] ?? ($globalCatalog[$mKey] ?? null);
                if ($menuItem !== null) {
                    $newMenus[] = $menuItem;
                    $addedKeys[$mKey] = true;
                }
            }

            $sections[$secKey]['menus'] = $newMenus;
        }

        return $sections;
    }

    /**
     * Tentukan section departemen milik user berdasarkan kolom departement dan jobdesk
     */
    public function resolve_user_section_key(array $context, array $sections)
    {
        $departemen = strtoupper(trim((string)($context['departemen'] ?? '')));
        if ($departemen !== '') {
            if ((strpos($departemen, 'KEUANGAN') !== false || strpos($departemen, 'SALES') !== false || strpos($departemen, 'KASIR') !== false || strpos($departemen, 'FINANCE') !== false || strpos($departemen, 'BISNIS') !== false) && isset($sections['keuangan_sales'])) {
                return 'keuangan_sales';
            }
            if ((strpos($departemen, 'LOGISTI') !== false || strpos($departemen, 'WAREHOUSE') !== false) && isset($sections['logistik'])) {
                return 'logistik';
            }
            if (strpos($departemen, 'PURCHAS') !== false && isset($sections['purchasing'])) {
                return 'purchasing';
            }
            if ((strpos($departemen, 'HRD') !== false || $departemen === 'GA') && isset($sections['hrd'])) {
                return 'hrd';
            }
            if (($departemen === 'IT' || strpos($departemen, 'IT ') === 0) && isset($sections['it'])) {
                return 'it';
            }
        }

        $jobdesk = strtoupper(trim((string)($context['jobdesk'] ?? '')));
        $map = array(
            // Keuangan, Accounting & Sales
            'ADMINKEU' => 'keuangan_sales',
            'ADMINKEUTC' => 'keuangan_sales',
            'ADMIN JURNAL PAJAK' => 'keuangan_sales',
            'ADMIN KEUANGAN' => 'keuangan_sales',
            'ADMIN JURNAL' => 'keuangan_sales',
            'MT ADMIN ACCOUNTING' => 'keuangan_sales',
            'MT MANAGER ACCOUNTING & TAX' => 'keuangan_sales',
            'MT MANAGER KEUANGAN' => 'keuangan_sales',
            'TEAM COLLECTION' => 'keuangan_sales',
            'MT TEAM COLLECTION' => 'keuangan_sales',
            'COLLECTION' => 'keuangan_sales',
            'KOLEKTOR' => 'keuangan_sales',
            'KASIR' => 'keuangan_sales',
            'DIREKTUR' => 'keuangan_sales',
            'DIREKSI' => 'keuangan_sales',
            'DIREKTUR UTAMA' => 'keuangan_sales',
            'DIREKTURUTAMA' => 'keuangan_sales',
            'MT DIREKTUR OPERASIONAL' => 'keuangan_sales',
            'DIREKTUROP' => 'keuangan_sales',
            'SALES' => 'keuangan_sales',
            'SALESONLINE' => 'keuangan_sales',
            'SALESCOUNTER' => 'keuangan_sales',
            'SALES COUNTER' => 'keuangan_sales',
            'MT SALES COUNTER' => 'keuangan_sales',
            'SC' => 'keuangan_sales',
            'ADMINSC' => 'keuangan_sales',
            'ADMSC' => 'keuangan_sales',
            'ADMIN SALES COUNTER' => 'keuangan_sales',
            'MT ADMIN SALES COUNTER' => 'keuangan_sales',
            'LOBY' => 'keuangan_sales',
            'SALESLOBY' => 'keuangan_sales',
            'ADMINLOBY' => 'keuangan_sales',
            'MNGSC' => 'keuangan_sales',
            'MANAGER SC' => 'keuangan_sales',
            'MANAGERSC' => 'keuangan_sales',
            'MANAGER SALES COUNTER' => 'keuangan_sales',
            'MANAGER SALES EXECUTIVE' => 'keuangan_sales',
            'MANAGERSE' => 'keuangan_sales',
            'KADEPSC' => 'keuangan_sales',
            'KADEPKS' => 'keuangan_sales',
            'MT KADEP KEUANGAN & SALES' => 'keuangan_sales',
            'MANAGERTC' => 'keuangan_sales',
            'MNGTC' => 'keuangan_sales',
            'KADEPUB' => 'keuangan_sales',
            'KEPALA UNIT BISNIS' => 'keuangan_sales',
            'MT ABM SEED JATIM TIMUR' => 'keuangan_sales',
            'ADMPNJ' => 'keuangan_sales',
            'ADMIN PRICELIST & RETUR PENJUALAN' => 'keuangan_sales',

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
        );

        if (isset($map[$jobdesk]) && isset($sections[$map[$jobdesk]])) {
            return $map[$jobdesk];
        }

        return null;
    }

    public function default_active_section(array $context, array $sections)
    {
        if (!empty($context['is_admin']) && isset($sections['admin'])) {
            return 'admin';
        }

        $resolved = $this->resolve_user_section_key($context, $sections);
        if ($resolved !== null && isset($sections[$resolved])) {
            return $resolved;
        }

        return key($sections);
    }

    /**
     * Ambil aturan akses dashboard kustom per user dari tabel tb_user_dashboard_access
     */
    public function get_user_dashboard_rules($userId)
    {
        $userId = (int)$userId;
        $CI =& get_instance();
        if ($userId <= 0 || !isset($CI->db) || !$CI->db->table_exists('tb_user_dashboard_access')) {
            return array();
        }

        $rows = $CI->db
            ->select('menu_key, is_allowed')
            ->where('user_id', $userId)
            ->get('tb_user_dashboard_access')
            ->result_array();

        $map = array();
        foreach ($rows as $row) {
            $map[(string)$row['menu_key']] = (int)$row['is_allowed'];
        }
        return $map;
    }

    /**
     * Katalog lengkap semua section dan menu unik untuk halaman pengaturan akses Admin
     */
    public function catalog_sections()
    {
        $sections = $this->apply_section_menu_config($this->base_module_sections(array()));
        $menuSections = array();

        foreach ($sections as $secKey => $section) {
            foreach ($section['menus'] as $menu) {
                $mKey = $menu['menu_key'];
                if (!isset($menuSections[$mKey])) {
                    $menuSections[$mKey] = array();
                }
                if (!in_array($section['label'], $menuSections[$mKey], true)) {
                    $menuSections[$mKey][] = $section['label'];
                }
            }
        }

        foreach ($sections as $secKey => $section) {
            foreach ($section['menus'] as $idx => $menu) {
                $mKey = $menu['menu_key'];
                $sections[$secKey]['menus'][$idx]['section_labels'] = $menuSections[$mKey] ?? array($section['label']);
            }
        }

        return $sections;
    }

    /**
     * Peta flat seluruh menu dashboard [menu_key => detail]
     */
    public function menu_catalog_map()
    {
        $map = array();
        foreach ($this->catalog_sections() as $secKey => $section) {
            foreach ($section['menus'] as $menu) {
                $mKey = $menu['menu_key'];
                if (!isset($map[$mKey])) {
                    $map[$mKey] = $menu + array('primary_section' => $secKey);
                }
            }
        }
        return $map;
    }

    /**
     * Pemetaan rute URL dan controller/method ke menu_key kanonik untuk proteksi sisi server
     */
    public function route_access_map()
    {
        return array(
            // Keuangan & Accounting
            'kas_bon' => array(
                'title' => 'Kas Bon',
                'uri_prefixes' => array('c_kasbon'),
                'controller_prefixes' => array('c_kasbon/'),
            ),
            'daily_stock' => array(
                'title' => 'Daily Stock',
                'uri_prefixes' => array('keuangan', 'get_data_a', 'detail_lot', 'gudang', 'get_data_global', 'list_stock_minimum'),
                'controller_prefixes' => array('keuangan/c_keuangan/index', 'keuangan/c_keuangan/get_stock_a', 'keuangan/c_keuangan/gudang', 'keuangan/c_keuangan/get_data_global', 'keuangan/c_keuangan/list_stock_minimum', 'keuangan/c_keuangan/stock_suplier'),
            ),
            'jurnal_pembelian' => array(
                'title' => 'Jurnal Pembelian',
                'uri_prefixes' => array('keuangan/pembelian'),
                'controller_prefixes' => array('keuangan/c_keuangan/menu_pembelian'),
            ),
            'pembayaran_supplier' => array(
                'title' => 'Pembayaran Supplier',
                'uri_prefixes' => array('keuangan/pembayaran-supplier'),
                'controller_prefixes' => array('keuangan/c_pembayaransupplier/'),
            ),
            'jurnal_penjualan' => array(
                'title' => 'Jurnal Penjualan',
                'uri_prefixes' => array('keuangan/penjualan'),
                'controller_prefixes' => array('keuangan/c_keuangan/menu_penjualan'),
            ),
            'retur_pembelian' => array(
                'title' => 'Retur Pembelian',
                'uri_prefixes' => array('ics/retur'),
                'controller_prefixes' => array(
                    'logistik/c_ics/dash_retur',
                    'logistik/c_ics/retur_pembelian',
                    'logistik/c_ics/retur_pembelian_monitoring',
                    'logistik/c_ics/retur_pembelian_adjustment',
                    'logistik/c_ics/detail_retur',
                    'logistik/c_ics/ajax_retur_',
                ),
            ),
            'penyesuaian_barang' => array(
                'title' => 'Penyesuaian Barang',
                'uri_prefixes' => array('persediaan/penyesuaian_barang'),
                'controller_prefixes' => array('keuangan/c_penyesuaianbarang/'),
            ),
            'daily_stock_lot' => array(
                'title' => 'Daily Stock Lot',
                'uri_prefixes' => array('daily_stock_lot'),
                'controller_prefixes' => array('keuangan/c_keuangan/daily_stock_lot', 'keuangan/c_keuangan/detail_lot'),
            ),
            'master_barang' => array(
                'title' => 'Master Barang',
                'uri_prefixes' => array('master_barang', 'purchase/listbarang'),
                'controller_prefixes' => array('keuangan/c_keuangan/master_barang'),
            ),
            'jurnal' => array(
                'title' => 'Jurnal',
                'uri_prefixes' => array('jurnal', 'keuangan/jurnal', 'accounting', 'keuangan/accounting', 'accounting-test', 'keuangan/accounting-test', 'keuangan/report_jurnal'),
                'controller_prefixes' => array('keuangan/c_keuangan/jurnal', 'keuangan/c_accounting/', 'keuangan/c_reportjurnal/'),
            ),
            'jurnal_umum' => array(
                'title' => 'Transaksi Jurnal Umum',
                'uri_prefixes' => array('buku_besar/jurnal_umum', 'buku_besar/jurnal_umum_list', 'buku_besar/jurnal_umum_store', 'buku_besar/jurnal_umum_delete', 'buku_besar/jurnal_umum_detail', 'buku_besar/jurnal_umum_next_ref'),
                'controller_prefixes' => array('keuangan/c_bukubesar/jurnal_umum', 'keuangan/c_bukubesar/get_next_ref'),
            ),
            'buku_besar' => array(
                'title' => 'Buku Besar',
                'uri_prefixes' => array('keuangan/buku_besar'),
                'controller_prefixes' => array('keuangan/c_bukubesar/index', 'keuangan/c_bukubesar/get_ledger_data', 'keuangan/c_bukubesar/get_accounts_lookup'),
            ),
            'kas_keluar' => array(
                'title' => 'Kas Keluar',
                'uri_prefixes' => array('keuangan/kas_keluar'),
                'controller_prefixes' => array('keuangan/c_kaskeluar/'),
            ),
            'kas_masuk' => array(
                'title' => 'Kas Masuk',
                'uri_prefixes' => array('keuangan/kas_masuk'),
                'controller_prefixes' => array('keuangan/c_kasmasuk/'),
            ),
            'kasir' => array(
                'title' => 'Kasir',
                'uri_prefixes' => array('keuangan/kasir'),
                'controller_prefixes' => array('keuangan/c_kasir/'),
            ),
            'piutang_customer' => array(
                'title' => 'Piutang Customer',
                'uri_prefixes' => array('keuangan/pembayaran'),
                'controller_prefixes' => array('keuangan/c_pembayaran/'),
            ),
            'test_market' => array(
                'title' => 'Test Market',
                'uri_prefixes' => array('test-market'),
                'controller_prefixes' => array('sales/c_testmarket/'),
            ),
            'pecah_faktur' => array(
                'title' => 'Pecah Faktur',
                'uri_prefixes' => array(
                    'sales_order/pecah_faktur',
                    'pecah_faktur',
                    'pecah_faktur_kios',
                    'sales_order/pecah_faktur_kios',
                    'sales_order/auto_split_kios',
                    'sales_order/split_faktur',
                    'sales_order/simpan_split_faktur',
                    'sales_order/detail_faktur_pecah',
                    'sales_order/cetak_faktur_pecah',
                    'sales_order/cetak_semua_faktur_pecah',
                    'sales_order/hapus_faktur_pecah',
                    'sales_order/unpost_faktur_pecah_batch',
                    'sales_order/ubah_customer_faktur_z',
                    'sales_order/customer_acak',
                    'sales_order/export_faktur_pecah',
                    'sales_order/export_faktur_z_belum',
                    'sales_order/export_faktur_z_selesai',
                    'sales_order/reset_pecah_faktur_induk',
                    'sales_order/template_import_pelunasan_faktur_z_pecah',
                    'sales_order/import_pelunasan_faktur_z_pecah',
                    'sales_order/unpost_pelunasan_faktur_z_pecah',
                    'sales_order/jurnal_pelunasan_faktur_z_pecah_json',
                    'sales_order/jurnal_penjualan_faktur_z_pecah_json',
                    'sales_order/sinkronkan_jurnal_penjualan_faktur_h',
                    'sales_order/export_faktur_z_pecah_lunas',
                ),
                'controller_prefixes' => array(
                    'sales/c_salesorder/pecah_faktur',
                    'sales/c_salesorder/split_faktur',
                    'sales/c_salesorder/simpan_split_faktur',
                    'sales/c_salesorder/detail_faktur_pecah',
                    'sales/c_salesorder/cetak_faktur_pecah',
                    'sales/c_salesorder/hapus_faktur_pecah',
                ),
            ),
            'data_customers' => array(
                'title' => 'Data Customers',
                'uri_prefixes' => array('data_customers', 'master_customer'),
                'controller_prefixes' => array('data_customers/', 'keuangan/c_keuangan/master_customer'),
            ),
            'retur_penjualan' => array(
                'title' => 'Retur Penjualan',
                'uri_prefixes' => array('retur_penjualan'),
                'controller_prefixes' => array('sales/c_returpenjualan/'),
            ),
            'semua_laporan' => array(
                'title' => 'Semua Laporan',
                'uri_prefixes' => array('laporan'),
                'controller_prefixes' => array('keuangan/c_laporan/'),
            ),

            // HRD
            'penilaian_lingkungan' => array(
                'title' => 'Penilaian Lingkungan',
                'uri_prefixes' => array('penilaian_lingkungan', 'hrd/penilaian_lingkungan/submit', 'hrd/penilaian_lingkungan/list', 'hrd/penilaian_lingkungan/detail'),
                'controller_prefixes' => array('hrd/c_hrd/penilaian_lingkungan'),
            ),
            'admin_penilaian' => array(
                'title' => 'Admin Penilaian',
                'uri_prefixes' => array('hrd/penilaian_lingkungan/admin', 'hrd/penilaian_lingkungan/monitoring'),
                'controller_prefixes' => array('hrd/c_hrd/penilaian_lingkungan_admin', 'hrd/c_hrd/penilaian_lingkungan_monitoring'),
            ),
            'user_management' => array(
                'title' => 'User Management',
                'uri_prefixes' => array('master/user-management'),
                'controller_prefixes' => array('master/c_usermanagement/'),
            ),
            'schedule_direktur' => array(
                'title' => 'Schedule Direktur',
                'uri_prefixes' => array('schedule_direktur', 'act_schedule'),
                'controller_prefixes' => array('schedule/c_schedule/'),
            ),

            // Logistik & ICS
            'delivery_order' => array(
                'title' => 'Delivery Order',
                'uri_prefixes' => array('logistik', 'logistikprepare', 'create_do', 'detail_do', 'detail_fk', 'save_do', 'rekam_do'),
                'controller_prefixes' => array('logistik/c_logistik/delivery_order', 'logistik/c_logistik/create_do', 'logistik/c_logistik/detail_do'),
            ),
            'activity_warehouse' => array(
                'title' => 'Activity Warehouse',
                'uri_prefixes' => array('checker'),
                'controller_prefixes' => array('logistik/c_checker/'),
            ),
            'data_all_barang' => array(
                'title' => 'Data All Barang',
                'uri_prefixes' => array('ics/by_allbarang', 'ics/by_allbarang_ics', 'ics/detail_allbarang'),
                'controller_prefixes' => array('logistik/c_ics/ics_by_allbarang', 'logistik/c_ics/by_allbarang_ics', 'logistik/c_ics/ics_detail_allbarang'),
            ),
            'data_by_expired' => array(
                'title' => 'Data By Expired Date',
                'uri_prefixes' => array('ics/by_expdate', 'ics/by_expdate_ics'),
                'controller_prefixes' => array('logistik/c_ics/ics_by_expdate', 'logistik/c_ics/by_expdate_ics'),
            ),
            'data_do' => array(
                'title' => 'Data DO',
                'uri_prefixes' => array('ics/icsdo'),
                'controller_prefixes' => array('logistik/c_ics/ics_do', 'logistik/c_ics/history_ics_do'),
            ),
            'master_gudang' => array(
                'title' => 'Master Gudang',
                'uri_prefixes' => array('ics/gudang', 'ics/gudang_list', 'ics/gudang_save', 'ics/update_gudang', 'ics/update_wilayah', 'ics/detail_wilayah'),
                'controller_prefixes' => array('logistik/c_ics/master_gudang', 'logistik/c_ics/gudang_'),
            ),
            'data_po' => array(
                'title' => 'Data PO',
                'uri_prefixes' => array('ics/icspo', 'ics/detail_po'),
                'controller_prefixes' => array('logistik/c_ics/ics_po', 'logistik/c_ics/detail_po'),
            ),
            'data_lpb' => array(
                'title' => 'Data LPB',
                'uri_prefixes' => array('ics/data_lpb', 'ics/detail_record_lpb', 'ics/print_lpb_record', 'ics/print_lpb_records_all'),
                'controller_prefixes' => array('logistik/c_ics/data_lpb', 'logistik/c_ics/detail_record_lpb', 'logistik/c_ics/print_lpb_record'),
            ),
            'input_lpb_manual' => array(
                'title' => 'Input LPB Manual',
                'uri_prefixes' => array('ics/lpb_manual'),
                'controller_prefixes' => array('logistik/c_ics/lpb_manual', 'logistik/c_ics/ajax_lpb_manual_'),
            ),
            'laporan_lpb' => array(
                'title' => 'Laporan LPB',
                'uri_prefixes' => array('ics/lpb_report'),
                'controller_prefixes' => array('logistik/c_ics/lpb_report', 'logistik/c_ics/ajax_lpb_report_data', 'logistik/c_ics/export_lpb_report_excel'),
            ),
            'master_barang_pic' => array(
                'title' => 'Master Barang PIC',
                'uri_prefixes' => array('ics/barangpic', 'ics/update_pic_lokasi'),
                'controller_prefixes' => array('logistik/c_ics/pic_barang', 'logistik/c_ics/update_pic_lokasi'),
            ),
            'barang_per_gudang' => array(
                'title' => 'Barang Per Gudang',
                'uri_prefixes' => array('ics/barangpergudang', 'ics/ajax_barang_pergudang'),
                'controller_prefixes' => array('logistik/c_ics/barangpergudang', 'logistik/c_ics/ajax_barang_pergudang'),
            ),
            'mutasi_barang' => array(
                'title' => 'Mutasi Barang Gudang',
                'uri_prefixes' => array('ics/mutasi_barang'),
                'controller_prefixes' => array('logistik/c_ics/mutasi_barang', 'logistik/c_ics/input_mutasi_barang', 'logistik/c_ics/list_barang_mutasi'),
            ),
            'show_diffrent' => array(
                'title' => 'Show Diffrent',
                'uri_prefixes' => array('ics/ics_diffrent', 'ics/diffrent'),
                'controller_prefixes' => array('logistik/c_ics/ics_diffrent'),
            ),
            'list_faktur_status' => array(
                'title' => 'List Faktur Terkirim / Belum',
                'uri_prefixes' => array('logistik/distibusi/list_faktur_status', 'logistik/distibusi/ajax_list_faktur_status'),
                'controller_prefixes' => array('logistik/c_distribusi/list_faktur_status', 'logistik/c_distribusi/ajax_list_faktur_status'),
            ),
            'export_expired' => array(
                'title' => 'Export Data Expired Date',
                'uri_prefixes' => array('export-stock'),
                'controller_prefixes' => array('logistik/c_exportstock/'),
            ),
            'stockopname' => array(
                'title' => 'Stockopname',
                'uri_prefixes' => array('admin/stockopname', 'stockopname'),
                'controller_prefixes' => array('admin/c_stockopname/', 'logistik/c_logistik/stockopname'),
            ),
            'realisasi_bundling' => array(
                'title' => 'Realisasi Paket Bundling',
                'uri_prefixes' => array('logistik/bundling'),
                'controller_prefixes' => array('logistik/c_bundlinglogistik/'),
            ),

            // Purchasing
            'request_bundling' => array(
                'title' => 'Request Paket Bundling',
                'uri_prefixes' => array('purchasing/bundling'),
                'controller_prefixes' => array('purchasing/c_bundlingrequest/'),
            ),
            'barang_konsinyasi' => array(
                'title' => 'Barang Konsinyasi',
                'uri_prefixes' => array('purchasing/konsinyasi', 'purchasing/konsinyasi_settlement'),
                'controller_prefixes' => array('purchasing/c_konsinyasi/'),
            ),
            'pending_po' => array(
                'title' => 'Pending PO',
                'uri_prefixes' => array('pendingpo'),
                'controller_prefixes' => array('keuangan/c_keuangan/pendingpo'),
            ),

            // IT
            'log_lpb_manual' => array(
                'title' => 'Log LPB Manual',
                'uri_prefixes' => array('ics/lpb_manual_log'),
                'controller_prefixes' => array('logistik/c_ics/lpb_manual_log'),
            ),

            // Sales
            'admin_sc' => array(
                'title' => 'Admin SC',
                'uri_prefixes' => array('sales_order/admin_sc', 'admin_sc', 'sales_order/form_faktur', 'sales_order/simpan_faktur', 'sales_order/detail_faktur'),
                'controller_prefixes' => array('sales/c_salesorder/admin_sc', 'sales/c_salesorder/simpan_faktur', 'sales/c_salesorder/detail_faktur'),
            ),
            'sales_order' => array(
                'title' => 'Sales Order',
                'uri_prefixes' => array('sales_order'),
                'controller_prefixes' => array('sales/c_salesorder/index', 'sales/c_salesorder/create', 'sales/c_salesorder/store', 'sales/c_salesorder/detail', 'sales/c_salesorder/edit', 'sales/c_salesorder/update', 'sales/c_salesorder/cancel', 'sales/c_salesorder/rekam', 'sales/c_salesorder/approval', 'sales/c_salesorder/approve'),
            ),
            'sales_order_loby' => array(
                'title' => 'Sales Order Loby',
                'uri_prefixes' => array('sales_order_loby'),
                'controller_prefixes' => array('sales/c_salesorderloby/'),
            ),
            'faktur_penjualan' => array(
                'title' => 'Faktur Penjualan',
                'uri_prefixes' => array('admin/transaksi', 'transaksi', 'faktur_penjualan'),
                'controller_prefixes' => array('admin/c_transaksi/'),
            ),
            'katalog_sales' => array(
                'title' => 'Katalog Sales',
                'uri_prefixes' => array('kiu_katalog'),
                'controller_prefixes' => array('sales/c_exportstock/'),
            ),
            'sales_report' => array(
                'title' => 'Sales Report',
                'uri_prefixes' => array('sales_report'),
                'controller_prefixes' => array('sales/c_sales/'),
            ),
            'stok_online' => array(
                'title' => 'Stok Online',
                'uri_prefixes' => array('stock'),
                'controller_prefixes' => array('stock/c_stock/'),
            ),
            'pengajuan_od' => array(
                'title' => 'Pengajuan OD',
                'uri_prefixes' => array('sales/c_pengajuanod'),
                'controller_prefixes' => array('sales/c_pengajuanod/'),
            ),
        );
    }

    /**
     * Resolusi menu_key berdasarkan URI atau controller/method (pencocokan prefix terpanjang)
     */
    public function resolve_menu_by_request($uriString, $controllerMethod = '')
    {
        $uri = trim(strtolower((string)$uriString), '/');
        $ctrl = trim(strtolower((string)$controllerMethod), '/');
        $map = $this->route_access_map();

        $bestMatch = null;
        $bestScore = -1;

        foreach ($map as $menuKey => $rule) {
            foreach ($rule['uri_prefixes'] as $prefix) {
                $prefix = trim(strtolower((string)$prefix), '/');
                if ($prefix === '') {
                    continue;
                }
                if ($uri === $prefix || strpos($uri, $prefix . '/') === 0) {
                    $score = strlen($prefix);
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestMatch = array('menu_key' => $menuKey, 'title' => $rule['title']);
                    }
                }
            }

            if ($ctrl !== '') {
                foreach ($rule['controller_prefixes'] as $cPrefix) {
                    $cPrefix = strtolower((string)$cPrefix);
                    $isWildcard = substr($cPrefix, -1) === '_' || substr($cPrefix, -1) === '/';
                    $matched = $isWildcard
                        ? (strpos($ctrl, $cPrefix) === 0 || rtrim($ctrl, '/') === rtrim($cPrefix, '/'))
                        : ($ctrl === $cPrefix || strpos($ctrl, $cPrefix . '/') === 0);

                    if ($matched) {
                        $score = strlen($cPrefix);
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestMatch = array('menu_key' => $menuKey, 'title' => $rule['title']);
                        }
                    }
                }
            }
        }

        return $bestMatch;
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

        // Ambil pengaturan akses dashboard per user jika bukan Admin
        $isAdmin = !empty($context['is_admin']);
        $userRules = !$isAdmin ? $this->get_user_dashboard_rules($context['id'] ?? 0) : array();

        foreach ($sections as $key => $section) {
            if (empty($section['menus']) || !is_array($section['menus'])) {
                continue;
            }

            $filteredMenus = array();
            foreach ($section['menus'] as $menu) {
                $route = (string)($menu['route'] ?? '');
                $menuKey = (string)($menu['menu_key'] ?? '');

                // Admin selalu mendapat akses terbuka untuk seluruh menu
                if ($isAdmin) {
                    $menu['is_locked'] = false;
                    $filteredMenus[] = $menu;
                    continue;
                }

                // Cek apakah ada override eksplisit dari Admin di tb_user_dashboard_access
                if ($menuKey !== '' && array_key_exists($menuKey, $userRules)) {
                    $menu['is_locked'] = ((int)$userRules[$menuKey] === 0);
                    $filteredMenus[] = $menu;
                    continue;
                }

                // Jika belum ada override, ikuti aturan jobdesk bawaan
                $legacyAllowed = true;
                if ($route === 'sales_order/admin_sc') {
                    $legacyAllowed = $has_admin_sc;
                } elseif ($route === 'sales_order' && $is_admin_sc_only) {
                    $legacyAllowed = false;
                } elseif ($route === 'ics/retur' || strpos($route, 'ics/retur/pembelian/monitoring') === 0) {
                    $legacyAllowed = $this->has_retur_access($context);
                } elseif (strpos($route, 'retur_penjualan') === 0) {
                    $legacyAllowed = $has_retur_pj;
                }

                if ($legacyAllowed) {
                    $menu['is_locked'] = false;
                    $filteredMenus[] = $menu;
                }
            }

            $sections[$key]['menus'] = $filteredMenus;

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
            $this->menu('Semua Transaksi', 'admin/transaksi', 'fas fa-exchange-alt', 'teal', 'Kelola, audit, unpost, edit, dan sinkronisasi seluruh transaksi & jurnal akuntansi.', 'faktur_penjualan'),
            $this->menu('User Management', 'master/user-management', 'fas fa-users-cog', 'dark', 'Kelola akun, akses level & matrix permission, status user, reset password, akses menu dashboard, fasilitas per user, dan menu departemen.', 'user_management'),
            $this->menu('Jobdesk', 'master/jobdesk', 'fas fa-briefcase', 'slate', 'Kelola master jobdesk yang dipakai rules akses aplikasi.', 'master_jobdesk'),
            $this->menu('Menu Aplikasi', 'master/menu', 'fas fa-bars', 'cyan', 'Kelola struktur menu dinamis aplikasi.', 'master_menu'),
        );

        // Pastikan master/akses-level tidak muncul di dashboard karena sudah terintegrasi di User Management
        $seenRoutes['master/akses-level'] = true;

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

    public function dynamic_app_menus(array $seenRoutes)
    {
        $db = $this->db;
        if (!$db || !$db->table_exists('tb_menu')) {
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
                'Buka modul ' . $title . ' dari menu aplikasi.',
                'dyn_' . md5($route)
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

    private function menu($title, $route, $icon, $tone, $description, $menuKey = '')
    {
        if ($menuKey === '') {
            $menuKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $title), '_'));
        }
        return array(
            'menu_key' => $menuKey,
            'title' => $title,
            'route' => $route,
            'icon' => $icon,
            'tone' => $tone,
            'description' => $description,
            'is_locked' => false,
        );
    }
}
