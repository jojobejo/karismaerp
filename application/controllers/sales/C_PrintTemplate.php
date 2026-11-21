<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Visual Template Designer untuk cetak faktur LX-310.
 */
class C_PrintTemplate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'success' => false,
                        'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    ]));
                $this->output->_display();
                exit;
            }
            redirect('Auth');
        }
        $this->load->model('sales/M_PrintTemplate');
    }

    /**
     * Halaman utama Visual Template Designer.
     */
    public function index()
    {
        $id = (int) $this->input->get('id');
        $data['page_title'] = 'KARISMA - Visual Template Designer Faktur';
        $data['templates']  = $this->M_PrintTemplate->get_all_templates();
        $data['active']     = $this->M_PrintTemplate->get_active_template($id);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/sales/faktur_template_designer.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }

    /**
     * Mengambil data template dalam format JSON.
     */
    public function ajax_get_template($id = 0)
    {
        $tpl = $this->M_PrintTemplate->get_active_template((int) $id);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'  => true,
                'template' => $tpl,
            ]));
    }

    /**
     * Menyimpan perubahan template yang sedang dibuka.
     */
    public function ajax_save()
    {
        $raw = json_decode($this->input->raw_input_stream, true);
        if (!is_array($raw) || empty($raw['id_template'])) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Data template tidak valid.',
            ]));
            return;
        }

        $saved = $this->M_PrintTemplate->update_template((int) $raw['id_template'], $raw);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'   => true,
            'message'   => 'Template berhasil disimpan.',
            'template'  => $saved,
            'templates' => $this->M_PrintTemplate->get_all_templates(),
        ]));
    }

    /**
     * Menyimpan sebagai template baru.
     */
    public function ajax_save_as()
    {
        $raw = json_decode($this->input->raw_input_stream, true);
        if (!is_array($raw) || trim((string) ($raw['nama_template'] ?? '')) === '') {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'Nama template baru wajib diisi.',
            ]));
            return;
        }

        $created = $this->M_PrintTemplate->create_template($raw);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'   => true,
            'message'   => 'Template baru berhasil dibuat.',
            'template'  => $created,
            'templates' => $this->M_PrintTemplate->get_all_templates(),
        ]));
    }

    /**
     * Menjadikan template tertentu sebagai template default.
     */
    public function ajax_set_default()
    {
        $raw = json_decode($this->input->raw_input_stream, true);
        $id  = (int) ($raw['id_template'] ?? 0);
        if ($id <= 0) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'ID template tidak valid.',
            ]));
            return;
        }

        $tpl = $this->M_PrintTemplate->set_default($id);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'   => true,
            'message'   => 'Template berhasil dijadikan default cetak faktur.',
            'template'  => $tpl,
            'templates' => $this->M_PrintTemplate->get_all_templates(),
        ]));
    }

    /**
     * Mengembalikan layout template ke pengaturan awal TTB 2022 Q LOT PAJAK 1.11.
     */
    public function ajax_reset_default()
    {
        $raw = json_decode($this->input->raw_input_stream, true);
        $id  = (int) ($raw['id_template'] ?? 0);
        if ($id <= 0) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'success' => false,
                'message' => 'ID template tidak valid.',
            ]));
            return;
        }

        $tpl = $this->M_PrintTemplate->reset_to_default($id);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'  => true,
            'message'  => 'Layout dikembalikan ke posisi awal TTB 2022.',
            'template' => $tpl,
        ]));
    }

    /**
     * Menghapus template non-default.
     */
    public function ajax_delete()
    {
        $raw = json_decode($this->input->raw_input_stream, true);
        $id  = (int) ($raw['id_template'] ?? 0);
        $ok  = $this->M_PrintTemplate->delete_template($id);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'success'   => $ok,
            'message'   => $ok ? 'Template berhasil dihapus.' : 'Template default atau satu-satunya template tidak dapat dihapus.',
            'templates' => $this->M_PrintTemplate->get_all_templates(),
        ]));
    }
}
