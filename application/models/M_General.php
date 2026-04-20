<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_General extends CI_Model {

    private $table = 'cctv_tracking';

    public function __construct() {
        parent::__construct();
    }

    public function get_summary() {
        $summary = [];
        $this->db->select("
            SUM(status = 'Online')          AS total_online,
            SUM(status = 'Offline')         AS total_offline,
            SUM(status_rekaman = 'Terekam') AS total_terekam,
            SUM(status_rekaman = 'Tidak')   AS total_tidak_terekam,
            COUNT(*)                        AS total
        ");
        $this->db->from($this->table);
        $query = $this->db->get();
        return $query->row();
    }

    public function get_all($filter = []) {
        $this->db->select('*');
        $this->db->from($this->table);

        if (!empty($filter['tgl_awal'])) {
            $this->db->where('tgl >=', $filter['tgl_awal']);
        }
        if (!empty($filter['tgl_akhir'])) {
            $this->db->where('tgl <=', $filter['tgl_akhir']);
        }
        if (!empty($filter['lokasi'])) {
            $this->db->where('lokasi', $filter['lokasi']);
        }
        if (!empty($filter['status'])) {
            $this->db->where('status', $filter['status']);
        }
        if (!empty($filter['status_rekaman'])) {
            $this->db->where('status_rekaman', $filter['status_rekaman']);
        }

        $this->db->order_by('tgl DESC, id DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id) {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    public function update_status($id, $status, $status_rekaman) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'status'         => $status,
            'status_rekaman' => $status_rekaman,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_lokasi_list() {
        $this->db->distinct();
        $this->db->select('lokasi');
        $this->db->order_by('lokasi ASC');
        return $this->db->get($this->table)->result();
    }
}