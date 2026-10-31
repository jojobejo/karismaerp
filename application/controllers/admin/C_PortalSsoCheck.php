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
        $data = ['page_title' => 'Cek API Klien SSO', 'clients' => $this->portalSso->clients(),
            'users' => $this->portalAccess->users(), 'form_token' => $token, 'report' => null,
            'app_code' => '', 'subject' => '', 'redirect_uri' => ''];
        if ($this->input->method(true) === 'POST') {
            if (!hash_equals($token, (string)$this->input->post('form_token'))) show_error('Token formulir tidak valid. Muat ulang halaman.', 403);
            $data['app_code'] = trim((string)$this->input->post('app_code'));
            $data['subject'] = trim((string)$this->input->post('subject'));
            $data['redirect_uri'] = trim((string)$this->input->post('redirect_uri'));
            $data['report'] = $this->diagnostics->inspect($data['app_code'], $data['subject'], $data['redirect_uri']);
        }
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_sso_check.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
