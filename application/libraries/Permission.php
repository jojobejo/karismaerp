<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Permission
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('master/M_Akseslevel', 'permissionAksesModel');
    }

    public function can($url, $permission = 'can_view')
    {
        $username = strtolower(trim((string) $this->CI->session->userdata('username')));
        $jobdesk = strtoupper(trim((string) $this->CI->session->userdata('jobdesk')));
        $lv = (int) $this->CI->session->userdata('lv');
        if ($username === 'admin' || $jobdesk === 'ADMIN' || $lv >= 99 || $this->CI->session->userdata('is_admin_dashboard')) {
            return true;
        }
        $aksesLevelId = $this->CI->session->userdata('akses_lv_id') ?: $this->CI->session->userdata('lv');
        return $this->CI->permissionAksesModel->has_permission($aksesLevelId, $url, $permission);
    }

    public function facility($facilityKey, $default = true)
    {
        $this->CI->load->model('master/M_Userfacility', 'permissionFacilityModel');
        $userId = (int) ($this->CI->session->userdata('id_karyawan')
            ?: $this->CI->session->userdata('id')
            ?: $this->CI->session->userdata('id_user'));

        if ($userId <= 0) {
            return (bool) $default;
        }

        return $this->CI->permissionFacilityModel->is_allowed($userId, $facilityKey, $default);
    }

    /**
     * Cek apakah user yang sedang login memiliki izin aksi granular (view, create, edit, delete)
     * pada menu dashboard tertentu berdasarkan menu_key
     */
    public function can_menu($menuKey, $action = 'view')
    {
        $username = strtolower(trim((string) $this->CI->session->userdata('username')));
        $jobdesk = strtoupper(trim((string) $this->CI->session->userdata('jobdesk')));
        $lv = (int) $this->CI->session->userdata('lv');
        if ($username === 'admin' || $jobdesk === 'ADMIN' || $lv >= 99 || $this->CI->session->userdata('is_admin_dashboard')) {
            return true;
        }

        $userId = (int) ($this->CI->session->userdata('id_karyawan')
            ?: $this->CI->session->userdata('id')
            ?: $this->CI->session->userdata('id_user'));

        if ($userId <= 0) {
            return false;
        }

        $this->CI->load->model('master/M_Dashboardaccess', 'permissionDashboardAccessModel');
        return $this->CI->permissionDashboardAccessModel->can_action($userId, $menuKey, $action);
    }

    /**
     * Penjaga (guard) aksi granular per menu: blokir request jika user tidak memiliki izin
     */
    public function guard_menu($menuKey, $action = 'view')
    {
        if (!$this->CI->session->userdata('logged_in')) {
            if ($this->CI->input->is_ajax_request()) {
                $this->CI->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => false,
                        'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                    ]));
                return false;
            }
            redirect('Auth');
            return false;
        }

        if (!$this->can_menu($menuKey, $action)) {
            $actionLabels = ['view' => 'melihat', 'create' => 'menambah', 'edit' => 'mengubah', 'delete' => 'menghapus'];
            $label = $actionLabels[strtolower((string) $action)] ?? 'mengakses';
            $msg = 'Anda tidak memiliki izin untuk ' . $label . ' data pada modul ini.';

            if ($this->CI->input->is_ajax_request()) {
                $this->CI->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => false,
                        'message' => $msg,
                    ]));
                return false;
            }
            show_error($msg, 403, 'Akses Ditolak');
            return false;
        }

        return true;
    }

    public function guard($url, $permission = 'can_view')
    {
        if (!$this->CI->session->userdata('logged_in')) {
            redirect('Auth');
            return false;
        }
        if (!$this->can($url, $permission)) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403);
            return false;
        }
        return true;
    }
}
