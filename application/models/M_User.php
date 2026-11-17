<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 
 */

class M_User extends CI_Model
{
    public function getAll()
    {
        return $this->db->get('tb_user')->result();
    }

    public function adddata($data)
    {
        return $this->db->insert("tb_user", $data);
    }

}
