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

    function cek_username($username)
    {
        $this->db->where('username', $username);
        return $this->db->get('tb_karyawan');
    }

    function cek_password($username)
    {
        $this->db->where('username', $username);
        return $this->db->get('tb_karyawan')->result();
    }

    public function update_last_login($id)
    {
        if (!$this->has('tb_karyawan', 'last_login')) {
            return true;
        }
        return $this->db->where('id', $id)->update('tb_karyawan', ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function log_login($user, $status, $message = null)
    {
        if (!$this->db->table_exists('tb_login_log')) {
            return true;
        }

        $payload = [
            'id_karyawan' => $user->id ?? null,
            'nik' => $user->nik ?? null,
            'username' => $user->username ?? null,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'status' => $status,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $payload = array_intersect_key($payload, array_flip($this->fields('tb_login_log')));
        return empty($payload) ? true : $this->db->insert('tb_login_log', $payload);
    }

    public function revoke_sso_session($portalSessionId)
    {
        if (!$this->db->table_exists('tb_sso_sessions')) {
            return true;
        }

        $update = array();
        if (in_array('revoked_at', $this->fields('tb_sso_sessions'), true)) {
            $update['revoked_at'] = date('Y-m-d H:i:s');
        }
        if (in_array('status', $this->fields('tb_sso_sessions'), true)) {
            $update['status'] = 'revoked';
        }

        if (empty($update)) {
            return true;
        }

        return $this->db->where('portal_session_id', (string)$portalSessionId)->update('tb_sso_sessions', $update);
    }
}
