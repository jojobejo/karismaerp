<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Hook Access_logger
 * Secara otomatis mencatat informasi perangkat dan aktivitas akses
 * setiap kali halaman aplikasi Karisma ERP dibuka.
 * Kompatibel dengan lingkungan Cloud & Shared Hosting (Hostinger).
 */
class Access_logger
{
    /**
     * Dieksekusi otomatis pada hook 'post_controller_constructor'
     */
    public function log_request()
    {
        // Jangan catat jika dijalankan melalui CLI (cron job / terminal)
        if (is_cli()) {
            return;
        }

        $CI =& get_instance();
        if (!$CI) {
            return;
        }

        // Hindari pencatatan aset statis jika kebetulan ditangani oleh CI
        $uriString = $CI->uri ? $CI->uri->uri_string() : '';
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $uriString)) {
            return;
        }

        // Hindari pencatatan berulang untuk endpoint sinkronisasi lokasi GPS
        if (strpos($uriString, 'admin/access_log/update_location') !== false ||
            strpos($uriString, 'admin/access_log/purge') !== false) {
            return;
        }

        // Siapkan library user_agent
        if (!isset($CI->agent)) {
            $CI->load->library('user_agent');
        }

        // Ambil IP Address (Dukungan Proxy Hostinger / Cloudflare)
        $ip = $this->get_client_ip($CI);

        // Parsing Informasi Perangkat
        $deviceInfo = $this->parse_device($CI);

        // Ambil data user yang sedang aktif (jika sudah login)
        $username = 'Guest';
        $namaUser = 'Guest';
        $idKaryawan = null;

        if (isset($CI->session)) {
            $sessUser = $CI->session->userdata('username');
            if (!empty($sessUser)) {
                $username = (string) $sessUser;
                $namaUser = (string) ($CI->session->userdata('nama') ?: $CI->session->userdata('nama_user') ?: $username);
                $idKaryawan = $CI->session->userdata('id') ?: $CI->session->userdata('id_karyawan');
            }
        }

        // Susun payload log
        $urlFull = current_url();
        if (!empty($_SERVER['QUERY_STRING'])) {
            $urlFull .= '?' . $_SERVER['QUERY_STRING'];
        }

        // Ambil data lokasi geografis perangkat (Kota, Kecamatan, Wilayah, Negara, ISP)
        $loc = [
            'city'            => '-',
            'district'        => '-',
            'region'          => '-',
            'country'         => 'Indonesia',
            'country_code'    => 'ID',
            'isp'             => '-',
            'latitude'        => null,
            'longitude'       => null,
            'accuracy'        => null,
            'location_source' => 'IP'
        ];

        try {
            if (!isset($CI->M_Access_Log)) {
                $CI->load->model('M_Access_Log');
            }
            $loc = $CI->M_Access_Log->resolve_location($ip);
        } catch (Exception $e) {
            // Fallback diam jika model belum siap
        }

        $logData = [
            'ip_address'      => $ip,
            'device_type'     => $deviceInfo['type'],
            'device_brand'    => $deviceInfo['brand'],
            'os'              => $deviceInfo['os'],
            'browser'         => $deviceInfo['browser'],
            'city'            => $loc['city'] ?? '-',
            'district'        => $loc['district'] ?? '-',
            'region'          => $loc['region'] ?? '-',
            'country'         => $loc['country'] ?? 'Indonesia',
            'country_code'    => $loc['country_code'] ?? 'ID',
            'isp'             => $loc['isp'] ?? '-',
            'latitude'        => $loc['latitude'] ?? null,
            'longitude'       => $loc['longitude'] ?? null,
            'accuracy'        => $loc['accuracy'] ?? null,
            'location_source' => $loc['location_source'] ?? 'IP',
            'user_agent'      => (string) $CI->input->user_agent(),
            'url_accessed'    => $urlFull,
            'http_method'     => strtoupper((string) $CI->input->server('REQUEST_METHOD') ?: 'GET'),
            'is_ajax'         => $CI->input->is_ajax_request() ? 1 : 0,
            'username'        => $username,
            'nama_user'       => $namaUser,
            'id_karyawan'     => $idKaryawan ? (int) $idKaryawan : null,
            'referrer'        => (string) ($CI->agent->is_referral() ? $CI->agent->referrer() : '')
        ];

        // Simpan log via model M_Access_Log
        try {
            if (!isset($CI->M_Access_Log)) {
                $CI->load->model('M_Access_Log');
            }
            $CI->M_Access_Log->record($logData);
        } catch (Exception $e) {
            // Fallback diam (fail-safe) agar tidak memutus eksekusi aplikasi di Hostinger
            if (isset($CI->M_Access_Log)) {
                $CI->M_Access_Log->write_file_log($logData, $e->getMessage());
            }
        }
    }

    /**
     * Mendapatkan IP address sebenarnya dengan dukungan Cloudflare / Hostinger proxy
     */
    private function get_client_ip($CI)
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy umum / load balancer
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ipList = explode(',', $_SERVER[$header]);
                $cleanIp = trim($ipList[0]);
                if (filter_var($cleanIp, FILTER_VALIDATE_IP)) {
                    return $cleanIp;
                }
            }
        }

        return $CI->input->ip_address() ?: '127.0.0.1';
    }

    /**
     * Mendeteksi jenis perangkat, merk/model, OS, dan browser
     */
    private function parse_device($CI)
    {
        $ua = (string) $CI->input->user_agent();
        $type = 'Desktop';
        $brand = 'PC Desktop / Laptop';
        $os = $CI->agent->platform() ?: 'Unknown OS';

        // Deteksi Bot
        if ($CI->agent->is_robot()) {
            return [
                'type'    => 'Bot / Crawler',
                'brand'   => $CI->agent->robot() ?: 'Web Crawler',
                'os'      => 'Server / Bot',
                'browser' => $CI->agent->robot() ?: 'Robot'
            ];
        }

        // Deteksi Tablet
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $type = 'Tablet';
            if (stripos($ua, 'ipad') !== false) {
                $brand = 'Apple iPad';
            } elseif (stripos($ua, 'samsung') !== false || stripos($ua, 'SM-T') !== false) {
                $brand = 'Samsung Galaxy Tab';
            } else {
                $brand = 'Android Tablet';
            }
        }
        // Deteksi Mobile / Smartphone
        elseif ($CI->agent->is_mobile() || preg_match('/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Opera M(obi|ini)/i', $ua)) {
            $type = 'Mobile Phone';
            if (stripos($ua, 'iPhone') !== false) {
                $brand = 'Apple iPhone';
            } elseif (preg_match('/(Samsung|SM-[A-Z0-9]+)/i', $ua)) {
                $brand = 'Samsung Mobile';
            } elseif (preg_match('/(Xiaomi|Redmi|POCO|Mi\s)/i', $ua)) {
                $brand = 'Xiaomi / Redmi';
            } elseif (preg_match('/(OPPO|CPH[0-9]+)/i', $ua)) {
                $brand = 'OPPO Mobile';
            } elseif (preg_match('/(Vivo|V[0-9]{4})/i', $ua)) {
                $brand = 'Vivo Mobile';
            } elseif (preg_match('/(Realme|RMX[0-9]+)/i', $ua)) {
                $brand = 'Realme';
            } elseif (preg_match('/(Infinix|X[0-9]{3})/i', $ua)) {
                $brand = 'Infinix';
            } elseif (stripos($ua, 'Android') !== false) {
                $brand = 'Android Smartphone';
            } else {
                $brand = 'Mobile Device';
            }
        }
        // Desktop / Laptop
        else {
            $type = 'Desktop';
            if (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS X') !== false) {
                $brand = 'Apple Mac';
            } elseif (stripos($ua, 'Windows') !== false) {
                $brand = 'Windows PC';
            } elseif (stripos($ua, 'Linux') !== false) {
                $brand = 'Linux PC';
            }
        }

        // Penyempurnaan nama OS
        if (empty($os) || $os === 'Unknown Platform') {
            if (stripos($ua, 'Windows NT 10.0') !== false) $os = 'Windows 10/11';
            elseif (stripos($ua, 'Windows NT 6.3') !== false) $os = 'Windows 8.1';
            elseif (stripos($ua, 'Windows NT 6.1') !== false) $os = 'Windows 7';
            elseif (stripos($ua, 'Android') !== false) $os = 'Android OS';
            elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';
            elseif (stripos($ua, 'Mac OS X') !== false) $os = 'macOS';
            elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';
            else $os = 'Unknown OS';
        }

        // Deteksi Browser
        $browser = 'Unknown Browser';
        if ($CI->agent->is_browser()) {
            $browser = $CI->agent->browser() . ' ' . $CI->agent->version();
        } elseif ($CI->agent->is_mobile()) {
            $browser = $CI->agent->mobile();
        } elseif (stripos($ua, 'Edg/') !== false) {
            $browser = 'Microsoft Edge';
        } elseif (stripos($ua, 'Chrome/') !== false) {
            $browser = 'Chrome Browser';
        } elseif (stripos($ua, 'Safari/') !== false) {
            $browser = 'Safari Browser';
        }

        return [
            'type'    => $type,
            'brand'   => $brand,
            'os'      => $os,
            'browser' => $browser
        ];
    }
}
