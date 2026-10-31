<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalSsoClientAccounts extends CI_Model
{
    private $table = 'tb_portal_sso_client_account';

    public function table_ready()
    {
        return $this->db->table_exists($this->table);
    }

    public function assignment_table_ready()
    {
        return $this->db->table_exists('tb_portal_sso_client_account_assignment');
    }

    public function supports_app($appCode)
    {
        return $this->table_ready()
            && $this->db->table_exists('tb_portal_sso_client')
            && $this->db->where('app_code', $appCode)->count_all_results('tb_portal_sso_client') > 0;
    }

    public function assignment_for_user($appCode, $authSource, $userId)
    {
        if (!$this->assignment_table_ready() || !$this->table_ready()) return null;
        $rows = $this->db->select('a.account_id, c.username')
            ->from('tb_portal_sso_client_account_assignment a')
            ->join($this->table . ' c', 'c.id = a.account_id AND c.app_code = a.app_code', 'inner')
            ->where(['a.app_code' => $appCode, 'a.auth_source' => $authSource, 'a.user_id' => (int)$userId])
            ->limit(2)->get()->result_array();
        return count($rows) === 1 && trim((string)$rows[0]['username']) !== '' ? $rows[0] : null;
    }

    public function credentials_for_user($appCode, $authSource, $userId)
    {
        if (!$this->assignment_table_ready() || !$this->table_ready()) return null;
        $row = $this->db->select('c.username, c.password_encrypted')
            ->from('tb_portal_sso_client_account_assignment a')
            ->join($this->table . ' c', 'c.id = a.account_id AND c.app_code = a.app_code', 'inner')
            ->where(['a.app_code' => $appCode, 'a.auth_source' => $authSource, 'a.user_id' => (int)$userId])
            ->get()->row_array();
        if (!$row) return null;

        $this->config->load('portal_sso_local', true);
        $key = getenv('PORTAL_SSO_ENCRYPTION_KEY');
        if ($key === false || $key === '') $key = (string)$this->config->item('encryption_key', 'portal_sso_local');
        if ($key === '') $key = (string)$this->config->item('encryption_key');
        if (strlen($key) < 32 || !function_exists('openssl_decrypt')) return null;
        $payload = base64_decode((string)$row['password_encrypted'], true);
        if ($payload === false || strlen($payload) < 29) return null;
        $password = openssl_decrypt(substr($payload, 28), 'aes-256-gcm', hash('sha256', $key, true), OPENSSL_RAW_DATA, substr($payload, 0, 12), substr($payload, 12, 16));
        if ($password === false) return null;
        return ['username' => (string)$row['username'], 'password' => $password];
    }

    public function available_for_user($appCode, $authSource, $userId)
    {
        if (!$this->table_ready() || !$this->assignment_table_ready()) return [];
        $assignments = $this->db->select('account_id, auth_source, user_id')
            ->where('app_code', $appCode)->get('tb_portal_sso_client_account_assignment')->result_array();
        $owned = [];
        foreach ($assignments as $assignment) $owned[(int)$assignment['account_id']] = $assignment;
        $available = [];
        foreach ($this->db->select('id, username')->where('app_code', $appCode)->order_by('username', 'ASC')->get($this->table)->result_array() as $account) {
            $owner = $owned[(int)$account['id']] ?? null;
            if (!$owner || ($owner['auth_source'] === $authSource && (int)$owner['user_id'] === (int)$userId)) {
                $available[] = ['id' => (int)$account['id'], 'username' => $account['username'], 'assigned_to_user' => (bool)$owner];
            }
        }
        return $available;
    }

    public function assign_to_user($appCode, $accountId, $authSource, $userId, $updatedBy = null)
    {
        if (!$this->supports_app($appCode) || !$this->assignment_table_ready() || !in_array($authSource, ['tb_karyawan', 'tb_users'], true) || (int)$userId < 1) return false;
        $this->db->trans_begin();
        if ((int)$accountId < 1) {
            $this->db->where(['app_code' => $appCode, 'auth_source' => $authSource, 'user_id' => (int)$userId])->delete('tb_portal_sso_client_account_assignment');
        } else {
            $account = $this->db->select('id')->where(['id' => (int)$accountId, 'app_code' => $appCode])->get($this->table)->row_array();
            if (!$account) {
                $this->db->trans_rollback();
                return false;
            }
            $owner = $this->db->where('account_id', (int)$accountId)->get('tb_portal_sso_client_account_assignment')->row_array();
            if ($owner && ($owner['auth_source'] !== $authSource || (int)$owner['user_id'] !== (int)$userId)) {
                $this->db->trans_rollback();
                return false;
            }
            $existing = $this->db->where(['app_code' => $appCode, 'auth_source' => $authSource, 'user_id' => (int)$userId])->get('tb_portal_sso_client_account_assignment')->row_array();
            if ($existing) {
                $this->db->where('id', (int)$existing['id'])->update('tb_portal_sso_client_account_assignment', ['account_id' => (int)$accountId, 'updated_by' => $updatedBy]);
            } elseif (!$owner) {
                $this->db->insert('tb_portal_sso_client_account_assignment', ['app_code' => $appCode, 'account_id' => (int)$accountId, 'auth_source' => $authSource, 'user_id' => (int)$userId, 'updated_by' => $updatedBy]);
            }
        }
        if ($this->db->trans_status()) {
            $this->db->trans_commit();
            return true;
        }
        $this->db->trans_rollback();
        return false;
    }

    public function all_for_client($appCode)
    {
        if (!$this->table_ready()) return [];
        return $this->db->select('id, app_code, username, password_encrypted, pic_auth_source, pic_user_id, updated_at')
            ->where('app_code', $appCode)->order_by('username', 'ASC')->get($this->table)->result_array();
    }

    public function count_for_client($appCode)
    {
        if (!$this->table_ready()) return 0;
        return $this->db->where('app_code', $appCode)->count_all_results($this->table);
    }

    public function requires_assignment($appCode)
    {
        return true;
    }

    public function save_imported_account($appCode, $username, $encryptedPassword, $updatedBy)
    {
        if (!$this->table_ready()) return false;
        $existing = $this->db->select('id')->where(['app_code' => $appCode, 'username' => $username])->get($this->table)->row_array();
        if ($existing) {
            return $this->db->where('id', (int)$existing['id'])->update($this->table, [
                'password_encrypted' => $encryptedPassword,
                'updated_by' => (int)$updatedBy,
            ]);
        }
        return $this->db->insert($this->table, [
            'app_code' => $appCode,
            'username' => $username,
            'password_encrypted' => $encryptedPassword,
            'pic_auth_source' => null,
            'pic_user_id' => null,
            'created_by' => (int)$updatedBy,
            'updated_by' => (int)$updatedBy,
        ]);
    }

    public function assign_pic($appCode, $accountId, $authSource, $userId, $updatedBy)
    {
        if (!$this->table_ready()) return false;
        return $this->db->where(['id' => (int)$accountId, 'app_code' => $appCode])->update($this->table, [
            'pic_auth_source' => $authSource,
            'pic_user_id' => $userId === null ? null : (int)$userId,
            'updated_by' => (int)$updatedBy,
        ]);
    }

    public function encrypted_password($appCode, $accountId)
    {
        if (!$this->table_ready()) return null;
        return $this->db->select('username, password_encrypted')->get_where($this->table, [
            'app_code' => $appCode,
            'id' => (int)$accountId,
        ])->row_array();
    }

    public function delete_account($appCode, $accountId)
    {
        if (!$this->table_ready()) return false;
        if ($this->assignment_table_ready()) $this->db->where(['app_code' => $appCode, 'account_id' => (int)$accountId])->delete('tb_portal_sso_client_account_assignment');
        $this->db->where(['app_code' => $appCode, 'id' => (int)$accountId])->delete($this->table);
        return $this->db->affected_rows() === 1;
    }
}
