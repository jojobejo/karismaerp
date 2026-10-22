<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Organisasi extends CI_Model
{
    public function jabatan_options()
    {
        if (!$this->db->table_exists('tb_jabatan')) return [];
        return $this->db->select('nama_jabatan AS id, nama_jabatan AS text')->where('status', 1)->order_by('nama_jabatan', 'ASC')->get('tb_jabatan')->result_array();
    }

    public function is_active_jabatan($name)
    {
        $name = trim((string)$name);
        if ($name === '' || !$this->db->table_exists('tb_jabatan')) return false;
        return $this->db->where('nama_jabatan', $name)->where('status', 1)->count_all_results('tb_jabatan') > 0;
    }

    private $entities = [
        'departemen' => ['table' => 'tb_departemen', 'id' => 'id', 'name' => 'nama_departemen', 'description' => null, 'status' => null],
        'jobdesk' => ['table' => 'tb_jobdesk', 'id' => 'id', 'name' => 'nama_jobdesk', 'description' => 'deskripsi', 'status' => 'status'],
        'jabatan' => ['table' => 'tb_jabatan', 'id' => 'id', 'name' => 'nama_jabatan', 'description' => 'deskripsi', 'status' => 'status'],
    ];

    private function config($entity)
    {
        return $this->entities[$entity] ?? null;
    }

    public function datatable($entity, $post)
    {
        $cfg = $this->config($entity);
        if (!$cfg || !$this->db->table_exists($cfg['table'])) return ['draw' => (int)($post['draw'] ?? 1), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];
        $search = trim((string)($post['search']['value'] ?? ''));
        $total = $this->db->count_all($cfg['table']);
        $this->db->from($cfg['table']);
        if ($search !== '') {
            $this->db->group_start()->like($cfg['name'], $search);
            if ($cfg['description']) $this->db->or_like($cfg['description'], $search);
            $this->db->group_end();
        }
        $filtered = $this->db->count_all_results('', false);
        $this->db->order_by($cfg['name'], 'ASC');
        $length = (int)($post['length'] ?? 10);
        if ($length > 0) $this->db->limit($length, (int)($post['start'] ?? 0));
        $rows = $this->db->get()->result_array();
        return ['draw' => (int)($post['draw'] ?? 1), 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $rows];
    }

    public function find($entity, $id)
    {
        $cfg = $this->config($entity);
        if (!$cfg || !$this->db->table_exists($cfg['table'])) return null;
        return $this->db->where($cfg['id'], (int)$id)->get($cfg['table'])->row_array();
    }

    public function nameExists($entity, $name, $excludeId = null)
    {
        $cfg = $this->config($entity);
        if (!$cfg || !$this->db->table_exists($cfg['table'])) return false;
        $this->db->where($cfg['name'], trim((string)$name));
        if ($excludeId) $this->db->where($cfg['id'] . ' !=', (int)$excludeId);
        return $this->db->count_all_results($cfg['table']) > 0;
    }

    public function save($entity, $input, $id = null)
    {
        $cfg = $this->config($entity);
        if (!$cfg || !$this->db->table_exists($cfg['table'])) return false;
        $name = trim((string)($input[$cfg['name']] ?? ''));
        if ($name === '') return false;
        $data = [$cfg['name'] => $name];
        if ($cfg['description']) $data[$cfg['description']] = trim((string)($input['deskripsi'] ?? ''));
        if ($cfg['status']) $data[$cfg['status']] = (int)($input['status'] ?? 1) === 1 ? 1 : 0;
        if ($id) return $this->db->where($cfg['id'], (int)$id)->update($cfg['table'], $data);
        return $this->db->insert($cfg['table'], $data) ? $this->db->insert_id() : false;
    }

    public function delete($entity, $id)
    {
        $cfg = $this->config($entity);
        if (!$cfg || !$this->db->table_exists($cfg['table'])) return false;
        return $this->db->where($cfg['id'], (int)$id)->delete($cfg['table']);
    }
}
