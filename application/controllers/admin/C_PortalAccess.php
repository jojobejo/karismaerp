<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalAccess extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
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
        return $this->json(true, 'Data akses berhasil dimuat.', [
            'user' => $user,
            'apps' => $this->portalAccess->apps_with_callbacks(),
            'denied_apps' => $this->portalAccess->denied_apps($source, $userId),
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
        if ($this->portalAccess->save_access($source, $userId, $this->input->post('allowed_apps'), (int)$this->session->userdata('id'))) {
            return $this->json(true, 'Akses aplikasi berhasil diperbarui.');
        }
        return $this->json(false, 'Perubahan akses gagal disimpan. Pastikan migrasi database sudah diterapkan.');
    }
}
