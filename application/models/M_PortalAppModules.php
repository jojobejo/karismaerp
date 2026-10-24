<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalAppModules extends CI_Model
{
    private $table = 'tb_portal_app_module';

    public function available()
    {
        return $this->db->table_exists($this->table);
    }

    public function all_for_app($appCode)
    {
        if (!$this->available()) return [];
        return $this->db->where('app_code', $appCode)->order_by('sort_order', 'ASC')->order_by('module_name', 'ASC')->get($this->table)->result_array();
    }

    public function find($id)
    {
        if (!$this->available()) return null;
        return $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
    }

    public function find_in_app($appCode, $moduleCode)
    {
        if (!$this->available()) return null;
        return $this->db->get_where($this->table, ['app_code' => $appCode, 'module_code' => $moduleCode])->row_array();
    }

    public function code_exists($appCode, $moduleCode, $exceptId = null)
    {
        if (!$this->available()) return false;
        $this->db->where(['app_code' => $appCode, 'module_code' => $moduleCode]);
        if ($exceptId) $this->db->where('id !=', (int)$exceptId);
        return $this->db->count_all_results($this->table) > 0;
    }

    public function create($data)
    {
        return $this->available() && $this->db->insert($this->table, $data) && $this->db->insert_id() > 0;
    }

    public function update($id, $data)
    {
        if (!$this->available()) return false;
        $this->db->where('id', (int)$id)->update($this->table, $data);
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        if (!$this->available()) return false;
        $this->db->where('id', (int)$id)->delete($this->table);
        return $this->db->affected_rows() === 1;
    }
}
