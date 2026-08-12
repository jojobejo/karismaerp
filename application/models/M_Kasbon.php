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
        'MNGSC'   => 'Manager SC',
        'KADEPSC' => 'Kepala Departemen SC',
        'KASIR'   => 'Kasir',
        'SC'      => 'Staff SC',
        'ADMSC'   => 'Admin SC',
        'ADMIN'   => 'Admin',
        'HRD'     => 'HRD',
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
     * Mengembalikan array rantai approver (jobdesk) untuk jobdesk pemohon tertentu.
     * Contoh: 'SC' => ['MNGSC', 'KADEPSC']
     */
    public function get_approval_chain($jobdesk_pemohon)
    {
        $key = strtoupper(trim((string)$jobdesk_pemohon));
        return isset($this->approval_chains[$key]) ? $this->approval_chains[$key] : [];
    }

    /**
     * Mengembalikan label tampilan untuk kode jobdesk.
     */
    public function get_approver_label($jobdesk_code)
    {
        $code = strtoupper(trim((string)$jobdesk_code));
        return isset($this->approver_labels[$code]) ? $this->approver_labels[$code] : $code;
    }

    /**
     * Mengambil daftar kasbon yang relevan dengan user yang sedang login.
     * Filter berbasis jobdesk dari session tb_karyawan.
     */
    public function get_kasbon_for_user($user)
    {
        $id_user = $user['id'];
        $jobdesk = strtoupper(trim((string)$user['jobdesk']));

        // KADEPSC dan ADMIN melihat semua pengajuan kasbon
        if (in_array($jobdesk, ['KADEPSC', 'ADMIN'])) {
            return $this->get_all_kasbon();
        }

        // KASIR melihat kasbon miliknya + semua yang berstatus approved (siap cair)
        if ($jobdesk === 'KASIR') {
            $this->db->select('*');
            $this->db->from('tb_kasbon');
            $this->db->group_start();
            $this->db->where('id_user', $id_user);
            $this->db->or_where_in('status', ['approved', 'cair']);
            $this->db->group_end();
            $this->db->order_by('id', 'DESC');
            return $this->db->get()->result_array();
        }

        // User biasa: pengajuan milik sendiri + kasbon yang menunggu approval dari jobdesk ini
        $this->db->select('*');
        $this->db->from('tb_kasbon');
        $this->db->group_start();

        // Milik sendiri
        $this->db->where('id_user', $id_user);

        // Menunggu approval stage 1 dari jobdesk ini
        $this->db->or_group_start();
        $this->db->where('status', 'pending_atasan');
        $this->db->where('approver_1', $jobdesk);
        $this->db->group_end();

        // Menunggu approval stage 2 dari jobdesk ini
        $this->db->or_group_start();
        $this->db->where('status', 'pending_penilai');
        $this->db->where('approver_2', $jobdesk);
        $this->db->group_end();

        $this->db->group_end();
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Memeriksa apakah user login berhak memberikan approval pada baris kasbon ini.
     * Berbasis perbandingan JOBDESK (dari session tb_karyawan).
     */
    public function can_approve($row, $session_user)
    {
        $jobdesk = strtoupper(trim((string)$session_user['jobdesk']));

        // Stage 1: menunggu approver_1
        if ($row['status'] === 'pending_atasan') {
            return ($jobdesk === strtoupper(trim((string)($row['approver_1'] ?? ''))));
        }

        // Stage 2: menunggu approver_2
        if ($row['status'] === 'pending_penilai') {
            return ($jobdesk === strtoupper(trim((string)($row['approver_2'] ?? ''))));
        }

        return false;
    }

    /**
     * Memeriksa apakah user login berhak mencairkan kasbon.
     * Hanya KASIR dan status 'approved'.
     */
    public function can_cair($row, $session_user)
    {
        return ($row['status'] === 'approved' && strtoupper((string)$session_user['jobdesk']) === 'KASIR');
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
