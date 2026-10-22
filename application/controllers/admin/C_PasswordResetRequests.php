<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PasswordResetRequests extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Auth');
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        $isAdmin = $this->session->userdata('is_admin_dashboard')
            || (int)$this->session->userdata('akses_lv') === 1;
        if (!$isAdmin) show_error('Akses hanya untuk administrator.', 403);
    }

    public function index()
    {
        if (!$this->db->table_exists('tb_password_reset_request')) show_error('Tabel permintaan reset password belum diterapkan.', 500);
        $data['page_title'] = 'Permintaan Reset Password';
        $data['requests'] = $this->db->order_by("CASE WHEN status = 'PENDING' THEN 0 ELSE 1 END", '', false)
            ->order_by('requested_at', 'DESC')->limit(200)->get('tb_password_reset_request')->result();
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/password_reset_requests.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function reset($requestId = null)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $request = $this->db->get_where('tb_password_reset_request', ['id' => (int)$requestId, 'status' => 'PENDING'])->row();
        if (!$request || !in_array($request->auth_source, ['tb_users', 'tb_karyawan'], true)) {
            $this->session->set_flashdata('reset_error', 'Permintaan tidak ditemukan atau sudah diproses.');
            redirect('admin/password-reset-requests');
            return;
        }
        $account = $this->db->get_where($request->auth_source, ['id' => (int)$request->user_id])->row();
        if (!$account || (string)($account->username ?? '') !== (string)$request->username) {
            $this->session->set_flashdata('reset_error', 'Akun sudah tidak ditemukan atau username telah berubah.');
            redirect('admin/password-reset-requests');
            return;
        }
        $this->db->trans_begin();
        $updated = $this->M_Auth->update_password_hash((int)$request->user_id, $request->auth_source, 'karisma123');
        if ($updated) {
            $this->db->where(['id' => (int)$request->id, 'status' => 'PENDING'])->update('tb_password_reset_request', [
                'status' => 'COMPLETED',
                'processed_at' => date('Y-m-d H:i:s'),
                'processed_by' => (int)$this->session->userdata('id'),
            ]);
            $updated = $this->db->affected_rows() === 1;
        }
        if ($updated && $this->db->trans_status()) {
            $this->db->trans_commit();
            $this->session->set_flashdata('reset_success', 'Password ' . $request->username . ' direset menjadi karisma123.');
        } else {
            $this->db->trans_rollback();
            $this->session->set_flashdata('reset_error', 'Reset password gagal. Tidak ada perubahan yang disimpan.');
        }
        redirect('admin/password-reset-requests');
    }
}
