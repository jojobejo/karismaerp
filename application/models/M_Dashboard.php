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
            'is_admin' => (bool)$this->session->userdata('is_admin_dashboard') || $username === 'admin' || ($lv === 1 && in_array($jobdesk, array('ADMIN', 'ADMINICS'), true)),
        );
    }

    public function module_sections($context = array())
    {
        $sections = array(
            'keuangan' => array(
                'label' => 'KEUANGAN',
                'icon' => 'fas fa-wallet',
                'description' => 'Kontrol stok harian, master barang, dan laporan keuangan operasional.',
                'menus' => array(
                    $this->menu('Daily Stock', 'keuangan', 'fas fa-chart-line', 'blue', 'Pantau dashboard daily stock dan ringkasan data keuangan.'),
                    $this->menu('Pembelian', 'keuangan/pembelian', 'fas fa-shopping-cart', 'orange', 'Akses modul pembelian and monitoring PO keuangan.'),
                    $this->menu('Penjualan', 'keuangan/penjualan', 'fas fa-handshake', 'green', 'Akses modul penjualan, jurnal penjualan, dan jurnal pembayaran.'),
                    $this->menu('Retur', 'ics/retur', 'fas fa-undo-alt', 'red', 'Akses retur pembelian dan retur penjualan untuk verifikasi dampak stok serta jurnal.'),
                    $this->menu('Daily Stock Lot', 'daily_stock_lot', 'fas fa-layer-group', 'teal', 'Buka stok harian berbasis lot untuk rekonsiliasi persediaan.'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-boxes', 'slate', 'Kelola master barang yang dipakai modul keuangan dan stok.'),
                    $this->menu('Jurnal', 'jurnal', 'fas fa-book-open', 'purple', 'Kelola chart of accounts dan akun jurnal general ledger.'),
                    $this->menu('Transaksi Jurnal Umum', 'buku_besar/jurnal_umum', 'fas fa-book-open', 'lime', 'Kelola dan rekam transaksi jurnal umum.'),
                    $this->menu('Semua Laporan', 'laporan', 'fas fa-chart-bar', 'cyan', 'Laporan keuangan, penjualan & piutang, pembelian & hutang, barang, dan lainnya.'),
                ),
            ),
            'hrd' => array(
                'label' => 'HRD',
                'icon' => 'fas fa-users',
                'description' => 'Akses pengelolaan user, struktur kerja, dan penilaian lingkungan.',
                'menus' => array(
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
                    $this->menu('Data All Barang', 'ics/by_allbarang', 'fas fa-boxes', 'blue', 'Pantau seluruh stok barang ICS lintas gudang dalam satu pintu data.'),
                    $this->menu('Data By Expired Date', 'ics/by_expdate', 'fas fa-calendar-alt', 'orange', 'Lihat barang berdasarkan tanggal expired untuk prioritas kontrol stok.'),
                    $this->menu('Data DO', 'ics/icsdo', 'fas fa-truck-loading', 'slate', 'Masuk ke data Delivery Order dan arus keluar barang.'),
                    $this->menu('Master Gudang', 'ics/gudang', 'fas fa-warehouse', 'green', 'Kelola master gudang dan wilayah penyimpanan.'),
                    $this->menu('Data PO', 'ics/icspo', 'fas fa-file-invoice', 'red', 'Buka data Purchase Order untuk kontrol barang masuk.'),
                    $this->menu('Laporan LPB', 'ics/lpb_report', 'fas fa-chart-bar', 'cyan', 'Pantau LPB manual dan LPB hasil input Logistik.'),
                    $this->menu('Retur', 'ics/retur', 'fas fa-undo-alt', 'orange', 'Buka dashboard retur pembelian dan penjualan berbasis stok gudang.'),
                    $this->menu('Master Barang PIC', 'ics/barangpic', 'fas fa-user-check', 'lime', 'Atur daftar barang yang menjadi tanggung jawab PIC.'),
                    $this->menu('Barang Per Gudang', 'ics/barangpergudang', 'fas fa-dolly-flatbed', 'purple', 'Cek komposisi barang pada setiap gudang.'),
                    $this->menu('Mutasi Barang Gudang', 'ics/mutasi_barang', 'fas fa-exchange-alt', 'teal', 'Telusuri perpindahan barang antar gudang secara operasional.'),
                    $this->menu('Show Diffrent', 'ics/ics_diffrent', 'fas fa-eye', 'dark', 'Bandingkan selisih data stok ICS untuk tindakan koreksi.'),
                    $this->menu('List Faktur Terkirim / Belum', 'logistik/distibusi/list_faktur_status', 'fas fa-file-signature', 'brown', 'Monitor status faktur distribusi yang sudah atau belum terkirim.'),
                    $this->menu('Export Data Expired Date', 'export-stock', 'fas fa-file-export', 'cyan', 'Unduh data expired date untuk kebutuhan pelaporan dan audit.'),
                    $this->menu('Stockopname', 'admin/stockopname', 'fas fa-tasks', 'blue', 'Buka dashboard admin stockopname dan monitoring stok fisik.'),
                ),
            ),
            'purchasing' => array(
                'label' => 'PURCHASING',
                'icon' => 'fas fa-shopping-cart',
                'description' => 'Akses pembelian, LPB, dan monitoring PO.',
                'menus' => array(
                    $this->menu('Data PO', 'ics/icspo', 'fas fa-file-invoice', 'red', 'Buka data PO dan LPB yang berjalan.'),
                    $this->menu('Input LPB Manual', 'ics/lpb_manual', 'fas fa-keyboard', 'green', 'Input LPB tanpa data PO dengan lot dan expired manual.'),
                    $this->menu('Laporan LPB', 'ics/lpb_report', 'fas fa-chart-bar', 'cyan', 'Pantau LPB manual Purchasing dan LPB hasil input Logistik.'),
                    $this->menu('Retur', 'ics/retur', 'fas fa-undo-alt', 'orange', 'Akses retur pembelian dari LPB final dan monitoring retur.'),
                    $this->menu('Pending PO', 'pendingpo', 'fas fa-hourglass-half', 'orange', 'Pantau PO yang masih membutuhkan tindak lanjut.'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-box', 'slate', 'Buka master barang untuk referensi pembelian.'),
                ),
            ),
            'it' => array(
                'label' => 'IT',
                'icon' => 'fas fa-laptop-code',
                'description' => 'Akses modul lintas departemen untuk support dan validasi teknis.',
                'menus' => array(
                    $this->menu('Log LPB Manual', 'ics/lpb_manual_log', 'fas fa-terminal', 'dark', 'Pantau log sistem khusus LPB Manual.'),
                    $this->menu('Retur', 'ics/retur', 'fas fa-undo-alt', 'red', 'Buka dashboard retur untuk support workflow Logistik, Purchasing, dan Keuangan.'),
                ),
            ),
            'sales' => array(
                'label' => 'SALES',
                'icon' => 'fas fa-handshake',
                'description' => 'Akses penjualan, katalog, order, dan laporan sales.',
                'menus' => array(
                    $this->menu('Sales Order', 'sales_order', 'fas fa-file-signature', 'blue', 'Kelola dokumen sales order dan approval.'),
                    $this->menu('Katalog Sales', 'kiu_katalog', 'fas fa-store', 'green', 'Buka katalog penjualan untuk tim sales.'),
                    $this->menu('Sales Report', 'sales_report', 'fas fa-chart-bar', 'purple', 'Pantau laporan sales counter dan aktivitas penjualan.'),
                    $this->menu('Stok Online', 'stock', 'fas fa-box-open', 'teal', 'Cek stok online yang dipakai kanal sales.'),
                ),
            ),
        );

        if (!empty($context['is_admin'])) {
            $sections = array('admin' => $this->admin_section($sections)) + $sections;
        }

        return $this->apply_access_rules($sections, $context);
    }

    public function default_active_section(array $context, array $sections)
    {
        if (!empty($context['is_admin']) && isset($sections['admin'])) {
            return 'admin';
        }

        $jobdesk = $context['jobdesk'];
        $map = array(
            'ADMINKEU' => 'keuangan',
            'ADMINKEUTC' => 'keuangan',
            'DIREKTUR' => 'keuangan',
            'ADMINGA' => 'hrd',
            'SUPERADMIN' => 'hrd',
            'LOGISTIK' => 'logistik',
            'ADMINICS' => 'logistik',
            'DISTRIBUSI' => 'logistik',
            'STOCKOPNAME' => 'logistik',
            'ADMINPURCHASING' => 'purchasing',
            'ADMIN PO' => 'purchasing',
            'PURCHASING' => 'purchasing',
            'IT' => 'it',
            'ADMINIT' => 'it',
            'ADMIN IT' => 'it',
            'PROGRAMMER' => 'it',
            'SALES' => 'sales',
            'SALESONLINE' => 'sales',
            'SALESCOUNTER' => 'sales',
            'SC' => 'sales',
            'ADMINSC' => 'sales',
            'MNGSC' => 'sales',
            'MANAGER SC' => 'sales',
            'MANAGERSC' => 'sales',
        );

        if (isset($map[$jobdesk]) && isset($sections[$map[$jobdesk]])) {
            return $map[$jobdesk];
        }

        return key($sections);
    }

    private function apply_access_rules(array $sections, array $context)
    {
        if ($this->has_retur_access($context)) {
            return $sections;
        }

        foreach ($sections as $key => $section) {
            if (empty($section['menus']) || !is_array($section['menus'])) {
                continue;
            }

            $sections[$key]['menus'] = array_values(array_filter($section['menus'], function ($menu) {
                return ($menu['route'] ?? '') !== 'ics/retur';
            }));

            if ($key === 'it' && empty($sections[$key]['menus'])) {
                unset($sections[$key]);
            }
        }

        return $sections;
    }

    private function has_retur_access(array $context)
    {
        if (!empty($context['is_admin'])) {
            return true;
        }

        $departemen = strtoupper(trim((string)($context['departemen'] ?? '')));
        $jobdesk = strtoupper(trim((string)($context['jobdesk'] ?? '')));

        foreach (array('LOGISTIK', 'PURCHASING', 'KEUANGAN', 'FINANCE', 'ACCOUNTING', 'IT') as $allowed) {
            if ($departemen === $allowed || strpos($departemen, $allowed) !== false) {
                return true;
            }
        }

        return in_array($jobdesk, array(
            'LOGISTIK',
            'ADMINLOGLPB',
            'ADMLPB2',
            'ADMINICS',
            'ADMLOG',
            'ADMINPURCHASING',
            'ADMIN PO',
            'PURCHASING',
            'ADMINKEU',
            'ADMINKEUTC',
            'KIUKEU',
            'KEUANGAN',
            'ACCOUNTING',
            'FINANCE',
            'IT',
            'ADMINIT',
            'ADMIN IT',
            'PROGRAMMER',
            'DEVELOPMENT'
        ), true);
    }

    private function admin_section(array $sections)
    {
        $menus = array();
        $seenRoutes = array();
        $adminMenus = array(
            $this->menu('User Management', 'master/user-management', 'fas fa-users-cog', 'dark', 'Kelola akun, level akses, status user, dan reset password.'),
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
