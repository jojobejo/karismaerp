<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TestQuery extends CI_Controller {
    public function index() {
        $this->load->model('M_Kasbon');
        $user = ['id' => 87, 'jobdesk' => 'MNGSC'];
        
        $this->db->select('*');
        $this->db->from('tb_kasbon');
        $this->db->group_start();
        $this->db->where('id_user', $user['id']);
        
        $this->db->or_group_start();
        $this->db->where('status', 'pending_atasan');
        $this->db->where('approver_1', $user['jobdesk']);
        $this->db->group_end();
        
        $this->db->or_group_start();
        $this->db->where('status', 'pending_penilai');
        $this->db->where('approver_2', $user['jobdesk']);
        $this->db->group_end();
        
        $this->db->group_end();
        $this->db->order_by('id', 'DESC');
        
        $query = $this->db->get_compiled_select();
        file_put_contents('query_output.txt', $query);
        echo "Done";
    }
}
