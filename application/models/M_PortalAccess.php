<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalAccess extends CI_Model
{
    public function apps()
    {
        return [
            'kiu_po' => ['name' => 'KIU PO', 'url' => 'https://kiupo.karismaerp.com', 'icon' => 'fa-shopping-cart', 'description' => 'Purchase Order & Procurement Management.', 'accent' => '#2563eb'],
            'warehouse' => ['name' => 'Warehouse', 'url' => 'https://kiuwarehouse.karismaerp.com', 'icon' => 'fa-warehouse', 'description' => 'Warehouse Management System.', 'accent' => '#0f766e'],
            'kmt_corn' => ['name' => 'KMT CORN', 'url' => 'https://kmtcorn.karismaerp.com', 'icon' => 'fa-seedling', 'description' => 'Sales & Operational Management.', 'accent' => '#d97706'],
            'kpi' => ['name' => 'KPI', 'url' => 'https://kpi.karismaerp.com', 'icon' => 'fa-chart-line', 'description' => 'Employee Performance Dashboard.', 'accent' => '#4338ca'],
            'pos_security' => ['name' => 'POS Security', 'url' => 'https://hrd.karismaerp.com', 'icon' => 'fa-shield-alt', 'description' => 'Security Activity Journal.', 'accent' => '#0ea5e9'],
            'katalog_produk' => ['name' => 'Katalog Produk', 'url' => 'https://kiukatalog.karismaerp.com', 'icon' => 'fa-book-open', 'description' => 'Digital Product Catalog.', 'accent' => '#7c3aed'],
            'plafon_sales' => ['name' => 'Plafon Sales', 'url' => 'https://plafon.karismaerp.com', 'icon' => 'fa-credit-card', 'description' => 'Customer Credit Limit Monitoring.', 'accent' => '#0284c7'],
            'stockopname' => ['name' => 'Stockopname', 'url' => 'https://stockopname.karismaerp.com', 'icon' => 'fa-boxes', 'description' => 'Stock counting and inventory reconciliation.', 'accent' => '#14b8a6'],
            'penilaian_lingkungan' => ['name' => 'Penilaian Lingkungan Kerja', 'url' => 'https://karismaerp.com/auth', 'icon' => 'fa-leaf', 'description' => 'Work Environment Assessment & Monitoring.', 'accent' => '#16a34a'],
            'overdue' => ['name' => 'Overdue', 'url' => 'https://tagpd.karismaerp.com/', 'icon' => 'fa-file-invoice-dollar', 'description' => 'Overdue Invoices & Receivables Monitoring.', 'accent' => '#e11d48'],
            'kios_transfer' => ['name' => 'Kios Transfer', 'url' => 'https://kiostransfer.karismaerp.com', 'icon' => 'fa-cash-register', 'description' => 'Pencatatan metode pembayaran kios.', 'accent' => '#0891b2'],
            'sapa_karisma' => ['name' => 'Sapa Karisma', 'url' => 'https://sapakarisma.karismaerp.com', 'icon' => 'fa-users-cog', 'description' => 'HRIS untuk manajemen SDM yang dikelola HRD.', 'accent' => '#7c3aed'],
        ];
    }

    public function users()
    {
        $users = [];
        foreach (['tb_karyawan', 'tb_users'] as $source) {
            if (!$this->db->table_exists($source)) continue;
            $fields = $this->db->list_fields($source);
            if (!in_array('id', $fields, true) || !in_array('username', $fields, true)) continue;
            $nameField = $source === 'tb_karyawan' ? (in_array('nm_karyawan', $fields, true) ? 'nm_karyawan' : 'username')
                : (in_array('nama_lngkp', $fields, true) ? 'nama_lngkp' : (in_array('nama_user', $fields, true) ? 'nama_user' : 'username'));
            $jobdeskField = in_array('jobdesk', $fields, true) ? 'jobdesk' : null;
            $select = ['id', 'username', $nameField . ' AS display_name'];
            if ($jobdeskField) $select[] = $jobdeskField . ' AS jobdesk';
            else $select[] = "'' AS jobdesk";
            if (in_array('status', $fields, true)) $select[] = 'status';
            else $select[] = '1 AS status';
            foreach ($this->db->select(implode(',', $select))->order_by('username', 'ASC')->get($source)->result_array() as $user) {
                $user['auth_source'] = $source;
                $users[] = $user;
            }
        }
        usort($users, function ($left, $right) {
            return strcasecmp($left['display_name'], $right['display_name']);
        });
        return $users;
    }

    public function denied_apps($authSource, $userId)
    {
        if (!$this->db->table_exists('tb_portal_app_access')) return [];
        $rows = $this->db->select('app_code')->where(['auth_source' => $authSource, 'user_id' => (int)$userId, 'is_allowed' => 0])->get('tb_portal_app_access')->result_array();
        return array_column($rows, 'app_code');
    }

    public function apps_for_user($authSource, $userId)
    {
        $apps = $this->apps();
        if ($this->db->table_exists('tb_portal_app_access')) {
            foreach ($this->denied_apps($authSource, $userId) as $code) unset($apps[$code]);
        }
        return array_values($apps);
    }

    public function save_access($authSource, $userId, $allowedApps, $updatedBy)
    {
        $table = 'tb_portal_app_access';
        if (!$this->db->table_exists($table)) return false;
        $validSources = ['tb_karyawan', 'tb_users'];
        if (!in_array($authSource, $validSources, true) || (int)$userId < 1) return false;
        $knownApps = array_keys($this->apps());
        $denied = array_diff($knownApps, array_intersect($knownApps, (array)$allowedApps));
        $this->db->trans_begin();
        $this->db->where(['auth_source' => $authSource, 'user_id' => (int)$userId])->delete($table);
        foreach ($denied as $code) {
            $this->db->insert($table, ['auth_source' => $authSource, 'user_id' => (int)$userId, 'app_code' => $code, 'is_allowed' => 0, 'updated_by' => (int)$updatedBy]);
        }
        if ($this->db->trans_status()) {
            $this->db->trans_commit();
            return true;
        }
        $this->db->trans_rollback();
        return false;
    }
}
