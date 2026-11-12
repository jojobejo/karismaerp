<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_ApprovalNotification
 * Mengumpulkan seluruh notifikasi approval / tindakan yang menunggu approval user saat ini.
 */
class M_ApprovalNotification extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mengambil seluruh notifikasi pending approval untuk user yang sedang login.
     * Mengembalikan array berisi items notifikasi dan total count.
     */
    public function get_pending_approvals_for_user($user)
    {
        $items = [];

        // 1. Kas Bon (Pending Approval Atasan & Penilai)
        $kasbonItems = $this->_get_kasbon_pending($user);
        foreach ($kasbonItems as $k) {
            $items[] = $k;
        }

        // 2. Pengajuan Overdue / OD (Pending Approval Manager & Kadep)
        $odItems = $this->_get_pengajuan_od_pending($user);
        foreach ($odItems as $o) {
            $items[] = $o;
        }

        // 3. Revisi Harga LPB (Khusus Admin / ADMPNJ)
        $lpbItems = $this->_get_lpb_revision_pending($user);
        foreach ($lpbItems as $l) {
            $items[] = $l;
        }

        // 4. Retur Penjualan / SPR (Menunggu verifikasi / approval Kadep)
        $returItems = $this->_get_retur_penjualan_pending($user);
        foreach ($returItems as $r) {
            $items[] = $r;
        }

        // Urutkan notifikasi berdasarkan created_at / tanggal terbaru
        usort($items, function ($a, $b) {
            return strcmp($b['datetime'] ?? '', $a['datetime'] ?? '');
        });

        return [
            'total' => count($items),
            'items' => $items,
        ];
    }

    /**
     * 1. Pengajuan Kas Bon yang menunggu approval user ini
     */
    private function _get_kasbon_pending($user)
    {
        $items = [];
        if (!$this->db->table_exists('tb_kasbon')) {
            return $items;
        }

        $this->load->model('M_Kasbon');
        $nama_user       = strtoupper(trim((string)($user['nama'] ?? '')));
        $username_user   = strtoupper(trim((string)($user['username'] ?? '')));
        $jobdesk_raw     = strtoupper(trim((string)($user['jobdesk'] ?? '')));
        $jobdesk_norm    = $this->M_Kasbon->normalize_jobdesk($jobdesk_raw);
        $jobdesk_aliases = array_map('strtoupper', $this->M_Kasbon->get_jobdesk_aliases($jobdesk_raw));

        // Ambil kasbon yang berstatus pending_atasan atau pending_penilai
        $this->db->select('id, no_kasbon, nama_pemohon, nominal, keterangan, tanggal_pengajuan, created_at, status, approver_1, approver_2');
        $this->db->from('tb_kasbon');
        $this->db->where_in('status', ['pending_atasan', 'pending_penilai']);
        $this->db->order_by('id', 'DESC');
        $rows = $this->db->get()->result_array();

        foreach ($rows as $row) {
            // Gunakan fungsi can_approve yang sudah mendukung atasan, penilai dari tb_users, dan alias jobdesk
            if ($this->M_Kasbon->can_approve($row, $user)) {
                $stageLabel = ($row['status'] === 'pending_atasan') ? 'Approval Atasan' : 'Approval Penilai';
                $items[] = [
                    'category'    => 'Kas Bon',
                    'badge_class' => 'badge-success',
                    'icon'        => 'fas fa-money-bill-wave text-success',
                    'title'       => 'Pengajuan Kas Bon ' . $row['no_kasbon'],
                    'subtitle'    => $row['nama_pemohon'] . ' · Rp ' . number_format($row['nominal'], 0, ',', '.'),
                    'detail'      => $stageLabel . ' · ' . (mb_strimwidth($row['keterangan'] ?? '', 0, 60, '...')),
                    'url'         => base_url('C_Kasbon/detail/' . $row['id']),
                    'datetime'    => $row['created_at'] ?: ($row['tanggal_pengajuan'] . ' 00:00:00'),
                    'time_label'  => date('d M Y', strtotime($row['tanggal_pengajuan'])),
                ];
            }
        }

        return $items;
    }

    /**
     * 2. Pengajuan OD (Overdue) yang menunggu approval user ini
     */
    private function _get_pengajuan_od_pending($user)
    {
        $items = [];
        if (!$this->db->table_exists('tb_pengajuan_od')) {
            return $items;
        }

        $jobdesk = strtoupper(trim((string)($user['jobdesk'] ?? '')));
        $targetStatuses = [];

        if (in_array($jobdesk, ['MANAGERSC', 'MNGSC', 'MANAGER SALES COUNTER'])) {
            $targetStatuses = ['pending_mngsc'];
        } elseif (in_array($jobdesk, ['MANAGERTC', 'MNGTC', 'MT MANAGER KEUANGAN', 'MANAGER KEUANGAN'])) {
            $targetStatuses = ['pending_mngtc'];
        } elseif (in_array($jobdesk, ['KADEPSC', 'MT KADEP KEUANGAN & SALES', 'KADEP KEUANGAN & SALES', 'ADMIN'])) {
            $targetStatuses = ['pending_kadepsc'];
        }

        if (empty($targetStatuses)) {
            return $items;
        }

        $this->db->select('id, id_customer, customer_name, catatan, komitmen_penyelesaian, create_by, tanggal_pengajuan, create_at, status');
        $this->db->from('tb_pengajuan_od');
        $this->db->where_in('status', $targetStatuses);
        $this->db->order_by('id', 'DESC');
        $rows = $this->db->get()->result_array();

        foreach ($rows as $row) {
            $catatanText = !empty($row['catatan']) ? $row['catatan'] : ($row['komitmen_penyelesaian'] ?? '');
            $items[] = [
                'category'    => 'Pengajuan OD',
                'badge_class' => 'badge-warning text-dark',
                'icon'        => 'fas fa-calendar-times text-warning',
                'title'       => 'Pengajuan OD #' . $row['id'],
                'subtitle'    => ($row['create_by'] ?: 'Sales') . ' · ' . ($row['customer_name'] ?: $row['id_customer']),
                'detail'      => 'Menunggu approval Anda' . ($catatanText ? ' · ' . mb_strimwidth($catatanText, 0, 50, '...') : ''),
                'url'         => base_url('sales/C_PengajuanOD'),
                'datetime'    => $row['create_at'] ?: ($row['tanggal_pengajuan'] . ' 00:00:00'),
                'time_label'  => date('d M Y', strtotime($row['tanggal_pengajuan'])),
            ];
        }

        return $items;
    }

    /**
     * 3. Permintaan Revisi Harga LPB (khusus ADMPNJ / Admin)
     */
    private function _get_lpb_revision_pending($user)
    {
        $items = [];
        if (!$this->db->table_exists('tblpb_revision_request')) {
            return $items;
        }

        $jobdesk = strtoupper(trim((string)($user['jobdesk'] ?? '')));
        $lv = (int)($user['lv'] ?? 1);
        $isAdminOrAdmpnj = ($lv >= 99) || in_array($jobdesk, [
            'ADMIN',
            'ADMPNJ',
            'ADMIN PRICELIST & RETUR PENJUALAN',
            'MANAGERSC',
            'KADEPSC',
            'MT KADEP KEUANGAN & SALES'
        ], true);

        if (!$isAdminOrAdmpnj) {
            return $items;
        }

        $this->db->select('no_request, nomor_lpb, nama_supplier, alasan_revisi, requested_by, requested_at, status');
        $this->db->from('tblpb_revision_request');
        $this->db->where('status', 'PENDING_REPOST');
        $this->db->order_by('id_request', 'DESC');
        $this->db->limit(20);
        $rows = $this->db->get()->result_array();

        foreach ($rows as $row) {
            $items[] = [
                'category'    => 'Revisi LPB',
                'badge_class' => 'badge-danger',
                'icon'        => 'fas fa-exclamation-triangle text-danger',
                'title'       => 'Revisi Harga LPB ' . ($row['no_request'] ?: $row['nomor_lpb']),
                'subtitle'    => ($row['nama_supplier'] ?: 'Supplier') . ' · dari ' . ($row['requested_by'] ?: 'Logistik'),
                'detail'      => 'Perlu Repost Faktur Penjualan',
                'url'         => base_url('admin/transaksi?ref=lpb_revision&no_request=' . urlencode($row['no_request'])),
                'datetime'    => $row['requested_at'] ?: date('Y-m-d H:i:s'),
                'time_label'  => !empty($row['requested_at']) ? date('d M H:i', strtotime($row['requested_at'])) : '-',
            ];
        }

        return $items;
    }

    /**
     * 4. Retur Penjualan / SPR (khusus role Kadep UB / Koordinator / Admin)
     */
    private function _get_retur_penjualan_pending($user)
    {
        $items = [];
        if (!$this->db->table_exists('tbrp_spr_header')) {
            return $items;
        }

        $jobdesk = strtoupper(trim((string)($user['jobdesk'] ?? '')));
        $lv = (int)($user['lv'] ?? 1);

        $statuses = [];
        if ($lv >= 99 || in_array($jobdesk, ['ADMIN', 'KADEPUB', 'KEPALA UNIT BISNIS', 'KADEP_UB'])) {
            $statuses = ['menunggu_kadepub', 'diajukan'];
        } elseif (in_array($jobdesk, ['KOORDINATOR', 'KOOR'])) {
            $statuses = ['diajukan'];
        }

        if (empty($statuses)) {
            return $items;
        }

        $this->db->select('id_spr, no_spr, tanggal, nama_customer, create_by, status, create_at');
        $this->db->from('tbrp_spr_header');
        $this->db->where_in('status', $statuses);
        $this->db->order_by('id_spr', 'DESC');
        $this->db->limit(15);
        $rows = $this->db->get()->result_array();

        foreach ($rows as $row) {
            $items[] = [
                'category'    => 'Retur Penjualan',
                'badge_class' => 'badge-info',
                'icon'        => 'fas fa-undo-alt text-info',
                'title'       => 'Pengajuan SPR ' . ($row['no_spr'] ?: ('#' . $row['id_spr'])),
                'subtitle'    => ($row['create_by'] ?: 'Sales') . ' · ' . ($row['nama_customer'] ?: '-'),
                'detail'      => 'Status: ' . str_replace('_', ' ', strtoupper($row['status'])),
                'url'         => base_url('sales/C_ReturPenjualan'),
                'datetime'    => $row['create_at'] ?: ($row['tanggal'] . ' 00:00:00'),
                'time_label'  => !empty($row['tanggal']) ? date('d M Y', strtotime($row['tanggal'])) : '-',
            ];
        }

        return $items;
    }
}

