<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalAccess extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalSsoClientAccounts', 'clientAccounts');
        $this->load->library('permission');
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        $this->permission->guard('master/user-management');
    }

    private function json($status, $message, $data = [])
    {
        while (ob_get_level()) ob_end_clean();
        return $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode([
            'status' => (bool)$status,
            'message' => $message,
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES));
    }

    private function account_form_token()
    {
        if (!$this->session->userdata('portal_account_form_token')) $this->session->set_userdata('portal_account_form_token', bin2hex(random_bytes(32)));
        return (string)$this->session->userdata('portal_account_form_token');
    }

    private function account_user($source, $userId)
    {
        foreach ($this->portalAccess->users() as $user) {
            if ($user['auth_source'] === $source && (int)$user['id'] === (int)$userId) return $user;
        }
        return null;
    }

    private function account_allowed($source, $userId, $appCode)
    {
        return $this->account_user($source, $userId)
            && $this->portalAccess->user_can_access_app($source, $userId, $appCode)
            && $this->clientAccounts->supports_app($appCode)
            && $this->clientAccounts->assignment_table_ready();
    }

    public function index()
    {
        $users = $this->portalAccess->users();
        $source = trim((string)$this->input->get('source', true));
        $userId = (int)$this->input->get('user_id');
        if ($source === '' || $userId < 1) redirect('master/user-management');
        $selected = null;
        foreach ($users as $user) {
            if ($user['auth_source'] === $source && (int)$user['id'] === $userId) {
                $selected = $user;
                break;
            }
        }
        if ($source !== '' && $userId > 0 && !$selected) show_404();
        if (!$selected && !empty($users)) $selected = $users[0];
        $data = [
            'page_title' => 'User Management - Akses Aplikasi',
            'users' => $users,
            'selected_user' => $selected,
            'apps' => $this->portalAccess->apps(),
            'denied_apps' => $selected ? $this->portalAccess->denied_apps($selected['auth_source'], $selected['id']) : [],
            'account_form_token' => $this->account_form_token(),
            'save_success' => $this->session->flashdata('portal_access_success'),
            'save_error' => $this->session->flashdata('portal_access_error'),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_app_access.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail()
    {
        if ($this->input->method(true) !== 'GET') show_error('Metode tidak diizinkan.', 405);
        $source = trim((string)$this->input->get('source', true));
        $userId = (int)$this->input->get('user_id');
        $user = null;
        foreach ($this->portalAccess->users() as $candidate) {
            if ($candidate['auth_source'] === $source && (int)$candidate['id'] === $userId) {
                $user = $candidate;
                break;
            }
        }
        if (!$user) return $this->json(false, 'Akun tidak ditemukan.');
        $rowCount = $this->db->table_exists('tb_portal_app_access')
            ? $this->db->where(['auth_source' => $source, 'user_id' => $userId])->count_all_results('tb_portal_app_access')
            : 0;
        $apps = $this->portalAccess->apps_with_callbacks();
        foreach ($apps as $code => &$app) {
            $app['supports_account_assignment'] = $this->clientAccounts->supports_app($code) && $this->clientAccounts->assignment_table_ready();
            $app['assigned_client_account'] = $app['supports_account_assignment'] ? $this->clientAccounts->assignment_for_user($code, $source, $userId) : null;
        }
        unset($app);
        $deniedApps = $this->portalAccess->denied_apps($source, $userId);
        if ($rowCount === 0) $deniedApps = array_keys($apps);
        return $this->json(true, 'Data akses berhasil dimuat.', [
            'user' => $user,
            'apps' => $apps,
            'denied_apps' => $deniedApps,
            'configured' => $rowCount > 0,
        ]);
    }

    public function save()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $source = trim((string)$this->input->post('auth_source', true));
        $userId = (int)$this->input->post('user_id');
        $knownUser = false;
        foreach ($this->portalAccess->users() as $user) {
            if ($user['auth_source'] === $source && (int)$user['id'] === $userId) {
                $knownUser = true;
                break;
            }
        }
        if (!$knownUser) return $this->json(false, 'Akun tidak ditemukan. Pengaturan akses tidak disimpan.');
        $allowedApps = (array)$this->input->post('allowed_apps');
        if ($this->portalAccess->save_access($source, $userId, $allowedApps, (int)$this->session->userdata('id'))) {
            if ($this->clientAccounts->assignment_table_ready()) {
                foreach (array_keys($this->portalAccess->apps()) as $appCode) {
                    if (!in_array($appCode, $allowedApps, true) && $this->clientAccounts->supports_app($appCode)) {
                        $this->clientAccounts->assign_to_user($appCode, 0, $source, $userId, (int)$this->session->userdata('id'));
                    }
                }
            }
            return $this->json(true, 'Akses aplikasi berhasil diperbarui.');
        }
        return $this->json(false, 'Perubahan akses gagal disimpan. Pastikan migrasi database sudah diterapkan.');
    }

    public function account_setting()
    {
        $appCode = trim((string)$this->input->get('app_code', true));
        $source = trim((string)$this->input->get('source', true));
        $userId = (int)$this->input->get('user_id');
        $user = $this->account_user($source, $userId);
        if (!$user || !$this->account_allowed($source, $userId, $appCode)) show_404();
        $app = $this->portalAccess->apps()[$appCode] ?? null;
        if (!$app) show_404();
        $data = ['page_title' => 'Akun Aplikasi - ' . $app['name'], 'app_code' => $appCode, 'app' => $app, 'user' => $user,
            'is_admin_setting' => true, 'form_token' => $this->account_form_token(),
            'back_url' => 'admin/portal-app-access?source=' . rawurlencode($source) . '&user_id=' . $userId];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_app_account_setting.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function account_options()
    {
        if ($this->input->method(true) !== 'GET') show_error('Metode tidak diizinkan.', 405);
        $appCode = trim((string)$this->input->get('app_code', true));
        $source = trim((string)$this->input->get('source', true));
        $userId = (int)$this->input->get('user_id');
        if (!$this->account_allowed($source, $userId, $appCode)) return $this->json(false, 'User tidak memiliki akses aplikasi atau daftar akun belum tersedia.');
        return $this->json(true, 'Daftar akun berhasil dimuat.', [
            'accounts' => $this->clientAccounts->available_for_user($appCode, $source, $userId),
            'assignment' => $this->clientAccounts->assignment_for_user($appCode, $source, $userId),
        ]);
    }

    public function account_save()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $token = (string)$this->input->post('form_token', true);
        $expected = $this->account_form_token();
        if ($token === '' || !hash_equals($expected, $token)) return $this->json(false, 'Token formulir tidak valid. Muat ulang halaman.');
        $appCode = trim((string)$this->input->post('app_code', true));
        $source = trim((string)$this->input->post('source', true));
        $userId = (int)$this->input->post('user_id');
        $accountId = (int)$this->input->post('account_id');
        if (!$this->account_allowed($source, $userId, $appCode)) return $this->json(false, 'User tidak memiliki akses aplikasi atau daftar akun belum tersedia.');
        if (!$this->clientAccounts->assign_to_user($appCode, $accountId, $source, $userId, (int)$this->session->userdata('id'))) {
            return $this->json(false, 'Akun sudah digunakan user lain atau gagal disimpan. Muat ulang daftar akun.');
        }
        return $this->json(true, $accountId > 0 ? 'Akun aplikasi berhasil ditetapkan.' : 'Penetapan akun aplikasi berhasil dilepas.', ['form_token' => $expected]);
    }
}
