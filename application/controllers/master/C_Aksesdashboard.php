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
        $this->access->ensure_schema();
        $data['page_title'] = 'KARISMA ERP - Akses Menu Dashboard';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/master/aksesdashboard/index.php', $data);
        $this->load->view('partial/main/footer.php');
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

