<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Loginmodel extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function cek_username($username)
    {
        $this->db->where('username', $username);
        return $this->db->get('tb_user');
    }

    function cek_password($username)
    {
        $this->db->where('username', $username);
        return $this->db->get('tb_user')->result();
    }
}
