<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalAppDirectory extends CI_Model
{
    private $table = 'tb_portal_app_directory';

    public function available()
    {
        return $this->db->table_exists($this->table);
    }

    public function save($appCode, $data)
    {
        if (!$this->available()) return false;

        $exists = $this->db->where('app_code', $appCode)->count_all_results($this->table) > 0;
        if ($exists) {
            return $this->db->where('app_code', $appCode)->update($this->table, $data);
        }

        $data['app_code'] = $appCode;
        return $this->db->insert($this->table, $data);
    }
}
