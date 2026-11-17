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
        $this->db->where('LOWER(username)', strtolower(trim((string) $username)));
        return $this->db->get('tb_users');
    }

    function cek_password($username)
    {
        $this->db->where('LOWER(username)', strtolower(trim((string) $username)));
        return $this->db->get('tb_users')->result();
    }

    public function get_auth_user($username)
    {
        $candidates = $this->get_auth_candidates($username);
        return $candidates[0] ?? null;
    }

    public function get_auth_candidates($username)
    {
        $username = trim((string) $username);
        if ($username === '') {
            return [];
        }

        // Autentikasi utama menggunakan tabel tb_users
        $tbUsers = $this->get_tb_users_user($username);
        if ($tbUsers) {
            return [$tbUsers];
        }

        return [];
    }

    public function get_tb_users_user($username)
    {
        if (!$this->db->table_exists('tb_users')) {
            return null;
        }

        $this->db->where('LOWER(username)', strtolower(trim((string) $username)));
        return $this->db->get('tb_users')->row() ?: null;
    }

    public function verify_password($plainPassword, $storedPassword)
    {
        $plainPassword = (string) $plainPassword;
        $storedPassword = (string) $storedPassword;

        if ($storedPassword === '') {
            return false;
        }

        $info = password_get_info($storedPassword);
        if (!empty($info['algo'])) {
            return password_verify($plainPassword, $storedPassword);
        }

        return hash_equals($storedPassword, $plainPassword);
    }

    public function password_should_be_hashed($storedPassword)
    {
        $storedPassword = (string) $storedPassword;
        if ($storedPassword === '') {
            return false;
        }

        $info = password_get_info($storedPassword);
        if ((int) $info['algo'] === 0) {
            return true;
        }

        return password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
    }

    public function update_password_hash($id, $table, $plainPassword)
    {
        if (!$id || !$this->has($table, 'password')) {
            return false;
        }

        return $this->db
            ->where('id', $id)
            ->update($table, ['password' => password_hash((string) $plainPassword, PASSWORD_DEFAULT)]);
    }

    public function update_last_login($id, $table = 'tb_users')
    {
        if (!$this->has($table, 'last_login')) {
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
}
