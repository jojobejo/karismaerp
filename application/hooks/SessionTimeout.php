<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SessionTimeout
{
    /**
     * Timeout aktivitas dalam detik (15 menit = 900 detik)
     */
    protected $timeout = 900;

    /**
     * Controller yang dikecualikan dari pemeriksaan login
     */
    protected $whitelist_controllers = array('auth');

    /**
     * Folder / directory controller yang dikecualikan
     */
    protected $whitelist_directories = array('api');

    public function check_session()
    {
        // Abaikan jika dijalankan melalui CLI
        if (is_cli()) {
            return;
        }

        $CI = &get_instance();

        // Pastikan session dan router tersedia
        if (!isset($CI->router) || !isset($CI->session)) {
            return;
        }

        $class     = strtolower($CI->router->fetch_class());
        $directory = strtolower(trim((string)$CI->router->fetch_directory(), '/\\'));

        // Cek jika controller berada di whitelist
        if (in_array($class, $this->whitelist_controllers, true)) {
            return;
        }

        // Cek jika direktori berada di whitelist
        if (in_array($directory, $this->whitelist_directories, true)) {
            return;
        }

        // Cek apakah user sudah login
        $logged_in = $CI->session->userdata('logged_in');
        if (!$logged_in) {
            $this->_handle_unauthenticated($CI, 'Silakan login terlebih dahulu untuk mengakses sistem.');
            return;
        }

        // Pengecekan Idle Timeout (15 menit)
        $now = time();
        $last_activity = $CI->session->userdata('last_activity');

        if ($last_activity && ($now - $last_activity > $this->timeout)) {
            // Sesi idle telah melewati 15 menit
            $this->_handle_timeout($CI);
            return;
        }

        // Perbarui timestamp aktivitas terakhir
        $CI->session->set_userdata('last_activity', $now);
    }

    protected function _handle_timeout($CI)
    {
        // Bersihkan session data pengguna
        $userdata = array('id', 'id_user', 'username', 'nik', 'departemen', 'lv', 'jobdesk', 'nama', 'tim', 'wilayah', 'logged_in', 'last_activity');
        $CI->session->unset_userdata($userdata);

        $message = 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 15 menit. Silakan login kembali.';

        if ($CI->input->is_ajax_request()) {
            $CI->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'   => false,
                    'message'  => $message,
                    'redirect' => site_url('Auth')
                )))
                ->_display();
            exit;
        }

        $CI->session->set_flashdata('gagal', $message);
        redirect('Auth');
        exit;
    }

    protected function _handle_unauthenticated($CI, $message)
    {
        if ($CI->input->is_ajax_request()) {
            $CI->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status'   => false,
                    'message'  => $message,
                    'redirect' => site_url('Auth')
                )))
                ->_display();
            exit;
        }

        $CI->session->set_flashdata('gagal', $message);
        redirect('Auth');
        exit;
    }
}
