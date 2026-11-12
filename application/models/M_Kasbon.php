<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Kasbon extends CI_Model
{
    /**
     * =====================================================================
     * KONFIGURASI RANTAI APPROVAL BERDASARKAN JOBDESK PEMOHON
     * =====================================================================
     * Format: 'JOBDESK_PEMOHON' => ['APPROVER_1', 'APPROVER_2', ...]
     *
     * Urutan approval dari kiri ke kanan.
     * Setelah semua approver menyetujui → status 'approved' (siap cair oleh KASIR).
     * Jobdesk yang tidak terdaftar tidak memerlukan approval → langsung 'approved'.
     * =====================================================================
     */
    private $approval_chains = [
        'SC'    => ['MNGSC', 'KADEPSC'],
        'ADMSC' => ['MNGSC', 'KADEPSC'],
        'ADMIN' => ['KADEPSC'],
        'HRD'   => ['KADEPSC'],
        // Tambahkan jobdesk lain beserta rantai approvalnya di sini
    ];

    /**
     * Label tampilan yang ramah untuk setiap kode jobdesk approver.
     */
    public $approver_labels = [
        'MNGSC'      => 'Manager SC',
        'MANAGERSC'  => 'Manager SC',
        'KADEPSC'    => 'Kepala Departemen SC',
        'KADEP_SC'   => 'Kepala Departemen SC',
        'KASIR'      => 'Kasir',
        'SC'         => 'Staff SC',
        'ADMSC'      => 'Admin SC',
        'ADMINSC'    => 'Admin SC',
        'ADMIN'      => 'Admin',
        'HRD'        => 'HRD',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->_check_schema();
    }

    /**
     * Memastikan kolom-kolom baru untuk workflow approval kasbon terbuat otomatis.
     */
    private function _check_schema()
    {
        if ($this->db->table_exists('tb_kasbon')) {
            $fields = [
                'lampiran'           => "VARCHAR(255) NULL AFTER `keterangan`",
                'workflow_type'      => "VARCHAR(50) NULL AFTER `tanggal_pengajuan`",
                'approver_1'         => "VARCHAR(100) NULL AFTER `workflow_type`",
                'approver_2'         => "VARCHAR(100) NULL AFTER `approver_1`",
                'approved_atasan_by' => "VARCHAR(100) NULL AFTER `status`",
                'approved_atasan_at' => "DATETIME NULL AFTER `approved_atasan_by`",
                'approved_penilai_by'=> "VARCHAR(100) NULL AFTER `approved_atasan_at`",
                'approved_penilai_at'=> "DATETIME NULL AFTER `approved_penilai_by`",
                'rejected_by'        => "VARCHAR(100) NULL AFTER `approved_penilai_at`",
                'rejected_at'        => "DATETIME NULL AFTER `rejected_by`",
                'rejected_reason'    => "TEXT NULL AFTER `rejected_at`",
                'cair_by'            => "VARCHAR(100) NULL AFTER `rejected_reason`",
                'cair_at'            => "DATETIME NULL AFTER `cair_by`"
            ];

            foreach ($fields as $field => $definition) {
                if (!$this->db->field_exists($field, 'tb_kasbon')) {
                    $this->db->query("ALTER TABLE `tb_kasbon` ADD COLUMN `$field` $definition");
                }
            }
        }
    }

    /**
     * Normalisasi kode jobdesk ke standar singkatan.
     */
    public function normalize_jobdesk($jobdesk)
    {
        $code = strtoupper(trim((string)$jobdesk));
        $map = [
            'MANAGERSC'                 => 'MNGSC',
            'MANAGER_SC'                => 'MNGSC',
            'MANAGER SALES COUNTER'     => 'MNGSC',
            'MANAGERACC'                => 'MNGACC',
            'MANAGER_ACC'               => 'MNGACC',
            'MT MANAGER ACCOUNTING & TAX' => 'MNGACC',
            'MANAGER ACCOUNTING & TAX'  => 'MNGACC',
            'MANAGERSE'                 => 'MNGSE',
            'MANAGER_SE'                => 'MNGSE',
            'MANAGER SALES EXECUTIVE'   => 'MNGSE',
            'MANAGERWH'                 => 'MNGWH',
            'MANAGER_WH'                => 'MNGWH',
            'MT MANAGER WAREHOUSE'      => 'MNGWH',
            'ADMIN DISTRIBUSI & MANAGER WAREHOUSE' => 'MNGWH',
            'ADMINSC'                   => 'ADMSC',
            'ADMIN_SC'                  => 'ADMSC',
            'ADMIN SALES COUNTER'       => 'ADMSC',
            'SALES COUNTER'             => 'SC',
            'MT SALES COUNTER'          => 'SC',
            'SALESCOUNTER'              => 'SC',
            'KADEP_SC'                  => 'KADEPSC',
            'MT KADEP KEUANGAN & SALES' => 'KADEPSC',
            'KADEP KEUANGAN & SALES'    => 'KADEPSC',
            'KADEP_UB'                  => 'KADEPUB',
            'KEPALA UNIT BISNIS'        => 'KADEPUB',
            'KADEP_KS'                  => 'KADEPKS',
            'MT KADEP HRGA'             => 'HRD',
            'ADMIN HRGA'                => 'HRD',
        ];
        return $map[$code] ?? $code;
    }

    /**
     * Mengembalikan daftar alias jobdesk yang setara.
     * Contoh: 'MANAGERSC' atau 'MNGSC' -> ['MNGSC', 'MANAGERSC', 'MANAGER_SC']
     */
    public function get_jobdesk_aliases($jobdesk)
    {
        $norm = $this->normalize_jobdesk($jobdesk);
        $raw  = strtoupper(trim((string)$jobdesk));

        $aliasMap = [
            'MNGSC'   => ['MNGSC', 'MANAGERSC', 'MANAGER_SC', 'MANAGER SC', 'MANAGER SALES COUNTER'],
            'MNGACC'  => ['MNGACC', 'MANAGERACC', 'MANAGER_ACC', 'MANAGER ACC', 'MT MANAGER ACCOUNTING & TAX', 'MANAGER ACCOUNTING & TAX'],
            'MNGSE'   => ['MNGSE', 'MANAGERSE', 'MANAGER_SE', 'MANAGER SE', 'MANAGER SALES EXECUTIVE'],
            'MNGWH'   => ['MNGWH', 'MANAGERWH', 'MANAGER_WH', 'MANAGER WH', 'MT MANAGER WAREHOUSE', 'ADMIN DISTRIBUSI & MANAGER WAREHOUSE'],
            'ADMSC'   => ['ADMSC', 'ADMINSC', 'ADMIN_SC', 'ADMIN SC', 'ADMIN SALES COUNTER'],
            'SC'      => ['SC', 'SALESCOUNTER', 'SALES COUNTER', 'MT SALES COUNTER'],
            'KADEPSC' => ['KADEPSC', 'KADEP_SC', 'KADEP SC', 'MT KADEP KEUANGAN & SALES', 'KADEP KEUANGAN & SALES'],
            'KADEPUB' => ['KADEPUB', 'KADEP_UB', 'KADEP UB', 'KEPALA UNIT BISNIS'],
            'KADEPKS' => ['KADEPKS', 'KADEP_KS', 'KADEP KS', 'MT KADEP KEUANGAN & SALES'],
        ];

        if (isset($aliasMap[$norm])) {
            return $aliasMap[$norm];
        }

        foreach ($aliasMap as $list) {
            if (in_array($norm, $list) || in_array($raw, $list)) {
                return $list;
            }
        }

        return array_unique(array_filter([$norm, $raw]));
    }

    /**
     * Mengembalikan array rantai approver (jobdesk) untuk jobdesk pemohon tertentu.
     * Contoh: 'SC' => ['MNGSC', 'KADEPSC']
     */
    public function get_approval_chain($jobdesk_pemohon)
    {
        $raw = strtoupper(trim((string)$jobdesk_pemohon));
        $norm = $this->normalize_jobdesk($raw);

        if (isset($this->approval_chains[$norm])) {
            return $this->approval_chains[$norm];
        }
        if (isset($this->approval_chains[$raw])) {
            return $this->approval_chains[$raw];
        }
        return [];
    }

    /**
     * Ambil rantai approval berdasarkan kolom atasan dan penilai pada tb_users.
     * Mengembalikan array berurutan [atasan, penilai] tanpa duplikat dan tanpa nilai kosong.
     */
    public function get_user_approval_chain($user_id)
    {
        if (!$this->db->table_exists('tb_users') || empty($user_id)) {
            return [];
        }

        $row = $this->db
            ->select('atasan, penilai')
            ->where('id', (int)$user_id)
            ->get('tb_users')
            ->row_array();

        if (!$row) {
            return [];
        }

        $chain = [];
        $atasan = trim((string)($row['atasan'] ?? ''));
        $penilai = trim((string)($row['penilai'] ?? ''));

        if ($atasan !== '') {
            $chain[] = $atasan;
        }
        // Hindari duplikasi jika atasan dan penilai adalah orang yang sama
        if ($penilai !== '' && strcasecmp($penilai, $atasan) !== 0) {
            $chain[] = $penilai;
        }

        return $chain;
    }

    /**
     * Mengembalikan label tampilan untuk kode jobdesk atau nama approver.
     */
    public function get_approver_label($jobdesk_code)
    {
        $raw  = trim((string)$jobdesk_code);
        $code = strtoupper($raw);
        return isset($this->approver_labels[$code]) ? $this->approver_labels[$code] : $raw;
    }

    /**
     * Mengambil daftar kasbon yang relevan dengan user yang sedang login.
     * Mendukung pencocokan berdasarkan nama/username (atasan & penilai dari tb_users) serta jobdesk legacy.
     */
    public function get_kasbon_for_user($user)
    {
        $id_user         = $user['id'];
        $nama_user       = trim((string)($user['nama'] ?? ''));
        $username_user   = trim((string)($user['username'] ?? ''));
        $jobdesk_raw     = strtoupper(trim((string)($user['jobdesk'] ?? '')));
        $jobdesk_norm    = $this->normalize_jobdesk($jobdesk_raw);
        $jobdesk_aliases = $this->get_jobdesk_aliases($jobdesk_raw);

        // Gabungkan identifier approver yang cocok untuk user ini (jobdesk aliases + nama lengkap + username)
        $approver_identifiers = array_values(array_unique(array_filter(array_merge(
            $jobdesk_aliases,
            [$nama_user, $username_user]
        ), function ($v) {
            return trim((string)$v) !== '';
        })));

        // KADEPSC dan ADMIN melihat semua pengajuan kasbon
        if (in_array($jobdesk_norm, ['KADEPSC', 'ADMIN']) || in_array($jobdesk_raw, ['KADEPSC', 'ADMIN'])) {
            return $this->get_all_kasbon();
        }

        // KASIR melihat kasbon miliknya + semua yang berstatus approved (siap cair) + yang menunggu approval dirinya
        if ($jobdesk_norm === 'KASIR' || $jobdesk_raw === 'KASIR') {
            $this->db->select('*');
            $this->db->from('tb_kasbon');
            $this->db->group_start();
            $this->db->where('id_user', $id_user);
            $this->db->or_where_in('status', ['approved', 'cair']);
            if (!empty($approver_identifiers)) {
                $this->db->or_group_start();
                $this->db->where('status', 'pending_atasan');
                $this->db->where_in('approver_1', $approver_identifiers);
                $this->db->group_end();
                $this->db->or_group_start();
                $this->db->where('status', 'pending_penilai');
                $this->db->where_in('approver_2', $approver_identifiers);
                $this->db->group_end();
            }
            $this->db->group_end();
            $this->db->order_by('id', 'DESC');
            return $this->db->get()->result_array();
        }

        // User biasa / Approver: pengajuan milik sendiri + kasbon yang menunggu approval dari user/jobdesk ini
        $this->db->select('*');
        $this->db->from('tb_kasbon');
        $this->db->group_start();

        // Milik sendiri
        $this->db->where('id_user', $id_user);

        if (!empty($approver_identifiers)) {
            // Menunggu approval stage 1 (atasan / approver_1)
            $this->db->or_group_start();
            $this->db->where('status', 'pending_atasan');
            $this->db->where_in('approver_1', $approver_identifiers);
            $this->db->group_end();

            // Menunggu approval stage 2 (penilai / approver_2)
            $this->db->or_group_start();
            $this->db->where('status', 'pending_penilai');
            $this->db->where_in('approver_2', $approver_identifiers);
            $this->db->group_end();
        }

        $this->db->group_end();
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Memeriksa apakah user login berhak memberikan approval pada baris kasbon ini.
     * Mencocokkan nama lengkap / username (atasan & penilai dari tb_users) serta jobdesk legacy.
     */
    public function can_approve($row, $session_user)
    {
        $nama_user       = strtoupper(trim((string)($session_user['nama'] ?? '')));
        $username_user   = strtoupper(trim((string)($session_user['username'] ?? '')));
        $jobdesk_raw     = strtoupper(trim((string)($session_user['jobdesk'] ?? '')));
        $jobdesk_aliases = array_map('strtoupper', $this->get_jobdesk_aliases($jobdesk_raw));

        $matchUser = function ($target) use ($nama_user, $username_user, $jobdesk_aliases) {
            $targetUpper = strtoupper(trim((string)$target));
            if ($targetUpper === '') {
                return false;
            }
            if ($nama_user !== '' && $targetUpper === $nama_user) {
                return true;
            }
            if ($username_user !== '' && $targetUpper === $username_user) {
                return true;
            }
            return in_array($targetUpper, $jobdesk_aliases, true);
        };

        // Stage 1: menunggu approver_1 (Atasan)
        if ($row['status'] === 'pending_atasan') {
            return $matchUser($row['approver_1'] ?? '');
        }

        // Stage 2: menunggu approver_2 (Penilai)
        if ($row['status'] === 'pending_penilai') {
            return $matchUser($row['approver_2'] ?? '');
        }

        return false;
    }

    /**
     * Memeriksa apakah user login berhak mencairkan kasbon.
     * Hanya KASIR dan status 'approved'.
     */
    public function can_cair($row, $session_user)
    {
        $jobdesk_raw = strtoupper(trim((string)($session_user['jobdesk'] ?? '')));
        return ($row['status'] === 'approved' && in_array($jobdesk_raw, ['KASIR']));
    }

    /**
     * Mengecek apakah user memiliki role/kapabilitas approver (Manager, Kadep, Admin, Kasir, lv >= 2, atau atasan/penilai).
     */
    public function is_approver_user($user)
    {
        if ((int)($user['lv'] ?? 1) >= 2) {
            return true;
        }
        $jobdesk_raw  = strtoupper(trim((string)($user['jobdesk'] ?? '')));
        $jobdesk_norm = $this->normalize_jobdesk($jobdesk_raw);
        $approver_roles = ['MNGSC', 'MANAGERSC', 'KADEPSC', 'KADEP_SC', 'ADMIN', 'KASIR', 'HRD', 'MNGACC', 'MANAGERACC', 'MNGSE', 'MANAGERSE', 'MNGWH', 'MANAGERWH'];
        if (in_array($jobdesk_norm, $approver_roles) || in_array($jobdesk_raw, $approver_roles)) {
            return true;
        }

        $nama = trim((string)($user['nama'] ?? ''));
        if ($nama !== '' && $this->db->table_exists('tb_users')) {
            $count = $this->db
                ->group_start()
                    ->where('atasan', $nama)
                    ->or_where('penilai', $nama)
                ->group_end()
                ->count_all_results('tb_users');
            if ($count > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Mengambil riwayat pengajuan kasbon yang pernah disetujui / ditolak / dicairkan oleh user yang sedang login.
     */
    public function get_approval_history_for_user($user)
    {
        $nama_user       = trim((string)($user['nama'] ?? ''));
        $jobdesk_raw     = strtoupper(trim((string)($user['jobdesk'] ?? '')));
        $jobdesk_norm    = $this->normalize_jobdesk($jobdesk_raw);
        $jobdesk_aliases = $this->get_jobdesk_aliases($jobdesk_raw);

        $this->db->select('*');
        $this->db->from('tb_kasbon');
        $this->db->group_start();

        // 1. Berdasarkan nama user yang mencatat tindakan approval
        if (!empty($nama_user)) {
            $this->db->where('approved_atasan_by', $nama_user);
            $this->db->or_where('approved_penilai_by', $nama_user);
            $this->db->or_where('rejected_by', $nama_user);
            $this->db->or_where('cair_by', $nama_user);
        }

        // 2. Atau jika user adalah approver stage 1 (misal MNGSC) dan kasbon sudah diproses approval tahap 1
        if (!empty($jobdesk_aliases)) {
            $this->db->or_group_start();
            $this->db->where_in('approver_1', $jobdesk_aliases);
            $this->db->where('approved_atasan_by IS NOT NULL', null, false);
            $this->db->group_end();

            // 3. Atau jika user adalah approver stage 2 (misal KADEPSC) dan kasbon sudah diproses approval tahap 2
            $this->db->or_group_start();
            $this->db->where_in('approver_2', $jobdesk_aliases);
            $this->db->where('approved_penilai_by IS NOT NULL', null, false);
            $this->db->group_end();
        }

        // 4. Khusus ADMIN: melihat semua history approval yang sudah pernah diproses
        if (in_array($jobdesk_norm, ['ADMIN', 'KADEPSC']) || in_array($jobdesk_raw, ['ADMIN', 'KADEPSC'])) {
            $this->db->or_group_start();
            $this->db->where('approved_atasan_by IS NOT NULL', null, false);
            $this->db->or_where('approved_penilai_by IS NOT NULL', null, false);
            $this->db->or_where('rejected_by IS NOT NULL', null, false);
            $this->db->or_where('cair_by IS NOT NULL', null, false);
            $this->db->group_end();
        }

        $this->db->group_end();
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_all_kasbon()
    {
        $this->db->select('*');
        $this->db->from('tb_kasbon');
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_kasbon_by_user($id_user)
    {
        $this->db->select('*');
        $this->db->from('tb_kasbon');
        $this->db->where('id_user', $id_user);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_kasbon_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('tb_kasbon')->row_array();
    }

    public function generate_no_kasbon()
    {
        $prefix   = "KB";
        $date_str = date('Ymd');

        $this->db->select('no_kasbon');
        $this->db->from('tb_kasbon');
        $this->db->like('no_kasbon', $prefix . '-' . $date_str, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $parts       = explode('-', $query->row()->no_kasbon);
            $new_number  = (int)end($parts) + 1;
        } else {
            $new_number = 1;
        }

        return $prefix . '-' . $date_str . '-' . sprintf("%04d", $new_number);
    }

    public function insert_kasbon($data)
    {
        $this->db->insert('tb_kasbon', $data);
        return $this->db->insert_id();
    }

    public function update_kasbon($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_kasbon', $data);
    }

    public function delete_kasbon($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('tb_kasbon');
    }
}
