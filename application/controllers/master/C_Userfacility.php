<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Userfacility extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('master/M_Userfacility', 'facility');
        $this->load->library('permission');
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
        $this->permission->guard('master/user-facility');
    }

    private function json($status, $message, $data = [])
    {
        while (ob_get_level()) ob_end_clean();
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => (bool) $status,
            'message' => $message,
            'data' => $data,
        ]));
    }

    public function index()
    {
        $this->facility->ensure_schema();
        $data['page_title'] = 'KARISMA ERP - Fasilitas Per User';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/master/userfacility/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function users()
    {
        $search = trim((string) $this->input->get('q', true));
        $this->json(true, 'OK', $this->facility->users($search));
    }

    public function matrix($userId = null)
    {
        $user = $this->facility->find_user((int) $userId);
        if (!$user) {
            return $this->json(false, 'User tidak ditemukan.');
        }

        $this->json(true, 'OK', [
            'user' => $user,
            'facilities' => $this->facility->matrix((int) $userId),
        ]);
    }

    public function update()
    {
        $userId = (int) $this->input->post('user_id', true);
        $facilityKey = trim((string) $this->input->post('facility_key', true));
        $isAllowed = (int) $this->input->post('is_allowed', true);

        if ($userId <= 0 || $facilityKey === '') {
            return $this->json(false, 'Parameter fasilitas belum lengkap.');
        }

        $ok = $this->facility->save_facility($userId, $facilityKey, $isAllowed);
        $this->json($ok, $ok ? 'Fasilitas user berhasil diperbarui.' : 'Fasilitas user gagal diperbarui.');
    }
}
