<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Checker extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        date_default_timezone_set('Asia/Jakarta'); // WIB UTC+7
    }

    // ================================================================
    // BONGKARAN
    // ================================================================
    public function get_list()
    {
        $this->db->select('b.*, bc.nik_checker, bc.nm_checker, bc.waktu_mulai, bc.waktu_selesai, bc.progres, bc.status_checker');
        $this->db->from('tb_bongkaran b');
        $this->db->join('tb_bongkaran_checker bc', 'bc.id_bongkaran = b.id', 'left');
        $this->db->where('b.is_archived', 0);
        $this->db->order_by('b.id', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_arsip_bongkaran()
    {
        $this->db->select('b.*, bc.nm_checker, bc.waktu_mulai, bc.waktu_selesai, bc.progres');
        $this->db->from('tb_bongkaran b');
        $this->db->join('tb_bongkaran_checker bc', 'bc.id_bongkaran = b.id', 'left');
        $this->db->where('b.is_archived', 1);
        $this->db->order_by('b.archived_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where('tb_bongkaran', ['id' => $id])->row_array();
    }

    // Ambil id_bongkaran yang sedang aktif (PROSES) milik checker ini
    public function get_active_id_by_checker($nik)
    {
        $row = $this->db
            ->select('b.id')
            ->from('tb_bongkaran b')
            ->join('tb_bongkaran_checker bc', 'bc.id_bongkaran = b.id')
            ->where('bc.nik_checker', $nik)
            ->where('bc.status_checker', 'PROSES')
            ->where('b.status !=', 'DONE')
            ->limit(1)
            ->get()->row();
        return $row ? (int)$row->id : null;
    }

    public function is_taken($id)
    {
        return $this->db->get_where('tb_bongkaran_checker', ['id_bongkaran' => $id])->num_rows() > 0;
    }

    public function get_checker($id)
    {
        return $this->db->get_where('tb_bongkaran_checker', ['id_bongkaran' => $id])->row_array();
    }

    public function generate_kode()
    {
        $prefix = 'BNG' . date('dmy');
        $last   = $this->db->like('kode_bongkar', $prefix, 'after')
                            ->order_by('id', 'DESC')->limit(1)
                            ->get('tb_bongkaran')->row();
        $urut = $last ? ((int) substr($last->kode_bongkar, -4)) + 1 : 1;
        return $prefix . str_pad($urut, 4, '0', STR_PAD_LEFT);
    }

    public function create($data)           { return $this->db->insert('tb_bongkaran', $data); }

    public function start($id, $nik, $nama)
    {
        $this->db->where('id', $id)->update('tb_bongkaran', ['status' => 'PROSES']);
        return $this->db->insert('tb_bongkaran_checker', [
            'id_bongkaran'   => $id,
            'nik_checker'    => $nik,
            'nm_checker'     => $nama,
            'waktu_mulai'    => date('Y-m-d H:i:s'),
            'progres'        => 0,
            'status_checker' => 'PROSES',
        ]);
    }

    public function update_progres($id, $progres)
    {
        return $this->db->where('id_bongkaran', $id)->update('tb_bongkaran_checker', ['progres' => $progres]);
    }

    public function checker_done($id)
    {
        $this->db->where('id_bongkaran', $id)->update('tb_bongkaran_checker', [
            'progres' => 100, 'waktu_selesai' => date('Y-m-d H:i:s'), 'status_checker' => 'DONE',
        ]);
        return $this->db->where('id', $id)->update('tb_bongkaran', ['status' => 'DONE']);
    }

    public function update_status($id, $status)
    {
        return $this->db->where('id', $id)->update('tb_bongkaran', ['status' => $status]);
    }

    public function archive($id, $by)
    {
        return $this->db->where('id', $id)->update('tb_bongkaran', [
            'is_archived' => 1, 'archived_at' => date('Y-m-d H:i:s'), 'archived_by' => $by,
        ]);
    }

    // ================================================================
    // LOADING KK
    // ================================================================
    public function get_list_kk()
    {
        return $this->db->where('is_archived', 0)->order_by('id', 'ASC')->get('tb_loading_kk')->result_array();
    }

    public function get_arsip_kk()
    {
        return $this->db->where('is_archived', 1)->order_by('archived_at', 'DESC')->get('tb_loading_kk')->result_array();
    }

    public function create_kk($data)        { return $this->db->insert('tb_loading_kk', $data); }

    public function update_kk($id, $data)   { return $this->db->where('id', $id)->update('tb_loading_kk', $data); }

    public function archive_kk($id, $by)
    {
        return $this->db->where('id', $id)->update('tb_loading_kk', [
            'is_archived' => 1, 'archived_at' => date('Y-m-d H:i:s'), 'archived_by' => $by,
        ]);
    }

    // ================================================================
    // LOADING LK
    // ================================================================
    public function get_list_lk()
    {
        return $this->db->where('is_archived', 0)->order_by('id', 'ASC')->get('tb_loading_lk')->result_array();
    }

    public function get_arsip_lk()
    {
        return $this->db->where('is_archived', 1)->order_by('archived_at', 'DESC')->get('tb_loading_lk')->result_array();
    }

    public function create_lk($data)        { return $this->db->insert('tb_loading_lk', $data); }

    public function update_lk($id, $data)   { return $this->db->where('id', $id)->update('tb_loading_lk', $data); }

    public function archive_lk($id, $by)
    {
        return $this->db->where('id', $id)->update('tb_loading_lk', [
            'is_archived' => 1, 'archived_at' => date('Y-m-d H:i:s'), 'archived_by' => $by,
        ]);
    }

    // ================================================================
    // ARCHIVE SEMUA YANG SUDAH DONE (tidak terbatas hari ini)
    // ================================================================
    public function archive_all_done($by)
    {
        $now      = date('Y-m-d H:i:s');
        $data_arc = ['is_archived' => 1, 'archived_at' => $now, 'archived_by' => $by];

        // Bongkaran: semua status DONE belum diarsipkan
        $this->db->where('is_archived', 0)
                 ->where('status', 'DONE')
                 ->update('tb_bongkaran', $data_arc);
        $b = $this->db->affected_rows();

        // Loading KK: semua status DONE belum diarsipkan
        $this->db->where('is_archived', 0)
                 ->where('status', 'DONE')
                 ->update('tb_loading_kk', $data_arc);
        $k = $this->db->affected_rows();

        // Loading LK: semua status DONE belum diarsipkan
        $this->db->where('is_archived', 0)
                 ->where('status', 'DONE')
                 ->update('tb_loading_lk', $data_arc);
        $l = $this->db->affected_rows();

        return ['bongkaran' => $b, 'kk' => $k, 'lk' => $l];
    }
}