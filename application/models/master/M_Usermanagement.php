<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Usermanagement extends CI_Model
{
    private $table = 'tb_users';

    private function fields($table = null)
    {
        static $cache = [];
        $table = $table ?: $this->table;
        if (!isset($cache[$table])) {
            $cache[$table] = $this->db->table_exists($table) ? $this->db->list_fields($table) : [];
        }
        return $cache[$table];
    }

    private function has($field, $table = null)
    {
        return in_array($field, $this->fields($table), true);
    }

    private function select_base()
    {
        $select = [
            'k.id',
            'k.nik',
            'k.nama_lngkp AS nm_karyawan',
            'k.nama_lngkp',
            'k.departement AS departemen',
            'k.departement',
            'k.bagian AS jobdesk',
            'k.bagian',
            'k.jabatan',
            'k.username',
            'k.level AS akses_lv',
            'k.level',
        ];
        foreach (['tim', 'wilayah', 'wilayah_id', 'faktur_prefix', 'jobdesk_id', 'akses_lv_id', 'status', 'status_karyawan', 'foto', 'last_login', 'email', 'no_telp'] as $field) {
            if ($this->has($field)) $select[] = 'k.' . $field;
        }
        $this->db->select(implode(',', $select));
        $this->db->from($this->table . ' k');
    }

    private function normalize($row)
    {
        if (!$row) return $row;
        $row['nm_karyawan'] = $row['nm_karyawan'] ?? $row['nama_lngkp'] ?? '';
        $row['departemen'] = $row['departemen'] ?? $row['departement'] ?? '';
        $row['jobdesk'] = $row['jobdesk'] ?? $row['bagian'] ?? '';
        $row['akses_lv'] = $row['akses_lv'] ?? $row['level'] ?? 1;
        $row['jobdesk_id'] = !empty($row['jobdesk_id']) ? $row['jobdesk_id'] : $row['jobdesk'];
        $row['akses_lv_id'] = !empty($row['akses_lv_id']) ? $row['akses_lv_id'] : (string)$row['akses_lv'];
        return $row;
    }

    private function apply_filters($post)
    {
        $search = trim((string)($post['search']['value'] ?? $post['search'] ?? ''));
        if ($search !== '') {
            $this->db->group_start()
                ->like('k.nik', $search)
                ->or_like('k.nama_lngkp', $search)
                ->or_like('k.username', $search)
                ->or_like('k.bagian', $search)
                ->or_like('k.departement', $search)
                ->or_like('k.jabatan', $search)
                ->group_end();
        }
        $departemen = trim((string)($post['departemen'] ?? $post['departement'] ?? ''));
        if ($departemen !== '') {
            $this->db->where('k.departement', $departemen);
        }
        $jobdesk = trim((string)($post['jobdesk'] ?? $post['bagian'] ?? ''));
        if ($jobdesk !== '') {
            $this->db->where('k.bagian', $jobdesk);
        }
        $aksesLv = trim((string)($post['akses_lv'] ?? $post['level'] ?? ''));
        if ($aksesLv !== '') {
            $this->db->where('k.level', (int)$aksesLv);
        }
        if ($this->has('jobdesk_id') && trim((string)($post['jobdesk_id'] ?? '')) !== '' && is_numeric($post['jobdesk_id'])) {
            $this->db->where('k.jobdesk_id', $post['jobdesk_id']);
        }
        if ($this->has('akses_lv_id') && trim((string)($post['akses_lv_id'] ?? '')) !== '' && is_numeric($post['akses_lv_id'])) {
            $this->db->where('k.level', (int)$post['akses_lv_id']);
        }
        if ($this->has('status') && trim((string)($post['status'] ?? '')) !== '') {
            $this->db->where('k.status', (int)$post['status']);
        }
    }

    public function datatable($post)
    {
        $length = (int)($post['length'] ?? 10);
        $start = (int)($post['start'] ?? 0);

        $total = $this->db->count_all($this->table);
        $this->select_base();
        $this->apply_filters($post);
        $filtered = $this->db->count_all_results('', false);
        $this->db->order_by('k.level', 'DESC');
        $this->db->order_by('k.id', 'DESC');
        if ($length > 0) $this->db->limit($length, $start);
        $rows = array_map([$this, 'normalize'], $this->db->get()->result_array());

        return [
            'draw' => (int)($post['draw'] ?? 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
        ];
    }

    public function find($id)
    {
        $this->select_base();
        $row = $this->db->where('k.id', $id)->get()->row_array();
        return $this->normalize($row);
    }

    public function unique($field, $value, $excludeId = null)
    {
        if (!$this->has($field)) return true;
        $value = trim((string)$value);
        if ($value === '') return true;
        $this->db->where($field, $value);
        if ($excludeId) $this->db->where('id !=', $excludeId);
        return $this->db->count_all_results($this->table) === 0;
    }

    public function resolve_jobdesk_name($id, $fallback)
    {
        $fallback = trim((string)$fallback);
        if ($fallback !== '' && strtoupper($fallback) !== 'PILIH') {
            return strtoupper($fallback);
        }
        if ($id !== '' && !is_numeric($id)) {
            return strtoupper(trim((string)$id));
        }
        if ($id && $this->db->table_exists('tb_jobdesk')) {
            $fields = $this->fields('tb_jobdesk');
            $idField = in_array('id_jobdesk', $fields, true) ? 'id_jobdesk' : (in_array('id', $fields, true) ? 'id' : null);
            $nameField = in_array('nama_jobdesk', $fields, true) ? 'nama_jobdesk' : (in_array('jobdesk', $fields, true) ? 'jobdesk' : null);
            if ($idField && $nameField) {
                $row = $this->db->select($nameField)->where($idField, $id)->get('tb_jobdesk')->row_array();
                if ($row) return strtoupper(trim((string)$row[$nameField]));
            }
        }
        return strtoupper($fallback);
    }

    public function resolve_akses_level($id, $fallback)
    {
        if (is_numeric($id) && (int)$id > 0) {
            return (int)$id;
        }
        if (is_numeric($fallback) && (int)$fallback > 0) {
            return (int)$fallback;
        }
        if (preg_match('/^(\d+)/', trim((string)$fallback), $m)) {
            return (int)$m[1];
        }
        return 1;
    }

    public function resolve_jabatan_by_level($level, $fallback = '')
    {
        $fallback = trim((string)$fallback);
        if ($fallback !== '') return strtoupper($fallback);
        $map = [
            1 => 'KARYAWAN',
            2 => 'KOORDINATOR',
            3 => 'MANAGER',
            4 => 'KADEP',
            5 => 'DIREKTUR',
            99 => 'ADMIN',
        ];
        return $map[(int)$level] ?? 'KARYAWAN';
    }

    private function payload($input, $isUpdate = false)
    {
        $jobdeskId = trim((string)($input['jobdesk_id'] ?? ''));
        $aksesId = trim((string)($input['akses_lv_id'] ?? ''));
        $bagian = $this->resolve_jobdesk_name($jobdeskId, $input['jobdesk'] ?? $input['bagian'] ?? '');
        $level = $this->resolve_akses_level($aksesId, $input['akses_lv'] ?? $input['level'] ?? 1);
        $statusInt = (int)($input['status'] ?? 1);

        $data = [
            'nik' => trim((string)($input['nik'] ?? '')),
            'nama_lngkp' => trim((string)($input['nm_karyawan'] ?? $input['nama_lngkp'] ?? '')),
            'departement' => strtoupper(trim((string)($input['departemen'] ?? $input['departement'] ?? ''))),
            'bagian' => $bagian,
            'jabatan' => $this->resolve_jabatan_by_level($level, $input['jabatan'] ?? ''),
            'username' => strtolower(trim((string)($input['username'] ?? ''))),
            'tim' => (int)($input['tim'] ?? 0),
            'wilayah' => (int)($input['wilayah'] ?? 0),
            'wilayah_id' => (int)($input['wilayah_id'] ?? $input['wilayah'] ?? 0),
            'level' => $level,
            'jobdesk_id' => is_numeric($jobdeskId) ? (int)$jobdeskId : null,
            'akses_lv_id' => $level,
            'status' => $statusInt,
            'status_karyawan' => $statusInt === 1 ? 'AKTIF' : 'NONAKTIF',
            'faktur_prefix' => trim((string)($input['faktur_prefix'] ?? '')) ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!empty($input['foto'])) {
            $data['foto'] = $input['foto'];
        }
        if (!$isUpdate) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!$isUpdate || trim((string)($input['password'] ?? '')) !== '') {
            $data['password'] = password_hash((string)$input['password'], PASSWORD_DEFAULT);
        }
        return array_intersect_key($data, array_flip($this->fields()));
    }

    public function save($input)
    {
        $this->db->insert($this->table, $this->payload($input, false));
        return $this->db->insert_id();
    }

    public function update($id, $input)
    {
        return $this->db->where('id', $id)->update($this->table, $this->payload($input, true));
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function reset_password($id, $password)
    {
        return $this->db->where('id', $id)->update($this->table, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function toggle_status($id)
    {
        if (!$this->has('status')) return false;
        $row = $this->db->select('status')->where('id', $id)->get($this->table)->row_array();
        if (!$row) return false;
        $newStatus = ((int)$row['status'] === 1 ? 0 : 1);
        $update = ['status' => $newStatus];
        if ($this->has('status_karyawan')) {
            $update['status_karyawan'] = $newStatus === 1 ? 'AKTIF' : 'NONAKTIF';
        }
        return $this->db->where('id', $id)->update($this->table, $update);
    }

    public function reference($table, $valueField, $textField)
    {
        if (!$this->db->table_exists($table)) return [];
        $fields = $this->fields($table);
        if (!in_array($valueField, $fields, true) || !in_array($textField, $fields, true)) return [];
        if (in_array('status', $fields, true)) $this->db->where('status', 1);
        return $this->db->select($valueField . ' AS id,' . $textField . ' AS text')->order_by($textField, 'ASC')->get($table)->result_array();
    }

    public function distinct_options($field)
    {
        $fieldMap = [
            'departemen' => 'departement',
            'jobdesk' => 'bagian',
            'akses_lv' => 'level',
        ];
        $actualField = $fieldMap[$field] ?? $field;
        if (!$this->has($actualField)) return [];
        return $this->db
            ->select($actualField . ' AS id, ' . $actualField . ' AS text')
            ->distinct()
            ->where($actualField . ' IS NOT NULL', null, false)
            ->where("TRIM(" . $actualField . ") !=", '')
            ->order_by($actualField, 'ASC')
            ->get($this->table)
            ->result_array();
    }

    public function bagian_options()
    {
        $rows = $this->distinct_options('bagian');
        $result = [];
        foreach ($rows as $r) {
            $val = strtoupper(trim((string)($r['id'] ?? '')));
            if ($val === '') continue;
            $result[$val] = [
                'id_jobdesk' => $val,
                'nama_jobdesk' => $val,
            ];
        }
        ksort($result);
        return array_values($result);
    }

    public function level_options()
    {
        return [
            ['id_akses_level' => '1', 'nama_akses_level' => '1 - Karyawan / Staff'],
            ['id_akses_level' => '2', 'nama_akses_level' => '2 - Koordinator'],
            ['id_akses_level' => '3', 'nama_akses_level' => '3 - Manager'],
            ['id_akses_level' => '4', 'nama_akses_level' => '4 - Kepala Departemen (Kadep)'],
            ['id_akses_level' => '5', 'nama_akses_level' => '5 - Direktur'],
            ['id_akses_level' => '99', 'nama_akses_level' => '99 - Super Admin'],
        ];
    }
}
