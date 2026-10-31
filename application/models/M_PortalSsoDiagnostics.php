<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_PortalSsoDiagnostics extends CI_Model
{
    public function inspect($appCode, $subject, $redirectUri)
    {
        $this->load->model('M_PortalSso', 'portalSso');
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalSsoClientAccounts', 'clientAccounts');
        $client = $this->portalSso->client_by_code($appCode);
        $validSubject = preg_match('/^(tb_karyawan|tb_users):([1-9][0-9]*)$/D', $subject, $matches);
        $source = $validSubject ? $matches[1] : '';
        $userId = $validSubject ? (int)$matches[2] : 0;
        $assignment = $validSubject ? $this->clientAccounts->assignment_for_user($appCode, $source, $userId) : null;
        $checks = [];
        $add = function ($key, $ok, $message) use (&$checks) {
            $checks[] = ['key' => $key, 'status' => $ok ? 'pass' : 'fail', 'message' => $message];
        };
        $add('client_enabled', $client && (int)$client['is_enabled'] === 1, 'Integrasi klien terdaftar dan aktif.');
        $add('secret_configured', $client && !empty($client['client_secret_hash']), 'Hash secret server tersedia; kecocokan secret diuji lewat API check dari backend klien.');
        $add('launch_https', $client && $this->valid_https($client['launch_uri'] ?? ''), 'URL peluncuran HTTPS tersedia; pastikan menunjuk route awal SSO klien.');
        $add('callback_match', $client && $this->valid_https($redirectUri) && hash_equals((string)$client['redirect_uri'], $redirectUri), 'Callback konfigurasi klien sama persis dengan callback terdaftar.');
        foreach (['tb_portal_sso_code', 'tb_portal_app_access', 'tb_portal_sso_client_account', 'tb_portal_sso_client_account_assignment', 'tb_portal_sso_login_history'] as $table) {
            $add($table, $this->db->table_exists($table), 'Tabel tersedia: ' . $table);
        }
        $add('portal_user_active', $validSubject && $this->portalSso->portal_user_active($source, $userId), 'Identitas Portal ditemukan dan aktif.');
        $add('app_access', $validSubject && $this->portalAccess->user_can_access_app($source, $userId, $appCode), 'User mempunyai izin eksplisit ke aplikasi.');
        $add('account_assignment', (bool)$assignment, 'Tepat satu akun kerja ditetapkan untuk identitas dan aplikasi ini.');
        $ready = !in_array('fail', array_column($checks, 'status'), true);
        return [
            'app_code' => $appCode, 'sub' => $subject, 'portal_ready' => $ready,
            'client_account_username' => $assignment ? $assignment['username'] : null,
            'checks' => $checks, 'client_session_verified' => false,
            'next_step' => 'Uji browser sebagai user tersebut: Mulai Bekerja, callback/state/PKCE, akun lokal unik dan aktif, level akses, session baru, dashboard, serta skenario penolakan. Pemeriksaan ini tidak membuat session klien.',
        ];
    }

    public function valid_https($uri)
    {
        $parts = parse_url($uri);
        return filter_var($uri, FILTER_VALIDATE_URL) && $parts && strtolower($parts['scheme'] ?? '') === 'https'
            && empty($parts['user']) && empty($parts['pass']) && !isset($parts['fragment']);
    }
}
