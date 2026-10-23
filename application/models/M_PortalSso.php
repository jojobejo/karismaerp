<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalSso extends CI_Model
{
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

    public function save_client($appCode, $redirectUri, $secretHash, $secretEncrypted, $updatedBy)
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) return false;
        $existing = $this->db->get_where('tb_portal_sso_client', ['app_code' => $appCode])->row_array();
        $data = ['redirect_uri' => $redirectUri, 'is_enabled' => 1, 'updated_by' => (int)$updatedBy, 'client_secret_encrypted' => $secretEncrypted];
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
        $this->db->trans_commit();
        return [
            'sub' => $row['auth_source'] . ':' . (int)$row['user_id'],
            'user_id' => (int)$row['user_id'],
            'auth_source' => $row['auth_source'],
            'username' => (string)$username,
            'name' => (string)$name,
            'state' => (string)$row['state'],
            'app_code' => $appCode,
        ];
    }
}
