<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Checker extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ----------------------------------------------------------------
    // GET LIST BONGKARAN (belum diarsipkan)
    // ----------------------------------------------------------------
    public function get_list($jobdesk = null)
    {
        $this->db->select('b.*, bc.nik_checker, bc.nm_checker, bc.waktu_mulai, bc.waktu_selesai, bc.progres, bc.status_checker');
        $this->db->from('tb_bongkaran b');
        $this->db->join('tb_bongkaran_checker bc', 'bc.id_bongkaran = b.id', 'left');
        $this->db->where('b.is_archived', 0);
        $this->db->order_by('b.id', 'DESC');
        return $this->db->get()->result_array();
    }

    // ----------------------------------------------------------------
    // GET LIST ARSIP (sudah diarsipkan)
    // ----------------------------------------------------------------
    public function get_arsip()
    {
        $this->db->select('b.*, bc.nm_checker, bc.waktu_mulai, bc.waktu_selesai, bc.progres, bc.status_checker');
        $this->db->from('tb_bongkaran b');
        $this->db->join('tb_bongkaran_checker bc', 'bc.id_bongkaran = b.id', 'left');
        $this->db->where('b.is_archived', 1);
        $this->db->order_by('b.archived_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // ----------------------------------------------------------------
    // GET DETAIL BONGKARAN BY ID
    // ----------------------------------------------------------------
    public function get_by_id($id)
    {
        return $this->db->get_where('tb_bongkaran', ['id' => $id])->row_array();
    }

    // ----------------------------------------------------------------
    // CHECKER: cek apakah bongkaran sudah diambil checker lain
    // ----------------------------------------------------------------
    public function is_taken($id_bongkaran)
    {
        return $this->db->get_where('tb_bongkaran_checker', ['id_bongkaran' => $id_bongkaran])->num_rows() > 0;
    }

    // ----------------------------------------------------------------
    // CHECKER: ambil data checker untuk bongkaran ini
    // ----------------------------------------------------------------
    public function get_checker($id_bongkaran)
    {
        return $this->db->get_where('tb_bongkaran_checker', ['id_bongkaran' => $id_bongkaran])->row_array();
    }

    // ----------------------------------------------------------------
    // GENERATE KODE BONGKARAN
    // ----------------------------------------------------------------
    public function generate_kode()
    {
        $prefix = 'BNG' . date('dmy');
        $last   = $this->db
            ->like('kode_bongkar', $prefix, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tb_bongkaran')
            ->row();

        $urut = $last ? ((int) substr($last->kode_bongkar, -4)) + 1 : 1;
        return $prefix . str_pad($urut, 4, '0', STR_PAD_LEFT);
    }

    // ----------------------------------------------------------------
    // MANAGER WH: buat bongkaran baru
    // ----------------------------------------------------------------
    public function create($data)
    {
        return $this->db->insert('tb_bongkaran', $data);
    }

    // ----------------------------------------------------------------
    // CHECKER: start bongkaran
    // ----------------------------------------------------------------
    public function start($id_bongkaran, $nik, $nm_checker)
    {
        // Update status bongkaran
        $this->db->where('id', $id_bongkaran)
                 ->update('tb_bongkaran', ['status' => 'PROSES']);

        // Insert record checker
        return $this->db->insert('tb_bongkaran_checker', [
            'id_bongkaran'   => $id_bongkaran,
            'nik_checker'    => $nik,
            'nm_checker'     => $nm_checker,
            'waktu_mulai'    => date('Y-m-d H:i:s'),
            'progres'        => 0,
            'status_checker' => 'PROSES',
        ]);
    }

    // ----------------------------------------------------------------
    // CHECKER: update progres
    // ----------------------------------------------------------------
    public function update_progres($id_bongkaran, $progres)
    {
        return $this->db->where('id_bongkaran', $id_bongkaran)
                        ->update('tb_bongkaran_checker', ['progres' => $progres]);
    }

    // ----------------------------------------------------------------
    // CHECKER: done bongkaran
    // ----------------------------------------------------------------
    public function checker_done($id_bongkaran)
    {
        $this->db->where('id_bongkaran', $id_bongkaran)
                 ->update('tb_bongkaran_checker', [
                     'progres'        => 100,
                     'waktu_selesai'  => date('Y-m-d H:i:s'),
                     'status_checker' => 'DONE',
                 ]);

        return $this->db->where('id', $id_bongkaran)
                        ->update('tb_bongkaran', ['status' => 'DONE']);
    }

    // ----------------------------------------------------------------
    // ADMLOG: update jalur & status
    // ----------------------------------------------------------------
    public function update_admlog($id, $data)
    {
        return $this->db->where('id', $id)->update('tb_bongkaran', $data);
    }

    // ----------------------------------------------------------------
    // MANAGER WH: archive
    // ----------------------------------------------------------------
    public function archive($id, $archived_by)
    {
        return $this->db->where('id', $id)->update('tb_bongkaran', [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
            'archived_by' => $archived_by,
        ]);
    }
}