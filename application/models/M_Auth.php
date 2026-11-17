<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model Autentikasi Lokal & Pemetaan Identitas SSO Portal KARISMA ERP
 * Aplikasi Klien: POS Security & HRD (pos_security)
 */
class M_Auth extends CI_Model
{
    /**
     * Mengambil daftar kolom tabel dengan caching per-request.
     */
    private function fields($table)
    {
        static $cache = [];
        if (!isset($cache[$table])) {
            $cache[$table] = $this->db->table_exists($table) ? $this->db->list_fields($table) : [];
        }
        return $cache[$table];
    }

    public function cek_username($username)
    {
        $this->db->where('username', (string) $username);
        return $this->db->get('tb_user');
    }

    public function cek_password($username)
    {
        $this->db->where('username', (string) $username);
        return $this->db->get('tb_user')->result();
    }

    /**
     * Memvalidasi dan menemukan tepat satu akun lokal pada tb_user sesuai client_account_username dari klaim SSO Portal.
     * Menolak akun yang hilang, duplikat, nonaktif, atau memiliki level akses yang tidak didukung.
     */
    public function resolve_sso_local_account($provider, array $claims)
    {
        $clientAccountUsername = isset($claims['client_account_username'])
            ? trim((string) $claims['client_account_username'])
            : '';
        $sub = isset($claims['user']['sub']) ? trim((string) $claims['user']['sub']) : '';
        $authSource = isset($claims['user']['auth_source']) ? trim((string) $claims['user']['auth_source']) : '';

        if ($clientAccountUsername === '' || $sub === '' || !in_array($authSource, ['tb_users', 'tb_karyawan'], true)) {
            return [
                'ok' => false,
                'reason' => 'invalid_claims',
                'message' => 'Klaim akun kerja atau identitas Portal tidak lengkap.',
            ];
        }

        // Cari akun lokal secara persis berdasarkan client_account_username (tanpa fallback ke username Portal)
        $candidates = $this->cek_password($clientAccountUsername);
        $exactMatches = [];
        foreach ($candidates as $candidate) {
            if (isset($candidate->username) && (string) $candidate->username === $clientAccountUsername) {
                $exactMatches[] = $candidate;
            }
        }

        if (count($exactMatches) === 0) {
            return [
                'ok' => false,
                'reason' => 'account_not_found',
                'message' => 'Akun lokal yang ditetapkan tidak ditemukan pada aplikasi POS Security.',
            ];
        }

        if (count($exactMatches) > 1) {
            return [
                'ok' => false,
                'reason' => 'account_duplicate',
                'message' => 'Ditemukan lebih dari satu akun lokal dengan username yang sama. Hubungi administrator.',
            ];
        }

        $account = $exactMatches[0];

        // Periksa status aktif akun lokal bila kolom status / user_status tersedia pada tb_user
        $isInactive = (isset($account->status) && (string) $account->status !== '' && (int) $account->status !== 1)
            || (isset($account->user_status) && (string) $account->user_status !== '' && (int) $account->user_status !== 1);

        if ($isInactive) {
            return [
                'ok' => false,
                'reason' => 'account_inactive',
                'user' => $account,
                'message' => 'Akun lokal yang ditetapkan sedang nonaktif. Hubungi administrator.',
            ];
        }

        // Periksa level akses lokal (akses_lv) yang didukung aplikasi POS Security
        $accessLevel = isset($account->akses_lv) ? (int) $account->akses_lv : 0;
        if (!in_array($accessLevel, [1, 2, 3, 4, 5], true)) {
            return [
                'ok' => false,
                'reason' => 'invalid_role',
                'user' => $account,
                'message' => 'Level akses akun lokal tidak valid untuk aplikasi POS Security.',
            ];
        }

        $localUserId = isset($account->id) ? (int) $account->id : 0;
        if ($localUserId < 1) {
            return [
                'ok' => false,
                'reason' => 'invalid_account_id',
                'user' => $account,
                'message' => 'ID akun lokal tidak valid.',
            ];
        }

        if (!$this->sync_sso_identity_link($provider, $authSource, $sub, 'tb_user', $localUserId, $clientAccountUsername)) {
            return [
                'ok' => false,
                'reason' => 'identity_link_failed',
                'user' => $account,
                'message' => 'Gagal menyelaraskan pemetaan identitas SSO lokal.',
            ];
        }

        return [
            'ok' => true,
            'user' => $account,
        ];
    }

