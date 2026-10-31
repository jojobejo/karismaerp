<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalSso extends CI_Model
{
    public function portal_user_active($authSource, $userId)
    {
        if (!in_array($authSource, ['tb_karyawan', 'tb_users'], true) || (int)$userId < 1
            || !$this->db->table_exists($authSource)) return false;
        $account = $this->db->get_where($authSource, ['id' => (int)$userId])->row_array();
        return $account && (!isset($account['status']) || (int)$account['status'] === 1);
    }
    public function clients()
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return [];
        return $this->db->order_by('app_code', 'ASC')->get('tb_portal_sso_client')->result_array();
    }

    public function client($appCode)
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return null;
        return $this->db->get_where('tb_portal_sso_client', ['app_code' => $appCode, 'is_enabled' => 1])->row_array();
    }

    public function client_by_code($appCode)
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return null;
        return $this->db->get_where('tb_portal_sso_client', ['app_code' => $appCode])->row_array();
    }

    public function authenticate_client_secret($appCode, $clientSecret)
    {
        $client = $this->client($appCode);
        return $client && !empty($client['client_secret_hash'])
            && hash_equals((string)$client['client_secret_hash'], hash('sha256', (string)$clientSecret));
    }

    public function update_client_uris($appCode, $redirectUri, $launchUri, $updatedBy)
    {
        if (!$this->db->table_exists('tb_portal_sso_client') || !$this->client_by_code($appCode)) return false;
        return $this->db->where('app_code', $appCode)->update('tb_portal_sso_client', [
            'redirect_uri' => $redirectUri,
            'launch_uri' => $launchUri,
            'updated_by' => (int)$updatedBy,
        ]);
    }

    public function rename_client_code($oldCode, $newCode, $redirectUri, $launchUri, $updatedBy)
    {
        if (!$this->db->table_exists('tb_portal_sso_client') || !$this->client_by_code($oldCode)) return false;
        if ($this->client_by_code($newCode)) return false;
        $this->db->trans_begin();
        if ($this->db->table_exists('tb_portal_app_access')) {
            $conflicts = $this->db->where('app_code', $newCode)->count_all_results('tb_portal_app_access');
            if ($conflicts > 0) {
                $this->db->trans_rollback();
                return false;
            }
            $this->db->where('app_code', $oldCode)->update('tb_portal_app_access', ['app_code' => $newCode, 'updated_by' => (int)$updatedBy]);
        }
        if ($this->db->table_exists('tb_portal_sso_code')) {
            $this->db->where('app_code', $oldCode)->update('tb_portal_sso_code', ['app_code' => $newCode]);
        }
        if ($this->db->table_exists('tb_portal_sso_client_account')) {
            $this->db->where('app_code', $oldCode)->update('tb_portal_sso_client_account', ['app_code' => $newCode]);
        }
        if ($this->db->table_exists('tb_portal_sso_client_account_assignment')) {
            $this->db->where('app_code', $oldCode)->update('tb_portal_sso_client_account_assignment', ['app_code' => $newCode]);
        }
        if ($this->db->table_exists('tb_portal_sso_login_history')) {
            $this->db->where('app_code', $oldCode)->update('tb_portal_sso_login_history', ['app_code' => $newCode]);
        }
        $this->db->where('app_code', $oldCode)->update('tb_portal_sso_client', [
            'app_code' => $newCode, 'redirect_uri' => $redirectUri, 'launch_uri' => $launchUri,
            'updated_by' => (int)$updatedBy,
        ]);
        if ($this->db->trans_status()) {
            $this->db->trans_commit();
            return true;
        }
        $this->db->trans_rollback();
        return false;
    }

    public function delete_client($appCode)
    {
        if (!$this->db->table_exists('tb_portal_sso_client') || !$this->client_by_code($appCode)) return false;
        $this->db->trans_begin();
        if ($this->db->table_exists('tb_portal_sso_code')) {
            $this->db->where('app_code', $appCode)->delete('tb_portal_sso_code');
        }
        if ($this->db->table_exists('tb_portal_sso_client_account_assignment')) {
            $this->db->where('app_code', $appCode)->delete('tb_portal_sso_client_account_assignment');
        }
        if ($this->db->table_exists('tb_portal_sso_client_account')) {
            $this->db->where('app_code', $appCode)->delete('tb_portal_sso_client_account');
        }
        $this->db->where('app_code', $appCode)->delete('tb_portal_sso_client');
        if ($this->db->trans_status() && $this->db->affected_rows() === 1) {
            $this->db->trans_commit();
            return true;
        }
        $this->db->trans_rollback();
        return false;
    }

    public function save_client($appCode, $redirectUri, $launchUri, $secretHash, $secretEncrypted, $updatedBy)
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return false;
        $existing = $this->db->get_where('tb_portal_sso_client', ['app_code' => $appCode])->row_array();
        $data = ['redirect_uri' => $redirectUri, 'launch_uri' => $launchUri, 'is_enabled' => 1, 'updated_by' => (int)$updatedBy, 'client_secret_encrypted' => $secretEncrypted];
        if ($secretHash !== null) $data['client_secret_hash'] = $secretHash;
        if ($existing) {
            $this->db->where('app_code', $appCode)->update('tb_portal_sso_client', $data);
            return $this->db->affected_rows() >= 0;
        }
        if ($secretHash === null) return false;
        $data['app_code'] = $appCode;
        $data['created_by'] = (int)$updatedBy;
        return $this->db->insert('tb_portal_sso_client', $data);
    }

    public function set_client_secret($appCode, $secretHash, $secretEncrypted, $updatedBy)
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return false;
        return $this->db->where('app_code', $appCode)->update('tb_portal_sso_client', [
            'client_secret_hash' => $secretHash,
            'client_secret_encrypted' => $secretEncrypted,
            'updated_by' => (int)$updatedBy,
        ]);
    }

    public function client_secret_encrypted($appCode)
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return null;
        return $this->db->select('client_secret_encrypted')->get_where('tb_portal_sso_client', ['app_code' => $appCode])->row_array();
    }

    public function set_client_enabled($appCode, $enabled, $updatedBy)
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return false;
        return $this->db->where('app_code', $appCode)->update('tb_portal_sso_client', [
            'is_enabled' => $enabled ? 1 : 0,
            'updated_by' => (int)$updatedBy,
        ]);
    }

    public function create_authorization_code($client, $authSource, $userId, $redirectUri, $state, $challenge)
    {
        if (!$this->db->table_exists('tb_portal_sso_code')) return false;
        $this->db->where('expires_at <', date('Y-m-d H:i:s', time() - 86400))->delete('tb_portal_sso_code');
        $code = bin2hex(random_bytes(32));
        $ok = $this->db->insert('tb_portal_sso_code', [
            'code_hash' => hash('sha256', $code),
            'app_code' => $client['app_code'],
            'auth_source' => $authSource,
            'user_id' => (int)$userId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'code_challenge' => $challenge,
            'expires_at' => date('Y-m-d H:i:s', time() + 120),
        ]);
        return $ok ? $code : false;
    }

    public function exchange_code($appCode, $clientSecret, $code, $redirectUri, $verifier)
    {
        if (!$this->db->table_exists('tb_portal_sso_code') || !$this->db->table_exists('tb_portal_sso_client')) return false;
        $client = $this->client($appCode);
        if (!$client || !hash_equals((string)$client['client_secret_hash'], hash('sha256', $clientSecret))) return false;
        if (!hash_equals((string)$client['redirect_uri'], $redirectUri)) return false;

        $verifierDigest = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $this->db->trans_begin();
        $row = $this->db->get_where('tb_portal_sso_code', [
            'code_hash' => hash('sha256', $code),
            'app_code' => $appCode,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $verifierDigest,
            'used_at' => null,
        ])->row_array();
        if (!$row || strtotime($row['expires_at']) < time()) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->where('id', (int)$row['id'])->where('used_at IS NULL', null, false)->where('expires_at >=', date('Y-m-d H:i:s'))
            ->update('tb_portal_sso_code', ['used_at' => date('Y-m-d H:i:s')]);
        if ($this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return false;
        }
        if (!in_array($row['auth_source'], ['tb_karyawan', 'tb_users'], true)) {
            $this->db->trans_rollback();
            return false;
        }
        $account = $this->db->get_where($row['auth_source'], ['id' => (int)$row['user_id']])->row_array();
        if (!$account || (isset($account['status']) && (int)$account['status'] !== 1)) {
            $this->db->trans_rollback();
            return false;
        }
        if (isset($account['username'])) $username = $account['username']; else $username = '';
        if ($row['auth_source'] === 'tb_karyawan') {
            $name = $account['nm_karyawan'] ?? $username;
        } else {
            $name = $account['nama_lngkp'] ?? ($account['nama_user'] ?? $username);
        }
        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalSsoClientAccounts', 'clientAccounts');
        $clientAccount = $this->clientAccounts->assignment_for_user($appCode, $row['auth_source'], (int)$row['user_id']);
        if (!$this->portalAccess->user_can_access_app($row['auth_source'], (int)$row['user_id'], $appCode) || !$clientAccount) {
            $this->db->trans_rollback();
            return false;
        }
        $clientAccountUsername = (string)$clientAccount['username'];
        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        $claims = [
            'sub' => $row['auth_source'] . ':' . (int)$row['user_id'],
            'user_id' => (int)$row['user_id'],
            'auth_source' => $row['auth_source'],
            'username' => (string)$username,
            'name' => (string)$name,
            'state' => (string)$row['state'],
            'app_code' => $appCode,
        ];
        if ($clientAccountUsername !== null && $clientAccountUsername !== '') $claims['client_account_username'] = $clientAccountUsername;
        return $claims;
    }
}
