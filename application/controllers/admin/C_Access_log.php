<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_Access_log
 * Menampilkan dan mengelola monitoring log perangkat & aktivitas akses pengguna ERP.
 */
class C_Access_log extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Access_Log');
        $this->load->helper(['url', 'form']);

        // Izinkan update_location tanpa redirect auth agar perangkat guest/karyawan di login page tetap bisa sinkronisasi GPS
        $currentMethod = $this->router->fetch_method();
        if ($currentMethod !== 'update_location') {
            if (!$this->session->userdata('logged_in') && !$this->session->userdata('username') && !$this->session->userdata('is_login')) {
                redirect('auth');
            }
        }
    }

    /**
     * Halaman Utama Log Perangkat & Akses
     */
    public function index()
    {
        $filters = [
            'date_from'   => $this->input->get('date_from') ?: '',
            'date_to'     => $this->input->get('date_to') ?: '',
            'device_type' => $this->input->get('device_type') ?: 'SEMUA',
            'search'      => $this->input->get('search') ?: ''
        ];

        // Pagination sederhana
        $page = (int) ($this->input->get('page') ?: 1);
        if ($page < 1) $page = 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $totalRows = $this->M_Access_Log->count_logs($filters);
        $logs = $this->M_Access_Log->get_logs($filters, $limit, $offset);
        $totalPages = ceil($totalRows / $limit);

        $data['page_title']  = 'Log Perangkat & Akses Pengguna - Karisma ERP';
        $data['filters']     = $filters;
        $data['stats']       = $this->M_Access_Log->get_stats();
        $data['logs']        = $logs;
        $data['total_rows']  = $totalRows;
        $data['current_page']= $page;
        $data['total_pages'] = $totalPages;
        $data['limit']       = $limit;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/access_log/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * Endpoint AJAX untuk sinkronisasi lokasi presisi GPS perangkat dari browser
     */
    public function update_location()
    {
        if ($this->input->method() !== 'post') {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid method']));
        }

        $lat = $this->input->post('latitude');
        $lng = $this->input->post('longitude');
        $acc = $this->input->post('accuracy');

        if (!is_numeric($lat) || !is_numeric($lng)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Koordinat tidak valid']));
        }

        $district = trim((string) $this->input->post('district'));
        $city     = trim((string) $this->input->post('city'));
        $region   = trim((string) $this->input->post('region'));
        $country  = trim((string) $this->input->post('country'));

        // Jika client belum melakukan reverse-geocoding, lakukan di server secara cepat
        if (empty($district) || $district === '-') {
            $geo = $this->reverse_geocode_server((float)$lat, (float)$lng);
            if (!empty($geo['district'])) $district = $geo['district'];
            if (!empty($geo['city'])) $city = $geo['city'];
            if (!empty($geo['region'])) $region = $geo['region'];
            if (!empty($geo['country'])) $country = $geo['country'];
        }

        $logId = $this->session->userdata('current_access_log_id');
        $success = $this->M_Access_Log->update_gps_location($logId, [
            'latitude'  => (float) $lat,
            'longitude' => (float) $lng,
            'accuracy'  => is_numeric($acc) ? (int) $acc : null,
            'district'  => $district,
            'city'      => $city,
            'region'    => $region,
            'country'   => $country
        ]);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'   => $success ? 'success' : 'failed',
                'district' => $district,
                'city'     => $city,
                'region'   => $region
            ]));
    }

    /**
     * Helper server reverse-geocoding koordinat GPS ke nama Kecamatan & Kota
     */
    private function reverse_geocode_server($lat, $lng)
    {
        $res = [
            'district' => '',
            'city'     => '',
            'region'   => '',
            'country'  => 'Indonesia'
        ];

        // 1. Coba BigDataCloud Reverse Geocoding Client API
        try {
            $url = "https://api.bigdatacloud.net/data/reverse-geocode-client?latitude={$lat}&longitude={$lng}&localityLanguage=id";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'KarismaERP/1.0');
            $raw = curl_exec($ch);
            curl_close($ch);

            if ($raw) {
                $json = json_decode($raw, true);
                if (!empty($json)) {
                    $res['city']     = $json['city'] ?? $json['locality'] ?? '';
                    $res['region']   = $json['principalSubdivision'] ?? '';
                    $res['country']  = $json['countryName'] ?? 'Indonesia';

                    // Cari nama kecamatan dari localityInfo
                    if (!empty($json['localityInfo']['administrative'])) {
                        foreach ($json['localityInfo']['administrative'] as $admin) {
                            $adminOrder = (int)($admin['adminLevel'] ?? 0);
                            $adminName  = (string)($admin['name'] ?? '');
                            // Admin level 6 atau 7 biasanya adalah Kecamatan di Indonesia
                            if (stripos($adminName, 'Kecamatan') !== false || stripos($adminName, 'Distrik') !== false) {
                                $res['district'] = $adminName;
                                break;
                            }
                        }
                    }

                    if (empty($res['district']) && !empty($json['locality'])) {
                        $res['district'] = 'Kec. ' . $json['locality'];
                    }

                    if (!empty($res['district'])) {
                        return $res;
                    }
                }
            }
        } catch (Exception $e) {
            // Lanjut ke fallback
        }

        // 2. Fallback Nominatim OpenStreetMap
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'KarismaERP-App/1.0');
            $raw = curl_exec($ch);
            curl_close($ch);

            if ($raw) {
                $json = json_decode($raw, true);
                $addr = $json['address'] ?? [];
                $res['district'] = $addr['suburb'] ?? $addr['municipality'] ?? $addr['city_district'] ?? $addr['town'] ?? $addr['village'] ?? '';
                if ($res['district'] && stripos($res['district'], 'Kecamatan') === false && stripos($res['district'], 'Kec.') === false) {
                    $res['district'] = 'Kec. ' . $res['district'];
                }
                $res['city']    = $addr['city'] ?? $addr['county'] ?? $addr['state_district'] ?? '';
                $res['region']  = $addr['state'] ?? '';
                $res['country'] = $addr['country'] ?? 'Indonesia';
            }
        } catch (Exception $e) {
            // Senyap
        }

        return $res;
    }

    /**
     * Bersihkan log lama (lebih dari 30 atau 60 hari)
     */
    public function purge()
    {
        // Hanya user admin yang berhak membersihkan log
        $username = strtolower((string) $this->session->userdata('username'));
        if ($username !== 'admin') {
            $this->session->set_flashdata('error', 'Hanya administrator yang berhak membersihkan data log.');
            redirect('admin/access_log');
            return;
        }

        $days = (int) ($this->input->post('days') ?: 30);
        if ($days < 7) $days = 7;

        $deletedCount = $this->M_Access_Log->purge_old_logs($days);
        $this->session->set_flashdata('success', "Berhasil menghapus {$deletedCount} baris log lama yang lebih dari {$days} hari.");
        redirect('admin/access_log');
    }
}