    /**
     * Menyimpan atau menyelaraskan tautan identitas subject Portal (external_sub) ke akun lokal pada tb_sso_identity.
     */
    public function sync_sso_identity_link($provider, $authSource, $externalSub, $localAuthTable, $localUserId, $localUsername)
    {
        if (!$this->db->table_exists('tb_sso_identity')) {
            return true;
        }

        $provider = rtrim(trim((string) $provider), '/');
        if ($provider === '') {
            $provider = 'https://portal.karismaerp.com';
        }

        $localUserId = (string) ((int) $localUserId);
        $now = date('Y-m-d H:i:s');
        $this->db->trans_begin();

        $bySub = $this->db->get_where('tb_sso_identity', [
            'provider' => $provider,
            'external_sub' => $externalSub,
        ])->row_array();

        $byLocal = $this->db->get_where('tb_sso_identity', [
            'provider' => $provider,
            'local_auth_table' => $localAuthTable,
            'local_user_id' => $localUserId,
        ])->row_array();

        // Jika akun lokal sebelumnya terikat ke subject lain, lepaskan ikatan lama agar mengikuti assignment terbaru Portal
        if ($byLocal && (!$bySub || (int) $byLocal['id'] !== (int) $bySub['id'])) {
            $this->db->where('id', (int) $byLocal['id'])->delete('tb_sso_identity');
        }

        if ($bySub) {
            $this->db->where('id', (int) $bySub['id'])->update('tb_sso_identity', [
                'auth_source' => $authSource,
                'local_auth_table' => $localAuthTable,
                'local_user_id' => $localUserId,
                'local_username' => $localUsername,
                'updated_at' => $now,
            ]);
        } else {
            $this->db->insert('tb_sso_identity', [
                'provider' => $provider,
                'auth_source' => $authSource,
                'external_sub' => $externalSub,
                'local_auth_table' => $localAuthTable,
                'local_user_id' => $localUserId,
                'local_username' => $localUsername,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    /**
     * Mencatat audit log autentikasi ke tabel tb_login_log (jika tersedia) tanpa data sensitif.
     */
    public function log_login($user, $status = 'success', $message = null)
    {
        if (!$this->db->table_exists('tb_login_log')) {
            return true;
        }

        $columns = $this->fields('tb_login_log');
        if (empty($columns)) {
            return true;
        }

        $candidatePayload = [
            'user_id' => is_object($user) && isset($user->id) ? (string) $user->id : null,
            'kode_user' => is_object($user) && isset($user->kode_user) ? (string) $user->kode_user : null,
            'username' => is_object($user) && isset($user->username) ? (string) $user->username : null,
            'nama_user' => is_object($user) && isset($user->nama_user) ? (string) $user->nama_user : null,
            'departemen' => is_object($user) && isset($user->departemen) ? (string) $user->departemen : null,
            'akses_lv' => is_object($user) && isset($user->akses_lv) ? (int) $user->akses_lv : null,
            'hak_akses' => is_object($user) && isset($user->akses_lv) ? (int) $user->akses_lv : null,
            'status' => substr((string) $status, 0, 32),
            'message' => $message !== null ? substr((string) $message, 0, 255) : null,
            'ip_address' => isset($this->input) ? substr((string) $this->input->ip_address(), 0, 45) : null,
            'user_agent' => isset($this->input) ? substr((string) $this->input->user_agent(), 0, 255) : null,
            'login_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $payload = array_intersect_key($candidatePayload, array_flip($columns));
        if (empty($payload)) {
            return true;
        }

        return $this->db->insert('tb_login_log', $payload);
    }
}
