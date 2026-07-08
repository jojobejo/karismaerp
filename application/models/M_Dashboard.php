<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Dashboard extends CI_Model
{
    public function current_user_context()
    {
        $lv = (int)$this->session->userdata('lv');
        $jobdesk = strtoupper(trim((string)$this->session->userdata('jobdesk')));
        $username = strtolower(trim((string)$this->session->userdata('username')));

        return array(
            'id' => $this->session->userdata('id'),
            'nama' => $this->session->userdata('nama') ?: $this->session->userdata('username'),
            'username' => $username,
            'lv' => $lv,
            'jobdesk' => $jobdesk,
            'is_admin' => (bool)$this->session->userdata('is_admin_dashboard') || $username === 'admin' || ($lv === 1 && in_array($jobdesk, array('ADMIN', 'ADMINICS'), true)),
        );
    }

    public function module_sections(array $context)
    {
        $sections = array(
            'keuangan' => array(
                'label' => 'KEUANGAN',
                'icon' => 'fas fa-wallet',
                'description' => 'Kontrol stok harian, master barang, dan laporan keuangan operasional.',
                'menus' => array(
                    $this->menu('Daily Stock', 'keuangan', 'fas fa-chart-line', 'blue', 'Pantau dashboard daily stock dan ringkasan data keuangan.'),
                    $this->menu('Pending PO', 'pendingpo', 'fas fa-hourglass-half', 'orange', 'Lihat daftar PO pending untuk tindak lanjut pembelian.'),
                    $this->menu('Daily Stock Lot', 'daily_stock_lot', 'fas fa-layer-group', 'teal', 'Buka stok harian berbasis lot untuk rekonsiliasi persediaan.'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-boxes', 'slate', 'Kelola master barang yang dipakai modul keuangan dan stok.'),
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
                    $this->menu('Pending PO', 'pendingpo', 'fas fa-hourglass-half', 'orange', 'Pantau PO yang masih membutuhkan tindak lanjut.'),
                    $this->menu('Master Barang', 'master_barang', 'fas fa-box', 'slate', 'Buka master barang untuk referensi pembelian.'),
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

        return $this->apply_access_rules($sections, $context);
    }

    public function default_active_section(array $context, array $sections)
    {
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
            'SALES' => 'sales',
            'SALESONLINE' => 'sales',
            'SALESCOUNTER' => 'sales',
            'SC' => 'sales',
        );

        if (isset($map[$jobdesk]) && isset($sections[$map[$jobdesk]])) {
            return $map[$jobdesk];
        }

        return key($sections);
    }

    private function apply_access_rules(array $sections, array $context)
    {
        // Rules detail per user dan level akan dipusatkan di sini.
        return $sections;
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
