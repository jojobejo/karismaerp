<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Sectionmenu
 * Pengaturan khusus Administrator untuk menentukan menu apa saja yang ditampilkan pada setiap departemen.
 */
class C_Sectionmenu extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Dashboard');
        $this->load->model('master/M_Sectionmenu', 'sectionMenu');

        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request()) {
                $this->json(false, 'Sesi login telah berakhir. Silakan login kembali.', array(), 401);
                exit;
            }
            redirect('Auth');
            exit;
        }

        // Hanya Administrator sejati yang dapat mengatur pengelompokan menu departemen
        if (!$this->M_Dashboard->is_strict_admin()) {
            if ($this->input->is_ajax_request()) {
                $this->json(false, 'Akses ditolak. Hanya Administrator yang dapat mengatur menu departemen.', array(), 403);
                exit;
            }
            show_error('Hanya Administrator yang memiliki akses ke pengaturan menu departemen.', 403, 'Akses Ditolak');
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
        redirect('master/user-management?tab=menu-departemen');
    }

    public function sections()
    {
        $this->json(true, 'OK', $this->sectionMenu->sections());
    }

    public function detail($sectionKey = '')
    {
        $data = $this->sectionMenu->catalog_for_section($sectionKey);
        if (!$data) {
            return $this->json(false, 'Departemen tidak ditemukan.', array(), 404);
        }

        $this->json(true, 'OK', $data);
    }

    public function save()
    {
        $sectionKey = trim((string)$this->input->post('section_key', true));
        $menuKeys = $this->input->post('menu_keys', true);
        if (!is_array($menuKeys)) {
            $menuKeys = array();
        }

        if ($sectionKey === '') {
            return $this->json(false, 'Departemen wajib dipilih.');
        }

        $ok = $this->sectionMenu->save_section($sectionKey, $menuKeys);
        $this->json($ok, $ok ? 'Pengaturan menu departemen berhasil disimpan.' : 'Gagal menyimpan pengaturan menu departemen.');
    }

    public function reset()
    {
        $sectionKey = trim((string)$this->input->post('section_key', true));
        if ($sectionKey === '') {
            return $this->json(false, 'Departemen wajib dipilih.');
        }

        $ok = $this->sectionMenu->reset_section($sectionKey);
        $this->json($ok, $ok ? 'Menu departemen berhasil dikembalikan ke pengaturan default.' : 'Gagal mereset menu departemen.');
    }
}

