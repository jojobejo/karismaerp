<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PengajuanOD extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all_pengajuan($filters = [])
    {
        $this->db->select('po.*, 
            (SELECT COUNT(*) FROM tb_pengajuan_od_faktur WHERE id_pengajuan = po.id) as jumlah_faktur,
            (SELECT MAX(tempo_baru) FROM tb_pengajuan_od_faktur WHERE id_pengajuan = po.id) as max_tempo_baru,
            (SELECT GROUP_CONCAT(no_faktur SEPARATOR ", ") FROM tb_pengajuan_od_faktur WHERE id_pengajuan = po.id) as faktur_list_str
        ');
        $this->db->from('tb_pengajuan_od po');

        $jobdesk = isset($filters['jobdesk']) ? strtoupper((string)$filters['jobdesk']) : '';

        if (isset($filters['only_history']) && $filters['only_history']) {
            // HISTORY PAGE: Show requests already processed/approved by this role or completed
            if (in_array($jobdesk, ['MANAGERSC', 'MNGSC'])) {
                $this->db->where_in('po.status', ['pending_mngtc', 'pending_kadepsc', 'approved', 'rejected']);
            } elseif (in_array($jobdesk, ['MANAGERTC', 'MNGTC'])) {
                $this->db->where_in('po.status', ['pending_kadepsc', 'approved', 'rejected']);
            } elseif ($jobdesk === 'KADEPSC') {
                $this->db->where_in('po.status', ['approved', 'rejected']);
                $this->db->having('max_tempo_baru >', 90);
            } else {
                $this->db->where_in('po.status', ['approved', 'rejected']);
            }
        } elseif (isset($filters['exclude_approved']) && $filters['exclude_approved']) {
            // ACTIVE / PENDING INBOX PAGE: Show requests waiting for action from this role
            if (in_array($jobdesk, ['MANAGERSC', 'MNGSC'])) {
                $this->db->where('po.status', 'pending_mngsc');
            } elseif (in_array($jobdesk, ['MANAGERTC', 'MNGTC'])) {
                $this->db->where('po.status', 'pending_mngtc');
            } elseif ($jobdesk === 'KADEPSC') {
                $this->db->where('po.status', 'pending_kadepsc');
                $this->db->having('max_tempo_baru >', 90);
            } else {
                $this->db->where_in('po.status', ['pending_mngsc', 'pending_mngtc', 'pending_kadepsc']);
            }
        } elseif (isset($filters['status']) && $filters['status'] != 'all') {
            $this->db->where('po.status', $filters['status']);
        }

        if (isset($filters['date1']) && $filters['date1'] != '') {
            $this->db->where('po.tanggal_pengajuan >=', $filters['date1']);
        }
        if (isset($filters['date2']) && $filters['date2'] != '') {
            $this->db->where('po.tanggal_pengajuan <=', $filters['date2']);
        }

        $this->db->order_by('po.id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_pengajuan_by_id($id)
    {
        $this->db->select('po.*, 
            (SELECT MAX(tempo_baru) FROM tb_pengajuan_od_faktur WHERE id_pengajuan = po.id) as max_tempo_baru
        ');
        $this->db->from('tb_pengajuan_od po');
        $this->db->where('po.id', $id);
        $data = $this->db->get()->row_array();
        
        if ($data) {
            $this->db->select('pod.*, fp.tanggal_faktur');
            $this->db->from('tb_pengajuan_od_faktur pod');
            $this->db->join('tbso_faktur_penjualan fp', 'fp.id_faktur = pod.id_faktur', 'left');
            $this->db->where('pod.id_pengajuan', $id);
            $data['fakturs'] = $this->db->get()->result_array();
        }
        
        return $data;
    }

    public function insert_pengajuan($data)
    {
        $this->db->insert('tb_pengajuan_od', $data);
        return $this->db->insert_id();
    }

    public function insert_pengajuan_faktur($data)
    {
        return $this->db->insert_batch('tb_pengajuan_od_faktur', $data);
    }

    public function delete_pengajuan_faktur($id_pengajuan)
    {
        $this->db->where('id_pengajuan', $id_pengajuan);
        return $this->db->delete('tb_pengajuan_od_faktur');
    }

    public function update_pengajuan($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_pengajuan_od', $data);
    }
    
    public function get_faktur_list()
    {
        // Only show faktur that doesn't have pending OD request
        $this->db->select('id_faktur, no_faktur, tempo, jtempo, customer_name, kd_customer, tanggal_faktur');
        $this->db->from('tbso_faktur_penjualan');
        // $this->db->where('status !=', 'batal'); // Assuming you want active faktur
        $this->db->order_by('tanggal_faktur', 'DESC');
        $this->db->limit(1000); // Limit to recent 1000 to avoid memory issues, or better use select2 ajax
        return $this->db->get()->result_array();
    }

    public function get_activity_log($limit = 500, $offset = 0)
    {
        $sql = "
            SELECT id, 'Buat Pengajuan' as aksi, create_by as actor, create_at as waktu, catatan as note 
            FROM tb_pengajuan_od 
            
            UNION 
            
            SELECT id, 
                   CASE WHEN status = 'rejected' THEN 'Ditolak MNGSC' ELSE 'Persetujuan MNGSC' END as aksi, 
                   approval_mngsc_by as actor, 
                   approval_mngsc_at as waktu, 
                   catatan_mngsc as note 
            FROM tb_pengajuan_od 
            WHERE approval_mngsc_at IS NOT NULL
            
            UNION 
            
            SELECT id, 
                   CASE WHEN status = 'rejected' THEN 'Ditolak MNGTC' ELSE 'Persetujuan MNGTC' END as aksi, 
                   approval_mngtc_by as actor, 
                   approval_mngtc_at as waktu, 
                   catatan_mngtc as note 
            FROM tb_pengajuan_od 
            WHERE approval_mngtc_at IS NOT NULL
            
            UNION 
            
            SELECT id, 
                   CASE WHEN status = 'rejected' THEN 'Ditolak KADEPSC' ELSE 'Persetujuan KADEPSC' END as aksi, 
                   approval_kadepsc_by as actor, 
                   approval_kadepsc_at as waktu, 
                   catatan_kadepsc as note 
            FROM tb_pengajuan_od 
            WHERE approval_kadepsc_at IS NOT NULL
            
            ORDER BY waktu DESC
            LIMIT ? OFFSET ?
        ";
        
        return $this->db->query($sql, [(int)$limit, (int)$offset])->result_array();
    }
}
