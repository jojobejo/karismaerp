<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalModules extends CI_Controller
{
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalSso', 'portalSso');
        $this->load->model('M_PortalAppModules', 'modules');
        $this->client = $this->authenticate_client();
    }

    private function response($status, $body)
    {
        return $this->output->set_status_header($status)->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($body, JSON_UNESCAPED_SLASHES));
    }

    private function authorization_header()
    {
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strtolower($name) === 'authorization') return trim((string)$value);
            }
        }
        return trim((string)$this->input->server('HTTP_AUTHORIZATION'));
    }

    private function authenticate_client()
    {
        $header = $this->authorization_header();
        if (!preg_match('/^Basic\s+([A-Za-z0-9+\/=]+)$/i', $header, $matches)) return false;
        $decoded = base64_decode($matches[1], true);
        if ($decoded === false || strpos($decoded, ':') === false) return false;
        list($appCode, $secret) = explode(':', $decoded, 2);
        $appCode = trim($appCode);
        if ($appCode === '' || $secret === '') return false;
        $client = $this->portalSso->client($appCode);
        if (!$client || empty($client['client_secret_hash']) || !hash_equals((string)$client['client_secret_hash'], hash('sha256', $secret))) return false;
        return $client;
    }

    private function body()
    {
        $body = json_decode((string)$this->input->raw_input_stream, true);
        return is_array($body) ? $body : null;
    }

    private function module_data($input, $appCode, $existing = [])
    {
        $input = array_merge($existing, $input);
        $code = trim((string)($input['module_code'] ?? ''));
        $name = trim((string)($input['module_name'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $url = trim((string)($input['module_url'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,79}$/', $code)) return 'Kode module wajib diisi, maksimal 80 karakter, dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
        if ($name === '' || mb_strlen($name, 'UTF-8') > 150) return 'Nama module wajib diisi dan maksimal 150 karakter.';
        if (mb_strlen($description, 'UTF-8') > 2000) return 'Deskripsi maksimal 2.000 karakter.';
        if (mb_strlen($url, 'UTF-8') > 500) return 'Route atau URL maksimal 500 karakter.';
        if ($url !== '') {
            $urlParts = parse_url($url);
            $relativeUrl = isset($url[0]) && $url[0] === '/' && substr($url, 0, 2) !== '//' && strpos($url, '\\') === false;
            $httpsUrl = $urlParts && strtolower($urlParts['scheme'] ?? '') === 'https' && !empty($urlParts['host']) && !isset($urlParts['user']) && !isset($urlParts['pass']);
            if (!$relativeUrl && !$httpsUrl) return 'Route harus berupa path relatif aplikasi atau URL HTTPS.';
        }
        return [
            'app_code' => $appCode,
            'module_code' => $code,
            'module_name' => $name,
            'description' => $description !== '' ? $description : null,
            'module_url' => $url !== '' ? $url : null,
            'sort_order' => max(0, (int)($input['sort_order'] ?? 0)),
            'is_active' => array_key_exists('is_active', $input) ? (!empty($input['is_active']) ? 1 : 0) : 1,
        ];
    }

    private function authorized()
    {
        if (!$this->client) {
            $this->output->set_header('WWW-Authenticate: Basic realm="KARISMA Portal Module API"');
            $this->response(401, ['success' => false, 'error' => 'invalid_client', 'message' => 'Kredensial klien tidak valid atau klien nonaktif.']);
            return false;
        }
        if (!$this->modules->available()) {
            $this->response(503, ['success' => false, 'error' => 'module_catalog_unavailable', 'message' => 'Katalog module belum tersedia.']);
            return false;
        }
        return true;
    }

    public function index()
    {
        if (!$this->authorized()) return;
        if ($this->input->method(true) === 'GET') {
            return $this->response(200, ['success' => true, 'data' => $this->modules->all_for_app($this->client['app_code'])]);
        }
        if ($this->input->method(true) !== 'POST') return $this->response(405, ['success' => false, 'error' => 'method_not_allowed']);
        $input = $this->body();
        if ($input === null) return $this->response(400, ['success' => false, 'error' => 'invalid_json']);
        $data = $this->module_data($input, $this->client['app_code']);
        if (!is_array($data)) return $this->response(422, ['success' => false, 'error' => 'validation_error', 'message' => $data]);
        if ($this->modules->code_exists($this->client['app_code'], $data['module_code'])) return $this->response(409, ['success' => false, 'error' => 'module_code_conflict']);
        $data['created_by_client'] = $this->client['app_code'];
        if (!$this->modules->create($data)) return $this->response(500, ['success' => false, 'error' => 'create_failed']);
        return $this->response(201, ['success' => true, 'data' => $this->modules->find_in_app($this->client['app_code'], $data['module_code'])]);
    }

    public function item($moduleCode = '')
    {
        if (!$this->authorized()) return;
        $moduleCode = rawurldecode((string)$moduleCode);
        $existing = $this->modules->find_in_app($this->client['app_code'], $moduleCode);
        $method = $this->input->method(true);
        if ($method === 'GET') {
            if (!$existing) return $this->response(404, ['success' => false, 'error' => 'module_not_found']);
            return $this->response(200, ['success' => true, 'data' => $existing]);
        }
        if ($method === 'DELETE') {
            if (!$existing) return $this->response(404, ['success' => false, 'error' => 'module_not_found']);
            if (!$this->modules->delete((int)$existing['id'])) return $this->response(500, ['success' => false, 'error' => 'delete_failed']);
            return $this->response(200, ['success' => true, 'message' => 'Module berhasil dihapus.']);
        }
        if ($method !== 'PUT' && $method !== 'PATCH') return $this->response(405, ['success' => false, 'error' => 'method_not_allowed']);
        if (!$existing) return $this->response(404, ['success' => false, 'error' => 'module_not_found']);
        $input = $this->body();
        if ($input === null) return $this->response(400, ['success' => false, 'error' => 'invalid_json']);
        if (!isset($input['module_code'])) $input['module_code'] = $existing['module_code'];
        $data = $this->module_data($input, $this->client['app_code'], $existing);
        if (!is_array($data)) return $this->response(422, ['success' => false, 'error' => 'validation_error', 'message' => $data]);
        if ($this->modules->code_exists($this->client['app_code'], $data['module_code'], (int)$existing['id'])) return $this->response(409, ['success' => false, 'error' => 'module_code_conflict']);
        $data['updated_by_client'] = $this->client['app_code'];
        if (!$this->modules->update((int)$existing['id'], $data)) return $this->response(500, ['success' => false, 'error' => 'update_failed']);
        return $this->response(200, ['success' => true, 'data' => $this->modules->find((int)$existing['id'])]);
    }
}
