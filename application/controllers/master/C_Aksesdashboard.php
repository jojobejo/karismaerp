<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Aksesdashboard
 * Halaman khusus Administrator untuk mengatur hak akses buka/kunci
 * setiap menu di halaman Dashboard per user.
 */
class C_Aksesdashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Dashboard');
        $this->load->model('master/M_Dashboardaccess', 'access');

        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request()) {
                $this->json(false, 'Sesi login telah berakhir. Silakan login kembali.', array(), 401);
                exit;
            }
            redirect('Auth');
            exit;
        }

        // Pastikan hanya Administrator yang dapat membuka dan mengubah pengaturan ini
        if (!$this->M_Dashboard->is_strict_admin()) {
            if ($this->input->is_ajax_request()) {
                $this->json(false, 'Akses ditolak. Hanya Administrator yang dapat mengatur hak akses menu dashboard.', array(), 403);
                exit;
            }
            show_error('Hanya Administrator yang memiliki akses ke halaman pengaturan menu dashboard.', 403, 'Akses Ditolak');
            exit;
        }
    }

    private function json($status, $message, $data = array(), $httpCode = 200)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $this->output
            ->set_status_header($httpCode)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => (bool)$status,
                'message' => $message,
                'data' => $data,
            )));
    }

    public function index()
    {
        // Halaman pengaturan akses menu dashboard telah digabungkan ke User Management
        redirect('master/user-management?tab=akses-dashboard');
    }

    public function users()
    {
        $search = trim((string)$this->input->get('q', true));
        $this->json(true, 'OK', $this->access->users($search));
    }

    public function matrix($userId = null)
    {
        $matrix = $this->access->matrix((int)$userId);
        if (!$matrix) {
            return $this->json(false, 'User tidak ditemukan.', array(), 404);
        }

        $this->json(true, 'OK', $matrix);
    }

    public function update()
    {
        $userId = (int)$this->input->post('user_id', true);
        $menuKey = trim((string)$this->input->post('menu_key', true));
        $isAllowed = (int)$this->input->post('is_allowed', true);

        if ($userId <= 0 || $menuKey === '') {
            return $this->json(false, 'Parameter akses menu belum lengkap.', array(), 400);
        }

        $user = $this->access->find_user($userId);
        if (!$user) {
            return $this->json(false, 'User tidak ditemukan.', array(), 404);
        }
        if (!empty($user['is_admin'])) {
            return $this->json(false, 'Akun Administrator selalu memiliki akses penuh dan tidak dapat dikunci.', array(), 400);
        }

        $actor = (string)($this->session->userdata('username') ?: 'admin');
        $ok = $this->access->save_access($userId, $menuKey, $isAllowed, $actor);

        if (!$ok) {
            return $this->json(false, 'Gagal memperbarui hak akses menu.');
        }

        $msg = $isAllowed === 1 ? 'Menu berhasil dibuka (diizinkan).' : 'Menu berhasil dikunci (ikon gembok aktif).';
        $this->json(true, $msg, array(
            'matrix' => $this->access->matrix($userId),
        ));
    }

    /**
     * Perbarui izin aksi granular (view, create, edit, delete) untuk satu menu & satu user
     */
    public function update_action()
    {
        $userId = (int)$this->input->post('user_id', true);
        $menuKey = trim((string)$this->input->post('menu_key', true));
        $action = strtolower(trim((string)$this->input->post('action', true)));
        $value = (int)$this->input->post('value', true);

        if ($userId <= 0 || $menuKey === '' || !in_array($action, array('view', 'create', 'edit', 'delete'), true)) {
            return $this->json(false, 'Parameter izin aksi belum lengkap atau tidak valid.', array(), 400);
        }

        $user = $this->access->find_user($userId);
        if (!$user) {
            return $this->json(false, 'User tidak ditemukan.', array(), 404);
        }
        if (!empty($user['is_admin'])) {
            return $this->json(false, 'Akun Administrator selalu memiliki akses penuh dan tidak dapat diubah.', array(), 400);
        }

        $actor = (string)($this->session->userdata('username') ?: 'admin');
        $ok = $this->access->save_action($userId, $menuKey, $action, $value, $actor);

        if (!$ok) {
            return $this->json(false, 'Gagal memperbarui izin aksi menu.');
        }

        $actionLabels = array(
            'view'   => 'Lihat',
            'create' => 'Tambah',
            'edit'   => 'Edit',
            'delete' => 'Hapus',
        );
        $label = $actionLabels[$action] ?? $action;
        $msg = $value === 1
            ? "Izin {$label} berhasil diaktifkan."
            : "Izin {$label} berhasil dinonaktifkan.";

        $this->json(true, $msg, array(
            'matrix' => $this->access->matrix($userId),
        ));
    }

    public function bulk()
    {
        $userId = (int)$this->input->post('user_id', true);
        $mode = trim((string)$this->input->post('mode', true));
        $sectionKey = trim((string)$this->input->post('section_key', true));
        $isAllowed = (int)$this->input->post('is_allowed', true);

        $user = $this->access->find_user($userId);
        if (!$user) {
            return $this->json(false, 'User tidak ditemukan.', array(), 404);
        }
        if (!empty($user['is_admin'])) {
            return $this->json(false, 'Akun Administrator selalu memiliki akses penuh dan tidak dapat dikunci.', array(), 400);
        }

        $sections = $this->M_Dashboard->catalog_sections();
        $menuKeys = array();

        if ($mode === 'section' && $sectionKey !== '' && isset($sections[$sectionKey])) {
            foreach ($sections[$sectionKey]['menus'] as $menu) {
                $menuKeys[] = $menu['menu_key'];
            }
        } elseif ($mode === 'all') {
            foreach ($sections as $section) {
                foreach ($section['menus'] as $menu) {
                    $menuKeys[] = $menu['menu_key'];
                }
            }
        } else {
            $rawKeys = $this->input->post('menu_keys', true);
            if (is_array($rawKeys)) {
                $menuKeys = $rawKeys;
            }
        }

        $actor = (string)($this->session->userdata('username') ?: 'admin');
        $ok = $this->access->bulk_update($userId, $menuKeys, $isAllowed, $actor);

        if (!$ok) {
            return $this->json(false, 'Gagal memperbarui hak akses massal.');
        }

        $msg = $isAllowed === 1 ? 'Menu terpilih berhasil diizinkan.' : 'Menu terpilih berhasil dikunci.';
        $this->json(true, $msg, array(
            'matrix' => $this->access->matrix($userId),
        ));
    }

    public function reset($userId = null)
    {
        $userId = (int)$userId;
        $user = $this->access->find_user($userId);
        if (!$user) {
            return $this->json(false, 'User tidak ditemukan.', array(), 404);
        }

        $ok = $this->access->reset_user($userId);
        $this->json($ok, $ok ? 'Pengaturan akses user berhasil direset ke default (semua terbuka).' : 'Gagal mereset pengaturan akses.', array(
            'matrix' => $this->access->matrix($userId),
        ));
    }
}

