<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalSsoLoginHistory extends CI_Model
{
    private $table = 'tb_portal_sso_login_history';

    public function table_ready()
    {
        return $this->db->table_exists($this->table);
    }

    public function record_success($data)
    {
        if (!$this->table_ready()
            || !$this->db->table_exists('tb_portal_sso_client_account_assignment')
            || !$this->db->table_exists('tb_portal_sso_client_account')
        ) return false;

        $assignment = $this->db->select('a.account_id, c.username')
            ->from('tb_portal_sso_client_account_assignment a')
            ->join('tb_portal_sso_client_account c', 'c.id = a.account_id AND c.app_code = a.app_code', 'inner')
            ->where([
                'a.app_code' => $data['app_code'],
                'a.auth_source' => $data['auth_source'],
                'a.user_id' => (int)$data['user_id'],
            ])->get()->row_array();
        if (!$assignment || !hash_equals((string)$assignment['username'], (string)$data['account_username'])) return false;

        $eventHash = hash('sha256', (string)$data['event_id']);
        $existing = $this->db->get_where($this->table, ['event_key_hash' => $eventHash])->row_array();
        if ($existing) {
            return $existing['app_code'] === $data['app_code']
                && $existing['auth_source'] === $data['auth_source']
                && (int)$existing['user_id'] === (int)$data['user_id']
                && (int)$existing['account_id'] === (int)$assignment['account_id'];
        }

        $inserted = $this->db->insert($this->table, [
            'event_key_hash' => $eventHash,
            'app_code' => $data['app_code'],
            'auth_source' => $data['auth_source'],
            'user_id' => (int)$data['user_id'],
            'account_id' => (int)$assignment['account_id'],
            'account_username' => (string)$assignment['username'],
            'source_ip' => $data['source_ip'],
            'user_agent' => $data['user_agent'],
        ]);
        if ($inserted) return true;
        $existing = $this->db->get_where($this->table, ['event_key_hash' => $eventHash])->row_array();
        return $existing
            && $existing['app_code'] === $data['app_code']
            && $existing['auth_source'] === $data['auth_source']
            && (int)$existing['user_id'] === (int)$data['user_id']
            && (int)$existing['account_id'] === (int)$assignment['account_id'];
    }

    public function recent($limit = 500)
    {
        if (!$this->table_ready()) return [];
        return $this->db->select('id, app_code, auth_source, user_id, account_id, account_username, source_ip, user_agent, login_at')
            ->order_by('login_at', 'DESC')->order_by('id', 'DESC')->limit(max(1, min(1000, (int)$limit)))
            ->get($this->table)->result_array();
    }

    public function summary()
    {
        if (!$this->table_ready()) return ['total' => 0, 'today' => 0, 'accounts' => 0];
        $total = $this->db->count_all($this->table);
        $today = $this->db->where('login_at >=', date('Y-m-d 00:00:00'))->count_all_results($this->table);
        $accounts = $this->db->select('COUNT(DISTINCT account_id) AS total', false)->get($this->table)->row_array();
        return ['total' => (int)$total, 'today' => (int)$today, 'accounts' => (int)($accounts['total'] ?? 0)];
    }
}
