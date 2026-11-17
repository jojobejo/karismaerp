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

    /**
     * Memvalidasi dan menemukan tepat satu akun lokal sesuai client_account_username dari klaim SSO Portal.
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
                'message' => 'Akun lokal yang ditetapkan tidak ditemukan pada aplikasi ini.',
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

        // Periksa status aktif akun lokal
        $isInactiveStatus = isset($account->status) && (int) $account->status !== 1;
        $isInactiveEmployee = isset($account->status_karyawan)
            && trim((string) $account->status_karyawan) !== ''
            && strtoupper(trim((string) $account->status_karyawan)) !== 'AKTIF';

        if ($isInactiveStatus || $isInactiveEmployee) {
            return [
                'ok' => false,
                'reason' => 'account_inactive',
                'user' => $account,
                'message' => 'Akun lokal yang ditetapkan sedang nonaktif. Hubungi administrator.',
            ];
        }

        // Periksa level akses lokal yang didukung aplikasi (1 s/d 5)
        $accessLevel = (int) ($account->akses_lv ?? $account->level ?? 0);
        if (!in_array($accessLevel, [1, 2, 3, 4, 5], true)) {
            return [
                'ok' => false,
                'reason' => 'invalid_role',
                'user' => $account,
                'message' => 'Level akses akun lokal tidak valid untuk aplikasi Penilaian Lingkungan.',
            ];
        }

        // Selaraskan pemetaan identitas subject Portal ke akun lokal
        $localAuthTable = isset($account->auth_table) ? (string) $account->auth_table : 'tb_users';
        $localUserId = isset($account->id) ? (int) $account->id : 0;
        if ($localUserId < 1) {
            return [
                'ok' => false,
                'reason' => 'invalid_account_id',
                'user' => $account,
                'message' => 'ID akun lokal tidak valid.',
            ];
        }

        if (!$this->sync_sso_identity_link($provider, $authSource, $sub, $localAuthTable, $localUserId, $clientAccountUsername)) {
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

        $now = date('Y-m-d H:i:s');
        $this->db->trans_begin();

        $bySub = $this->db->get_where('tb_sso_identity', [
            'provider' => $provider,
            'external_sub' => $externalSub,
        ])->row_array();

        $byLocal = $this->db->get_where('tb_sso_identity', [
            'provider' => $provider,
            'local_auth_table' => $localAuthTable,
            'local_user_id' => (int) $localUserId,
        ])->row_array();

        // Jika akun lokal sebelumnya terikat ke subject lain, lepaskan ikatan lama agar mengikuti assignment terbaru Portal
        if ($byLocal && (!$bySub || (int) $byLocal['id'] !== (int) $bySub['id'])) {
            $this->db->where('id', (int) $byLocal['id'])->delete('tb_sso_identity');
        }

        if ($bySub) {
            $this->db->where('id', (int) $bySub['id'])->update('tb_sso_identity', [
                'auth_source' => $authSource,
                'local_auth_table' => $localAuthTable,
                'local_user_id' => (int) $localUserId,
                'local_username' => $localUsername,
                'updated_at' => $now,
            ]);
        } else {
            $this->db->insert('tb_sso_identity', [
                'provider' => $provider,
                'auth_source' => $authSource,
                'external_sub' => $externalSub,
                'local_auth_table' => $localAuthTable,
                'local_user_id' => (int) $localUserId,
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
}
