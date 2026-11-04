<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalSsoCheck extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            exit;
        }
        if (!$this->session->userdata('is_admin_dashboard')) show_error('Akses hanya untuk administrator.', 403);
        $this->load->model('M_PortalSso', 'portalSso');
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalSsoClientAccounts', 'clientAccounts');
        $this->load->model('M_PortalSsoDiagnostics', 'diagnostics');
        $this->output->set_header('Cache-Control: no-store');
    }

    public function index()
    {
        if (!in_array($this->input->method(true), ['GET', 'POST'], true)) show_error('Metode tidak diizinkan.', 405);
        $token = (string)$this->session->userdata('portal_sso_check_csrf');
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata('portal_sso_check_csrf', $token);
        }
        $clients = $this->portalSso->clients();
        $users = [];
        foreach ($this->portalAccess->users() as $user) $users[$user['auth_source'] . ':' . (int)$user['id']] = $user;
        $linkedUsers = [];
        foreach ($clients as $client) {
            $code = (string)$client['app_code'];
            $linkedUsers[$code] = [];
            foreach ($this->clientAccounts->assigned_users_for_app($code) as $assignment) {
                $subject = $assignment['auth_source'] . ':' . (int)$assignment['user_id'];
                if (!isset($users[$subject]) || !$this->portalAccess->user_can_access_app($assignment['auth_source'], (int)$assignment['user_id'], $code)) continue;
                $linkedUsers[$code][] = ['subject' => $subject, 'label' => $users[$subject]['display_name'] . ' / ' . $users[$subject]['username'] . ' — ' . $assignment['account_username']];
            }
        }
        $data = ['page_title' => 'Cek API Klien SSO', 'clients' => $clients,
            'linked_users' => $linkedUsers, 'form_token' => $token, 'report' => null, 'warning' => '',
            'app_code' => '', 'subject' => '', 'redirect_uri' => ''];
        if ($this->input->method(true) === 'POST') {
            if (!hash_equals($token, (string)$this->input->post('form_token'))) show_error('Token formulir tidak valid. Muat ulang halaman.', 403);
            $data['app_code'] = trim((string)$this->input->post('app_code'));
            $data['subject'] = trim((string)$this->input->post('subject'));
            $client = $this->portalSso->client_by_code($data['app_code']);
            if (!$client) show_error('Aplikasi klien tidak terdaftar.', 400);
            $data['redirect_uri'] = trim((string)($client['redirect_uri'] ?? ''));
            $allowed = array_column($linkedUsers[$data['app_code']] ?? [], 'subject');
            if (!$allowed) $data['warning'] = 'Belum ada identitas Portal dengan akses dan akun kerja yang dikaitkan ke aplikasi klien ini.';
            elseif (!in_array($data['subject'], $allowed, true)) $data['warning'] = 'Identitas Portal yang dipilih belum dikaitkan ke aplikasi klien ini.';
            else $data['report'] = $this->diagnostics->inspect($data['app_code'], $data['subject'], $data['redirect_uri']);
        }
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_sso_check.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
