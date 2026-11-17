<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Hook Dashboard_access_guard
 * Memastikan menu dashboard yang dikunci (ikon gembok) untuk suatu user
 * juga terblokir secara langsung di sisi server apabila URL diakses manual.
 */
class Dashboard_access_guard
{
    public function enforce()
    {
        if (is_cli()) {
            return;
        }

        $CI =& get_instance();
        if (!$CI || !isset($CI->session) || !isset($CI->uri) || !isset($CI->router)) {
            return;
        }

        // Lewati jika belum login
        if (!$CI->session->userdata('logged_in')) {
            return;
        }

        if (!isset($CI->M_Dashboard)) {
            $CI->load->model('M_Dashboard');
        }

        $context = $CI->M_Dashboard->current_user_context();
        // Administrator selalu bebas mengakses seluruh modul
        if ($CI->M_Dashboard->is_strict_admin($context)) {
            return;
        }

        $userId = (int)($context['id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        $rules = $CI->M_Dashboard->get_user_dashboard_rules($userId);
        if (empty($rules)) {
            return;
        }

        $lockedKeys = array();
        foreach ($rules as $menuKey => $allowed) {
            if ((int)$allowed === 0) {
                $lockedKeys[$menuKey] = true;
            }
        }

        if (empty($lockedKeys)) {
            return;
        }

        $uriString = trim(strtolower((string)$CI->uri->uri_string()), '/');
        $safePrefixes = array(
            'auth',
            'logout',
            'dashboard',
            'dashboad',
            'dasboard',
            'portal',
            'welcome',
            'admin/access_log/update_location',
            'master/menu/sidebar',
        );

        if ($uriString === '') {
            return;
        }
        foreach ($safePrefixes as $safe) {
            if ($uriString === $safe || strpos($uriString, $safe . '/') === 0) {
                return;
            }
        }

        $controllerMethod = strtolower(trim(
            (string)$CI->router->fetch_directory() .
            (string)$CI->router->fetch_class() . '/' .
            (string)$CI->router->fetch_method(),
            '/'
        ));

        $match = $CI->M_Dashboard->resolve_menu_by_request($uriString, $controllerMethod);
        if (!$match || empty($lockedKeys[$match['menu_key']])) {
            return;
        }

        $menuTitle = (string)($match['title'] ?? 'Modul Ini');
        $acceptHeader = strtolower((string)$CI->input->get_request_header('Accept', true));
        $isAjax = $CI->input->is_ajax_request() || strpos($acceptHeader, 'application/json') !== false;

        if ($isAjax) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            $CI->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => false,
                    'locked' => true,
                    'message' => 'Akses ditolak. Menu "' . $menuTitle . '" terkunci untuk akun Anda.',
                )))
                ->_display();
            exit;
        }

        $this->render_forbidden_page($menuTitle);
        exit;
    }

    private function render_forbidden_page($menuTitle)
    {
        $dashboardUrl = function_exists('base_url') ? base_url('dashboard') : '/dashboard';
        $safeTitle = htmlspecialchars($menuTitle, ENT_QUOTES, 'UTF-8');

        http_response_code(403);
        echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Terkunci - KARISMA ERP</title>
    <style>
        body { margin: 0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f1f5f9; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .lock-card { max-width: 480px; width: 100%; background: #fff; border-radius: 16px; padding: 36px 32px; text-align: center; box-shadow: 0 20px 45px rgba(15, 23, 42, .12); border: 1px solid #e2e8f0; }
        .lock-icon { width: 82px; height: 82px; margin: 0 auto 20px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 36px; }
        h1 { margin: 0 0 10px; font-size: 24px; color: #0f172a; }
        p { margin: 0 0 24px; color: #475569; line-height: 1.6; font-size: 15px; }
        .btn-back { display: inline-block; background: #127fad; color: #fff; text-decoration: none; font-weight: 700; padding: 12px 24px; border-radius: 10px; transition: background .2s ease; }
        .btn-back:hover { background: #0d668c; }
    </style>
</head>
<body>
    <div class="lock-card">
        <div class="lock-icon">&#128274;</div>
        <h1>Akses Modul Terkunci</h1>
        <p>Maaf, akun Anda tidak memiliki izin untuk membuka menu <strong>' . $safeTitle . '</strong>. Silakan hubungi Administrator jika Anda membutuhkan akses ke halaman ini.</p>
        <a href="' . htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') . '" class="btn-back">Kembali ke Dashboard</a>
    </div>
</body>
</html>';
    }
}

