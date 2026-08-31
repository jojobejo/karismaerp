<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Userfacility extends CI_Model
{
    private $table = 'tb_user_facility';
    private $userTable = 'tb_karyawan';

    public function ensure_schema()
    {
        if (!$this->db->table_exists($this->table)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id_user_facility` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL,
                `facility_key` VARCHAR(120) NOT NULL,
                `facility_label` VARCHAR(180) NOT NULL,
                `module_key` VARCHAR(80) NOT NULL DEFAULT 'general',
                `facility_group` VARCHAR(80) NOT NULL DEFAULT 'Umum',
                `is_allowed` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id_user_facility`),
                UNIQUE KEY `uniq_user_facility` (`user_id`, `facility_key`),
                KEY `idx_facility_key` (`facility_key`),
                KEY `idx_module_key` (`module_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    public function catalog()
    {
        return [
            ['key' => 'menu.ics_lpb', 'label' => 'Akses Menu LPB', 'module' => 'LPB', 'group' => 'Menu / Module'],
            ['key' => 'action.add', 'label' => 'Tambah Data', 'module' => 'GENERAL', 'group' => 'Aksi'],
            ['key' => 'action.edit', 'label' => 'Edit Data', 'module' => 'GENERAL', 'group' => 'Aksi'],
            ['key' => 'action.delete', 'label' => 'Hapus Data', 'module' => 'GENERAL', 'group' => 'Aksi'],
            ['key' => 'action.approve', 'label' => 'Approve / Afirmasi', 'module' => 'GENERAL', 'group' => 'Aksi'],
            ['key' => 'action.print', 'label' => 'Print Dokumen', 'module' => 'GENERAL', 'group' => 'Aksi'],
            ['key' => 'action.export', 'label' => 'Export Data', 'module' => 'GENERAL', 'group' => 'Aksi'],
            ['key' => 'lpb.view_nominal', 'label' => 'Lihat Nominal LPB', 'module' => 'LPB', 'group' => 'Data Sensitif'],
            ['key' => 'sensitive.view_hpp', 'label' => 'Lihat HPP', 'module' => 'GENERAL', 'group' => 'Data Sensitif'],
            ['key' => 'sensitive.view_margin', 'label' => 'Lihat Margin', 'module' => 'GENERAL', 'group' => 'Data Sensitif'],
            ['key' => 'sensitive.view_purchase_price', 'label' => 'Lihat Harga Beli', 'module' => 'GENERAL', 'group' => 'Data Sensitif'],
            ['key' => 'sensitive.view_sales_price', 'label' => 'Lihat Harga Jual', 'module' => 'GENERAL', 'group' => 'Data Sensitif'],
            ['key' => 'scope.branch', 'label' => 'Akses Cabang', 'module' => 'GENERAL', 'group' => 'Scope Data'],
            ['key' => 'scope.warehouse', 'label' => 'Akses Gudang', 'module' => 'GENERAL', 'group' => 'Scope Data'],
            ['key' => 'scope.document_status', 'label' => 'Akses Status Dokumen', 'module' => 'GENERAL', 'group' => 'Scope Data'],
        ];
    }

    public function users($search = '', $limit = 200)
    {
        if (!$this->db->table_exists($this->userTable)) {
            return [];
        }

        $this->db->select('id, nik, nm_karyawan, username, departemen, jobdesk, akses_lv');
        $this->db->from($this->userTable);
        if ($search !== '') {
            $this->db->group_start()
                ->like('nik', $search)
                ->or_like('nm_karyawan', $search)
                ->or_like('username', $search)
                ->or_like('jobdesk', $search)
                ->or_like('departemen', $search)
                ->group_end();
        }
        $this->db->order_by('nm_karyawan', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function find_user($userId)
    {
        if (!$this->db->table_exists($this->userTable)) {
            return null;
        }

        return $this->db
            ->select('id, nik, nm_karyawan, username, departemen, jobdesk, akses_lv')
            ->where('id', (int) $userId)
            ->get($this->userTable)
            ->row_array();
    }

    public function matrix($userId)
    {
        $this->ensure_schema();
        $rows = $this->db
            ->where('user_id', (int) $userId)
            ->get($this->table)
            ->result_array();
        $saved = [];
        foreach ($rows as $row) {
            $saved[$row['facility_key']] = (int) $row['is_allowed'];
        }

        $matrix = [];
        foreach ($this->catalog() as $item) {
            $matrix[] = $item + [
                'is_allowed' => $saved[$item['key']] ?? $this->default_allowed_for_user($userId, $item['key']),
                'has_override' => array_key_exists($item['key'], $saved) ? 1 : 0,
            ];
        }

        return $matrix;
    }

    public function save_facility($userId, $facilityKey, $isAllowed)
    {
        $this->ensure_schema();
        $item = $this->catalog_item($facilityKey);
        if (!$item) {
            return false;
        }

        $payload = [
            'user_id' => (int) $userId,
            'facility_key' => $item['key'],
            'facility_label' => $item['label'],
            'module_key' => $item['module'],
            'facility_group' => $item['group'],
            'is_allowed' => (int) $isAllowed,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $existing = $this->db
            ->where('user_id', (int) $userId)
            ->where('facility_key', $item['key'])
            ->get($this->table)
            ->row_array();

        if ($existing) {
            return $this->db
                ->where('id_user_facility', (int) $existing['id_user_facility'])
                ->update($this->table, $payload);
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $payload);
    }

    public function is_allowed($userId, $facilityKey, $default = true)
    {
        $this->ensure_schema();
        $row = $this->db
            ->select('is_allowed')
            ->where('user_id', (int) $userId)
            ->where('facility_key', $facilityKey)
            ->get($this->table)
            ->row_array();

        if ($row) {
            return (int) $row['is_allowed'] === 1;
        }

        return $this->default_allowed_for_user($userId, $facilityKey, $default) === 1;
    }

    private function catalog_item($facilityKey)
    {
        foreach ($this->catalog() as $item) {
            if ($item['key'] === $facilityKey) {
                return $item;
            }
        }
        return null;
    }

    private function default_allowed_for_user($userId, $facilityKey, $fallback = true)
    {
        if ($facilityKey !== 'lpb.view_nominal') {
            return $fallback ? 1 : 0;
        }

        $user = $this->find_user($userId);
        if (!$user) {
            return $fallback ? 1 : 0;
        }

        $username = strtolower(trim((string) ($user['username'] ?? '')));
        $jobdesk = strtoupper(trim((string) ($user['jobdesk'] ?? '')));
        $departemen = strtoupper(trim((string) ($user['departemen'] ?? '')));

        if (in_array($username, ['admlpb', 'adminloglpb', 'admlpb2'], true)
            || in_array($jobdesk, ['ADMLPB', 'ADMINLOGLPB', 'ADMLPB2'], true)
            || ($departemen === 'LOGISTIK' && $jobdesk === 'ADMLPB')) {
            return 0;
        }

        return $fallback ? 1 : 0;
    }
}
