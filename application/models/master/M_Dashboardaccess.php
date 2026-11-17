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
     * Pastikan tabel tb_user_dashboard_access dan kolom izin aksi granular tersedia di database
     */
    public function ensure_schema()
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        if (!$this->db->table_exists($this->table)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT UNSIGNED NOT NULL,
                `menu_key` VARCHAR(120) NOT NULL,
                `is_allowed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Diizinkan (Terbuka), 0 = Tidak Diizinkan (Terkunci / Gembok)',
                `can_view` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Boleh melihat halaman/data',
                `can_create` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Boleh membuat/menambah data',
                `can_edit` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Boleh mengubah data',
                `can_delete` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Boleh menghapus data',
                `updated_by` VARCHAR(100) NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_user_menu` (`user_id`, `menu_key`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_menu_key` (`menu_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return;
        }

        // Tambahkan kolom granular jika tabel lama belum memilikinya
        $actionColumns = array(
            'can_view' => "ALTER TABLE `{$this->table}` ADD COLUMN `can_view` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_allowed`",
            'can_create' => "ALTER TABLE `{$this->table}` ADD COLUMN `can_create` TINYINT(1) NOT NULL DEFAULT 1 AFTER `can_view`",
            'can_edit' => "ALTER TABLE `{$this->table}` ADD COLUMN `can_edit` TINYINT(1) NOT NULL DEFAULT 1 AFTER `can_create`",
            'can_delete' => "ALTER TABLE `{$this->table}` ADD COLUMN `can_delete` TINYINT(1) NOT NULL DEFAULT 1 AFTER `can_edit`",
        );
        $added = false;
        foreach ($actionColumns as $col => $sql) {
            if (!$this->db->field_exists($col, $this->table)) {
                $this->db->query($sql);
                $added = true;
            }
        }
        // Sinkronkan baris lama yang terkunci (is_allowed = 0) agar seluruh aksi ikut 0
        if ($added) {
            $this->db->query("UPDATE `{$this->table}` SET `can_view` = 0, `can_create` = 0, `can_edit` = 0, `can_delete` = 0 WHERE `is_allowed` = 0");
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
     * Ambil map aturan akses granular (is_allowed, can_view, can_create, can_edit, can_delete) per menu_key untuk satu user
     */
    public function get_user_action_rules($userId)
    {
        $this->ensure_schema();
        $rows = $this->db
            ->select('menu_key, is_allowed, can_view, can_create, can_edit, can_delete')
            ->where('user_id', (int)$userId)
            ->get($this->table)
            ->result_array();

        $map = array();
        foreach ($rows as $row) {
            $isAllowed = ((int)$row['is_allowed'] === 1) ? 1 : 0;
            $map[$row['menu_key']] = array(
                'is_allowed' => $isAllowed,
                'can_view'   => $isAllowed ? ((int)$row['can_view'] === 1 ? 1 : 0) : 0,
                'can_create' => $isAllowed ? ((int)$row['can_create'] === 1 ? 1 : 0) : 0,
                'can_edit'   => $isAllowed ? ((int)$row['can_edit'] === 1 ? 1 : 0) : 0,
                'can_delete' => $isAllowed ? ((int)$row['can_delete'] === 1 ? 1 : 0) : 0,
            );
        }
        return $map;
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
        $saved = $this->get_user_action_rules($userId);
        $sections = $this->M_Dashboard->catalog_sections();

        $uniqueSeen = array();
        $allowedUnique = 0;
        $lockedUnique = 0;

        foreach ($sections as $secKey => $section) {
            foreach ($section['menus'] as $idx => $menu) {
                $menuKey = $menu['menu_key'];
                $hasOverride = !$isAdmin && array_key_exists($menuKey, $saved);

                if ($isAdmin) {
                    $isAllowed = 1;
                    $canView = 1;
                    $canCreate = 1;
                    $canEdit = 1;
                    $canDelete = 1;
                } elseif ($hasOverride) {
                    $rule = $saved[$menuKey];
                    $isAllowed = $rule['is_allowed'];
                    $canView = $rule['can_view'];
                    $canCreate = $rule['can_create'];
                    $canEdit = $rule['can_edit'];
                    $canDelete = $rule['can_delete'];
                } else {
                    $isAllowed = 1;
                    $canView = 1;
                    $canCreate = 1;
                    $canEdit = 1;
                    $canDelete = 1;
                }

                $sections[$secKey]['menus'][$idx]['is_allowed'] = $isAllowed;
                $sections[$secKey]['menus'][$idx]['can_view'] = $canView;
                $sections[$secKey]['menus'][$idx]['can_create'] = $canCreate;
                $sections[$secKey]['menus'][$idx]['can_edit'] = $canEdit;
                $sections[$secKey]['menus'][$idx]['can_delete'] = $canDelete;
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
            'can_view' => $isAllowed,
            'can_create' => $isAllowed,
            'can_edit' => $isAllowed,
            'can_delete' => $isAllowed,
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
     * Simpan satu izin aksi granular (view, create, edit, delete) per user & per menu
     */
    public function save_action($userId, $menuKey, $action, $value, $actor = '')
    {
        $this->ensure_schema();
        $user = $this->find_user($userId);
        if (!$user || !empty($user['is_admin'])) {
            return false;
        }

        $allowedActions = array('view', 'create', 'edit', 'delete');
        if (!in_array($action, $allowedActions, true)) {
            return false;
        }

        $catalog = $this->M_Dashboard->menu_catalog_map();
        if (!isset($catalog[$menuKey])) {
            return false;
        }

        $value = ((int)$value === 1) ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        $existing = $this->db
            ->where('user_id', (int)$userId)
            ->where('menu_key', $menuKey)
            ->get($this->table)
            ->row_array();

        if ($existing) {
            $isAllowed = ((int)$existing['is_allowed'] === 1) ? 1 : 0;
            $state = array(
                'is_allowed' => $isAllowed,
                'can_view'   => $isAllowed ? ((int)$existing['can_view'] === 1 ? 1 : 0) : 0,
                'can_create' => $isAllowed ? ((int)$existing['can_create'] === 1 ? 1 : 0) : 0,
                'can_edit'   => $isAllowed ? ((int)$existing['can_edit'] === 1 ? 1 : 0) : 0,
                'can_delete' => $isAllowed ? ((int)$existing['can_delete'] === 1 ? 1 : 0) : 0,
            );
        } else {
            $state = array(
                'is_allowed' => 1,
                'can_view'   => 1,
                'can_create' => 1,
                'can_edit'   => 1,
                'can_delete' => 1,
            );
        }

        $state['can_' . $action] = $value;

        // Konsistensi: jika aksi create/edit/delete diaktifkan, view & menu otomatis dibuka;
        // jika view dimatikan, seluruh aksi & menu ikut dikunci
        if ($action !== 'view' && $value === 1) {
            $state['can_view'] = 1;
            $state['is_allowed'] = 1;
        } elseif ($action === 'view' && $value === 0) {
            $state['is_allowed'] = 0;
            $state['can_create'] = 0;
            $state['can_edit'] = 0;
            $state['can_delete'] = 0;
        } elseif ($state['can_view'] === 1) {
            $state['is_allowed'] = 1;
        }

        $payload = array_merge($state, array(
            'user_id' => (int)$userId,
            'menu_key' => $menuKey,
            'updated_by' => $actor !== '' ? $actor : null,
            'updated_at' => $now,
        ));

        if ($existing) {
            return $this->db
                ->where('id', (int)$existing['id'])
                ->update($this->table, $payload);
        }

        $payload['created_at'] = $now;
        return $this->db->insert($this->table, $payload);
    }

    /**
     * Cek apakah user memiliki izin aksi (view, create, edit, delete) pada menu_key tertentu
     */
    public function can_action($userId, $menuKey, $action = 'view')
    {
        $this->ensure_schema();
        $user = $this->find_user($userId);
        if (!$user) {
            return false;
        }
        if (!empty($user['is_admin'])) {
            return true;
        }

        $action = strtolower(trim((string)$action));
        if (!in_array($action, array('view', 'create', 'edit', 'delete'), true)) {
            $action = 'view';
        }

        $row = $this->db
            ->select('is_allowed, can_view, can_create, can_edit, can_delete')
            ->where('user_id', (int)$userId)
            ->where('menu_key', $menuKey)
            ->get($this->table)
            ->row_array();

        if (!$row) {
            return true;
        }
        if ((int)$row['is_allowed'] !== 1) {
            return false;
        }

        return (int)$row['can_' . $action] === 1;
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

    /**
     * Salin seluruh aturan akses menu dashboard (termasuk izin granular) dari user sumber ke user target
     */
    public function clone_from_user($sourceUserId, $targetUserId, $actor = '')
    {
        $this->ensure_schema();
        $sourceUserId = (int)$sourceUserId;
        $targetUserId = (int)$targetUserId;
        if ($sourceUserId <= 0 || $targetUserId <= 0 || $sourceUserId === $targetUserId) {
            return false;
        }

        $targetUser = $this->find_user($targetUserId);
        if (!$targetUser || !empty($targetUser['is_admin'])) {
            return false;
        }

        $sourceRows = $this->db->where('user_id', $sourceUserId)->get($this->table)->result_array();
        $now = date('Y-m-d H:i:s');

        $this->db->trans_begin();
        $this->db->where('user_id', $targetUserId)->delete($this->table);

        foreach ($sourceRows as $row) {
            $this->db->insert($this->table, array(
                'user_id'    => $targetUserId,
                'menu_key'   => $row['menu_key'],
                'is_allowed' => (int)$row['is_allowed'],
                'can_view'   => (int)($row['can_view'] ?? $row['is_allowed']),
                'can_create' => (int)($row['can_create'] ?? $row['is_allowed']),
                'can_edit'   => (int)($row['can_edit'] ?? $row['is_allowed']),
                'can_delete' => (int)($row['can_delete'] ?? $row['is_allowed']),
                'updated_by' => $actor !== '' ? $actor : null,
                'created_at' => $now,
                'updated_at' => $now,
            ));
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }
}

