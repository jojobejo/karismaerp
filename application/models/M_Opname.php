<?php
defined('BASEPATH') or exit('No direct script access allowed');


class M_Opname extends CI_Model
{
    public function getAll()
    {
        return $this->db->get('tb_user')->result();
    }
}
