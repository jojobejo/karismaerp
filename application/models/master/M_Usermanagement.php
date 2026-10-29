<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Usermanagement extends CI_Model
{
    private $table = 'tb_users';

    private function fields()
    {
        static $fields;
        if ($fields === null) {
            $fields = $this->db->table_exists($this->table) ? $this->db->list_fields($this->table) : [];
        }
        return $fields;
    }

    private function has($field)
    {
        return in_array($field, $this->fields(), true);
    }

    private function departmentField()
    {
        return $this->has('departemen') ? 'departemen' : ($this->has('departement') ? 'departement' : null);
    }

    private function select_base()
    {
        $fields = ['id', 'username', 'nama_lngkp', 'nik', 'bagian', 'jabatan', 'atasan', 'penilai', 'level', 'status', 'default_redirect', 'last_login'];
        $select = [];
        foreach ($fields as $field) {
            if ($this->has($field)) $select[] = 'u.' . $field;
        }
        $departmentField = $this->departmentField();
        if ($departmentField) $select[] = 'u.' . $departmentField . ' AS departemen';
        $this->db->select(implode(',', $select), false)->from($this->table . ' u');
    }

    private function apply_filters($post)
    {
        $search = trim((string)($post['search']['value'] ?? $post['search'] ?? ''));
        if ($search !== '') {
            $this->db->group_start();
            $searchFields = ['username', 'nama_lngkp', 'nik', 'bagian', 'jabatan'];
            if ($this->departmentField()) $searchFields[] = $this->departmentField();
            foreach ($searchFields as $index => $field) {
                if (!$this->has($field)) continue;
                if ($index === 0) $this->db->like('u.' . $field, $search);
                else $this->db->or_like('u.' . $field, $search);
            }
            $this->db->group_end();
        }
        foreach (['level', 'status'] as $field) {
            $value = trim((string)($post[$field] ?? ''));
            if ($value !== '' && $this->has($field)) $this->db->where('u.' . $field, $value);
        }
        $departmentField = $this->departmentField();
        $department = trim((string)($post['departemen'] ?? ''));
        if ($departmentField && $department !== '') {
            $this->db->where('u.' . $departmentField, $department);
        }
    }

    public function datatable($post)
    {
        if (!$this->db->table_exists($this->table)) {
            return ['draw' => (int)($post['draw'] ?? 1), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];
        }
        $length = (int)($post['length'] ?? 10);
        $start = (int)($post['start'] ?? 0);
        $total = $this->db->count_all($this->table);

        $this->db->from($this->table . ' u');
        $this->apply_filters($post);
        $filtered = $this->db->count_all_results();

        $this->select_base();
        $this->apply_filters($post);
        $this->db->order_by('u.id', 'DESC');
        if ($length > 0) $this->db->limit($length, $start);

        return [
            'draw' => (int)($post['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $this->db->get()->result_array(),
        ];
    }

    public function find($id)
    {
        if (!$this->db->table_exists($this->table)) return null;
        $this->select_base();
        return $this->db->where('u.id', (int)$id)->get()->row_array();
    }

    public function unique($field, $value, $excludeId = null)
    {
        if (!in_array($field, ['username', 'nik'], true) || !$this->has($field)) return false;
        $this->db->where($field, trim((string)$value));
        if ($excludeId) $this->db->where('id !=', (int)$excludeId);
        return $this->db->count_all_results($this->table) === 0;
    }

    private function payload($input, $isUpdate = false)
    {
        $data = [
            'username' => trim((string)($input['username'] ?? '')),
            'nama_lngkp' => trim((string)($input['nama_lngkp'] ?? '')),
            'nik' => trim((string)($input['nik'] ?? '')),
            'bagian' => trim((string)($input['bagian'] ?? '')),
            'departemen' => trim((string)($input['departemen'] ?? '')),
            'jabatan' => trim((string)($input['jabatan'] ?? '')),
            'atasan' => trim((string)($input['atasan'] ?? '')),
            'penilai' => trim((string)($input['penilai'] ?? '')),
            'level' => (int)($input['level'] ?? 1),
            'status' => (int)($input['status'] ?? 1) === 1 ? 1 : 0,
            'default_redirect' => trim((string)($input['default_redirect'] ?? '')) ?: null,
        ];
        if (!$isUpdate || trim((string)($input['password'] ?? '')) !== '') {
            $data['password'] = password_hash((string)($input['password'] ?? ''), PASSWORD_DEFAULT);
        }
        if (!$isUpdate && $this->has('created_at')) $data['created_at'] = date('Y-m-d H:i:s');
        if ($this->has('updated_at')) $data['updated_at'] = date('Y-m-d H:i:s');
        $departmentField = $this->departmentField();
        if ($departmentField && array_key_exists('departemen', $data)) {
            $data[$departmentField] = $data['departemen'];
            unset($data['departemen']);
        }
        return array_intersect_key($data, array_flip($this->fields()));
    }

    public function save($input)
    {
        if (!$this->db->insert($this->table, $this->payload($input))) return false;
        return $this->db->insert_id();
    }

    public function update($id, $input)
    {
        $id = (int)$id;
        if ($id < 1) return false;
        $data = $this->payload($input, true);
        return $data ? $this->db->where('id', $id)->update($this->table, $data) : false;
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete($this->table);
        return $this->db->affected_rows() > 0;
    }

    public function reset_password($id, $password)
    {
        return $this->db->where('id', (int)$id)->update($this->table, ['password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function toggle_status($id)
    {
        if (!$this->has('status')) return false;
        $row = $this->db->select('status')->where('id', (int)$id)->get($this->table)->row_array();
        if (!$row) return false;
        return $this->db->where('id', (int)$id)->update($this->table, ['status' => ((int)$row['status'] === 1 ? 0 : 1)]);
    }

    public function distinct_options($field)
    {
        if ($field === 'departemen') $field = $this->departmentField();
        if (!in_array($field, ['level'], true) && !($field && in_array($field, ['departemen', 'departement'], true))) return [];
        if (!$field || !$this->has($field)) return [];
        return $this->db->select($field . ' AS id, ' . $field . ' AS text')->distinct()
            ->where($field . ' IS NOT NULL', null, false)->where($field . ' !=', '')
            ->order_by($field, 'ASC')->get($this->table)->result_array();
    }
}
