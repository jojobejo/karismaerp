<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sso extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Sso');
    }

    private function json($success, $message = '', $extra = array(), $code = 200)
    {
        $payload = array_merge(array(
            'success' => (bool)$success,
            'message' => (string)$message,
        ), $extra);

        $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function buildAuthorizeReturnTo($clientCode, $redirectUri, $state)
    {
        $query = http_build_query(array(
            'client_id' => $clientCode,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'response_type' => 'code',
        ), '', '&', PHP_QUERY_RFC3986);

        return 'sso/authorize?' . $query;
    }

    private function currentSessionPayload()
    {
        return $this->M_Sso->sanitizeUserSession($this->session->userdata());
    }

    public function index()
    {
        redirect('Auth');
    }

    public function authorize()
    {
        $clientCode = trim((string)$this->input->get('client_id', true));
        $redirectUri = trim((string)$this->input->get('redirect_uri', true));
        $state = trim((string)$this->input->get('state', true));
        $responseType = strtolower(trim((string)$this->input->get('response_type', true)));

        if ($clientCode === '' || $redirectUri === '') {
            show_error('Parameter SSO tidak lengkap.', 400);
            return;
        }

        if ($responseType !== '' && $responseType !== 'code') {
            show_error('response_type tidak didukung.', 400);
            return;
        }

        $client = $this->M_Sso->findClient($clientCode);
        if (!$this->M_Sso->isClientActive($client)) {
            show_error('Client SSO tidak valid.', 400);
            return;
        }

        if (!$this->M_Sso->isRedirectAllowed($client, $redirectUri)) {
            show_error('Redirect URI tidak diizinkan.', 400);
            return;
        }

        if (!$this->session->userdata('logged_in')) {
            $returnTo = $this->buildAuthorizeReturnTo($clientCode, $redirectUri, $state);
            redirect('Auth?return_to=' . rawurlencode($returnTo));
            return;
        }

        $result = $this->M_Sso->issueAuthorizationCode($this->currentSessionPayload(), $client, $redirectUri, $state);
        if (empty($result['success'])) {
            show_error($result['message'] ?? 'Gagal membuat kode SSO.', 500);
            return;
        }

        $separator = strpos($redirectUri, '?') === false ? '?' : '&';
        $target = $redirectUri . $separator . http_build_query(array(
            'code' => $result['code'],
            'state' => $state,
        ), '', '&', PHP_QUERY_RFC3986);

        redirect($target);
    }

    public function token()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Method not allowed.', 405);
            return;
        }

        $clientCode = trim((string)$this->input->post('client_id', true));
        $clientSecret = trim((string)$this->input->post('client_secret', true));
        $code = trim((string)$this->input->post('code', true));
        $redirectUri = trim((string)$this->input->post('redirect_uri', true));

        if ($clientCode === '' || $clientSecret === '' || $code === '' || $redirectUri === '') {
            $this->json(false, 'Parameter token SSO tidak lengkap.', array(), 400);
            return;
        }

        $result = $this->M_Sso->exchangeAuthorizationCode($clientCode, $clientSecret, $code, $redirectUri);
        if (empty($result['success'])) {
            $this->json(false, $result['message'] ?? 'Gagal menukar kode SSO.', array(), 400);
            return;
        }

        $this->json(true, 'Token SSO berhasil dibuat.', array(
            'token_type' => $result['token_type'],
            'access_token' => $result['access_token'],
            'expires_at' => $result['expires_at'],
            'expires_in' => $result['expires_in'],
            'user' => $result['user'],
        ));
    }

    public function introspect()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Method not allowed.', 405);
            return;
        }

        $clientCode = trim((string)$this->input->post('client_id', true));
        $accessToken = trim((string)$this->input->post('access_token', true));

        if ($clientCode === '' || $accessToken === '') {
            $this->json(false, 'Parameter introspect SSO tidak lengkap.', array(), 400);
            return;
        }

        $result = $this->M_Sso->introspectSession($accessToken, $clientCode);
        if (empty($result['active'])) {
            $this->json(false, $result['message'] ?? 'Sesi SSO tidak aktif.', array('active' => false), 400);
            return;
        }

        $this->json(true, 'Sesi SSO aktif.', array(
            'active' => true,
            'expires_at' => $result['expires_at'],
            'user' => $result['user'],
        ));
    }

    public function revoke()
    {
        if (strtoupper($this->input->method()) !== 'POST') {
            show_error('Method not allowed.', 405);
            return;
        }

        $clientCode = trim((string)$this->input->post('client_id', true));
        $accessToken = trim((string)$this->input->post('access_token', true));

        if ($clientCode === '' || $accessToken === '') {
            $this->json(false, 'Parameter revoke SSO tidak lengkap.', array(), 400);
            return;
        }

        if (!$this->M_Sso->revokeSession($accessToken, $clientCode)) {
            $this->json(false, 'Sesi SSO tidak dapat dicabut.', array(), 400);
            return;
        }

        $this->json(true, 'Sesi SSO berhasil dicabut.');
    }

    public function logout()
    {
        $accessToken = trim((string)$this->input->get_post('access_token', true));
        if ($accessToken !== '') {
            $this->M_Sso->revokeSession($accessToken);
        }

        $this->M_Sso->revokePortalSession(session_id());
        $this->session->sess_destroy();

        $this->json(true, 'Logout SSO berhasil.');
    }
}
