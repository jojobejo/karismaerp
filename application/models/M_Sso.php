<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Sso extends CI_Model
{
    private $clientsTable = 'tb_sso_clients';
    private $codesTable = 'tb_sso_auth_codes';
    private $sessionsTable = 'tb_sso_sessions';

    private function fields($table)
    {
        static $cache = array();
        if (!isset($cache[$table])) {
            $cache[$table] = $this->db->table_exists($table) ? $this->db->list_fields($table) : array();
        }
        return $cache[$table];
    }

    private function now()
    {
        return date('Y-m-d H:i:s');
    }

    private function ttl($key, $fallback)
    {
        $value = (int)$this->config->item($key);
        return $value > 0 ? $value : $fallback;
    }

    private function secret()
    {
        return trim((string)$this->config->item('sso_portal_secret'));
    }

    private function token($bytes = 32)
    {
        return bin2hex(random_bytes($bytes));
    }

    private function hashToken($token)
    {
        return hash('sha256', (string)$token);
    }

    private function normalizeUri($uri)
    {
        return trim((string)$uri);
    }

    private function redirectUris($client)
    {
        $raw = trim((string)($client['redirect_uris'] ?? ''));
        if ($raw === '') {
            return array();
        }

        $parts = preg_split('/[\r\n,]+/', $raw);
        $uris = array();
        foreach ($parts as $part) {
            $uri = trim((string)$part);
            if ($uri !== '') {
                $uris[] = $uri;
            }
        }
        return array_values(array_unique($uris));
    }

    public function findClient($clientCode)
    {
        if (!$this->db->table_exists($this->clientsTable)) {
            return null;
        }

        $client = $this->db->where('client_code', trim((string)$clientCode))->get($this->clientsTable)->row_array();
        return $client ?: null;
    }

    public function isClientActive($client)
    {
        return $client && (int)($client['status'] ?? 0) === 1;
    }

    public function verifyClientSecret($client, $secret)
    {
        $hash = (string)($client['client_secret_hash'] ?? '');
        if ($hash === '') {
            return false;
        }

        return password_verify((string)$secret, $hash);
    }

    public function isRedirectAllowed($client, $redirectUri)
    {
        $redirectUri = $this->normalizeUri($redirectUri);
        if ($redirectUri === '') {
            return false;
        }

        return in_array($redirectUri, $this->redirectUris($client), true);
    }

    public function sanitizeUserSession($session)
    {
        $session = is_array($session) ? $session : (array)$session;
        return array_intersect_key($session, array_flip(array(
            'id', 'id_karyawan', 'nik', 'username', 'departemen', 'lv', 'akses_lv', 'akses_lv_id',
            'jobdesk', 'jobdesk_id', 'nama', 'nm_karyawan', 'tim', 'wilayah', 'is_admin_dashboard'
        )));
    }

    public function issueAuthorizationCode($session, $client, $redirectUri, $state = '')
    {
        if (!$this->db->table_exists($this->codesTable)) {
            return array('success' => false, 'message' => 'Tabel SSO auth code belum tersedia.');
        }

        $code = $this->token(32);
        $payload = array(
            'code_hash' => $this->hashToken($code),
            'id_karyawan' => (int)($session['id_karyawan'] ?? $session['id'] ?? 0),
            'nik' => (string)($session['nik'] ?? ''),
            'client_id' => (int)($client['id'] ?? 0),
            'client_code' => (string)($client['client_code'] ?? ''),
            'redirect_uri' => $this->normalizeUri($redirectUri),
            'state' => trim((string)$state),
            'portal_session_id' => session_id(),
            'issued_at' => $this->now(),
            'expires_at' => date('Y-m-d H:i:s', time() + $this->ttl('sso_code_ttl', 60)),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'status' => 'active',
        );

        $payload = array_intersect_key($payload, array_flip($this->fields($this->codesTable)));
        if (empty($payload)) {
            return array('success' => false, 'message' => 'Skema SSO auth code belum cocok.');
        }

        if (!$this->db->insert($this->codesTable, $payload)) {
            return array('success' => false, 'message' => 'Gagal membuat authorization code.');
        }

        return array(
            'success' => true,
            'code' => $code,
            'expires_at' => $payload['expires_at'],
            'state' => $payload['state'],
            'client_code' => $payload['client_code'],
            'redirect_uri' => $payload['redirect_uri'],
        );
    }

    public function exchangeAuthorizationCode($clientCode, $clientSecret, $code, $redirectUri)
    {
        if (!$this->db->table_exists($this->clientsTable) || !$this->db->table_exists($this->codesTable) || !$this->db->table_exists($this->sessionsTable)) {
            return array('success' => false, 'message' => 'Skema SSO belum lengkap.');
        }

        if ($this->secret() === '') {
            return array('success' => false, 'message' => 'Secret SSO belum dikonfigurasi.');
        }

        $client = $this->findClient($clientCode);
        if (!$this->isClientActive($client)) {
            return array('success' => false, 'message' => 'Client SSO tidak valid.');
        }

        if (!$this->verifyClientSecret($client, $clientSecret)) {
            return array('success' => false, 'message' => 'Client secret tidak valid.');
        }

        $redirectUri = $this->normalizeUri($redirectUri);
        if (!$this->isRedirectAllowed($client, $redirectUri)) {
            return array('success' => false, 'message' => 'Redirect URI tidak diizinkan.');
        }

        $row = $this->db->where('code_hash', $this->hashToken($code))->get($this->codesTable)->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Authorization code tidak ditemukan.');
        }
        if (!empty($row['used_at'])) {
            return array('success' => false, 'message' => 'Authorization code sudah dipakai.');
        }
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            return array('success' => false, 'message' => 'Authorization code sudah kedaluwarsa.');
        }
        if ((int)$row['client_id'] !== (int)$client['id']) {
            return array('success' => false, 'message' => 'Authorization code tidak cocok dengan client.');
        }
        if ($this->normalizeUri($row['redirect_uri']) !== $redirectUri) {
            return array('success' => false, 'message' => 'Redirect URI tidak cocok.');
        }

        $user = $this->db->where('id', (int)$row['id_karyawan'])->get('tb_karyawan')->row_array();
        if (!$user) {
            return array('success' => false, 'message' => 'User tidak ditemukan.');
        }

        $token = $this->token(32);
        $expiresAt = date('Y-m-d H:i:s', time() + $this->ttl('sso_session_ttl', 7200));

        $this->db->trans_start();

        $codeUpdate = array('used_at' => $this->now(), 'status' => 'used');
        $codeUpdate = array_intersect_key($codeUpdate, array_flip($this->fields($this->codesTable)));
        if (!empty($codeUpdate)) {
            $this->db->where('id', (int)$row['id'])->where('used_at IS NULL', null, false)->update($this->codesTable, $codeUpdate);
            if ($this->db->affected_rows() < 1) {
                $this->db->trans_complete();
                return array('success' => false, 'message' => 'Authorization code sudah dipakai.');
            }
        }

        $sessionPayload = array(
            'session_hash' => $this->hashToken($token),
            'id_karyawan' => (int)$user['id'],
            'nik' => (string)($user['nik'] ?? ''),
            'client_id' => (int)$client['id'],
            'client_code' => (string)$client['client_code'],
            'portal_session_id' => (string)($row['portal_session_id'] ?? ''),
            'created_at' => $this->now(),
            'last_seen_at' => $this->now(),
            'expires_at' => $expiresAt,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'status' => 'active',
        );

        $sessionPayload = array_intersect_key($sessionPayload, array_flip($this->fields($this->sessionsTable)));
        if (!$this->db->insert($this->sessionsTable, $sessionPayload)) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => 'Gagal membuat sesi SSO.');
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return array('success' => false, 'message' => 'Gagal membuat sesi SSO.');
        }

        return array(
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'expires_in' => $this->ttl('sso_session_ttl', 7200),
            'user' => $this->buildIdentityPayload($user, $client, $token, $expiresAt),
        );
    }

    public function introspectSession($accessToken, $clientCode)
    {
        if (!$this->db->table_exists($this->sessionsTable)) {
            return array('active' => false, 'message' => 'Skema sesi SSO belum tersedia.');
        }

        $client = $this->findClient($clientCode);
        if (!$this->isClientActive($client)) {
            return array('active' => false, 'message' => 'Client SSO tidak valid.');
        }

        $row = $this->db->where('session_hash', $this->hashToken($accessToken))->get($this->sessionsTable)->row_array();
        if (!$row) {
            return array('active' => false, 'message' => 'Sesi SSO tidak ditemukan.');
        }
        if (!empty($row['revoked_at'])) {
            return array('active' => false, 'message' => 'Sesi SSO sudah dicabut.');
        }
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            return array('active' => false, 'message' => 'Sesi SSO sudah kedaluwarsa.');
        }
        if ((int)$row['client_id'] !== (int)$client['id']) {
            return array('active' => false, 'message' => 'Sesi SSO tidak cocok dengan client.');
        }

        $user = $this->db->where('id', (int)$row['id_karyawan'])->get('tb_karyawan')->row_array();
        if (!$user) {
            return array('active' => false, 'message' => 'User tidak ditemukan.');
        }

        $lastSeen = array('last_seen_at' => $this->now());
        $lastSeen = array_intersect_key($lastSeen, array_flip($this->fields($this->sessionsTable)));
        if (!empty($lastSeen)) {
            $this->db->where('id', (int)$row['id'])->update($this->sessionsTable, $lastSeen);
        }

        return array(
            'active' => true,
            'expires_at' => $row['expires_at'],
            'user' => $this->buildIdentityPayload($user, $client, $accessToken, $row['expires_at']),
        );
    }

    public function revokeSession($accessToken, $clientCode = null)
    {
        if (!$this->db->table_exists($this->sessionsTable)) {
            return false;
        }

        $this->db->where('session_hash', $this->hashToken($accessToken));
        if ($clientCode !== null) {
            $client = $this->findClient($clientCode);
            if ($client) {
                $this->db->where('client_id', (int)$client['id']);
            }
        }

        $update = array();
        if (in_array('revoked_at', $this->fields($this->sessionsTable), true)) {
            $update['revoked_at'] = $this->now();
        }
        if (in_array('status', $this->fields($this->sessionsTable), true)) {
            $update['status'] = 'revoked';
        }

        if (empty($update)) {
            return false;
        }

        return $this->db->update($this->sessionsTable, $update);
    }

    public function revokePortalSession($portalSessionId)
    {
        if (!$this->db->table_exists($this->sessionsTable)) {
            return false;
        }

        $update = array();
        if (in_array('revoked_at', $this->fields($this->sessionsTable), true)) {
            $update['revoked_at'] = $this->now();
        }
        if (in_array('status', $this->fields($this->sessionsTable), true)) {
            $update['status'] = 'revoked';
        }

        if (empty($update)) {
            return false;
        }

        return $this->db->where('portal_session_id', (string)$portalSessionId)->update($this->sessionsTable, $update);
    }

    public function buildIdentityPayload($user, $client, $accessToken, $expiresAt)
    {
        $payload = array(
            'id' => (int)($user['id'] ?? 0),
            'id_karyawan' => (int)($user['id'] ?? 0),
            'nik' => (string)($user['nik'] ?? ''),
            'username' => (string)($user['username'] ?? ''),
            'nm_karyawan' => (string)($user['nm_karyawan'] ?? ''),
            'nama' => (string)($user['nm_karyawan'] ?? ''),
            'departemen' => (string)($user['departemen'] ?? ''),
            'jobdesk' => strtoupper(trim((string)($user['jobdesk'] ?? ''))),
            'tim' => (int)($user['tim'] ?? 0),
            'wilayah' => (int)($user['wilayah'] ?? 0),
            'akses_lv' => (int)($user['akses_lv'] ?? 0),
            'lv' => (int)($user['akses_lv'] ?? 0),
            'jobdesk_id' => $user['jobdesk_id'] ?? null,
            'akses_lv_id' => $user['akses_lv_id'] ?? null,
            'client_code' => (string)($client['client_code'] ?? ''),
            'client_name' => (string)($client['client_name'] ?? ''),
            'access_token' => $accessToken,
            'expires_at' => $expiresAt,
        );

        $payload['signature'] = $this->signPayload($payload);
        return $payload;
    }

    public function signPayload($payload)
    {
        $secret = $this->secret();
        if ($secret === '') {
            return '';
        }

        unset($payload['signature']);
        ksort($payload);
        return hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $secret);
    }
}
