<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Kasbon extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
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
        $prefix = "KB";
        $date_str = date('Ymd'); // e.g., 20260807
        
        $this->db->select('no_kasbon');
        $this->db->from('tb_kasbon');
        $this->db->like('no_kasbon', $prefix . '-' . $date_str, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row();
            // Expected format: KB-20260807-0001
            $parts = explode('-', $row->no_kasbon);
            $last_number = (int)end($parts);
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }

        $formatted_number = sprintf("%04d", $new_number);
        return $prefix . '-' . $date_str . '-' . $formatted_number;
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
