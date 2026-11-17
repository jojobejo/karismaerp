<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class M_Auth extends CI_Model
{
    private function fields($table)
    {
        static $cache = [];
        if (!isset($cache[$table])) {
            $cache[$table] = $this->db->table_exists($table) ? $this->db->list_fields($table) : [];
        }
        return $cache[$table];
    }

    private function has($table, $field)
    {
        return in_array($field, $this->fields($table), true);
    }

    private function uses_employee_auth($username)
    {
        return strtolower(trim((string)$username)) === 'superadmin';
    }

    function cek_username($username)
    {
        if ($this->uses_employee_auth($username)) {
            $this->db->where('username', $username);
            return $this->db->get('tb_karyawan');
        }
        $this->db->where('username', $username);
        return $this->db->get('tb_users');
    }

    function cek_password($username)
    {
        if ($this->uses_employee_auth($username)) {
            $this->db->select("k.*, k.id AS id_karyawan, 'tb_karyawan' AS auth_table", false);
            $this->db->from('tb_karyawan k');
            $this->db->where('k.username', $username);
            return $this->db->get()->result();
        }

        $userFields = $this->fields('tb_users');
        $employeeFields = $this->fields('tb_karyawan');
        $matchByNik = in_array('nik', $userFields, true) && in_array('nik', $employeeFields, true);
        $matchByUsername = in_array('username', $userFields, true) && in_array('username', $employeeFields, true);

        // Tables can have different collations. Normalize both sides for the lookup.
        $match = [];
        if ($matchByNik) {
            $match[] = "(u.nik IS NOT NULL AND CHAR_LENGTH(u.nik) > 0 AND CONVERT(k.nik USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(u.nik USING utf8mb4) COLLATE utf8mb4_general_ci)";
        }
        if ($matchByUsername) {
            $match[] = "CONVERT(k.username USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(u.username USING utf8mb4) COLLATE utf8mb4_general_ci";
        }
        $matchSql = empty($match) ? '1 = 0' : '(' . implode(' OR ', $match) . ')';
        $orderByNik = $matchByNik
            ? "(CONVERT(k.nik USING utf8mb4) COLLATE utf8mb4_general_ci = CONVERT(u.nik USING utf8mb4) COLLATE utf8mb4_general_ci) DESC, "
            : '';
        $employeeLookup = function ($field) use ($employeeFields, $matchSql, $orderByNik) {
            if (!in_array($field, $employeeFields, true)) {
                return null;
            }
            return "(SELECT k.`{$field}` FROM tb_karyawan k WHERE {$matchSql} ORDER BY {$orderByNik} k.id ASC LIMIT 1)";
        };
        $profileAliases = [
            'departemen' => ['departemen', 'departement', 'bagian'],
            'akses_lv' => ['akses_lv', 'level'],
            'jobdesk' => ['jobdesk', 'jabatan', 'jobdesk_hrd'],
            'nm_karyawan' => ['nm_karyawan', 'nama_lngkp'],
            'tim' => ['tim'],
            'wilayah' => ['wilayah'],
        ];

        $this->db->select("u.*, 'tb_users' AS auth_table", false);
        $this->db->select("(SELECT k.id FROM tb_karyawan k WHERE {$matchSql} ORDER BY {$orderByNik} k.id ASC LIMIT 1) AS id_karyawan", false);
        foreach ($profileAliases as $alias => $fields) {
            $expressions = [];
            $employeeExpression = $employeeLookup(array_shift($fields));
            if ($employeeExpression !== null) {
                $expressions[] = $employeeExpression;
            }
            foreach ($fields as $field) {
                if (in_array($field, $userFields, true)) {
                    $expressions[] = 'u.`' . $field . '`';
                }
            }
            $valueExpression = empty($expressions) ? "''" : 'COALESCE(' . implode(', ', $expressions) . ", '')";
            $this->db->select($valueExpression . " AS `{$alias}`", false);
        }
        $this->db->from('tb_users u');
        $this->db->where('u.username', $username);
        return $this->db->get()->result();
    }

    public function update_last_login($id, $table = 'tb_users')
    {
        if (!in_array($table, ['tb_users', 'tb_karyawan'], true) || !$this->has($table, 'last_login')) {
            return true;
        }
        return $this->db->where('id', $id)->update($table, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function log_login($user, $status, $message = null)
    {
        if (!$this->db->table_exists('tb_login_log')) {
            return true;
        }

        $payload = [
            'id_karyawan' => $user->id_karyawan ?? null,
            'nik' => $user->nik ?? null,
            'username' => $user->username ?? null,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'status' => $status,
            'status_login' => strtolower((string)$status) === 'success' ? 'SUCCESS' : 'FAILED',
            'browser' => $this->input->user_agent(),
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $payload = array_intersect_key($payload, array_flip($this->fields('tb_login_log')));
        return empty($payload) ? true : $this->db->insert('tb_login_log', $payload);
    }
}
