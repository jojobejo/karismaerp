<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_Dashboardaccess
 * Mengelola pengaturan hak akses buka/kunci setiap menu dashboard per user.
 */
class M_Dashboardaccess extends CI_Model
{
    private $table = 'tb_user_dashboard_access';
    private $userTable = 'tb_users';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Dashboard');
    }

    /**
     * Pastikan tabel tb_user_dashboard_access tersedia di database
     */
    public function ensure_schema()
    {
        if (!$this->db->table_exists($this->table)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL,
                `menu_key` VARCHAR(120) NOT NULL,
                `is_allowed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Diizinkan (Terbuka), 0 = Tidak Diizinkan (Terkunci / Gembok)',
                `updated_by` VARCHAR(100) NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_user_menu` (`user_id`, `menu_key`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_menu_key` (`menu_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }

    /**
     * Cek apakah baris user termasuk akun Administrator
     */
    public function is_admin_row(array $user)
    {
        $username = strtolower(trim((string)($user['username'] ?? '')));
        $jobdesk = strtoupper(trim((string)($user['jobdesk'] ?? ($user['bagian'] ?? ''))));
        $lv = (int)($user['akses_lv'] ?? ($user['level'] ?? 0));

        return $username === 'admin'
            || $lv >= 99
            || in_array($jobdesk, array('ADMIN', 'SUPER ADMIN', 'SUPERADMIN'), true);
    }

    /**
     * Ambil daftar user aktif beserta jumlah menu yang sedang dikunci
     */
    public function users($search = '', $limit = 300)
    {
        $this->ensure_schema();
        if (!$this->db->table_exists($this->userTable)) {
            return array();
        }

        // Hitung jumlah menu terkunci per user
        $lockedCounts = array();
        $lockRows = $this->db
            ->select('user_id, COUNT(*) AS locked_total')
            ->where('is_allowed', 0)
            ->group_by('user_id')
            ->get($this->table)
            ->result_array();
        foreach ($lockRows as $lr) {
            $lockedCounts[(int)$lr['user_id']] = (int)$lr['locked_total'];
        }

        $fields = $this->db->list_fields($this->userTable);
        $this->db->select('id, nik, nama_lngkp AS nm_karyawan, username, departement AS departemen, bagian AS jobdesk, level AS akses_lv');
        $this->db->from($this->userTable);

        if (in_array('status', $fields, true)) {
            $this->db->where('status', 1);
        }

        if ($search !== '') {
            $this->db->group_start()
                ->like('nik', $search)
                ->or_like('nama_lngkp', $search)
                ->or_like('username', $search)
                ->or_like('bagian', $search)
                ->or_like('departement', $search)
                ->group_end();
        }

        $this->db->order_by('level', 'DESC');
        $this->db->order_by('nama_lngkp', 'ASC');
        $this->db->limit($limit);

        $rows = $this->db->get()->result_array();
        foreach ($rows as $idx => $row) {
            $uid = (int)$row['id'];
            $isAdmin = $this->is_admin_row($row);
            $rows[$idx]['is_admin'] = $isAdmin ? 1 : 0;
            $rows[$idx]['locked_count'] = $isAdmin ? 0 : ($lockedCounts[$uid] ?? 0);
        }

        return $rows;
    }

    /**
     * Cari satu user berdasarkan ID
     */
    public function find_user($userId)
    {
        if (!$this->db->table_exists($this->userTable)) {
            return null;
        }

        $row = $this->db
            ->select('id, nik, nama_lngkp AS nm_karyawan, username, departement AS departemen, bagian AS jobdesk, level AS akses_lv')
            ->where('id', (int)$userId)
            ->get($this->userTable)
            ->row_array();

        if (!$row) {
            return null;
        }

        $row['is_admin'] = $this->is_admin_row($row) ? 1 : 0;
        return $row;
    }

    /**
     * Ambil matriks lengkap section dan menu dashboard untuk user tertentu
     */
    public function matrix($userId)
    {
        $this->ensure_schema();
        $user = $this->find_user($userId);
        if (!$user) {
            return null;
        }

        $isAdmin = !empty($user['is_admin']);
        $saved = $this->M_Dashboard->get_user_dashboard_rules($userId);
        $sections = $this->M_Dashboard->catalog_sections();

        $uniqueSeen = array();
        $allowedUnique = 0;
        $lockedUnique = 0;

        foreach ($sections as $secKey => $section) {
            foreach ($section['menus'] as $idx => $menu) {
                $menuKey = $menu['menu_key'];
                $hasOverride = !$isAdmin && array_key_exists($menuKey, $saved);
                $isAllowed = $isAdmin ? 1 : ($hasOverride ? (int)$saved[$menuKey] : 1);

                $sections[$secKey]['menus'][$idx]['is_allowed'] = $isAllowed;
                $sections[$secKey]['menus'][$idx]['has_override'] = $hasOverride ? 1 : 0;

                if (!isset($uniqueSeen[$menuKey])) {
                    $uniqueSeen[$menuKey] = true;
                    if ($isAllowed === 1) {
                        $allowedUnique++;
                    } else {
                        $lockedUnique++;
                    }
                }
            }
        }

        return array(
            'user' => $user,
            'sections' => $sections,
            'summary' => array(
                'total_unique' => count($uniqueSeen),
                'allowed_unique' => $allowedUnique,
                'locked_unique' => $lockedUnique,
            ),
        );
    }

    /**
     * Simpan status akses satu menu untuk satu user
     */
    public function save_access($userId, $menuKey, $isAllowed, $actor = '')
    {
        $this->ensure_schema();
        $user = $this->find_user($userId);
        if (!$user || !empty($user['is_admin'])) {
            return false;
        }

        $catalog = $this->M_Dashboard->menu_catalog_map();
        if (!isset($catalog[$menuKey])) {
            return false;
        }

        $isAllowed = ((int)$isAllowed === 1) ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        $existing = $this->db
            ->where('user_id', (int)$userId)
            ->where('menu_key', $menuKey)
            ->get($this->table)
            ->row_array();

        $payload = array(
            'user_id' => (int)$userId,
            'menu_key' => $menuKey,
            'is_allowed' => $isAllowed,
            'updated_by' => $actor !== '' ? $actor : null,
            'updated_at' => $now,
        );

        if ($existing) {
            return $this->db
                ->where('id', (int)$existing['id'])
                ->update($this->table, $payload);
        }

        $payload['created_at'] = $now;
        return $this->db->insert($this->table, $payload);
    }

    /**
     * Simpan status akses secara massal (per section atau semua menu)
     */
    public function bulk_update($userId, array $menuKeys, $isAllowed, $actor = '')
    {
        $this->ensure_schema();
        $user = $this->find_user($userId);
        if (!$user || !empty($user['is_admin'])) {
            return false;
        }

        $catalog = $this->M_Dashboard->menu_catalog_map();
        $validKeys = array();
        foreach ($menuKeys as $key) {
            $key = trim((string)$key);
            if ($key !== '' && isset($catalog[$key])) {
                $validKeys[$key] = $key;
            }
        }

        if (empty($validKeys)) {
            return false;
        }

        $this->db->trans_begin();
        foreach ($validKeys as $menuKey) {
            $this->save_access($userId, $menuKey, $isAllowed, $actor);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    /**
     * Hapus semua aturan kustom user sehingga kembali ke akses default
     */
    public function reset_user($userId)
    {
        $this->ensure_schema();
        $user = $this->find_user($userId);
        if (!$user) {
            return false;
        }

        return $this->db
            ->where('user_id', (int)$userId)
            ->delete($this->table);
    }
}

