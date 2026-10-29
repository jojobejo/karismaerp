<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalAppDirectory extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalAppDirectory', 'directory');
        $this->load->library('permission');
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        if (strtolower(trim((string)$this->session->userdata('username'))) !== 'admin') {
            show_error('Halaman Application Directory hanya dapat diakses oleh user admin.', 403);
            return;
        }
        $this->permission->guard('master/user-management');
    }

    private function token()
    {
        if (!$this->session->userdata('portal_app_directory_token')) {
            $this->session->set_userdata('portal_app_directory_token', bin2hex(random_bytes(32)));
        }
        return (string)$this->session->userdata('portal_app_directory_token');
    }

    private function respond($status, $message, $data = [])
    {
        return $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode([
            'status' => (bool)$status,
            'message' => $message,
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES));
    }

    public function index()
    {
        $data = [
            'page_title' => 'Application Directory',
            'apps' => $this->portalAccess->apps(),
            'database_ready' => $this->directory->available(),
            'form_token' => $this->token(),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_app_directory.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function save()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);

        $expected = $this->token();
        $provided = (string)$this->input->post('form_token', true);
        if ($provided === '' || !hash_equals($expected, $provided)) show_error('Token formulir tidak valid. Muat ulang halaman.', 403);
        $newToken = bin2hex(random_bytes(32));
        $this->session->set_userdata('portal_app_directory_token', $newToken);

        $input = $this->input->post(null, true) ?: [];
        $appCode = trim((string)($input['app_code'] ?? ''));
        $apps = $this->portalAccess->apps();
        if (!isset($apps[$appCode])) return $this->respond(false, 'Aplikasi tidak dikenal.', ['form_token' => $newToken]);

        $name = trim((string)($input['app_name'] ?? ''));
        $url = trim((string)($input['app_url'] ?? ''));
        $description = trim((string)($input['description'] ?? ''));
        $icon = trim((string)($input['icon'] ?? ''));
        $accent = trim((string)($input['accent'] ?? ''));
        $urlParts = parse_url($url);
        $validHttpsUrl = filter_var($url, FILTER_VALIDATE_URL)
            && $urlParts
            && strtolower($urlParts['scheme'] ?? '') === 'https'
            && !empty($urlParts['host'])
            && !isset($urlParts['user'])
            && !isset($urlParts['pass']);

        if (!$this->directory->available()) return $this->respond(false, 'Tabel katalog belum tersedia. Terapkan docs/database/portal_app_directory_20261029.sql.', ['form_token' => $newToken]);
        if ($name === '' || mb_strlen($name, 'UTF-8') > 150) return $this->respond(false, 'Judul wajib diisi dan maksimal 150 karakter.', ['form_token' => $newToken]);
        if (!$validHttpsUrl || mb_strlen($url, 'UTF-8') > 500) return $this->respond(false, 'URL wajib berupa alamat HTTPS yang valid, maksimal 500 karakter.', ['form_token' => $newToken]);
        if (mb_strlen($description, 'UTF-8') > 2000) return $this->respond(false, 'Deskripsi maksimal 2.000 karakter.', ['form_token' => $newToken]);
        if (!preg_match('/^fa-[a-z0-9-]{1,60}$/', $icon)) return $this->respond(false, 'Nama ikon tidak valid. Gunakan format kelas Font Awesome, misalnya fa-globe.', ['form_token' => $newToken]);
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) return $this->respond(false, 'Warna aksen harus menggunakan format hex, misalnya #2563eb.', ['form_token' => $newToken]);

        $saved = $this->directory->save($appCode, [
            'app_name' => $name,
            'app_url' => $url,
            'description' => $description,
            'icon' => $icon,
            'accent' => strtolower($accent),
            'is_hidden' => !empty($input['is_hidden']) ? 1 : 0,
            'updated_by' => (int)$this->session->userdata('id'),
        ]);
        if (!$saved) return $this->respond(false, 'Pengaturan aplikasi gagal disimpan.', ['form_token' => $newToken]);

        return $this->respond(true, 'Pengaturan aplikasi berhasil disimpan.', ['form_token' => $newToken]);
    }
}
