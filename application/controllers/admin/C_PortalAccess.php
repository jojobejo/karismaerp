<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalAccess extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        if (!$this->session->userdata('is_admin_dashboard')) show_error('Akses hanya untuk administrator.', 403);
    }

    public function index()
    {
        $users = $this->portalAccess->users();
        $source = trim((string)$this->input->get('source', true));
        $userId = (int)$this->input->get('user_id');
        $selected = null;
        foreach ($users as $user) {
            if ($user['auth_source'] === $source && (int)$user['id'] === $userId) {
                $selected = $user;
                break;
            }
        }
        if (!$selected && !empty($users)) $selected = $users[0];
        $data = [
            'page_title' => 'Pengaturan Akses Aplikasi Portal',
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
        if (!$knownUser) {
            $this->session->set_flashdata('portal_access_error', 'Akun tidak ditemukan. Pengaturan akses tidak disimpan.');
        } elseif ($this->portalAccess->save_access($source, $userId, $this->input->post('allowed_apps'), (int)$this->session->userdata('id'))) {
            $this->session->set_flashdata('portal_access_success', 'Akses aplikasi untuk akun berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('portal_access_error', 'Perubahan akses gagal disimpan. Pastikan migrasi database sudah diterapkan.');
        }
        redirect('admin/portal-app-access?source=' . rawurlencode($source) . '&user_id=' . $userId);
    }
}
