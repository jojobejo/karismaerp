<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalAppModules extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalAppModules', 'modules');
        $this->load->library('permission');
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        $this->permission->guard('master/user-management');
    }

    private function token()
    {
        if (!$this->session->userdata('portal_module_form_token')) {
            $this->session->set_userdata('portal_module_form_token', bin2hex(random_bytes(32)));
        }
        return (string)$this->session->userdata('portal_module_form_token');
    }

    private function verify_token()
    {
        $expected = $this->token();
        $provided = (string)$this->input->post('form_token', true);
        if ($provided === '' || !hash_equals($expected, $provided)) show_error('Token formulir tidak valid. Muat ulang halaman.', 403);
        $newToken = bin2hex(random_bytes(32));
        $this->session->set_userdata('portal_module_form_token', $newToken);
        return $newToken;
    }

    private function respond($status, $message, $data = [])
    {
        return $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode([
            'status' => (bool)$status,
            'message' => $message,
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES));
    }

    private function valid_app($appCode)
    {
        return isset($this->portalAccess->apps()[$appCode]);
    }

    private function module_input($input, $appCode)
    {
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
            'is_active' => !empty($input['is_active']) && (string)$input['is_active'] !== '0' ? 1 : 0,
        ];
    }

    public function index()
    {
        $appCode = trim((string)$this->input->get('app_code', true));
        $apps = $this->portalAccess->apps();
        if (!$this->valid_app($appCode)) show_404();
        if ($this->input->get('source', true) && (int)$this->input->get('user_id') > 0) {
            $backUrl = 'admin/portal-app-access?source=' . rawurlencode((string)$this->input->get('source', true)) . '&user_id=' . (int)$this->input->get('user_id');
        } else {
            $backUrl = 'master/user-management';
        }
        $data = [
            'page_title' => 'Setting Module - ' . $apps[$appCode]['name'],
            'app_code' => $appCode,
            'app' => $apps[$appCode],
            'modules' => $this->modules->all_for_app($appCode),
            'database_ready' => $this->modules->available(),
            'form_token' => $this->token(),
            'back_url' => $backUrl,
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_app_modules.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function list()
    {
        $appCode = trim((string)$this->input->get('app_code', true));
        if (!$this->valid_app($appCode)) return $this->respond(false, 'Aplikasi tidak dikenal.');
        if (!$this->modules->available()) return $this->respond(false, 'Tabel katalog module belum tersedia.');
        return $this->respond(true, 'Daftar module berhasil dimuat.', ['modules' => $this->modules->all_for_app($appCode)]);
    }

    public function save()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $newToken = $this->verify_token();
        $appCode = trim((string)$this->input->post('app_code', true));
        if (!$this->valid_app($appCode)) return $this->respond(false, 'Aplikasi tidak dikenal.', ['form_token' => $newToken]);
        $data = $this->module_input($this->input->post(null, true) ?: [], $appCode);
        if (!is_array($data)) return $this->respond(false, $data, ['form_token' => $newToken]);
        if (!$this->modules->available()) return $this->respond(false, 'Tabel katalog module belum tersedia.', ['form_token' => $newToken]);
        if ($this->modules->code_exists($appCode, $data['module_code'])) return $this->respond(false, 'Kode module sudah digunakan pada aplikasi ini.', ['form_token' => $newToken]);
        $data['created_by'] = (int)$this->session->userdata('id');
        if (!$this->modules->create($data)) return $this->respond(false, 'Module gagal disimpan.', ['form_token' => $newToken]);
        return $this->respond(true, 'Module berhasil ditambahkan.', ['form_token' => $newToken]);
    }

    public function update($id = null)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $newToken = $this->verify_token();
        $id = (int)$id;
        $existing = $this->modules->find($id);
        if (!$existing) return $this->respond(false, 'Module tidak ditemukan.', ['form_token' => $newToken]);
        $data = $this->module_input($this->input->post(null, true) ?: [], $existing['app_code']);
        if (!is_array($data)) return $this->respond(false, $data, ['form_token' => $newToken]);
        if ($this->modules->code_exists($existing['app_code'], $data['module_code'], $id)) return $this->respond(false, 'Kode module sudah digunakan pada aplikasi ini.', ['form_token' => $newToken]);
        $data['updated_by'] = (int)$this->session->userdata('id');
        if (!$this->modules->update($id, $data)) return $this->respond(false, 'Module gagal diperbarui.', ['form_token' => $newToken]);
        return $this->respond(true, 'Module berhasil diperbarui.', ['form_token' => $newToken]);
    }

    public function delete($id = null)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $newToken = $this->verify_token();
        if (!$this->modules->find((int)$id)) return $this->respond(false, 'Module tidak ditemukan.', ['form_token' => $newToken]);
        if (!$this->modules->delete((int)$id)) return $this->respond(false, 'Module gagal dihapus.', ['form_token' => $newToken]);
        return $this->respond(true, 'Module berhasil dihapus.', ['form_token' => $newToken]);
    }
}
