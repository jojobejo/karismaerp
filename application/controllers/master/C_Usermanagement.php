<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Usermanagement extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('master/M_Usermanagement', 'users');
        $this->load->library('permission');
        $this->guard();
    }

    private function guard()
    {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('gagal', 'Sesi Anda berakhir. Silakan login ulang.');
            redirect('Auth');
        }
        $this->permission->guard('master/user-management');
    }

    private function json($status, $message, $data = [])
    {
        while (ob_get_level()) ob_end_clean();
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => (bool)$status,
            'message' => $message,
            'data' => $data,
        ]));
    }

    private function post()
    {
        return $this->input->post(null, true) ?: [];
    }

    public function index()
    {
        $data['page_title'] = 'KARISMA ERP - User Management';
        $data['departemen_options'] = $this->users->distinct_options('departemen');
        $data['level_options'] = $this->users->distinct_options('level');
        if (!$this->session->userdata('user_sql_import_token')) {
            $this->session->set_userdata('user_sql_import_token', bin2hex(random_bytes(32)));
        }
        $data['sql_import_token'] = $this->session->userdata('user_sql_import_token');
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/master/usermanagement/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function list()
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($this->users->datatable($this->post())));
    }

    public function detail($id = null)
    {
        $row = $this->users->find((int)$id);
        $this->json((bool)$row, $row ? 'Data ditemukan' : 'Data tidak ditemukan', $row ?: []);
    }

    private function validate_user($input, $id = null, $existing = null)
    {
        foreach (['nik' => 'NIK', 'nama_lngkp' => 'Nama lengkap', 'username' => 'Username', 'bagian' => 'Bagian', 'departemen' => 'Departemen', 'jabatan' => 'Jabatan'] as $field => $label) {
            if (trim((string)($input[$field] ?? '')) === '') {
                return $label . ' wajib diisi.';
            }
        }
        if (!$id && strlen((string)($input['password'] ?? '')) < 6) return 'Password minimum 6 karakter.';
        if ((int)($input['level'] ?? 0) < 1) return 'Level akses minimal 1.';
        if (!$id || trim((string)($input['username'] ?? '')) !== trim((string)($existing['username'] ?? ''))) {
            if (!$this->users->unique('username', $input['username'] ?? '', $id)) return 'Username sudah digunakan.';
        }
        if (!$id || trim((string)($input['nik'] ?? '')) !== trim((string)($existing['nik'] ?? ''))) {
            if (!$this->users->unique('nik', $input['nik'] ?? '', $id)) return 'NIK sudah digunakan.';
        }
        return true;
    }

    public function save()
    {
        $input = $this->post();
        $valid = $this->validate_user($input);
        if ($valid !== true) return $this->json(false, $valid);
        $id = $this->users->save($input);
        $this->json((bool)$id, $id ? 'Data berhasil disimpan' : 'Data gagal disimpan', ['id' => $id]);
    }

    public function update($id = null)
    {
        $id = (int)$id;
        $existing = $id > 0 ? $this->users->find($id) : null;
        if (!$existing) {
            return $this->json(false, 'Data user tidak ditemukan.');
        }
        $input = $this->post();
        $valid = $this->validate_user($input, $id, $existing);
        if ($valid !== true) return $this->json(false, $valid);
        $ok = $this->users->update($id, $input);
        $this->json($ok, $ok ? 'Data berhasil diperbarui' : 'Data gagal diperbarui');
    }

    public function delete($id = null)
    {
        $id = (int)$id;
        if ($id < 1 || !$this->users->find($id)) {
            return $this->json(false, 'Data user tidak ditemukan.');
        }
        $ok = $this->users->delete($id);
        $this->json($ok, $ok ? 'Data berhasil dihapus' : 'Data gagal dihapus');
    }

    public function reset_password($id = null)
    {
        if (!$this->users->find((int)$id)) return $this->json(false, 'Data user tidak ditemukan.');
        $password = trim((string)$this->input->post('password', true));
        if (strlen($password) < 6) return $this->json(false, 'Password minimum 6 karakter.');
        $ok = $this->users->reset_password((int)$id, $password);
        $this->json($ok, $ok ? 'Password berhasil direset' : 'Password gagal direset');
    }

    public function toggle_status($id = null)
    {
        $ok = $this->users->toggle_status((int)$id);
        $this->json($ok, $ok ? 'Status user berhasil diperbarui' : 'Kolom status belum tersedia atau data tidak ditemukan');
    }

    public function select_options()
    {
        $data = [
            'departemen' => $this->users->distinct_options('departemen'),
            'level' => $this->users->distinct_options('level'),
        ];
        $this->json(true, 'OK', $data);
    }

    public function import_sql()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        if (!$this->session->userdata('is_admin_dashboard')
            || strtolower(trim((string)$this->session->userdata('username'))) !== 'admin') {
            show_error('Impor SQL hanya untuk admin Portal.', 403);
        }
        $expected = (string)$this->session->userdata('user_sql_import_token');
        $provided = (string)$this->input->post('form_token', true);
        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            show_error('Token formulir tidak valid. Muat ulang halaman.', 403);
        }
        $newToken = bin2hex(random_bytes(32));
        $this->session->set_userdata('user_sql_import_token', $newToken);
        $mode = (string)$this->input->post('mode', true);
        $file = $_FILES['sql_file'] ?? null;
        if (!in_array($mode, ['preview', 'apply'], true)) {
            return $this->json(false, 'Mode impor tidak valid.', ['form_token' => $newToken]);
        }
        if (!$file || (int)$file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return $this->json(false, 'Unggah berkas SQL yang valid.', ['form_token' => $newToken]);
        }
        if (strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION)) !== 'sql'
            || (int)$file['size'] < 1 || (int)$file['size'] > 2097152) {
            return $this->json(false, 'Berkas harus .sql dan berukuran maksimal 2 MB.', ['form_token' => $newToken]);
        }
        $digest = hash_file('sha256', $file['tmp_name']);
        if ($mode === 'apply') {
            $preview = $this->session->userdata('user_sql_import_preview');
            if (!is_array($preview) || (int)($preview['expires'] ?? 0) < time()
                || !hash_equals((string)($preview['digest'] ?? ''), $digest)) {
                return $this->json(false, 'Periksa ulang berkas SQL sebelum mengimpor.', ['form_token' => $newToken]);
            }
        } else {
            $this->session->unset_userdata('user_sql_import_preview');
        }
        try {
            $sql = file_get_contents($file['tmp_name']);
            if ($sql === false) throw new RuntimeException('Berkas SQL tidak dapat dibaca.');
            $this->load->library('Tb_users_sql_parser');
            $rows = $this->tb_users_sql_parser->parse($sql, $this->db->list_fields('tb_users'));
            $summary = $this->users->import_sql_rows($rows, $mode === 'apply');
            if ($mode === 'preview') {
                $this->session->set_userdata('user_sql_import_preview', [
                    'digest' => $digest,
                    'expires' => time() + 600,
                ]);
            } else {
                $this->session->unset_userdata('user_sql_import_preview');
            }
            return $this->json(true, $mode === 'preview' ? 'Pemeriksaan SQL selesai.' : 'Impor user selesai.', [
                'form_token' => $newToken,
                'summary' => $summary,
            ]);
        } catch (RuntimeException $exception) {
            return $this->json(false, $exception->getMessage(), ['form_token' => $newToken]);
        }
    }
}
