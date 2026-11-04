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

    public function import_sql_rows($rows, $apply = false)
    {
        $summary = ['total' => count($rows), 'baru' => 0, 'dilewati' => 0, 'diimpor' => 0,
            'nik_duplikat' => 0, 'departemen_kosong' => 0];
        $seenUsernames = [];
        $seenNiks = [];
        $seenIds = [];
        $prepared = [];
        foreach ($rows as $index => $row) {
            $number = $index + 1;
            $username = trim((string)($row['username'] ?? ''));
            $nik = trim((string)($row['nik'] ?? ''));
            if ($username === '' || $nik === '') throw new RuntimeException('Baris ' . $number . ': username dan NIK wajib diisi.');
            $usernameKey = mb_strtolower($username, 'UTF-8');
            $nikKey = mb_strtolower($nik, 'UTF-8');
            if (isset($seenNiks[$nikKey])) $summary['nik_duplikat']++;
            $seenNiks[$nikKey] = true;
            if (isset($seenUsernames[$usernameKey])) {
                $summary['dilewati']++;
                continue;
            }
            $seenUsernames[$usernameKey] = true;
            if (!$this->unique('username', $username)) {
                $summary['dilewati']++;
                continue;
            }
            $sourceId = (string)($row['id'] ?? '');
            if ($sourceId !== '') {
                if (!ctype_digit($sourceId) || (int)$sourceId < 1 || isset($seenIds[$sourceId])) {
                    throw new RuntimeException('Baris ' . $number . ': ID sumber tidak valid atau berulang.');
                }
                $seenIds[$sourceId] = true;
                if ($this->db->where('id', (int)$sourceId)->count_all_results($this->table) > 0) {
                    throw new RuntimeException('Baris ' . $number . ': ID sumber sudah dipakai akun berbeda.');
                }
            }
            foreach (['nama_lngkp' => 'nama lengkap', 'bagian' => 'bagian', 'jabatan' => 'jabatan'] as $field => $label) {
                if (trim((string)($row[$field] ?? '')) === '') throw new RuntimeException('Baris ' . $number . ': ' . $label . ' wajib diisi.');
            }
            $departemen = trim((string)($row['departement'] ?? $row['departemen'] ?? ''));
            if ($departemen === '') $summary['departemen_kosong']++;
            $level = (string)($row['level'] ?? '');
            if (!ctype_digit($level) || (int)$level < 1) throw new RuntimeException('Baris ' . $number . ': level tidak valid.');
            $status = (string)($row['status'] ?? '1');
            if (!in_array($status, ['0', '1'], true)) throw new RuntimeException('Baris ' . $number . ': status tidak valid.');
            $password = (string)($row['password'] ?? '');
            if ($password === '') throw new RuntimeException('Baris ' . $number . ': password wajib diisi.');
            $passwordInfo = password_get_info($password);
            if (empty($passwordInfo['algo']) && preg_match('/^[a-f0-9]{32,128}$/i', $password)) {
                throw new RuntimeException('Baris ' . $number . ': format hash password tidak didukung.');
            }
            if (empty($passwordInfo['algo']) && strlen($password) < 6) {
                throw new RuntimeException('Baris ' . $number . ': password teks biasa minimal 6 karakter.');
            }
            $statusKaryawan = (string)($row['status_karyawan'] ?? 'AKTIF');
            if (!in_array($statusKaryawan, ['AKTIF', 'NONAKTIF'], true)) {
                throw new RuntimeException('Baris ' . $number . ': status karyawan tidak valid.');
            }
            $data = [
                'username' => $username,
                'password' => empty($passwordInfo['algo']) ? password_hash($password, PASSWORD_DEFAULT) : $password,
                'level' => (int)$level,
                'nama_lngkp' => trim((string)$row['nama_lngkp']),
                'nik' => $nik,
                'bagian' => trim((string)$row['bagian']),
                'departement' => $departemen,
                'jabatan' => trim((string)$row['jabatan']),
                'atasan' => trim((string)($row['atasan'] ?? '-')) ?: '-',
                'penilai' => trim((string)($row['penilai'] ?? '-')) ?: '-',
                'status_karyawan' => $statusKaryawan,
                'status' => (int)$status,
            ];
            if ($sourceId !== '') $data['id'] = (int)$sourceId;
            foreach (['jobdesk_hrd', 'default_redirect'] as $field) {
                if (isset($row[$field])) $data[$field] = trim((string)$row[$field]);
            }
            foreach (['last_login', 'created_at', 'updated_at'] as $field) {
                if (!array_key_exists($field, $row)) continue;
                $value = $row[$field];
                if ($value !== null && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string)$value)) {
                    throw new RuntimeException('Baris ' . $number . ': format ' . $field . ' tidak valid.');
                }
                $data[$field] = $value;
            }
            $prepared[] = array_intersect_key($data, array_flip($this->fields()));
            $summary['baru']++;
        }
        if (!$apply) return $summary;
        $this->db->trans_begin();
        foreach ($prepared as $data) {
            if (!$this->db->insert($this->table, $data)) {
                $this->db->trans_rollback();
                throw new RuntimeException('Penyimpanan user gagal; seluruh impor dibatalkan.');
            }
            $summary['diimpor']++;
        }
        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            throw new RuntimeException('Transaksi impor gagal; seluruh impor dibatalkan.');
        }
        $this->db->trans_commit();
        return $summary;
    }
}
