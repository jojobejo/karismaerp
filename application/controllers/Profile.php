<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Pastikan user sudah login
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }
        $this->load->model('M_Profile');
    }

    public function index()
    {
        $id_user = $this->session->userdata('id');
        $user = $this->M_Profile->get_user_by_id($id_user);

        if (!$user) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('dashboard');
        }

        $data['page_title'] = 'Profile Setting';
        $data['user'] = $user;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/profile/setting.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update_password()
    {
        $id_user = $this->session->userdata('id');
        $user = $this->M_Profile->get_user_by_id($id_user);

        if (!$user) {
            $this->session->set_flashdata('error', 'Data pengguna tidak ditemukan.');
            redirect('Auth');
        }

        $password_lama        = trim($this->input->post('password_lama'));
        $password_baru        = trim($this->input->post('password_baru'));
        $konfirmasi_password  = trim($this->input->post('konfirmasi_password'));

        // Validasi input
        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            $this->session->set_flashdata('error', 'Semua kolom password wajib diisi.');
            redirect('profile');
        }

        if (strlen($password_baru) < 6) {
            $this->session->set_flashdata('error', 'Password baru minimal harus 6 karakter.');
            redirect('profile');
        }

        if ($password_baru !== $konfirmasi_password) {
            $this->session->set_flashdata('error', 'Konfirmasi password baru tidak cocok.');
            redirect('profile');
        }

        // Cek password lama
        if (!password_verify($password_lama, $user->password)) {
            $this->session->set_flashdata('error', 'Password lama yang Anda masukkan salah.');
            redirect('profile');
        }

        // Hash password baru
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);

        // Update database
        $update = $this->M_Profile->update_password($id_user, $hash_baru);
        if ($update) {
            // Sinkronkan ke tb_user jika ada username yang sesuai
            if (!empty($user->username)) {
                $this->M_Profile->sync_tb_user_password($user->username, $hash_baru);
            }

            $this->session->set_flashdata('success', 'Password berhasil diperbarui! Silakan gunakan password baru ini.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui password. Silakan coba beberapa saat lagi.');
        }

        redirect('profile');
    }

    public function update_info()
    {
        $id_user = $this->session->userdata('id');
        $nama    = trim($this->input->post('nm_karyawan'));

        if (empty($nama)) {
            $this->session->set_flashdata('error', 'Nama tidak boleh kosong.');
            redirect('profile');
        }

        $update = $this->M_Profile->update_info($id_user, array(
            'nm_karyawan' => $nama,
            'updated_at'  => date('Y-m-d H:i:s')
        ));

        if ($update) {
            // Update session nama
            $this->session->set_userdata('nama', $nama);
            $this->session->set_flashdata('success', 'Informasi profil berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui profil.');
        }

        redirect('profile');
    }
}
