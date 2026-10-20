<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function is_logged_in()
{
    $CI = &get_instance();
    if (!$CI->session->userdata('logged_in')) {
        $CI->session->set_flashdata('gagal', 'Sesi Anda berakhir. Silakan login ulang.');
        redirect('Auth');
    }
}
