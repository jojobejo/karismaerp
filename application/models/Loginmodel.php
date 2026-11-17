<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model Autentikasi Lokal & Pemetaan Identitas SSO Portal KARISMA ERP
 * Aplikasi Klien: Katalog Produk (katalog_produk)
 */
class Loginmodel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Memeriksa keberadaan username pada tabel tb_user.
     */
    public function cek_username($username)
    {
        $this->db->where('username', trim((string) $username));
        return $this->db->get('tb_user');
    }

    /**
     * Mengambil daftar baris akun dari tb_user berdasarkan username.
     */
    public function cek_password($username)
    {
        $this->db->where('username', trim((string) $username));
        return $this->db->get('tb_user')->result();
    }

    /**
     * Memvalidasi dan menemukan tepat satu akun lokal pada tb_user sesuai client_account_username dari klaim SSO Portal.
     * Menolak akun yang hilang, duplikat, nonaktif, atau memiliki hak_akses yang tidak didukung.
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
                'message' => 'Akun lokal yang ditetapkan tidak ditemukan pada aplikasi Katalog Produk.',
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

        // Periksa status aktif akun lokal (user_status = 1)
        $userStatus = isset($account->user_status) ? (int) $account->user_status : 1;
        if ($userStatus !== 1) {
            return [
                'ok' => false,
                'reason' => 'account_inactive',
                'user' => $account,
                'message' => 'Akun lokal yang ditetapkan sedang nonaktif. Hubungi administrator.',
            ];
        }

        // Periksa level hak_akses lokal yang didukung aplikasi Katalog Produk (1 s/d 5)
        $accessLevel = isset($account->hak_akses) ? (int) $account->hak_akses : 0;
        if (!in_array($accessLevel, [1, 2, 3, 4, 5], true)) {
            return [
                'ok' => false,
                'reason' => 'invalid_role',
                'user' => $account,
                'message' => 'Level hak akses akun lokal tidak valid untuk aplikasi Katalog Produk.',
            ];
        }

        $localUserId = isset($account->id_user) ? trim((string) $account->id_user) : '';
        if ($localUserId === '') {
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

        $localUserId = trim((string) $localUserId);
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

        $payload = [
            'user_id' => is_object($user) && isset($user->id_user) ? (string) $user->id_user : null,
            'username' => is_object($user) && isset($user->username) ? (string) $user->username : null,
            'nama_user' => is_object($user) && isset($user->nama_user) ? (string) $user->nama_user : null,
            'hak_akses' => is_object($user) && isset($user->hak_akses) ? (int) $user->hak_akses : null,
            'status' => substr((string) $status, 0, 32),
            'message' => $message !== null ? substr((string) $message, 0, 255) : null,
            'ip_address' => isset($this->input) ? substr((string) $this->input->ip_address(), 0, 45) : null,
            'user_agent' => isset($this->input) ? substr((string) $this->input->user_agent(), 0, 255) : null,
            'login_at' => date('Y-m-d H:i:s'),
        ];

        return $this->db->insert('tb_login_log', $payload);
    }
}
