<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Profile extends CI_Model
{
    public function get_user_by_id($id)
    {
        return $this->db->get_where('tb_karyawan', array('id' => $id))->row();
    }

    public function update_password($id, $new_hash)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_karyawan', array(
            'password'   => $new_hash,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function sync_tb_user_password($username, $new_hash)
    {
        $this->db->where('username', $username);
        return $this->db->update('tb_user', array('password' => $new_hash));
    }

    public function update_info($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tb_karyawan', $data);
    }
}
