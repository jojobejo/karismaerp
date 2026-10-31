<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalAccess extends CI_Model
{
    public function apps()
    {
        $apps = [
            'kiu_po' => ['name' => 'KIU PO', 'url' => 'https://poerp.karismaerp.com', 'icon' => 'fa-shopping-cart', 'description' => 'Purchase Order & Procurement Management.', 'accent' => '#2563eb'],
            'warehouse' => ['name' => 'Warehouse', 'url' => 'https://kiuwarehouse.karismaerp.com', 'icon' => 'fa-warehouse', 'description' => 'Warehouse Management System.', 'accent' => '#0f766e'],
            'kmt_corn' => ['name' => 'KMT CORN', 'url' => 'https://kmtcorn.karismaerp.com', 'icon' => 'fa-seedling', 'description' => 'Sales & Operational Management.', 'accent' => '#d97706'],
            'kpi' => ['name' => 'KPI', 'url' => 'https://kpi.karismaerp.com', 'icon' => 'fa-chart-line', 'description' => 'Employee Performance Dashboard.', 'accent' => '#4338ca'],
            'pos_security' => ['name' => 'POS Security', 'url' => 'https://hrd.karismaerp.com', 'icon' => 'fa-shield-alt', 'description' => 'Security Activity Journal.', 'accent' => '#0ea5e9'],
            'katalog_produk' => ['name' => 'Katalog Produk', 'url' => 'https://kiukatalog.karismaerp.com', 'icon' => 'fa-book-open', 'description' => 'Digital Product Catalog.', 'accent' => '#7c3aed'],
            'karisma_online' => ['name' => 'Karisma Online', 'url' => 'https://karismaonline.karismaerp.com', 'icon' => 'fa-shopping-bag', 'description' => 'Online Sales Platform.', 'accent' => '#db2777', 'hidden' => true],
            'plafon_sales' => ['name' => 'Plafon Sales', 'url' => 'https://plafon.karismaerp.com', 'icon' => 'fa-credit-card', 'description' => 'Customer Credit Limit Monitoring.', 'accent' => '#0284c7'],
            'stockopname' => ['name' => 'Stockopname', 'url' => 'https://stockopname.karismaerp.com', 'icon' => 'fa-boxes', 'description' => 'Stock counting and inventory reconciliation.', 'accent' => '#14b8a6'],
            'penilaian_lingkungan' => ['name' => 'Penilaian Lingkungan Kerja', 'url' => 'https://lingkungan.karismaerp.com/', 'icon' => 'fa-leaf', 'description' => 'Work Environment Assessment & Monitoring.', 'accent' => '#16a34a'],
            'overdue' => ['name' => 'Overdue', 'url' => 'https://tagpd.karismaerp.com/', 'icon' => 'fa-file-invoice-dollar', 'description' => 'Overdue Invoices & Receivables Monitoring.', 'accent' => '#e11d48'],
            'kios_transfer' => ['name' => 'Kios Transfer', 'url' => 'https://kiostransfer.karismaerp.com', 'icon' => 'fa-cash-register', 'description' => 'Pencatatan metode pembayaran kios.', 'accent' => '#0891b2'],
            'sapa_karisma' => ['name' => 'Sapa Karisma', 'url' => 'https://sapakarisma.karismaerp.com', 'icon' => 'fa-users-cog', 'description' => 'HRIS untuk manajemen SDM yang dikelola HRD.', 'accent' => '#7c3aed'],
        ];

        if ($this->db->table_exists('tb_portal_app_directory')) {
            foreach ($this->db->get('tb_portal_app_directory')->result_array() as $directoryApp) {
                $code = (string)($directoryApp['app_code'] ?? '');
                if (!isset($apps[$code])) continue;
                $apps[$code]['name'] = (string)$directoryApp['app_name'];
                $apps[$code]['url'] = (string)$directoryApp['app_url'];
                $apps[$code]['icon'] = (string)$directoryApp['icon'];
                $apps[$code]['description'] = (string)($directoryApp['description'] ?? '');
                $apps[$code]['accent'] = (string)$directoryApp['accent'];
                $apps[$code]['hidden'] = (int)$directoryApp['is_hidden'] === 1;
            }
        }

        // Klien SSO di luar katalog bawaan tetap dapat dikelola melalui halaman akses Portal.
        if ($this->db->table_exists('tb_portal_sso_client')) {
            $fields = $this->db->list_fields('tb_portal_sso_client');
            if (in_array('app_code', $fields, true) && in_array('launch_uri', $fields, true)) {
                foreach ($this->db->select('app_code, launch_uri')->get('tb_portal_sso_client')->result_array() as $client) {
                    $code = (string)$client['app_code'];
                    if ($code === '' || isset($apps[$code])) continue;
                    $apps[$code] = ['name' => $code, 'url' => (string)$client['launch_uri'], 'icon' => 'fa-plug', 'description' => 'Aplikasi klien SSO.', 'accent' => '#2563eb'];
                }
            }
        }

        return $apps;
    }

    public function registered_callbacks()
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return [];
        $fields = $this->db->list_fields('tb_portal_sso_client');
        if (!in_array('app_code', $fields, true) || !in_array('redirect_uri', $fields, true) || !in_array('is_enabled', $fields, true)) return [];
        $rows = $this->db->select('app_code, redirect_uri')->where('is_enabled', 1)->get('tb_portal_sso_client')->result_array();
        $callbacks = [];
        foreach ($rows as $row) $callbacks[$row['app_code']] = trim((string)$row['redirect_uri']);
        return $callbacks;
    }

    public function registered_launch_urls()
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return [];
        $fields = $this->db->list_fields('tb_portal_sso_client');
        if (!in_array('app_code', $fields, true) || !in_array('launch_uri', $fields, true) || !in_array('is_enabled', $fields, true)) return [];
        $rows = $this->db->select('app_code, launch_uri')->where('is_enabled', 1)->get('tb_portal_sso_client')->result_array();
        $launchUrls = [];
        foreach ($rows as $row) $launchUrls[$row['app_code']] = trim((string)$row['launch_uri']);
        return $launchUrls;
    }

    public function apps_with_callbacks()
    {
        $apps = $this->apps();
        $callbacks = $this->registered_callbacks();
        $launchUrls = $this->registered_launch_urls();
        foreach ($apps as $code => &$app) $app['callback_url'] = $callbacks[$code] ?? '';
        foreach ($apps as $code => &$app) $app['launch_url'] = $launchUrls[$code] ?? '';
        unset($app);
        return $apps;
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
            $departmentField = in_array('departemen', $fields, true) ? 'departemen' : null;
            $select = ['id', 'username', $nameField . ' AS display_name'];
            if ($jobdeskField) $select[] = $jobdeskField . ' AS jobdesk';
            else $select[] = "'' AS jobdesk";
            if ($departmentField) $select[] = $departmentField . ' AS departemen';
            else $select[] = "'' AS departemen";
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

    public function accounts_for_app($appCode)
    {
        if (!$this->db->table_exists('tb_portal_app_access')) return [];
        $allowedAccounts = $this->db->select('auth_source, user_id')
            ->where(['app_code' => $appCode, 'is_allowed' => 1])
            ->get('tb_portal_app_access')->result_array();
        if (empty($allowedAccounts)) return [];

        $allowedKeys = [];
        foreach ($allowedAccounts as $account) {
            $allowedKeys[$account['auth_source'] . ':' . (int)$account['user_id']] = true;
        }
        return array_values(array_filter($this->users(), function ($user) use ($allowedKeys) {
            return isset($allowedKeys[$user['auth_source'] . ':' . (int)$user['id']]);
        }));
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
        $launchUrls = $this->registered_launch_urls();
        if ($this->db->table_exists('tb_portal_app_access')) {
            $select = ['app_code', 'is_allowed'];
            $rows = $this->db->select(implode(',', $select))->where([
                'auth_source' => $authSource,
                'user_id' => (int)$userId,
            ])->get('tb_portal_app_access')->result_array();
            $settings = [];
            foreach ($rows as $row) $settings[$row['app_code']] = $row;
            foreach ($apps as $code => &$app) {
                if (!empty($app['hidden'])) {
                    unset($apps[$code]);
                    continue;
                }
                if (isset($settings[$code]) && (int)$settings[$code]['is_allowed'] !== 1) {
                    unset($apps[$code]);
                    continue;
                }
                $app['redirect_url'] = !empty($launchUrls[$code]) ? $launchUrls[$code] : $app['url'];
                $app['client_configured'] = !empty($launchUrls[$code]);
            }
            unset($app);
        } else {
            foreach ($apps as $code => &$app) {
                if (!empty($app['hidden'])) {
                    unset($apps[$code]);
                    continue;
                }
                $app['redirect_url'] = !empty($launchUrls[$code]) ? $launchUrls[$code] : $app['url'];
                $app['client_configured'] = !empty($launchUrls[$code]);
            }
            unset($app);
        }
        foreach ($apps as $code => &$app) {
            if (empty($launchUrls[$code]) || !$this->user_can_access_app($authSource, $userId, $code)) {
                unset($apps[$code]);
                continue;
            }
            $app['app_code'] = $code;
        }
        unset($app);
        return array_values($apps);
    }

    public function user_can_access_app($authSource, $userId, $appCode)
    {
        if (!isset($this->apps()[$appCode])) return false;
        if (!$this->db->table_exists('tb_portal_app_access')) return false;
        $userRows = $this->db->where(['auth_source' => $authSource, 'user_id' => (int)$userId])->count_all_results('tb_portal_app_access');
        if ($userRows === 0) return false;
        $assignment = $this->db->select('is_allowed')->where([
            'auth_source' => $authSource,
            'user_id' => (int)$userId,
            'app_code' => $appCode,
        ])->get('tb_portal_app_access')->row_array();
        if (!$assignment) return false;
        return (int)$assignment['is_allowed'] === 1;
    }

    public function save_access($authSource, $userId, $allowedApps, $updatedBy)
    {
        $table = 'tb_portal_app_access';
        if (!$this->db->table_exists($table)) return false;
        $validSources = ['tb_karyawan', 'tb_users'];
        if (!in_array($authSource, $validSources, true) || (int)$userId < 1) return false;
        $knownApps = array_keys($this->apps());
        $allowed = array_intersect($knownApps, (array)$allowedApps);
        $this->db->trans_begin();
        $this->db->where(['auth_source' => $authSource, 'user_id' => (int)$userId])->delete($table);
        foreach ($knownApps as $code) {
            $row = ['auth_source' => $authSource, 'user_id' => (int)$userId, 'app_code' => $code, 'is_allowed' => in_array($code, $allowed, true) ? 1 : 0, 'updated_by' => (int)$updatedBy];
            $this->db->insert($table, $row);
        }
        if ($this->db->trans_status()) {
            $this->db->trans_commit();
            return true;
        }
        $this->db->trans_rollback();
        return false;
    }
}
