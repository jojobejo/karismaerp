<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sso extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalSso', 'portalSso');
        $this->load->model('M_PortalSsoClientAccounts', 'clientAccounts');
        $this->load->model('M_PortalSsoLoginHistory', 'loginHistory');
    }

    private function json_response($status, $body)
    {
        return $this->output->set_header('Cache-Control: no-store')->set_header('Pragma: no-cache')
            ->set_status_header($status)->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($body, JSON_UNESCAPED_SLASHES));
    }

    private function valid_authorize_request($clientId, $redirectUri, $state, $challenge, $method)
    {
        $client = $this->portalSso->client($clientId);
        return $client && hash_equals((string)$client['redirect_uri'], $redirectUri)
            && strlen($state) >= 16 && strlen($state) <= 512
            && preg_match('/^[A-Za-z0-9_-]{43}$/', $challenge)
            && $method === 'S256' ? $client : false;
    }

    public function authorize()
    {
        if ($this->input->method(true) !== 'GET') show_error('Metode tidak diizinkan.', 405);
        $clientId = trim((string)$this->input->get('client_id', true));
        $redirectUri = trim((string)$this->input->get('redirect_uri', true));
        $state = (string)$this->input->get('state', true);
        $challenge = (string)$this->input->get('code_challenge', true);
        $method = (string)$this->input->get('code_challenge_method', true);
        $client = $this->valid_authorize_request($clientId, $redirectUri, $state, $challenge, $method);
        if (!$client) return $this->json_response(400, ['error' => 'invalid_request']);

        if (!$this->session->userdata('logged_in')) {
            $this->session->set_userdata('portal_sso_pending', [
                'client_id' => $clientId, 'redirect_uri' => $redirectUri, 'state' => $state,
                'code_challenge' => $challenge, 'code_challenge_method' => $method,
            ]);
            redirect('Auth');
            return;
        }
        $authSource = (string)$this->session->userdata('auth_source');
        $userId = (int)$this->session->userdata('id');
        if (!$this->portalSso->portal_user_active($authSource, $userId)) {
            return $this->json_response(403, ['error' => 'access_denied']);
        }
        if (!$this->portalAccess->user_can_access_app($authSource, $userId, $clientId)) {
            return $this->json_response(403, ['error' => 'access_denied']);
        }
        if ($this->clientAccounts->requires_assignment($clientId)
            && (!$this->clientAccounts->assignment_table_ready() || !$this->clientAccounts->assignment_for_user($clientId, $authSource, $userId))
        ) {
            return $this->json_response(403, ['error' => 'account_not_assigned']);
        }
        $code = $this->portalSso->create_authorization_code($client, $authSource, $userId, $redirectUri, $state, $challenge);
        if (!$code) return $this->json_response(503, ['error' => 'temporarily_unavailable']);
        redirect($redirectUri . (strpos($redirectUri, '?') === false ? '?' : '&') . http_build_query(['code' => $code, 'state' => $state], '', '&', PHP_QUERY_RFC3986));
    }

    public function token()
    {
        if ($this->input->method(true) !== 'POST') return $this->json_response(405, ['error' => 'method_not_allowed']);
        $appCode = trim((string)$this->input->post('client_id', true));
        $secret = (string)$this->input->post('client_secret');
        $code = (string)$this->input->post('code', true);
        $redirectUri = trim((string)$this->input->post('redirect_uri', true));
        $verifier = (string)$this->input->post('code_verifier', true);
        if ($appCode === '' || $secret === '' || $code === '' || $redirectUri === '' || !preg_match('/^[A-Za-z0-9._~-]{43,128}$/', $verifier)) {
            return $this->json_response(400, ['error' => 'invalid_request']);
        }
        $claims = $this->portalSso->exchange_code($appCode, $secret, $code, $redirectUri, $verifier);
        if (!$claims) return $this->json_response(400, ['error' => 'invalid_grant']);
        return $this->json_response(200, ['success' => true, 'data' => [
            'user' => [
                'sub' => $claims['sub'], 'user_id' => $claims['user_id'], 'auth_source' => $claims['auth_source'],
                'username' => $claims['username'], 'name' => $claims['name'],
            ],
            'app_code' => $claims['app_code'], 'state' => $claims['state'],
            'client_account_username' => $claims['client_account_username'] ?? null,
        ]]);
    }

    public function check()
    {
        if ($this->input->method(true) !== 'POST') return $this->json_response(405, ['error' => 'method_not_allowed']);
        $appCode = trim((string)$this->input->post('client_id'));
        if (!$this->portalSso->authenticate_client_secret($appCode, (string)$this->input->post('client_secret'))) {
            return $this->json_response(401, ['error' => 'invalid_client']);
        }
        $subject = trim((string)$this->input->post('sub'));
        $redirectUri = trim((string)$this->input->post('redirect_uri'));
        if (!preg_match('/^(tb_karyawan|tb_users):([1-9][0-9]*)$/D', $subject) || $redirectUri === '' || strlen($redirectUri) > 2048) {
            return $this->json_response(400, ['error' => 'invalid_request']);
        }
        $this->load->model('M_PortalSsoDiagnostics', 'diagnostics');
        $report = $this->diagnostics->inspect($appCode, $subject, $redirectUri);
        return $this->json_response(200, ['success' => true, 'data' => $report]);
    }

    public function login_event()
    {
        if ($this->input->method(true) !== 'POST') return $this->json_response(405, ['error' => 'method_not_allowed']);
        $appCode = trim((string)$this->input->post('client_id', true));
        $clientSecret = (string)$this->input->post('client_secret');
        if (!$this->portalSso->authenticate_client_secret($appCode, $clientSecret)) {
            return $this->json_response(401, ['error' => 'invalid_client']);
        }

        $subject = trim((string)$this->input->post('sub', true));
        if (!preg_match('/^(tb_karyawan|tb_users):([1-9][0-9]*)$/', $subject, $matches)) {
            return $this->json_response(400, ['error' => 'invalid_request']);
        }
        $eventId = (string)$this->input->post('event_id', true);
        $accountUsername = (string)$this->input->post('account_username', true);
        $sourceIp = (string)$this->input->post('source_ip', true);
        $userAgent = (string)$this->input->post('user_agent', true);
        if (!preg_match('/^[a-f0-9]{64}$/i', $eventId) || $accountUsername === '' || strlen($accountUsername) > 190) {
            return $this->json_response(400, ['error' => 'invalid_request']);
        }
        if ($sourceIp !== '' && filter_var($sourceIp, FILTER_VALIDATE_IP) === false) $sourceIp = '';
        $saved = $this->loginHistory->record_success([
            'event_id' => strtolower($eventId), 'app_code' => $appCode,
            'auth_source' => $matches[1], 'user_id' => (int)$matches[2],
            'account_username' => $accountUsername,
            'source_ip' => $sourceIp !== '' ? substr($sourceIp, 0, 45) : null,
            'user_agent' => $userAgent !== '' ? substr($userAgent, 0, 255) : null,
        ]);
        if (!$saved) return $this->json_response(409, ['error' => 'login_event_rejected']);
        return $this->json_response(200, ['success' => true]);
    }
}
